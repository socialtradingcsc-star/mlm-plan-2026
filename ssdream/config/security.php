<?php
/**
 * Social Service Dream - Security Foundation
 * CSRF Protection, Input Sanitization, Output Escaping, Rate Limiting, File Validation, and Authentication Guards
 */

require_once __DIR__ . '/config.php';

// Set standard HTTP security headers
if (!headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
}

class Security {
    /**
     * Generate or retrieve CSRF token
     * 
     * @return string
     */
    public static function getCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Render hidden CSRF token input field
     * 
     * @return string
     */
    public static function csrfField(): string {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify submitted CSRF token
     * 
     * @param string|null $token
     * @return bool
     */
    public static function verifyCsrfToken(?string $token = null): bool {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? '';
        }

        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Sanitize string input to prevent XSS
     * 
     * @param string|null $data
     * @return string
     */
    public static function sanitize(?string $data): string {
        if ($data === null) {
            return '';
        }
        $data = trim($data);
        $data = stripslashes($data);
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize alphanumeric usernames and codes
     * 
     * @param string $data
     * @return string
     */
    public static function sanitizeUsername(string $data): string {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', trim($data));
    }

    /**
     * Escape output for HTML context
     * 
     * @param mixed $data
     * @return string
     */
    public static function escape($data): string {
        if ($data === null) {
            return '';
        }
        return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Hash user password securely using BCRYPT
     * 
     * @param string $password
     * @return string
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password hash against plain text password
     * 
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /**
     * Regenerate session ID after authentication changes (Fixation protection)
     */
    public static function regenerateSession(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Validate uploaded file (Extension, MIME type, Size, Executable blocking)
     * 
     * @param array $file $_FILES['input_name']
     * @param array $allowedExtensions Default: ['jpg', 'jpeg', 'png', 'pdf']
     * @param int $maxSizeBytes Default: 5MB
     * @return array ['valid' => bool, 'message' => string, 'extension' => string]
     */
    public static function validateUploadedFile(array $file, array $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'], int $maxSizeBytes = 5242880): array {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['valid' => false, 'message' => 'Invalid upload parameter structure.'];
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['valid' => false, 'message' => 'No file uploaded.'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['valid' => false, 'message' => 'Exceeded maximum allowable upload size limit.'];
            default:
                return ['valid' => false, 'message' => 'Unknown file upload error.'];
        }

        if ($file['size'] > $maxSizeBytes) {
            return ['valid' => false, 'message' => 'File size exceeds maximum permitted limit (' . round($maxSizeBytes / 1048576) . ' MB).'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions, true)) {
            return ['valid' => false, 'message' => 'Unsupported file extension. Permitted types: ' . implode(', ', $allowedExtensions)];
        }

        // Check for dangerous double extensions or script extensions
        $dangerousExts = ['php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'phar', 'js', 'sh', 'exe', 'cgi', 'pl', 'py', 'asp', 'aspx', 'jsp'];
        if (in_array($ext, $dangerousExts, true)) {
            return ['valid' => false, 'message' => 'Executable script uploads are strictly prohibited.'];
        }

        // Verify MIME type using finfo
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = [
                'jpg'  => ['image/jpeg', 'image/pjpeg'],
                'jpeg' => ['image/jpeg', 'image/pjpeg'],
                'png'  => ['image/png', 'image/x-png'],
                'pdf'  => ['application/pdf', 'application/x-pdf']
            ];

            $expectedMimes = $allowedMimes[$ext] ?? [];
            if (!empty($expectedMimes) && !in_array($mime, $expectedMimes, true)) {
                return ['valid' => false, 'message' => 'File MIME type mismatch. Upload rejected for security.'];
            }
        }

        return [
            'valid'     => true,
            'message'   => 'File is valid.',
            'extension' => $ext
        ];
    }

    /**
     * Guard: Ensure user is authenticated, otherwise redirect to login
     */
    public static function requireUserAuth(): void {
        if (!isUserLoggedIn()) {
            $_SESSION['flash_error'] = "Please log in to access your dashboard.";
            header("Location: " . BASE_URL . "/login.php");
            exit;
        }
    }

    /**
     * Guard: Ensure admin is authenticated, otherwise redirect to admin login
     */
    public static function requireAdminAuth(): void {
        if (!isAdminLoggedIn()) {
            $_SESSION['flash_admin_error'] = "Admin access required. Please sign in.";
            header("Location: " . BASE_URL . "/admin/login.php");
            exit;
        }
    }
}

// =======================================================
// REUSABLE AUTHENTICATION & SECURITY PROCEDURAL FUNCTIONS
// =======================================================

function isUserLoggedIn(): bool {
    return !empty($_SESSION['user_logged_in']) && !empty($_SESSION['user_id']);
}

function requireUserLogin(): void {
    Security::requireUserAuth();
}

function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_id']);
}

function requireAdminLogin(): void {
    Security::requireAdminAuth();
}

function csrfToken(): string {
    return Security::getCsrfToken();
}

function csrfField(): string {
    return Security::csrfField();
}

function verifyCsrfToken(?string $token = null): bool {
    return Security::verifyCsrfToken($token);
}

function e($value): string {
    return Security::escape($value);
}

function sanitize(?string $value): string {
    return Security::sanitize($value);
}

/**
 * IP Rate Limiting Check
 * 
 * @param string $actionKey Unique action identifier (e.g. 'login', 'register', 'withdrawal')
 * @param int $maxAttempts
 * @param int $lockoutMinutes
 * @return bool True if permitted, False if locked out
 */
function checkRateLimit(string $actionKey, int $maxAttempts = 5, int $lockoutMinutes = 15): bool {
    $db = Database::getConnection();
    if (!$db) return true; // Fallback if DB disconnected

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    try {
        $stmt = $db->prepare("SELECT attempts, last_attempt FROM rate_limits WHERE ip_address = :ip AND action_key = :act LIMIT 1");
        $stmt->execute(['ip' => $ip, 'act' => $actionKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return true;
        }

        $lastTime = strtotime($row['last_attempt']);
        $timeDiffMinutes = (time() - $lastTime) / 60;

        // If lockout window expired, reset rate limit row
        if ($timeDiffMinutes >= $lockoutMinutes) {
            $del = $db->prepare("DELETE FROM rate_limits WHERE ip_address = :ip AND action_key = :act");
            $del->execute(['ip' => $ip, 'act' => $actionKey]);
            return true;
        }

        return (int)$row['attempts'] < $maxAttempts;
    } catch (PDOException $e) {
        return true;
    }
}

/**
 * Record a failed attempt for Rate Limiting
 * 
 * @param string $actionKey
 */
function recordFailedAttempt(string $actionKey): void {
    $db = Database::getConnection();
    if (!$db) return;

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    try {
        $stmt = $db->prepare("
            INSERT INTO rate_limits (ip_address, action_key, attempts, last_attempt)
            VALUES (:ip, :act, 1, NOW())
            ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()
        ");
        $stmt->execute(['ip' => $ip, 'act' => $actionKey]);
    } catch (PDOException $e) {
        // Fallback
    }
}

/**
 * Clear rate limit attempts on successful operation
 * 
 * @param string $actionKey
 */
function clearRateLimit(string $actionKey): void {
    $db = Database::getConnection();
    if (!$db) return;

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    try {
        $stmt = $db->prepare("DELETE FROM rate_limits WHERE ip_address = :ip AND action_key = :act");
        $stmt->execute(['ip' => $ip, 'act' => $actionKey]);
    } catch (PDOException $e) {
        // Fallback
    }
}

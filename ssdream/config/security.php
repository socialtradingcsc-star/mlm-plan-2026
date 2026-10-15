<?php
/**
 * Social Service Dream - Security Foundation
 * CSRF Protection, Input Sanitization, Output Escaping, and Authentication Guards
 */

require_once __DIR__ . '/config.php';

// Set standard HTTP security headers
if (!headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
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

/**
 * Check if a regular user is logged in
 * 
 * @return bool
 */
function isUserLoggedIn(): bool {
    return !empty($_SESSION['user_logged_in']) && !empty($_SESSION['user_id']);
}

/**
 * Enforce user login on protected pages
 */
function requireUserLogin(): void {
    if (!isUserLoggedIn()) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash']['warning'] = "Please log in to access this page.";
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
}

/**
 * Check if an administrator is logged in
 * 
 * @return bool
 */
function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_id']);
}

/**
 * Enforce admin login on admin pages
 */
function requireAdminLogin(): void {
    if (!isAdminLoggedIn()) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash']['danger'] = "Admin credentials required. Please sign in.";
        header("Location: " . BASE_URL . "/admin/login.php");
        exit;
    }
}

/**
 * Generate CSRF token string
 * 
 * @return string
 */
function csrfToken(): string {
    return Security::getCsrfToken();
}

/**
 * Render hidden CSRF form input field
 * 
 * @return string
 */
function csrfField(): string {
    return Security::csrfField();
}

/**
 * Verify submitted CSRF token
 * 
 * @param string|null $token
 * @return bool
 */
function verifyCsrfToken(?string $token = null): bool {
    return Security::verifyCsrfToken($token);
}

/**
 * Global helper for HTML escaping
 * 
 * @param mixed $value
 * @return string
 */
function e($value): string {
    return Security::escape($value);
}

/**
 * Global helper for input sanitization
 * 
 * @param string|null $value
 * @return string
 */
function sanitize(?string $value): string {
    return Security::sanitize($value);
}

<?php
/**
 * Social Service Dream - Common Application Functions
 * Technology: PHP 8+, PDO, MySQL
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once __DIR__ . '/income-engine.php';

/**
 * Get active PDO connection safely
 * 
 * @return PDO|null
 */
function getDb(): ?PDO {
    return Database::getConnection();
}

/**
 * Format amount as Indian Currency (e.g., ₹1,200.00 or ₹2,00,000)
 * 
 * @param float|int|string $amount
 * @param bool $decimals
 * @return string
 */
function formatCurrency($amount, bool $decimals = false): string {
    $num = (float)$amount;
    if ($decimals) {
        return CURRENCY_SYMBOL . number_format($num, 2);
    }
    return CURRENCY_SYMBOL . number_format($num, 0);
}

/**
 * Set flash message in session
 * 
 * @param string $type ('success', 'danger', 'warning', 'info')
 * @param string $message
 */
function setFlashMessage(string $type, string $message): void {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][$type] = $message;
}

/**
 * Retrieve and clear flash message
 * 
 * @param string $type
 * @return string|null
 */
function getFlashMessage(string $type): ?string {
    if (isset($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}

/**
 * Render all queued flash messages as Bootstrap alerts
 * 
 * @return string
 */
function renderFlashMessages(): string {
    if (empty($_SESSION['flash'])) {
        return '';
    }

    $html = '';
    foreach ($_SESSION['flash'] as $type => $message) {
        $alertClass = match($type) {
            'error', 'danger' => 'danger',
            'success' => 'success',
            'warning' => 'warning',
            default => 'info'
        };
        $html .= '<div class="alert alert-' . $alertClass . ' alert-dismissible fade show shadow-sm" role="alert">';
        $html .= e($message);
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        $html .= '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

/**
 * Check if a username is already taken
 * 
 * @param string $username
 * @return bool
 */
function isUsernameTaken(string $username): bool {
    $db = getDb();
    if (!$db) return false;
    $stmt = $db->prepare("SELECT id FROM users WHERE username = :u LIMIT 1");
    $stmt->execute(['u' => $username]);
    return (bool)$stmt->fetch();
}

/**
 * Check if an email is already taken
 * 
 * @param string $email
 * @return bool
 */
function isEmailTaken(string $email): bool {
    $db = getDb();
    if (!$db) return false;
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :e LIMIT 1");
    $stmt->execute(['e' => $email]);
    return (bool)$stmt->fetch();
}

/**
 * Check if a mobile number is already taken
 * 
 * @param string $mobile
 * @return bool
 */
function isMobileTaken(string $mobile): bool {
    $db = getDb();
    if (!$db) return false;
    $stmt = $db->prepare("SELECT id FROM users WHERE mobile = :m LIMIT 1");
    $stmt->execute(['m' => $mobile]);
    return (bool)$stmt->fetch();
}

/**
 * Get user by sponsor username/ID
 * 
 * @param string $sponsorUsername
 * @return array|null
 */
function getSponsorByUsername(string $sponsorUsername): ?array {
    $db = getDb();
    if (!$db) return null;
    $stmt = $db->prepare("SELECT id, username, full_name, status, current_level FROM users WHERE username = :u LIMIT 1");
    $stmt->execute(['u' => $sponsorUsername]);
    return $stmt->fetch() ?: null;
}

/**
 * Fetch full user record by ID
 * 
 * @param int $userId
 * @return array|null
 */
function getUserById(int $userId): ?array {
    $db = getDb();
    if (!$db) return null;
    try {
        $stmt = $db->prepare("
            SELECT u.*, p.address, p.city, p.state, p.pincode, p.bank_name, p.account_number, 
                   p.ifsc_code, p.account_holder_name, p.upi_id, p.pan_number, p.aadhar_number, p.avatar
            FROM users u
            LEFT JOIN user_profiles p ON u.id = p.user_id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Fetch user profile record
 * 
 * @param int $userId
 * @return array
 */
function getUserProfile(int $userId): array {
    $db = getDb();
    $defaults = [
        'address' => '', 'city' => '', 'state' => '', 'pincode' => '',
        'bank_name' => '', 'account_number' => '', 'ifsc_code' => '',
        'account_holder_name' => '', 'upi_id' => '', 'pan_number' => '',
        'aadhar_number' => '', 'avatar' => 'default-avatar.png'
    ];
    if (!$db) return $defaults;

    try {
        $stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch();
        if ($row) {
            return array_merge($defaults, $row);
        }
        $initStmt = $db->prepare("INSERT IGNORE INTO user_profiles (user_id) VALUES (:uid)");
        $initStmt->execute(['uid' => $userId]);
        return $defaults;
    } catch (PDOException $e) {
        return $defaults;
    }
}

/**
 * Update user profile details
 * 
 * @param int $userId
 * @param array $data
 * @return bool
 */
function updateUserProfile(int $userId, array $data): bool {
    $db = getDb();
    if (!$db) return false;

    try {
        $check = $db->prepare("SELECT id FROM user_profiles WHERE user_id = :uid LIMIT 1");
        $check->execute(['uid' => $userId]);
        if (!$check->fetch()) {
            $insert = $db->prepare("INSERT INTO user_profiles (user_id) VALUES (:uid)");
            $insert->execute(['uid' => $userId]);
        }

        $stmt = $db->prepare("
            UPDATE user_profiles SET
                address = :address,
                city = :city,
                state = :state,
                pincode = :pincode,
                bank_name = :bank_name,
                account_number = :account_number,
                ifsc_code = :ifsc_code,
                account_holder_name = :account_holder_name,
                upi_id = :upi_id,
                pan_number = :pan_number,
                aadhar_number = :aadhar_number,
                updated_at = NOW()
            WHERE user_id = :user_id
        ");

        return $stmt->execute([
            'address'             => $data['address'] ?? null,
            'city'                => $data['city'] ?? null,
            'state'               => $data['state'] ?? null,
            'pincode'             => $data['pincode'] ?? null,
            'bank_name'           => $data['bank_name'] ?? null,
            'account_number'      => $data['account_number'] ?? null,
            'ifsc_code'           => $data['ifsc_code'] ?? null,
            'account_holder_name' => $data['account_holder_name'] ?? null,
            'upi_id'              => $data['upi_id'] ?? null,
            'pan_number'          => $data['pan_number'] ?? null,
            'aadhar_number'       => $data['aadhar_number'] ?? null,
            'user_id'             => $userId
        ]);
    } catch (PDOException $e) {
        error_log("Profile Update Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetch KYC status and document for user
 * 
 * @param int $userId
 * @return array|null
 */
function getUserKyc(int $userId): ?array {
    $db = getDb();
    if (!$db) return null;

    try {
        $stmt = $db->prepare("SELECT * FROM kyc WHERE user_id = :uid ORDER BY id DESC LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Submit or update KYC document record
 * 
 * @param int $userId
 * @param string $docType
 * @param string $docNumber
 * @param string $filePath
 * @return bool
 */
function submitUserKyc(int $userId, string $docType, string $docNumber, string $filePath): bool {
    $db = getDb();
    if (!$db) return false;

    try {
        $existing = getUserKyc($userId);
        if ($existing) {
            $stmt = $db->prepare("
                UPDATE kyc 
                SET pan_card_path = :path,
                    status = 'pending',
                    admin_notes = NULL,
                    updated_at = NOW()
                WHERE user_id = :uid
            ");
            return $stmt->execute(['path' => $filePath, 'uid' => $userId]);
        } else {
            $stmt = $db->prepare("
                INSERT INTO kyc (user_id, pan_card_path, status, submitted_at)
                VALUES (:uid, :path, 'pending', NOW())
            ");
            return $stmt->execute(['uid' => $userId, 'path' => $filePath]);
        }
    } catch (PDOException $e) {
        error_log("KYC Submit Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Initialize or fetch user wallet safely
 * 
 * @param int $userId
 * @param PDO|null $pdo
 * @return array
 */
function initUserWalletIfNotExists(int $userId, ?PDO $pdo = null): array {
    $db = $pdo ?: getDb();
    $defaults = [
        'id'                       => 0,
        'user_id'                  => $userId,
        'balance'                  => 0.00,
        'total_credit'             => 0.00,
        'total_debit'              => 0.00,
        'total_income'             => 0.00,
        'direct_income'            => 0.00,
        'level_income'             => 0.00,
        'sponsor_income'           => 0.00,
        'total_withdrawn'          => 0.00,
        'pending_withdrawal'       => 0.00,
        'available_for_withdrawal' => 0.00
    ];

    if (!$db) return $defaults;

    try {
        $stmt = $db->prepare("SELECT * FROM wallets WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $ins = $db->prepare("
                INSERT INTO wallets (user_id, balance, total_income, direct_income, level_income, sponsor_income, total_withdrawn, created_at, updated_at)
                VALUES (:uid, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NOW(), NOW())
            ");
            $ins->execute(['uid' => $userId]);
            $stmt->execute(['uid' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($row) {
            $balance = (float)$row['balance'];
            $totalIncome = (float)($row['total_income'] ?? 0.00);
            $totalWithdrawn = (float)($row['total_withdrawn'] ?? 0.00);
            $totalCredit = isset($row['total_credit']) ? (float)$row['total_credit'] : $totalIncome;
            $totalDebit = isset($row['total_debit']) ? (float)$row['total_debit'] : $totalWithdrawn;

            // Fetch pending withdrawals sum
            $pStmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE user_id = :uid AND status = 'pending'");
            $pStmt->execute(['uid' => $userId]);
            $pendingWithdrawal = (float)$pStmt->fetchColumn();

            return [
                'id'                       => (int)$row['id'],
                'user_id'                  => (int)$row['user_id'],
                'balance'                  => $balance,
                'total_credit'             => $totalCredit,
                'total_debit'              => $totalDebit,
                'total_income'             => $totalIncome,
                'direct_income'            => (float)($row['direct_income'] ?? 0.00),
                'level_income'             => (float)($row['level_income'] ?? 0.00),
                'sponsor_income'           => (float)($row['sponsor_income'] ?? 0.00),
                'total_withdrawn'          => $totalWithdrawn,
                'pending_withdrawal'       => $pendingWithdrawal,
                'available_for_withdrawal' => max(0.00, $balance)
            ];
        }
    } catch (PDOException $e) {
        error_log("Wallet Fetch Error: " . $e->getMessage());
    }

    return $defaults;
}

/**
 * Fetch wallet summary for a user
 * 
 * @param int $userId
 * @return array
 */
function getUserWallet(int $userId): array {
    return initUserWalletIfNotExists($userId);
}

/**
 * Fetch all transactions for user with balance before and after
 * 
 * @param int $userId
 * @param int $limit
 * @return array
 */
function getUserTransactions(int $userId, int $limit = 50): array {
    $db = getDb();
    if (!$db) return [];

    try {
        $stmt = $db->prepare("
            SELECT * FROM transactions 
            WHERE user_id = :uid 
            ORDER BY id DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Count unread notifications for user
 * 
 * @param int $userId
 * @return int
 */
function getUnreadNotificationCount(int $userId): int {
    $db = getDb();
    if (!$db) return 0;
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE (user_id = :uid OR user_id IS NULL) AND is_read = 0");
        $stmt->execute(['uid' => $userId]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Fetch all 7 official packages from database (with fallback to config constants)
 * 
 * @return array
 */
/**
 * Fetch all 7 official packages from database (with fallback to config constants)
 * 
 * @return array
 */
function getPackages(): array {
    $db = getDb();
    if ($db) {
        try {
            $stmt = $db->query("SELECT * FROM packages ORDER BY level_number ASC");
            $rows = $stmt->fetchAll();
            if (!empty($rows)) {
                // Ensure level_no and amount keys are always present
                foreach ($rows as &$r) {
                    if (!isset($r['level_no'])) $r['level_no'] = $r['level_number'];
                    if (!isset($r['amount'])) $r['amount'] = $r['price'];
                }
                return $rows;
            }
        } catch (PDOException $e) {
            // Fallback to constants
        }
    }

    $packages = [];
    foreach (PLAN_PACKAGES as $lvl => $info) {
        $packages[] = [
            'id'                     => $lvl,
            'level_number'           => $lvl,
            'level_no'               => $lvl,
            'package_name'           => $info['name'],
            'price'                  => $info['price'],
            'amount'                 => $info['price'],
            'team_size'              => $info['team_size'],
            'direct_income_percent'  => ($lvl === 1 ? 100.00 : 0.00),
            'level_income_percent'   => ($lvl >= 2 ? 50.00 : 0.00),
            'sponsor_income_percent' => ($lvl >= 2 ? 50.00 : 0.00),
            'status'                 => 'active'
        ];
    }
    return $packages;
}

/**
 * Alias for getPackages()
 * 
 * @return array
 */
function getAllPackages(): array {
    return getPackages();
}

/**
 * Fetch package information by level number (1 to 7) strictly from database/constants
 * 
 * @param int $level
 * @return array|null
 */
function getPackageByLevel(int $level): ?array {
    if ($level < 1 || $level > 7) {
        return null;
    }

    $db = getDb();
    if ($db) {
        try {
            $stmt = $db->prepare("SELECT * FROM packages WHERE level_number = :lvl OR level_no = :lvl2 LIMIT 1");
            $stmt->execute(['lvl' => $level, 'lvl2' => $level]);
            $row = $stmt->fetch();
            if ($row) {
                if (!isset($row['level_no'])) $row['level_no'] = $row['level_number'];
                if (!isset($row['amount'])) $row['amount'] = $row['price'];
                return $row;
            }
        } catch (PDOException $e) {
            // Fallback to constants
        }
    }

    if (isset(PLAN_PACKAGES[$level])) {
        $p = PLAN_PACKAGES[$level];
        return [
            'id'           => $level,
            'level_number' => $level,
            'level_no'     => $level,
            'package_name' => $p['name'],
            'price'        => (float)$p['price'],
            'amount'       => (float)$p['price'],
            'team_size'    => (int)$p['team_size'],
            'status'       => 'active'
        ];
    }

    return null;
}

/**
 * Get user's current level number
 * 
 * @param int $userId
 * @return int
 */
function getUserCurrentLevel(int $userId): int {
    $db = getDb();
    if (!$db) return 1;

    try {
        $stmt = $db->prepare("SELECT current_level FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        $val = $stmt->fetchColumn();
        return $val ? (int)$val : 1;
    } catch (PDOException $e) {
        return 1;
    }
}

/**
 * Check if user currently has any pending upgrade request
 * 
 * @param int $userId
 * @return array|null
 */
function getUserPendingUpgrade(int $userId): ?array {
    $db = getDb();
    if (!$db) return null;

    try {
        $stmt = $db->prepare("
            SELECT u.*, p.package_name 
            FROM upgrades u
            LEFT JOIN packages p ON u.package_id = p.id
            WHERE u.user_id = :uid AND u.status = 'pending'
            ORDER BY u.id DESC
            LIMIT 1
        ");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Retrieve upgrade history for a user
 * 
 * @param int $userId
 * @return array
 */
function getUserUpgradeHistory(int $userId): array {
    $db = getDb();
    if (!$db) return [];

    try {
        $stmt = $db->prepare("
            SELECT u.*, p.package_name, p.team_size 
            FROM upgrades u
            LEFT JOIN packages p ON u.to_level = p.level_number
            WHERE u.user_id = :uid
            ORDER BY u.id DESC
        ");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll() ?: [];
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Retrieve paginated upgrade history for a user with optional status filter
 * 
 * @param int $userId
 * @param int $page
 * @param int $perPage
 * @param string $statusFilter ('all', 'pending', 'approved', 'rejected')
 * @return array
 */
function getUserPaginatedUpgradeHistory(int $userId, int $page = 1, int $perPage = 10, string $statusFilter = 'all'): array {
    $db = getDb();
    if (!$db) {
        return [
            'records'      => [],
            'total'        => 0,
            'pages'        => 1,
            'current_page' => 1,
            'per_page'     => $perPage,
            'counts'       => ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0]
        ];
    }

    try {
        // Fetch counts for all statuses
        $countsStmt = $db->prepare("
            SELECT 
                COUNT(*) as all_count,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
            FROM upgrades 
            WHERE user_id = :uid
        ");
        $countsStmt->execute(['uid' => $userId]);
        $countsRow = $countsStmt->fetch() ?: [];

        $counts = [
            'all'      => (int)($countsRow['all_count'] ?? 0),
            'pending'  => (int)($countsRow['pending_count'] ?? 0),
            'approved' => (int)($countsRow['approved_count'] ?? 0),
            'rejected' => (int)($countsRow['rejected_count'] ?? 0),
        ];

        // Determine total for current filter
        $validStatuses = ['pending', 'approved', 'rejected'];
        $hasFilter = in_array(strtolower($statusFilter), $validStatuses, true);
        $total = $hasFilter ? ($counts[strtolower($statusFilter)] ?? 0) : $counts['all'];

        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $totalPages = max(1, (int)ceil($total / $perPage));
        if ($page > $totalPages && $total > 0) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT u.*, p.package_name, p.team_size 
            FROM upgrades u
            LEFT JOIN packages p ON u.to_level = p.level_number
            WHERE u.user_id = :uid
        ";
        $params = ['uid' => $userId];

        if ($hasFilter) {
            $sql .= " AND u.status = :status";
            $params['status'] = strtolower($statusFilter);
        }

        $sql .= " ORDER BY u.id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $records = $stmt->fetchAll() ?: [];

        return [
            'records'      => $records,
            'total'        => $total,
            'pages'        => $totalPages,
            'current_page' => $page,
            'per_page'     => $perPage,
            'counts'       => $counts
        ];
    } catch (PDOException $e) {
        return [
            'records'      => [],
            'total'        => 0,
            'pages'        => 1,
            'current_page' => 1,
            'per_page'     => $perPage,
            'counts'       => ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0]
        ];
    }
}

/**
 * Process and record an upgrade request with strict server-side validation
 * 
 * @param int $userId
 * @param int $requestedLevel
 * @param string $paymentMode
 * @param string|null $transactionRef
 * @return array ['success' => bool, 'message' => string]
 */
function createUpgradeRequest(int $userId, int $requestedLevel, string $paymentMode = 'Wallet / Direct Payment', ?string $transactionRef = null): array {
    $db = getDb();
    if (!$db) {
        return ['success' => false, 'message' => 'Database connection failed. Please try again later.'];
    }

    $currentLevel = getUserCurrentLevel($userId);

    // Rule 1: Cannot upgrade beyond Level 7 or below Level 2
    if ($requestedLevel > 7 || $requestedLevel < 2) {
        return ['success' => false, 'message' => 'Invalid level requested. Upgrades are available from Level 2 to Level 7.'];
    }

    // Rule 2: Cannot request a level <= current level
    if ($requestedLevel <= $currentLevel) {
        return ['success' => false, 'message' => "You are already at Level {$currentLevel}. You cannot upgrade to an equal or lower level."];
    }

    // Rule 3: No skipping levels (Strict progression: Current + 1)
    $nextEligibleLevel = $currentLevel + 1;
    if ($requestedLevel !== $nextEligibleLevel) {
        return ['success' => false, 'message' => "Level skipping is not allowed. Your next eligible package is Level {$nextEligibleLevel}."];
    }

    // Rule 4: Check if duplicate pending upgrade already exists
    $pending = getUserPendingUpgrade($userId);
    if ($pending) {
        return ['success' => false, 'message' => "You already have a pending upgrade request for Level {$pending['to_level']}. Please await administrator review."];
    }

    // Rule 5: Retrieve official package amount strictly from database (never trust client)
    $pkg = getPackageByLevel($requestedLevel);
    if (!$pkg) {
        return ['success' => false, 'message' => 'Requested package definition not found.'];
    }

    $officialAmount = (float)($pkg['amount'] ?? $pkg['price']);
    $packageId = (int)$pkg['id'];

    try {
        $stmt = $db->prepare("
            INSERT INTO upgrades (user_id, from_level, to_level, package_id, amount, payment_mode, transaction_ref, status, created_at)
            VALUES (:uid, :from_lvl, :to_lvl, :pkg_id, :amt, :mode, :ref, 'pending', NOW())
        ");
        $stmt->execute([
            'uid'      => $userId,
            'from_lvl' => $currentLevel,
            'to_lvl'   => $requestedLevel,
            'pkg_id'   => $packageId,
            'amt'      => $officialAmount,
            'mode'     => $paymentMode,
            'ref'      => $transactionRef
        ]);

        return [
            'success' => true,
            'message' => "Upgrade request for Level {$requestedLevel} (" . formatCurrency($officialAmount) . ") has been submitted successfully with status Pending."
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to save upgrade request: ' . $e->getMessage()];
    }
}

/**
 * Get active member distribution across the 7 levels
 * 
 * @return array
 */
function getLevelDistributionStats(): array {
    $distribution = [];
    for ($i = 1; $i <= 7; $i++) {
        $distribution[$i] = [
            'level'      => $i,
            'name'       => PLAN_PACKAGES[$i]['name'],
            'price'      => PLAN_PACKAGES[$i]['price'],
            'team_size'  => PLAN_PACKAGES[$i]['team_size'],
            'user_count' => 0
        ];
    }

    $db = getDb();
    if (!$db) return $distribution;

    try {
        $stmt = $db->query("SELECT current_level, COUNT(*) as cnt FROM users GROUP BY current_level");
        while ($row = $stmt->fetch()) {
            $lvl = (int)$row['current_level'];
            if (isset($distribution[$lvl])) {
                $distribution[$lvl]['user_count'] = (int)$row['cnt'];
            }
        }
    } catch (PDOException $e) {
        // Fallback
    }

    return $distribution;
}

/**
 * Retrieve all upgrades for administration
 * 
 * @param string|null $status
 * @return array
 */
function getAllUpgrades(?string $status = null): array {
    $db = getDb();
    if (!$db) return [];

    try {
        $sql = "
            SELECT u.*, usr.username, usr.full_name, usr.email, usr.mobile, usr.current_level as user_active_level,
                   p.package_name, p.team_size
            FROM upgrades u
            JOIN users usr ON u.user_id = usr.id
            LEFT JOIN packages p ON u.to_level = p.level_number
        ";
        if ($status !== null) {
            $sql .= " WHERE u.status = :status";
        }
        $sql .= " ORDER BY u.id DESC";

        $stmt = $db->prepare($sql);
        if ($status !== null) {
            $stmt->execute(['status' => $status]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll() ?: [];
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Count pending upgrades
 * 
 * @return int
 */
function getPendingUpgradeCount(): int {
    $db = getDb();
    if (!$db) return 0;
    try {
        return (int)$db->query("SELECT COUNT(*) FROM upgrades WHERE status = 'pending'")->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Admin action: Approve upgrade request
 * CRITICAL RULE: Updates upgrade status to approved and advances user current_level.
 * DOES NOT calculate or generate any income (income engine comes in later Part).
 * 
 * @param int $upgradeId
 * @param string|null $remarks
 * @return bool
 */
function approveUpgradeRequest(int $upgradeId, ?string $remarks = null): bool {
    $db = getDb();
    if (!$db) return false;

    try {
        $stmt = $db->prepare("SELECT * FROM upgrades WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $upgradeId]);
        $upgrade = $stmt->fetch();

        if (!$upgrade || $upgrade['status'] !== 'pending') {
            return false;
        }

        $db->beginTransaction();

        // 1. Mark upgrade as approved
        $upStmt = $db->prepare("
            UPDATE upgrades 
            SET status = 'approved', admin_remarks = :remarks, updated_at = NOW() 
            WHERE id = :id
        ");
        $upStmt->execute([
            'remarks' => $remarks ?: 'Approved by Administrator',
            'id'      => $upgradeId
        ]);

        // 2. Advance user's current_level to the approved level
        $userLevelStmt = $db->prepare("
            UPDATE users 
            SET current_level = :lvl, updated_at = NOW() 
            WHERE id = :uid
        ");
        $userLevelStmt->execute([
            'lvl' => (int)$upgrade['to_level'],
            'uid' => (int)$upgrade['user_id']
        ]);

        // 3. Log completed transaction ledger record for package upgrade
        $refNo = 'SSD-UPG' . (int)$upgrade['to_level'] . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $desc = "Package Upgrade to Level {$upgrade['to_level']} (" . formatCurrency($upgrade['amount']) . ")";
        $txStmt = $db->prepare("
            INSERT INTO transactions (user_id, transaction_type, category, amount, reference_no, description, status, created_at)
            VALUES (:uid, 'debit', 'upgrade', :amt, :ref, :desc, 'completed', NOW())
        ");
        $txStmt->execute([
            'uid'  => (int)$upgrade['user_id'],
            'amt'  => (float)$upgrade['amount'],
            'ref'  => $refNo,
            'desc' => $desc
        ]);

        // 4. Send notification to user
        $notifStmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, is_read, created_at)
            VALUES (:uid, :title, :msg, 0, NOW())
        ");
        $notifStmt->execute([
            'uid'   => (int)$upgrade['user_id'],
            'title' => "Package Upgrade Approved!",
            'msg'   => "Congratulations! Your upgrade to Level {$upgrade['to_level']} has been approved. Your account is now active on Level {$upgrade['to_level']}."
        ]);

        // 5. PART 5: Execute Income Engine Distribution (Level Income 50% + Sponsor Income 50%)
        if (function_exists('processUpgradeIncomeDistribution')) {
            processUpgradeIncomeDistribution((int)$upgrade['user_id'], (int)$upgrade['to_level'], (int)$upgradeId, $db);
        }

        $db->commit();
        return true;
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log("Approve Upgrade Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Admin action: Reject upgrade request
 * Updates upgrade status to rejected, records remarks, does NOT change user's current level.
 * 
 * @param int $upgradeId
 * @param string|null $remarks
 * @return bool
 */
function rejectUpgradeRequest(int $upgradeId, ?string $remarks = null): bool {
    $db = getDb();
    if (!$db) return false;

    try {
        $stmt = $db->prepare("SELECT * FROM upgrades WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $upgradeId]);
        $upgrade = $stmt->fetch();

        if (!$upgrade || $upgrade['status'] !== 'pending') {
            return false;
        }

        $db->beginTransaction();

        $upStmt = $db->prepare("
            UPDATE upgrades 
            SET status = 'rejected', admin_remarks = :remarks, updated_at = NOW() 
            WHERE id = :id
        ");
        $upStmt->execute([
            'remarks' => $remarks ?: 'Rejected by Administrator. Payment not verified.',
            'id'      => $upgradeId
        ]);

        // Send notification to user
        $notifStmt = $db->prepare("
            INSERT INTO notifications (user_id, title, message, is_read, created_at)
            VALUES (:uid, :title, :msg, 0, NOW())
        ");
        $notifStmt->execute([
            'uid'   => (int)$upgrade['user_id'],
            'title' => "Upgrade Request Update",
            'msg'   => "Your upgrade request to Level {$upgrade['to_level']} could not be approved at this time. Reason: " . ($remarks ?: 'Payment verification unsuccessful.')
        ]);

        $db->commit();
        return true;
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log("Reject Upgrade Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get current system settings
 * 
 * @return array
 */
function getSystemSettings(): array {
    $defaults = [
        'site_name'              => APP_NAME,
        'joining_package'        => (string)JOINING_PACKAGE_AMOUNT,
        'currency'               => CURRENCY_SYMBOL,
        'direct_income_percent'  => '100',
        'level_income_percent'   => '50',
        'sponsor_income_percent' => '50'
    ];

    $db = getDb();
    if (!$db) return $defaults;

    try {
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        return array_merge($defaults, $results ?: []);
    } catch (PDOException $e) {
        return $defaults;
    }
}

/**
 * Get aggregated dashboard statistics for Admin
 * 
 * @return array
 */
function getAdminStats(): array {
    $stats = [
        'total_users'        => 0,
        'active_users'       => 0,
        'pending_users'      => 0,
        'total_transactions' => 0,
        'total_withdrawals'  => 0,
        'pending_kyc'        => 0,
        'pending_upgrades'   => 0
    ];

    $db = getDb();
    if (!$db) return $stats;

    try {
        $stats['total_users'] = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['active_users'] = (int)$db->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
        $stats['pending_users'] = (int)$db->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();
        $stats['total_transactions'] = (int)$db->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
        $stats['total_withdrawals'] = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE status = 'approved'")->fetchColumn();
        $stats['pending_kyc'] = (int)$db->query("SELECT COUNT(*) FROM kyc WHERE status = 'pending'")->fetchColumn();
        $stats['pending_upgrades'] = (int)$db->query("SELECT COUNT(*) FROM upgrades WHERE status = 'pending'")->fetchColumn();
    } catch (PDOException $e) {
        // Return defaults
    }

    return $stats;
}

/**
 * Get user dashboard metrics for logged in member
 * 
 * @param int $userId
 * @return array
 */
function getUserDashboardData(int $userId): array {
    $db = getDb();
    $data = [
        'wallet_balance'      => 0.00,
        'total_income'        => 0.00,
        'direct_income'       => 0.00,
        'level_income'        => 0.00,
        'sponsor_income'      => 0.00,
        'team_members'        => 0,
        'current_level'       => 1,
        'recent_transactions' => []
    ];

    if (!$db) return $data;

    try {
        $uStmt = $db->prepare("SELECT current_level FROM users WHERE id = :uid");
        $uStmt->execute(['uid' => $userId]);
        $userRow = $uStmt->fetch();
        if ($userRow) {
            $data['current_level'] = (int)$userRow['current_level'];
        }

        $wStmt = $db->prepare("SELECT * FROM wallets WHERE user_id = :uid");
        $wStmt->execute(['uid' => $userId]);
        $wallet = $wStmt->fetch();
        if ($wallet) {
            $data['wallet_balance'] = (float)$wallet['balance'];
            $data['total_income']   = (float)$wallet['total_income'];
            $data['direct_income']  = (float)$wallet['direct_income'];
            $data['level_income']   = (float)$wallet['level_income'];
            $data['sponsor_income'] = (float)$wallet['sponsor_income'];
        }

        $tStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE sponsor_id = :uid");
        $tStmt->execute(['uid' => $userId]);
        $data['team_members'] = (int)$tStmt->fetchColumn();

        $txStmt = $db->prepare("SELECT * FROM transactions WHERE user_id = :uid ORDER BY id DESC LIMIT 5");
        $txStmt->execute(['uid' => $userId]);
        $data['recent_transactions'] = $txStmt->fetchAll();
    } catch (PDOException $e) {
        // Handle gracefully
    }

    return $data;
}

/**
 * =========================================================================
 * PART 4: 2x2 BINARY TREE, SPONSOR & TEAM MANAGEMENT FUNCTIONS
 * Technology: PHP 8+, PDO MySQL
 * Strictly preserves:
 * - Sponsor relationship (sponsor_id) separate from Binary Parent (parent_id)
 * - 2x2 Binary Structure (maximum 2 children: LEFT and RIGHT)
 * - Zero income calculations in Part 4 (reserved for Part 5)
 * =========================================================================
 */

/**
 * Find sponsor by either numeric ID or Username
 * 
 * @param string|int $identifier
 * @return array|null
 */
function getSponsorByIdentifier(string|int $identifier): ?array {
    $db = getDb();
    if (!$db) return null;

    $clean = trim((string)$identifier);
    if ($clean === '') return null;

    try {
        if (is_numeric($clean)) {
            $stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => (int)$clean]);
            $row = $stmt->fetch();
            if ($row) return $row;
        }

        $stmt = $db->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
        $stmt->execute(['u' => $clean]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get direct sponsor details for a given user
 * 
 * @param int $userId
 * @return array|null
 */
function getUserSponsor(int $userId): ?array {
    $db = getDb();
    if (!$db) return null;

    try {
        $stmt = $db->prepare("
            SELECT s.id, s.username, s.full_name, s.email, s.mobile, 
                   s.current_level, s.status, s.created_at, p.package_name
            FROM users u
            JOIN users s ON u.sponsor_id = s.id
            LEFT JOIN packages p ON s.current_level = p.level_number
            WHERE u.id = :uid
            LIMIT 1
        ");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Retrieve all directly sponsored members for a user
 * 
 * @param int $userId
 * @return array
 */
function getDirectTeam(int $userId): array {
    $db = getDb();
    if (!$db) return [];

    try {
        $stmt = $db->prepare("
            SELECT u.id, u.username, u.full_name, u.email, u.mobile, 
                   u.current_level, u.status, u.created_at, u.position, u.parent_id,
                   parent.username as parent_username,
                   p.package_name
            FROM users u
            LEFT JOIN users parent ON u.parent_id = parent.id
            LEFT JOIN packages p ON u.current_level = p.level_number
            WHERE u.sponsor_id = :uid
            ORDER BY u.id DESC
        ");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll() ?: [];
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Count total direct team members
 * 
 * @param int $userId
 * @return int
 */
function getDirectTeamCount(int $userId): int {
    $db = getDb();
    if (!$db) return 0;
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE sponsor_id = :uid");
        $stmt->execute(['uid' => $userId]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Count active direct team members
 * 
 * @param int $userId
 * @return int
 */
function getActiveDirectTeamCount(int $userId): int {
    $db = getDb();
    if (!$db) return 0;
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE sponsor_id = :uid AND status = 'active'");
        $stmt->execute(['uid' => $userId]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Check if a specific binary position under a parent is already occupied
 * 
 * @param int $parentId
 * @param string $position ('left' or 'right')
 * @return bool
 */
function isBinaryPositionOccupied(int $parentId, string $position): bool {
    $db = getDb();
    if (!$db) return false;

    $pos = strtolower(trim($position));
    if (!in_array($pos, ['left', 'right'], true)) {
        return true;
    }

    try {
        $stmt = $db->prepare("SELECT id FROM users WHERE parent_id = :pid AND position = :pos LIMIT 1");
        $stmt->execute(['pid' => $parentId, 'pos' => $pos]);
        return (bool)$stmt->fetch();
    } catch (PDOException $e) {
        return true;
    }
}

/**
 * Get count of direct binary children under a parent (max 2)
 * 
 * @param int $parentId
 * @return int
 */
function getChildCount(int $parentId): int {
    $db = getDb();
    if (!$db) return 0;
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE parent_id = :pid");
        $stmt->execute(['pid' => $parentId]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Validate binary parent and position placement
 * Enforces:
 * - Parent exists and is active
 * - Position is strictly 'left' or 'right'
 * - Parent has < 2 children
 * - Position is not already occupied
 * 
 * @param int $parentId
 * @param string $position
 * @return array ['valid' => bool, 'message' => string]
 */
function validateBinaryPlacement(int $parentId, string $position): array {
    $db = getDb();
    if (!$db) {
        return ['valid' => false, 'message' => 'Database connection failed.'];
    }

    $pos = strtolower(trim($position));
    if (!in_array($pos, ['left', 'right'], true)) {
        return ['valid' => false, 'message' => 'Invalid binary position. Allowed positions are left and right only.'];
    }

    // Check parent exists
    $parent = getUserById($parentId);
    if (!$parent) {
        return ['valid' => false, 'message' => 'Specified binary parent does not exist.'];
    }

    if ($parent['status'] !== 'active') {
        return ['valid' => false, 'message' => 'Specified binary parent is not active.'];
    }

    // Check if position already occupied
    if (isBinaryPositionOccupied($parentId, $pos)) {
        return ['valid' => false, 'message' => 'This binary position is already occupied.'];
    }

    // Check parent has less than 2 children
    if (getChildCount($parentId) >= 2) {
        return ['valid' => false, 'message' => 'A binary parent can have a maximum of two children (LEFT and RIGHT).'];
    }

    return ['valid' => true, 'message' => 'Placement is valid.'];
}

/**
 * Technical placement helper:
 * Finds the first available open position (left or right) under a given root user's binary subtree
 * using standard breadth-first search (BFS).
 * Note: Configurable default technical placement helper; not an official income/business rule.
 * 
 * @param int $rootUserId
 * @return array|null ['parent_id' => int, 'position' => 'left'|'right', 'parent_username' => string]
 */
function findNextAvailableBinarySlot(int $rootUserId): ?array {
    $db = getDb();
    if (!$db) return null;

    $queue = [$rootUserId];
    $visited = [];

    while (!empty($queue)) {
        $currentParentId = array_shift($queue);
        if (isset($visited[$currentParentId])) continue;
        $visited[$currentParentId] = true;

        // Check left slot
        if (!isBinaryPositionOccupied($currentParentId, 'left')) {
            $p = getUserById($currentParentId);
            return [
                'parent_id'       => $currentParentId,
                'position'        => 'left',
                'parent_username' => $p['username'] ?? 'User'
            ];
        }

        // Check right slot
        if (!isBinaryPositionOccupied($currentParentId, 'right')) {
            $p = getUserById($currentParentId);
            return [
                'parent_id'       => $currentParentId,
                'position'        => 'right',
                'parent_username' => $p['username'] ?? 'User'
            ];
        }

        // Both occupied: enqueue left child, then right child
        $children = getBinaryChildren($currentParentId);
        if (!empty($children['left']['id'])) {
            $queue[] = (int)$children['left']['id'];
        }
        if (!empty($children['right']['id'])) {
            $queue[] = (int)$children['right']['id'];
        }
    }

    return null;
}

/**
 * Retrieve the two direct binary children (left and right) for a parent
 * 
 * @param int $userId
 * @return array ['left' => array|null, 'right' => array|null]
 */
function getBinaryChildren(int $userId): array {
    $db = getDb();
    $result = ['left' => null, 'right' => null];
    if (!$db) return $result;

    try {
        $stmt = $db->prepare("
            SELECT u.id, u.username, u.full_name, u.email, u.mobile, 
                   u.current_level, u.status, u.position, u.parent_id, 
                   u.sponsor_id, u.sponsor_username, u.created_at,
                   p.package_name
            FROM users u
            LEFT JOIN packages p ON u.current_level = p.level_number
            WHERE u.parent_id = :pid
        ");
        $stmt->execute(['pid' => $userId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $pos = strtolower($row['position'] ?? '');
            if ($pos === 'left' || $pos === 'right') {
                $result[$pos] = $row;
            }
        }
    } catch (PDOException $e) {
        // Fallback
    }

    return $result;
}

/**
 * Check if target user is in the downline of root user (or is the root user)
 * Security check: Prevents URL manipulation and cross-network viewing
 * 
 * @param int $rootUserId
 * @param int $targetUserId
 * @return bool
 */
function isUserInDownline(int $rootUserId, int $targetUserId): bool {
    if ($rootUserId === $targetUserId) {
        return true;
    }

    $db = getDb();
    if (!$db) return false;

    $curr = $targetUserId;
    $visited = [];
    $maxHops = 150; // Guard against potential infinite loops

    while ($curr > 0 && $maxHops-- > 0) {
        if (isset($visited[$curr])) break;
        $visited[$curr] = true;

        $stmt = $db->prepare("SELECT parent_id FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $curr]);
        $parentId = $stmt->fetchColumn();

        if (!$parentId) {
            return false;
        }

        if ((int)$parentId === $rootUserId) {
            return true;
        }

        $curr = (int)$parentId;
    }

    return false;
}

/**
 * Retrieve all downline members in the binary network of a user
 * 
 * @param int $rootUserId
 * @param int $maxDepth
 * @return array
 */
function getBinaryDownline(int $rootUserId, int $maxDepth = 7): array {
    $db = getDb();
    if (!$db) return [];

    $downline = [];
    $queue = [['id' => $rootUserId, 'depth' => 0]];
    $visited = [];

    try {
        while (!empty($queue)) {
            $current = array_shift($queue);
            $currId = $current['id'];
            $currDepth = $current['depth'];

            if (isset($visited[$currId])) continue;
            $visited[$currId] = true;

            if ($currDepth > 0) {
                // Fetch member record
                $stmt = $db->prepare("
                    SELECT u.id, u.username, u.full_name, u.email, u.mobile, 
                           u.current_level, u.status, u.position, u.parent_id, 
                           u.sponsor_id, u.sponsor_username, u.created_at,
                           parent.username as parent_username,
                           p.package_name
                    FROM users u
                    LEFT JOIN users parent ON u.parent_id = parent.id
                    LEFT JOIN packages p ON u.current_level = p.level_number
                    WHERE u.id = :id
                    LIMIT 1
                ");
                $stmt->execute(['id' => $currId]);
                $uData = $stmt->fetch();
                if ($uData) {
                    $uData['tree_level'] = $currDepth;
                    $downline[] = $uData;
                }
            }

            if ($currDepth < $maxDepth) {
                $children = getBinaryChildren($currId);
                if (!empty($children['left']['id'])) {
                    $queue[] = ['id' => (int)$children['left']['id'], 'depth' => $currDepth + 1];
                }
                if (!empty($children['right']['id'])) {
                    $queue[] = ['id' => (int)$children['right']['id'], 'depth' => $currDepth + 1];
                }
            }
        }
    } catch (PDOException $e) {
        // Fallback
    }

    return $downline;
}

/**
 * Count total binary downline members under a user
 * 
 * @param int $rootUserId
 * @return int
 */
function getBinaryTeamCount(int $rootUserId): int {
    $downline = getBinaryDownline($rootUserId, 15);
    return count($downline);
}

/**
 * Count active binary downline members under a user
 * 
 * @param int $rootUserId
 * @return int
 */
function getActiveBinaryTeamCount(int $rootUserId): int {
    $downline = getBinaryDownline($rootUserId, 15);
    $active = 0;
    foreach ($downline as $m) {
        if (($m['status'] ?? '') === 'active') {
            $active++;
        }
    }
    return $active;
}

/**
 * Build 2x2 Binary Tree structure for interactive visualization
 * Generates exact nodes up to specified depth (default 3 levels = 1 root + 2 + 4 + 8 = 15 slots)
 * Empty positions are flagged with 'is_empty' => true.
 * 
 * @param int $userId
 * @param int $maxLevels (default 3 levels visible at once)
 * @return array
 */
function getBinaryTreeHierarchy(int $userId, int $maxLevels = 3): array {
    $root = getUserById($userId);
    if (!$root) {
        return ['is_empty' => true, 'position' => null];
    }

    // Attach package info
    $pkg = getPackageByLevel((int)$root['current_level']);
    $root['package_name'] = $pkg['package_name'] ?? ('Level ' . $root['current_level']);
    $root['is_empty'] = false;

    return buildTreeRecursive($root, 1, $maxLevels);
}

/**
 * Recursive tree builder for visual nodes
 */
function buildTreeRecursive(array $node, int $currentDepth, int $maxDepth): array {
    if ($currentDepth > $maxDepth || ($node['is_empty'] ?? false)) {
        return $node;
    }

    $children = getBinaryChildren((int)$node['id']);

    // Left Child
    if (!empty($children['left'])) {
        $leftNode = $children['left'];
        $leftNode['is_empty'] = false;
        $node['left'] = buildTreeRecursive($leftNode, $currentDepth + 1, $maxDepth);
    } else {
        $node['left'] = [
            'is_empty'  => true,
            'parent_id' => $node['id'],
            'position'  => 'left'
        ];
    }

    // Right Child
    if (!empty($children['right'])) {
        $rightNode = $children['right'];
        $rightNode['is_empty'] = false;
        $node['right'] = buildTreeRecursive($rightNode, $currentDepth + 1, $maxDepth);
    } else {
        $node['right'] = [
            'is_empty'  => true,
            'parent_id' => $node['id'],
            'position'  => 'right'
        ];
    }

    return $node;
}

/**
 * =========================================================================
 * PART 6: WALLET + TRANSACTIONS + WITHDRAWAL ENGINE
 * =========================================================================
 */

/**
 * Credit a user's wallet with pessimistic row locking and record ledger transaction
 * 
 * @param PDO $pdo Active PDO connection inside an open transaction
 * @param int $userId
 * @param float $amount
 * @param string $category 'direct_income', 'level_income', 'sponsor_income', 'admin_adjustment'
 * @param string $description
 * @param string $refNo Unique reference number
 * @param string $refType 'income', 'refund', 'system'
 * @param string|null $refId
 * @return array ['wallet_id', 'balance_before', 'balance_after', 'transaction_id']
 */
function creditWalletWithLock(
    PDO $pdo,
    int $userId,
    float $amount,
    string $category,
    string $description,
    string $refNo,
    string $refType = 'income',
    ?string $refId = null
): array {
    // 1. Lock wallet row
    $stmt = $pdo->prepare("SELECT id, balance, total_credit, total_income FROM wallets WHERE user_id = :uid FOR UPDATE");
    $stmt->execute(['uid' => $userId]);
    $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$wallet) {
        initUserWalletIfNotExists($userId, $pdo);
        $stmt->execute(['uid' => $userId]);
        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $walletId = (int)$wallet['id'];
    $balanceBefore = (float)$wallet['balance'];
    $balanceAfter = $balanceBefore + $amount;

    // 2. Update wallet record
    $upd = $pdo->prepare("
        UPDATE wallets 
        SET balance = :bal_after,
            total_credit = total_credit + :amt,
            total_income = total_income + :amt,
            updated_at = NOW()
        WHERE id = :wid
    ");
    $upd->execute([
        'bal_after' => $balanceAfter,
        'amt'       => $amount,
        'wid'       => $walletId
    ]);

    // 3. Insert transaction ledger entry
    $tIns = $pdo->prepare("
        INSERT INTO transactions (
            wallet_id, user_id, transaction_type, category, amount, 
            balance_before, balance_after, reference_type, reference_id, reference_no, 
            description, status, created_at
        ) VALUES (
            :wid, :uid, 'credit', :cat, :amt,
            :b_before, :b_after, :ref_type, :ref_id, :ref_no,
            :desc, 'completed', NOW()
        )
    ");
    $tIns->execute([
        'wid'      => $walletId,
        'uid'      => $userId,
        'cat'      => $category,
        'amt'      => $amount,
        'b_before' => $balanceBefore,
        'b_after'  => $balanceAfter,
        'ref_type' => $refType,
        'ref_id'   => $refId ?: $refNo,
        'ref_no'   => $refNo,
        'desc'     => $description
    ]);

    return [
        'wallet_id'      => $walletId,
        'balance_before' => $balanceBefore,
        'balance_after'  => $balanceAfter,
        'transaction_id' => (int)$pdo->lastInsertId()
    ];
}

/**
 * Debit a user's wallet with pessimistic row locking and record ledger transaction
 * 
 * @param PDO $pdo Active PDO connection inside an open transaction
 * @param int $userId
 * @param float $amount
 * @param string $category 'withdrawal', 'package_purchase', 'upgrade'
 * @param string $description
 * @param string $refNo
 * @param string $refType 'withdrawal', 'upgrade'
 * @param string|null $refId
 * @param string $status 'completed', 'pending'
 * @return array
 * @throws Exception
 */
function debitWalletWithLock(
    PDO $pdo,
    int $userId,
    float $amount,
    string $category,
    string $description,
    string $refNo,
    string $refType = 'withdrawal',
    ?string $refId = null,
    string $status = 'completed'
): array {
    $stmt = $pdo->prepare("SELECT id, balance, total_debit, total_withdrawn FROM wallets WHERE user_id = :uid FOR UPDATE");
    $stmt->execute(['uid' => $userId]);
    $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$wallet) {
        throw new Exception("User wallet not found");
    }

    $walletId = (int)$wallet['id'];
    $balanceBefore = (float)$wallet['balance'];
    if ($balanceBefore < $amount) {
        throw new Exception("Insufficient wallet balance for this debit transaction");
    }

    $balanceAfter = $balanceBefore - $amount;

    $upd = $pdo->prepare("
        UPDATE wallets 
        SET balance = :bal_after,
            total_debit = total_debit + :amt,
            updated_at = NOW()
        WHERE id = :wid
    ");
    $upd->execute([
        'bal_after' => $balanceAfter,
        'amt'       => $amount,
        'wid'       => $walletId
    ]);

    $tIns = $pdo->prepare("
        INSERT INTO transactions (
            wallet_id, user_id, transaction_type, category, amount, 
            balance_before, balance_after, reference_type, reference_id, reference_no, 
            description, status, created_at
        ) VALUES (
            :wid, :uid, 'debit', :cat, :amt,
            :b_before, :b_after, :ref_type, :ref_id, :ref_no,
            :desc, :st, NOW()
        )
    ");
    $tIns->execute([
        'wid'      => $walletId,
        'uid'      => $userId,
        'cat'      => $category,
        'amt'      => $amount,
        'b_before' => $balanceBefore,
        'b_after'  => $balanceAfter,
        'ref_type' => $refType,
        'ref_id'   => $refId ?: $refNo,
        'ref_no'   => $refNo,
        'desc'     => $description,
        'st'       => $status
    ]);

    return [
        'wallet_id'      => $walletId,
        'balance_before' => $balanceBefore,
        'balance_after'  => $balanceAfter,
        'transaction_id' => (int)$pdo->lastInsertId()
    ];
}

/**
 * Submit user withdrawal request with KYC check, balance lock, and ledger deduction
 * 
 * @param int $userId
 * @param float $amount
 * @param string $paymentMode
 * @param array $paymentDetails
 * @return array
 */
function requestUserWithdrawal(int $userId, float $amount, string $paymentMode, array $paymentDetails): array {
    $minWithdrawal = 500.00; // Minimum withdrawal rule
    if ($amount < $minWithdrawal) {
        return ['success' => false, 'message' => "Minimum withdrawal amount is ₹" . number_format($minWithdrawal, 2)];
    }

    $db = getDb();
    if (!$db) {
        return ['success' => false, 'message' => "Database connection error"];
    }

    try {
        $db->beginTransaction();

        // 1. Verify KYC approved
        $kycStmt = $db->prepare("SELECT status FROM kyc WHERE user_id = :uid ORDER BY id DESC LIMIT 1");
        $kycStmt->execute(['uid' => $userId]);
        $kyc = $kycStmt->fetch(PDO::FETCH_ASSOC);
        if (!$kyc || ($kyc['status'] ?? '') !== 'approved') {
            $db->rollBack();
            return [
                'success' => false, 
                'message' => "KYC verification is required before initiating withdrawal requests. Please submit your KYC documents and wait for approval."
            ];
        }

        // 2. Lock wallet and check balance
        $wStmt = $db->prepare("SELECT id, balance FROM wallets WHERE user_id = :uid FOR UPDATE");
        $wStmt->execute(['uid' => $userId]);
        $wallet = $wStmt->fetch(PDO::FETCH_ASSOC);

        if (!$wallet || (float)$wallet['balance'] < $amount) {
            $avail = $wallet ? (float)$wallet['balance'] : 0.00;
            $db->rollBack();
            return ['success' => false, 'message' => "Insufficient wallet balance. Available spendable balance is ₹" . number_format($avail, 2)];
        }

        // 3. Compute Deductions: 5% TDS + 5% Admin Charge (Net 90%)
        $tdsRate = 0.05;
        $adminRate = 0.05;
        $tdsDeduction = round($amount * $tdsRate, 2);
        $adminCharge = round($amount * $adminRate, 2);
        $netAmount = round($amount - ($tdsDeduction + $adminCharge), 2);

        $refNo = 'WTH-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        $payDetailsJson = json_encode($paymentDetails);

        // 4. Debit wallet with row lock (prevents double-spending)
        $debitResult = debitWalletWithLock(
            $db,
            $userId,
            $amount,
            'withdrawal',
            "Withdrawal request of ₹" . number_format($amount, 2) . " (Net: ₹" . number_format($netAmount, 2) . " via {$paymentMode})",
            $refNo,
            'withdrawal',
            $refNo,
            'pending'
        );

        // 5. Insert withdrawal request
        $insW = $db->prepare("
            INSERT INTO withdrawals (
                user_id, amount, tds_deduction, admin_charge, net_amount, 
                payment_mode, payment_details, transaction_ref, status, created_at
            ) VALUES (
                :uid, :amt, :tds, :adm, :net,
                :mode, :details, :ref, 'pending', NOW()
            )
        ");
        $insW->execute([
            'uid'     => $userId,
            'amt'     => $amount,
            'tds'     => $tdsDeduction,
            'adm'     => $adminCharge,
            'net'     => $netAmount,
            'mode'    => $paymentMode,
            'details' => $payDetailsJson,
            'ref'     => $refNo
        ]);
        $withdrawalId = (int)$db->lastInsertId();

        // 6. User notification
        $notif = $db->prepare("
            INSERT INTO notifications (user_id, title, message, is_read, created_at)
            VALUES (:uid, 'Withdrawal Request Submitted', :msg, 0, NOW())
        ");
        $notif->execute([
            'uid' => $userId,
            'msg' => "Your withdrawal request #{$refNo} for ₹" . number_format($amount, 2) . " (Net ₹" . number_format($netAmount, 2) . ") was submitted successfully and is pending admin approval."
        ]);

        $db->commit();
        return [
            'success'       => true,
            'message'       => "Withdrawal request of ₹" . number_format($amount, 2) . " submitted successfully! Net payout: ₹" . number_format($netAmount, 2),
            'withdrawal_id' => $withdrawalId,
            'reference_no'  => $refNo
        ];
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return ['success' => false, 'message' => "Withdrawal request failed: " . $e->getMessage()];
    }
}

/**
 * Fetch withdrawals for a specific user
 * 
 * @param int $userId
 * @param int $limit
 * @return array
 */
function getUserWithdrawals(int $userId, int $limit = 50): array {
    $db = getDb();
    if (!$db) return [];

    try {
        $stmt = $db->prepare("
            SELECT * FROM withdrawals 
            WHERE user_id = :uid 
            ORDER BY id DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch paginated withdrawals for admin panel
 * 
 * @param string $status 'all', 'pending', 'approved', 'rejected'
 * @param int $page
 * @param int $limit
 * @param string $search
 * @return array
 */
function getAllWithdrawals(string $status = 'all', int $page = 1, int $limit = 20, string $search = ''): array {
    $db = getDb();
    if (!$db) return ['records' => [], 'total' => 0, 'total_pages' => 0];

    $offset = max(0, ($page - 1) * $limit);
    $params = [];
    $where = [];

    if ($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected'])) {
        $where[] = "w.status = :st";
        $params['st'] = $status;
    }

    if (!empty($search)) {
        $where[] = "(u.username LIKE :q OR u.full_name LIKE :q OR u.mobile LIKE :q OR w.transaction_ref LIKE :q)";
        $params['q'] = "%{$search}%";
    }

    $whereSql = empty($where) ? "" : "WHERE " . implode(" AND ", $where);

    try {
        // Count
        $cStmt = $db->prepare("
            SELECT COUNT(*) 
            FROM withdrawals w 
            JOIN users u ON w.user_id = u.id 
            {$whereSql}
        ");
        $cStmt->execute($params);
        $total = (int)$cStmt->fetchColumn();

        // Records
        $rStmt = $db->prepare("
            SELECT w.*, u.username, u.full_name, u.mobile, u.current_level,
                   p.bank_name, p.account_number, p.ifsc_code, p.account_holder_name, p.upi_id,
                   k.status AS kyc_status
            FROM withdrawals w
            JOIN users u ON w.user_id = u.id
            LEFT JOIN user_profiles p ON u.id = p.user_id
            LEFT JOIN kyc k ON u.id = k.user_id
            {$whereSql}
            ORDER BY w.id DESC
            LIMIT :offset, :limit
        ");
        foreach ($params as $k => $v) {
            $rStmt->bindValue(":{$k}", $v);
        }
        $rStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $rStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $rStmt->execute();

        return [
            'records'     => $rStmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'total'       => $total,
            'total_pages' => ceil($total / max(1, $limit))
        ];
    } catch (PDOException $e) {
        return ['records' => [], 'total' => 0, 'total_pages' => 0];
    }
}

/**
 * Admin approves a withdrawal request and marks it completed
 * 
 * @param int $withdrawalId
 * @param string $adminRemarks
 * @param string $txnRef Payout transaction / UTR reference
 * @param PDO|null $pdo
 * @return array
 */
function adminApproveWithdrawal(int $withdrawalId, string $adminRemarks = '', string $txnRef = '', ?PDO $pdo = null): array {
    $db = $pdo ?: getDb();
    if (!$db) return ['success' => false, 'message' => 'Database connection error'];

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("SELECT * FROM withdrawals WHERE id = :id FOR UPDATE");
        $stmt->execute(['id' => $withdrawalId]);
        $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$withdrawal) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Withdrawal record not found'];
        }

        if ($withdrawal['status'] !== 'pending') {
            $db->rollBack();
            return ['success' => false, 'message' => 'Withdrawal request is already ' . $withdrawal['status']];
        }

        $userId = (int)$withdrawal['user_id'];
        $amount = (float)$withdrawal['amount'];
        $netAmount = (float)$withdrawal['net_amount'];
        $refNo = $withdrawal['transaction_ref'] ?: ('WTH-' . $withdrawal['id']);

        // 1. Update withdrawal status
        $updW = $db->prepare("
            UPDATE withdrawals 
            SET status = 'approved',
                admin_remarks = :remarks,
                transaction_ref = :txn_ref,
                processed_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
        ");
        $updW->execute([
            'remarks' => $adminRemarks ?: 'Disbursed and approved by Admin',
            'txn_ref' => $txnRef ?: $refNo,
            'id'      => $withdrawalId
        ]);

        // 2. Update wallet total_withdrawn
        $wUpd = $db->prepare("
            UPDATE wallets 
            SET total_withdrawn = total_withdrawn + :amt,
                updated_at = NOW()
            WHERE user_id = :uid
        ");
        $wUpd->execute(['amt' => $amount, 'uid' => $userId]);

        // 3. Mark transaction as completed
        $tUpd = $db->prepare("
            UPDATE transactions 
            SET status = 'completed',
                description = CONCAT(description, ' [APPROVED: ', :rem, ']')
            WHERE reference_no = :ref AND user_id = :uid
        ");
        $tUpd->execute([
            'rem' => $adminRemarks ?: ($txnRef ? "UTR: {$txnRef}" : 'Disbursed'),
            'ref' => $refNo,
            'uid' => $userId
        ]);

        // 4. Notification
        $notif = $db->prepare("
            INSERT INTO notifications (user_id, title, message, is_read, created_at)
            VALUES (:uid, 'Withdrawal Approved & Paid', :msg, 0, NOW())
        ");
        $notif->execute([
            'uid' => $userId,
            'msg' => "Your withdrawal request #{$refNo} for Net ₹" . number_format($netAmount, 2) . " has been approved and paid. Payout Ref: " . ($txnRef ?: 'Processed')
        ]);

        $db->commit();
        return ['success' => true, 'message' => "Withdrawal request #{$refNo} successfully approved!"];
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return ['success' => false, 'message' => 'Approval failed: ' . $e->getMessage()];
    }
}

/**
 * Admin rejects withdrawal request and automatically refunds amount back to member's wallet
 * 
 * @param int $withdrawalId
 * @param string $adminRemarks
 * @param PDO|null $pdo
 * @return array
 */
function adminRejectWithdrawal(int $withdrawalId, string $adminRemarks = '', ?PDO $pdo = null): array {
    $db = $pdo ?: getDb();
    if (!$db) return ['success' => false, 'message' => 'Database connection error'];

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("SELECT * FROM withdrawals WHERE id = :id FOR UPDATE");
        $stmt->execute(['id' => $withdrawalId]);
        $withdrawal = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$withdrawal) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Withdrawal record not found'];
        }

        if ($withdrawal['status'] !== 'pending') {
            $db->rollBack();
            return ['success' => false, 'message' => 'Withdrawal request is already ' . $withdrawal['status']];
        }

        $userId = (int)$withdrawal['user_id'];
        $amount = (float)$withdrawal['amount'];
        $refNo = $withdrawal['transaction_ref'] ?: ('WTH-' . $withdrawal['id']);
        $refundRef = 'REF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // 1. Mark withdrawal as rejected
        $updW = $db->prepare("
            UPDATE withdrawals 
            SET status = 'rejected',
                admin_remarks = :remarks,
                processed_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
        ");
        $updW->execute([
            'remarks' => $adminRemarks ?: 'Rejected by administrator. Funds refunded to wallet.',
            'id'      => $withdrawalId
        ]);

        // 2. Re-credit user's wallet with row locking
        $wStmt = $db->prepare("SELECT id, balance, total_debit FROM wallets WHERE user_id = :uid FOR UPDATE");
        $wStmt->execute(['uid' => $userId]);
        $wallet = $wStmt->fetch(PDO::FETCH_ASSOC);

        $walletId = (int)$wallet['id'];
        $balanceBefore = (float)$wallet['balance'];
        $balanceAfter = $balanceBefore + $amount;

        $wRefund = $db->prepare("
            UPDATE wallets 
            SET balance = :bal_after,
                total_debit = GREATEST(0.00, total_debit - :amt),
                updated_at = NOW()
            WHERE id = :wid
        ");
        $wRefund->execute([
            'bal_after' => $balanceAfter,
            'amt'       => $amount,
            'wid'       => $walletId
        ]);

        // 3. Insert REFUND transaction into ledger
        $tIns = $db->prepare("
            INSERT INTO transactions (
                wallet_id, user_id, transaction_type, category, amount, 
                balance_before, balance_after, reference_type, reference_id, reference_no, 
                description, status, created_at
            ) VALUES (
                :wid, :uid, 'credit', 'admin_adjustment', :amt,
                :b_before, :b_after, 'refund', :ref_id, :ref_no,
                :desc, 'completed', NOW()
            )
        ");
        $tIns->execute([
            'wid'      => $walletId,
            'uid'      => $userId,
            'amt'      => $amount,
            'b_before' => $balanceBefore,
            'b_after'  => $balanceAfter,
            'ref_id'   => $refNo,
            'ref_no'   => $refundRef,
            'desc'     => "Refund of rejected withdrawal #{$refNo}. Reason: " . ($adminRemarks ?: 'Rejected by Admin')
        ]);

        // 4. Mark original pending withdrawal transaction as failed
        $tOrig = $db->prepare("
            UPDATE transactions 
            SET status = 'failed',
                description = CONCAT(description, ' [REJECTED & REFUNDED: ', :rem, ']')
            WHERE reference_no = :ref AND user_id = :uid
        ");
        $tOrig->execute([
            'rem' => $adminRemarks ?: 'Rejected',
            'ref' => $refNo,
            'uid' => $userId
        ]);

        // 5. User Notification
        $notif = $db->prepare("
            INSERT INTO notifications (user_id, title, message, is_read, created_at)
            VALUES (:uid, 'Withdrawal Rejected & Refunded', :msg, 0, NOW())
        ");
        $notif->execute([
            'uid' => $userId,
            'msg' => "Your withdrawal request #{$refNo} for ₹" . number_format($amount, 2) . " was rejected. ₹" . number_format($amount, 2) . " has been refunded back to your wallet. Reason: " . ($adminRemarks ?: 'Rejected by Admin')
        ]);

        $db->commit();
        return [
            'success' => true, 
            'message' => "Withdrawal #{$refNo} rejected and ₹" . number_format($amount, 2) . " refunded back to user's wallet."
        ];
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return ['success' => false, 'message' => 'Rejection failed: ' . $e->getMessage()];
    }
}

/**
 * Filtered transaction ledger history with before/after balances
 * 
 * @param int $userId
 * @param string $type 'all', 'income', 'withdrawal', 'refund', 'credit', 'debit'
 * @param int $limit
 * @param int $offset
 * @return array
 */
function getUserFilteredTransactions(int $userId, string $type = 'all', int $limit = 50, int $offset = 0): array {
    $db = getDb();
    if (!$db) return ['records' => [], 'total' => 0];

    $where = ["user_id = :uid"];
    $params = ['uid' => $userId];

    if ($type === 'income') {
        $where[] = "category IN ('direct_income', 'level_income', 'sponsor_income')";
    } elseif ($type === 'withdrawal') {
        $where[] = "category = 'withdrawal'";
    } elseif ($type === 'refund') {
        $where[] = "(reference_type = 'refund' OR category = 'admin_adjustment')";
    } elseif ($type === 'credit') {
        $where[] = "transaction_type = 'credit'";
    } elseif ($type === 'debit') {
        $where[] = "transaction_type = 'debit'";
    }

    $whereSql = implode(" AND ", $where);

    try {
        $cStmt = $db->prepare("SELECT COUNT(*) FROM transactions WHERE {$whereSql}");
        $cStmt->execute($params);
        $total = (int)$cStmt->fetchColumn();

        $qStmt = $db->prepare("
            SELECT * FROM transactions 
            WHERE {$whereSql}
            ORDER BY id DESC 
            LIMIT :offset, :limit
        ");
        foreach ($params as $k => $v) {
            $qStmt->bindValue(":{$k}", $v);
        }
        $qStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $qStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $qStmt->execute();

        return [
            'records' => $qStmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'total'   => $total
        ];
    } catch (PDOException $e) {
        return ['records' => [], 'total' => 0];
    }
}

/**
 * Count pending withdrawal requests for admin badge
 * 
 * @return int
 */
function getPendingWithdrawalsCount(): int {
    $db = getDb();
    if (!$db) return 0;
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM withdrawals WHERE status = 'pending'");
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

// =========================================================================
// PART 7 FUNCTIONS — MEMBER MANAGEMENT, KYC, SETTINGS, & SUPPORT HELPDESK
// =========================================================================

/**
 * Get summary stats of members (Total, Active, Blocked, Pending)
 * 
 * @return array
 */
function getMembersSummaryStats(): array {
    $db = getDb();
    if (!$db) return ['total' => 0, 'active' => 0, 'blocked' => 0, 'pending' => 0];

    try {
        $stmt = $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
            FROM users
        ");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total'   => (int)($res['total'] ?? 0),
            'active'  => (int)($res['active'] ?? 0),
            'blocked' => (int)($res['blocked'] ?? 0),
            'pending' => (int)($res['pending'] ?? 0)
        ];
    } catch (PDOException $e) {
        return ['total' => 0, 'active' => 0, 'blocked' => 0, 'pending' => 0];
    }
}

/**
 * Get filtered list of members with wallet, KYC, and sponsor details
 * 
 * @param array $filters ['search' => '', 'status' => '', 'level' => '']
 * @param int $limit
 * @param int $offset
 * @return array ['records' => array, 'total' => int]
 */
function getAdminMemberList(array $filters = [], int $limit = 20, int $offset = 0): array {
    $db = getDb();
    if (!$db) return ['records' => [], 'total' => 0];

    $where = ["1=1"];
    $params = [];

    if (!empty($filters['search'])) {
        $where[] = "(u.username LIKE :s OR u.full_name LIKE :s OR u.email LIKE :s OR u.mobile LIKE :s)";
        $params['s'] = '%' . trim($filters['search']) . '%';
    }

    if (!empty($filters['status']) && in_array($filters['status'], ['active', 'pending', 'blocked'], true)) {
        $where[] = "u.status = :status";
        $params['status'] = $filters['status'];
    }

    if (!empty($filters['level']) && is_numeric($filters['level'])) {
        $where[] = "u.current_level = :lvl";
        $params['lvl'] = (int)$filters['level'];
    }

    $whereSql = implode(" AND ", $where);

    try {
        $cStmt = $db->prepare("SELECT COUNT(*) FROM users u WHERE {$whereSql}");
        $cStmt->execute($params);
        $total = (int)$cStmt->fetchColumn();

        $sql = "
            SELECT 
                u.*,
                w.balance as wallet_balance,
                w.total_income,
                k.status as kyc_status,
                k.document_type,
                k.document_number,
                sp.full_name as sponsor_name
            FROM users u
            LEFT JOIN wallets w ON u.id = w.user_id
            LEFT JOIN kyc k ON u.id = k.user_id
            LEFT JOIN users sp ON u.sponsor_id = sp.id
            WHERE {$whereSql}
            ORDER BY u.id DESC
            LIMIT :offset, :limit
        ";

        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(":{$k}", $v);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'records' => $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'total'   => $total
        ];
    } catch (PDOException $e) {
        return ['records' => [], 'total' => 0];
    }
}

/**
 * Update member account status (active, blocked)
 * 
 * @param int $userId
 * @param string $newStatus 'active', 'blocked'
 * @return array
 */
function updateMemberAccountStatus(int $userId, string $newStatus): array {
    $db = getDb();
    if (!$db) return ['success' => false, 'message' => 'Database connection failed.'];

    if (!in_array($newStatus, ['active', 'blocked', 'pending'], true)) {
        return ['success' => false, 'message' => 'Invalid status parameter.'];
    }

    try {
        $stmt = $db->prepare("UPDATE users SET status = :status, updated_at = NOW() WHERE id = :uid");
        $stmt->execute(['status' => $newStatus, 'uid' => $userId]);

        // Add user notification
        $msg = ($newStatus === 'blocked') 
            ? 'Your account has been temporarily blocked by administration. Please contact support.' 
            : 'Your account status has been activated.';
        $notif = $db->prepare("INSERT INTO notifications (user_id, title, message, is_read, created_at) VALUES (:uid, 'Account Status Updated', :msg, 0, NOW())");
        $notif->execute(['uid' => $userId, 'msg' => $msg]);

        return ['success' => true, 'message' => "Member status updated to " . ucfirst($newStatus) . "."];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to update member: ' . $e->getMessage()];
    }
}

/**
 * Reset member password from admin panel
 * 
 * @param int $userId
 * @param string $newPassword
 * @return array
 */
function adminResetMemberPassword(int $userId, string $newPassword): array {
    $db = getDb();
    if (!$db) return ['success' => false, 'message' => 'Database connection failed.'];

    if (strlen($newPassword) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
    }

    try {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET password = :p, updated_at = NOW() WHERE id = :uid");
        $stmt->execute(['p' => $hash, 'uid' => $userId]);

        // Notify member
        $notif = $db->prepare("INSERT INTO notifications (user_id, title, message, is_read, created_at) VALUES (:uid, 'Security Notice: Password Reset', 'Your account password was updated by the system administrator.', 0, NOW())");
        $notif->execute(['uid' => $userId]);

        return ['success' => true, 'message' => 'Password updated successfully.'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to reset password: ' . $e->getMessage()];
    }
}

/**
 * Fetch KYC verification requests with member details
 * 
 * @param string $status 'all', 'pending', 'approved', 'rejected'
 * @param int $limit
 * @param int $offset
 * @return array
 */
function getAdminKycRequests(string $status = 'all', int $limit = 20, int $offset = 0): array {
    $db = getDb();
    if (!$db) return ['records' => [], 'total' => 0];

    $where = ["1=1"];
    $params = [];

    if ($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected'], true)) {
        $where[] = "k.status = :st";
        $params['st'] = $status;
    }

    $whereSql = implode(" AND ", $where);

    try {
        $cStmt = $db->prepare("SELECT COUNT(*) FROM kyc k WHERE {$whereSql}");
        $cStmt->execute($params);
        $total = (int)$cStmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT 
                k.*,
                u.username,
                u.full_name,
                u.email,
                u.mobile,
                u.current_level,
                p.bank_name,
                p.account_number,
                p.ifsc_code,
                p.account_holder_name,
                p.upi_id
            FROM kyc k
            JOIN users u ON k.user_id = u.id
            LEFT JOIN user_profiles p ON u.id = p.user_id
            WHERE {$whereSql}
            ORDER BY 
                CASE WHEN k.status = 'pending' THEN 1 ELSE 2 END,
                k.id DESC
            LIMIT :offset, :limit
        ");

        foreach ($params as $k => $v) {
            $stmt->bindValue(":{$k}", $v);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'records' => $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'total'   => $total
        ];
    } catch (PDOException $e) {
        return ['records' => [], 'total' => 0];
    }
}

/**
 * Get count summary of KYC requests
 * 
 * @return array
 */
function getKycCountStats(): array {
    $db = getDb();
    if (!$db) return ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0];

    try {
        $stmt = $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM kyc
        ");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total'    => (int)($res['total'] ?? 0),
            'pending'  => (int)($res['pending'] ?? 0),
            'approved' => (int)($res['approved'] ?? 0),
            'rejected' => (int)($res['rejected'] ?? 0)
        ];
    } catch (PDOException $e) {
        return ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0];
    }
}

/**
 * Process Admin KYC Action (Approve or Reject)
 * 
 * @param int $kycId
 * @param string $action 'approve', 'reject'
 * @param string|null $adminRemarks
 * @param int $adminId
 * @return array
 */
function processAdminKycAction(int $kycId, string $action, ?string $adminRemarks = null, int $adminId = 1): array {
    $db = getDb();
    if (!$db) return ['success' => false, 'message' => 'Database connection failed.'];

    try {
        $stmt = $db->prepare("SELECT * FROM kyc WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $kycId]);
        $kyc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$kyc) {
            return ['success' => false, 'message' => 'KYC record not found.'];
        }

        $newStatus = ($action === 'approve') ? 'approved' : 'rejected';

        $uStmt = $db->prepare("
            UPDATE kyc 
            SET status = :st,
                admin_notes = :notes,
                reviewed_by = :admin_id,
                reviewed_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
        ");
        $uStmt->execute([
            'st'       => $newStatus,
            'notes'    => $adminRemarks,
            'admin_id' => $adminId,
            'id'       => $kycId
        ]);

        // Send Member notification
        $userId = (int)$kyc['user_id'];
        $notifMsg = ($action === 'approve')
            ? 'Congratulations! Your KYC documents have been reviewed and APPROVED. You can now request payouts.'
            : 'Your KYC submission was rejected. Reason: ' . ($adminRemarks ?: 'Document mismatch. Please upload clear documents.');

        $notif = $db->prepare("INSERT INTO notifications (user_id, title, message, is_read, created_at) VALUES (:uid, 'KYC Verification Status', :msg, 0, NOW())");
        $notif->execute(['uid' => $userId, 'msg' => $notifMsg]);

        return [
            'success' => true, 
            'message' => "KYC document #{$kycId} has been successfully " . strtoupper($newStatus) . "."
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'KYC processing error: ' . $e->getMessage()];
    }
}

/**
 * Get count of pending KYC requests for admin badge
 * 
 * @return int
 */
function getPendingKycCount(): int {
    $db = getDb();
    if (!$db) return 0;
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM kyc WHERE status = 'pending'");
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Get all system settings as key-value associative array
 * 
 * @return array
 */
function getAllSystemSettings(): array {
    $db = getDb();
    if (!$db) return [];

    try {
        $stmt = $db->query("SELECT setting_key, setting_value, description FROM settings");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = [
                'value'       => $r['setting_value'],
                'description' => $r['description'] ?? ''
            ];
        }
        return $settings;
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Update multiple system settings in database
 * 
 * @param array $settingsKeyValuePairs
 * @return bool
 */
function saveSystemSettings(array $settingsKeyValuePairs): bool {
    $db = getDb();
    if (!$db) return false;

    try {
        $db->beginTransaction();
        $stmt = $db->prepare("
            INSERT INTO settings (setting_key, setting_value, updated_at) 
            VALUES (:k, :v, NOW())
            ON DUPLICATE KEY UPDATE setting_value = :v, updated_at = NOW()
        ");

        foreach ($settingsKeyValuePairs as $key => $val) {
            $stmt->execute(['k' => $key, 'v' => (string)$val]);
        }

        $db->commit();
        return true;
    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return false;
    }
}

/**
 * Create a new support ticket
 * 
 * @param int $userId
 * @param string $subject
 * @param string $category
 * @param string $priority
 * @param string $message
 * @return array
 */
function createSupportTicket(int $userId, string $subject, string $category, string $priority, string $message): array {
    $db = getDb();
    if (!$db) return ['success' => false, 'message' => 'Database connection failed.'];

    $subj = trim($subject);
    $cat = trim($category);
    $pri = strtolower(trim($priority));
    $msg = trim($message);

    if (empty($subj) || empty($msg)) {
        return ['success' => false, 'message' => 'Please provide both a ticket subject and initial message.'];
    }

    if (!in_array($pri, ['low', 'medium', 'high'], true)) {
        $pri = 'medium';
    }

    $ticketNo = 'TKT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

    try {
        $db->beginTransaction();

        $tStmt = $db->prepare("
            INSERT INTO support_tickets (user_id, ticket_no, subject, category, priority, status, created_at, updated_at)
            VALUES (:uid, :tno, :subj, :cat, :pri, 'open', NOW(), NOW())
        ");
        $tStmt->execute([
            'uid'  => $userId,
            'tno'  => $ticketNo,
            'subj' => $subj,
            'cat'  => $cat,
            'pri'  => $pri
        ]);
        $ticketId = (int)$db->lastInsertId();

        // Get user's name
        $user = getUserById($userId);
        $senderName = $user['full_name'] ?? 'Member';

        // Insert first message
        $mStmt = $db->prepare("
            INSERT INTO support_messages (ticket_id, sender_type, sender_id, sender_name, message, created_at)
            VALUES (:tid, 'user', :sid, :sname, :msg, NOW())
        ");
        $mStmt->execute([
            'tid'   => $ticketId,
            'sid'   => $userId,
            'sname' => $senderName,
            'msg'   => $msg
        ]);

        $db->commit();
        return ['success' => true, 'ticket_id' => $ticketId, 'ticket_no' => $ticketNo, 'message' => "Support ticket #{$ticketNo} created successfully! Our team will respond shortly."];
    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return ['success' => false, 'message' => 'Failed to create ticket: ' . $e->getMessage()];
    }
}

/**
 * Add a reply message to an existing support ticket
 * 
 * @param int $ticketId
 * @param string $senderType 'user' or 'admin'
 * @param int $senderId
 * @param string $senderName
 * @param string $message
 * @param string|null $newStatus
 * @return array
 */
function addSupportTicketReply(int $ticketId, string $senderType, int $senderId, string $senderName, string $message, ?string $newStatus = null): array {
    $db = getDb();
    if (!$db) return ['success' => false, 'message' => 'Database connection failed.'];

    $msg = trim($message);
    if (empty($msg)) {
        return ['success' => false, 'message' => 'Reply message cannot be empty.'];
    }

    try {
        $db->beginTransaction();

        $mStmt = $db->prepare("
            INSERT INTO support_messages (ticket_id, sender_type, sender_id, sender_name, message, created_at)
            VALUES (:tid, :stype, :sid, :sname, :msg, NOW())
        ");
        $mStmt->execute([
            'tid'   => $ticketId,
            'stype' => $senderType,
            'sid'   => $senderId,
            'sname' => $senderName,
            'msg'   => $msg
        ]);

        // Update ticket updated_at and optionally status
        if ($newStatus && in_array($newStatus, ['open', 'in_progress', 'resolved', 'closed'], true)) {
            $uStmt = $db->prepare("UPDATE support_tickets SET status = :st, updated_at = NOW() WHERE id = :tid");
            $uStmt->execute(['st' => $newStatus, 'tid' => $ticketId]);
        } else {
            // Default status change if admin replied
            $nextStatus = ($senderType === 'admin') ? 'in_progress' : 'open';
            $uStmt = $db->prepare("UPDATE support_tickets SET status = :st, updated_at = NOW() WHERE id = :tid");
            $uStmt->execute(['st' => $nextStatus, 'tid' => $ticketId]);
        }

        // If admin responded, notify user
        if ($senderType === 'admin') {
            $tStmt = $db->prepare("SELECT user_id, ticket_no FROM support_tickets WHERE id = :tid");
            $tStmt->execute(['tid' => $ticketId]);
            $ticket = $tStmt->fetch(PDO::FETCH_ASSOC);
            if ($ticket) {
                $notif = $db->prepare("INSERT INTO notifications (user_id, title, message, is_read, created_at) VALUES (:uid, 'New Support Reply', :msg, 0, NOW())");
                $notif->execute([
                    'uid' => $ticket['user_id'],
                    'msg' => "Admin replied to ticket #{$ticket['ticket_no']}: \"" . substr($msg, 0, 80) . "...\""
                ]);
            }
        }

        $db->commit();
        return ['success' => true, 'message' => 'Reply posted successfully.'];
    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return ['success' => false, 'message' => 'Failed to add reply: ' . $e->getMessage()];
    }
}

/**
 * Fetch support tickets list with message counts
 * 
 * @param int $userId 0 for all (admin), >0 for specific member
 * @param string $status 'all', 'open', 'in_progress', 'resolved', 'closed'
 * @param int $limit
 * @param int $offset
 * @return array ['records' => array, 'total' => int]
 */
function getSupportTicketsList(int $userId = 0, string $status = 'all', int $limit = 20, int $offset = 0): array {
    $db = getDb();
    if (!$db) return ['records' => [], 'total' => 0];

    $where = ["1=1"];
    $params = [];

    if ($userId > 0) {
        $where[] = "t.user_id = :uid";
        $params['uid'] = $userId;
    }

    if ($status !== 'all' && in_array($status, ['open', 'in_progress', 'resolved', 'closed'], true)) {
        $where[] = "t.status = :st";
        $params['st'] = $status;
    }

    $whereSql = implode(" AND ", $where);

    try {
        $cStmt = $db->prepare("SELECT COUNT(*) FROM support_tickets t WHERE {$whereSql}");
        $cStmt->execute($params);
        $total = (int)$cStmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT 
                t.*,
                u.username,
                u.full_name,
                u.mobile,
                (SELECT COUNT(*) FROM support_messages WHERE ticket_id = t.id) as message_count,
                (SELECT created_at FROM support_messages WHERE ticket_id = t.id ORDER BY id DESC LIMIT 1) as latest_message_at
            FROM support_tickets t
            JOIN users u ON t.user_id = u.id
            WHERE {$whereSql}
            ORDER BY 
                CASE 
                    WHEN t.status = 'open' THEN 1 
                    WHEN t.status = 'in_progress' THEN 2 
                    ELSE 3 
                END,
                t.updated_at DESC
            LIMIT :offset, :limit
        ");

        foreach ($params as $k => $v) {
            $stmt->bindValue(":{$k}", $v);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'records' => $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'total'   => $total
        ];
    } catch (PDOException $e) {
        return ['records' => [], 'total' => 0];
    }
}

/**
 * Get ticket details and conversation thread messages
 * 
 * @param int $ticketId
 * @param int $userId Optional security check (if user is fetching their own)
 * @return array|null ['ticket' => array, 'messages' => array]
 */
function getSupportTicketThread(int $ticketId, int $userId = 0): ?array {
    $db = getDb();
    if (!$db) return null;

    try {
        $sql = "
            SELECT t.*, u.username, u.full_name, u.email, u.mobile, u.current_level
            FROM support_tickets t
            JOIN users u ON t.user_id = u.id
            WHERE t.id = :tid
        ";
        if ($userId > 0) {
            $sql .= " AND t.user_id = :uid";
        }
        $sql .= " LIMIT 1";

        $stmt = $db->prepare($sql);
        $params = ['tid' => $ticketId];
        if ($userId > 0) $params['uid'] = $userId;
        $stmt->execute($params);

        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ticket) return null;

        // Fetch thread messages
        $mStmt = $db->prepare("
            SELECT * FROM support_messages 
            WHERE ticket_id = :tid 
            ORDER BY id ASC
        ");
        $mStmt->execute(['tid' => $ticketId]);
        $messages = $mStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'ticket'   => $ticket,
            'messages' => $messages
        ];
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Count open support tickets for admin badge
 * 
 * @return int
 */
function getOpenSupportTicketsCount(): int {
    $db = getDb();
    if (!$db) return 0;
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM support_tickets WHERE status IN ('open', 'in_progress')");
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}



<?php
/**
 * Social Service Dream - Official Income Calculation Engine
 * =========================================================================
 * PART 5: CORE INCOME CALCULATION & DISTRIBUTION ENGINE
 * File Target: C:\xampp\htdocs\ssdream\includes\income-engine.php
 *
 * 3 LOCKED BUSINESS RULES (NO MODIFICATION, NO INVENTED COMMISSIONS):
 * Rule 1: Direct Income = 100% (On Level 1 referral entry ₹1,200)
 * Rule 2: Level Income = 50% (Level 2 -> Level 7; USER MUST BE UPGRADED TO THAT LEVEL)
 * Rule 3: Sponsor Income = 50% (Level 2 -> Unlimited Level, to direct sponsor)
 * =========================================================================
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';

/**
 * Helper to ensure an open PDO connection
 */
function getIncomeEngineDb(?PDO $db = null): PDO {
    if ($db instanceof PDO) {
        return $db;
    }
    return getDbConnection();
}

/**
 * Fetch package details for a specific level from packages table
 */
function getPackageByLevel(int $level, ?PDO $db = null): ?array {
    $pdo = getIncomeEngineDb($db);
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE level = :lvl LIMIT 1");
    $stmt->execute(['lvl' => $level]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * -------------------------------------------------------------------------
 * RULE 1: DIRECT INCOME (100%)
 * -------------------------------------------------------------------------
 * Triggered on Level 1 registration / activation.
 * Base Amount: Level 1 package price (₹1,200).
 * Payout: 100% = ₹1,200 to the direct sponsor.
 *
 * @param int $newUserId Newly registered user ID
 * @param PDO|null $db Optional active transaction connection
 * @return int|null Created income ID or null if skipped/failed
 */
function calculateDirectIncome(int $newUserId, ?PDO $db = null): ?int {
    $pdo = getIncomeEngineDb($db);

    // Fetch user and sponsor info
    $stmt = $pdo->prepare("SELECT id, username, sponsor_id, current_level, created_at FROM users WHERE id = :uid LIMIT 1");
    $stmt->execute(['uid' => $newUserId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || empty($user['sponsor_id'])) {
        return null; // No direct sponsor or invalid user
    }

    $sponsorId = (int)$user['sponsor_id'];

    // Verify sponsor exists and is active
    $sStmt = $pdo->prepare("SELECT id, username, status FROM users WHERE id = :sid LIMIT 1");
    $sStmt->execute(['sid' => $sponsorId]);
    $sponsor = $sStmt->fetch(PDO::FETCH_ASSOC);

    if (!$sponsor || $sponsor['status'] !== 'active') {
        return null;
    }

    // Level 1 package amount
    $pkg = getPackageByLevel(1, $pdo);
    $packageAmount = $pkg ? (float)$pkg['price'] : 1200.00;
    $percentage = 100.00;
    $incomeAmount = $packageAmount * ($percentage / 100.00); // 100% = ₹1,200

    // Duplicate check: ensure no direct income already generated from this user
    $dupStmt = $pdo->prepare("
        SELECT id FROM incomes 
        WHERE income_type = 'direct' 
          AND from_user_id = :from_uid 
          AND user_id = :to_uid 
        LIMIT 1
    ");
    $dupStmt->execute(['from_uid' => $newUserId, 'to_uid' => $sponsorId]);
    if ($dupStmt->fetch()) {
        return null; // Already credited
    }

    $txnRef = 'TXN-DIR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    // 1. Insert into incomes table
    $insStmt = $pdo->prepare("
        INSERT INTO incomes (user_id, income_type, amount, percentage, from_user_id, source_level, upgrade_id, transaction_ref, status, created_at)
        VALUES (:uid, 'direct', :amount, :pct, :from_uid, 1, NULL, :txn, 'credited', NOW())
    ");
    $insStmt->execute([
        'uid'      => $sponsorId,
        'amount'   => $incomeAmount,
        'pct'      => $percentage,
        'from_uid' => $newUserId,
        'txn'      => $txnRef
    ]);
    $incomeId = (int)$pdo->lastInsertId();

    // 2. Lock and Credit sponsor's wallet
    $curBalStmt = $pdo->prepare("SELECT id, balance, total_income FROM wallets WHERE user_id = :uid FOR UPDATE");
    $curBalStmt->execute(['uid' => $sponsorId]);
    $wallet = $curBalStmt->fetch(PDO::FETCH_ASSOC);
    if (!$wallet) {
        $insW = $pdo->prepare("INSERT INTO wallets (user_id, balance, total_income, direct_income, level_income, sponsor_income, total_withdrawn, created_at, updated_at) VALUES (:uid, 0, 0, 0, 0, 0, 0, NOW(), NOW())");
        $insW->execute(['uid' => $sponsorId]);
        $curBalStmt->execute(['uid' => $sponsorId]);
        $wallet = $curBalStmt->fetch(PDO::FETCH_ASSOC);
    }
    $walletId = (int)$wallet['id'];
    $balanceBefore = (float)$wallet['balance'];
    $balanceAfter = $balanceBefore + $incomeAmount;

    $wStmt = $pdo->prepare("
        UPDATE wallets 
        SET balance = :bal_after, 
            total_income = total_income + :amt, 
            direct_income = direct_income + :amt,
            total_credit = total_credit + :amt,
            updated_at = NOW() 
        WHERE id = :wid
    ");
    $wStmt->execute(['bal_after' => $balanceAfter, 'amt' => $incomeAmount, 'wid' => $walletId]);

    // 3. Record transaction ledger entry with balance before and after
    $tStmt = $pdo->prepare("
        INSERT INTO transactions (wallet_id, user_id, transaction_type, category, amount, balance_before, balance_after, reference_type, reference_id, reference_no, description, status, created_at)
        VALUES (:wid, :uid, 'credit', 'direct_income', :amt, :b_before, :b_after, 'income', :ref, :ref_no, :desc, 'completed', NOW())
    ");
    $tStmt->execute([
        'wid'      => $walletId,
        'uid'      => $sponsorId,
        'amt'      => $incomeAmount,
        'b_before' => $balanceBefore,
        'b_after'  => $balanceAfter,
        'ref'      => $txnRef,
        'ref_no'   => $txnRef,
        'desc'     => "100% Direct Income from user @" . $user['username'] . " (Level 1 Activation)"
    ]);

    // 4. Send internal notification
    $notifStmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, is_read, created_at)
        VALUES (:uid, 'Direct Income Received', :msg, 0, NOW())
    ");
    $notifStmt->execute([
        'uid' => $sponsorId,
        'msg' => "You have received ₹" . number_format($incomeAmount, 2) . " Direct Income (100%) from referral @" . $user['username'] . "."
    ]);

    return $incomeId;
}

/**
 * -------------------------------------------------------------------------
 * FIND BINARY ANCESTOR AT EXACT DISTANCE N
 * -------------------------------------------------------------------------
 * Level Income (50%) from Level N goes to the binary ancestor located exactly
 * N levels directly above the upgrading user in the 2x2 binary tree.
 *
 * Level 2 upgrade -> 2nd upline binary ancestor
 * Level 3 upgrade -> 3rd upline binary ancestor
 * ...
 * Level 7 upgrade -> 7th upline binary ancestor
 */
function getBinaryAncestorAtDistance(int $userId, int $distance, PDO $pdo): ?array {
    if ($distance <= 0) {
        return null;
    }

    $currentId = $userId;
    for ($i = 1; $i <= $distance; $i++) {
        $stmt = $pdo->prepare("SELECT parent_id FROM users WHERE id = :uid LIMIT 1");
        $stmt->execute(['uid' => $currentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['parent_id'])) {
            return null; // Tree ends before reaching distance
        }
        $currentId = (int)$row['parent_id'];
    }

    // Return the user at this exact distance
    $uStmt = $pdo->prepare("SELECT id, username, current_level, status, sponsor_id FROM users WHERE id = :uid LIMIT 1");
    $uStmt->execute(['uid' => $currentId]);
    $ancestor = $uStmt->fetch(PDO::FETCH_ASSOC);

    return $ancestor ?: null;
}

/**
 * -------------------------------------------------------------------------
 * PROCESS UPGRADE INCOME DISTRIBUTION (PART 5 MAIN ENGINE)
 * -------------------------------------------------------------------------
 * Executes when an upgrade to Level 2..7 is approved by Admin.
 *
 * Rules:
 * 1. Level Income (50%):
 *    - Destination: Binary ancestor at distance == $toLevel
 *    - CRITICAL VALIDATION: Ancestor's current_level MUST BE >= $toLevel!
 *      If ancestor has not upgraded to $toLevel, they are INELIGIBLE.
 *
 * 2. Sponsor Income (50%):
 *    - Destination: Direct sponsor (`sponsor_id`) of the upgrading member.
 *    - Applicable for Level 2 -> Unlimited Level.
 *
 * @param int $userId Upgrading member ID
 * @param int $toLevel Approved level (2 to 7)
 * @param int $upgradeId ID of the approved upgrade request
 * @param PDO|null $db Optional active transaction connection
 * @return array Results summary of distributions
 */
function processUpgradeIncomeDistribution(int $userId, int $toLevel, int $upgradeId, ?PDO $db = null): array {
    $pdo = getIncomeEngineDb($db);

    $results = [
        'upgrade_id'       => $upgradeId,
        'user_id'          => $userId,
        'level'            => $toLevel,
        'level_income'     => null,
        'sponsor_income'   => null,
        'messages'         => []
    ];

    // Only applicable for Level 2 through Level 7
    if ($toLevel < 2 || $toLevel > 7) {
        $results['messages'][] = "Level {$toLevel} is outside applicable upgrade income range (2-7).";
        return $results;
    }

    // Get package amount for this level
    $pkg = getPackageByLevel($toLevel, $pdo);
    if (!$pkg) {
        $results['messages'][] = "Package for level {$toLevel} not found.";
        return $results;
    }

    $packageAmount = (float)$pkg['price'];
    $fiftyPercent = $packageAmount * 0.50; // 50%

    // Get upgrading user details
    $uStmt = $pdo->prepare("SELECT id, username, sponsor_id FROM users WHERE id = :uid LIMIT 1");
    $uStmt->execute(['uid' => $userId]);
    $user = $uStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $results['messages'][] = "User {$userId} not found.";
        return $results;
    }

    // =========================================================================
    // 1. LEVEL INCOME (50%)
    // =========================================================================
    // Find binary ancestor at distance == $toLevel
    $ancestor = getBinaryAncestorAtDistance($userId, $toLevel, $pdo);

    if ($ancestor && $ancestor['status'] === 'active') {
        $ancestorId = (int)$ancestor['id'];
        $ancestorLevel = (int)$ancestor['current_level'];

        // CRITICAL BUSINESS RULE CHECK:
        // User must be upgraded to $toLevel or higher to be eligible for Level Income!
        if ($ancestorLevel >= $toLevel) {
            // Check duplicate
            $dupLvl = $pdo->prepare("
                SELECT id FROM incomes 
                WHERE upgrade_id = :upg AND income_type = 'level' AND user_id = :uid 
                LIMIT 1
            ");
            $dupLvl->execute(['upg' => $upgradeId, 'uid' => $ancestorId]);

            if (!$dupLvl->fetch()) {
                $lvlTxnRef = 'TXN-LVL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

                // Insert into incomes
                $inLvl = $pdo->prepare("
                    INSERT INTO incomes (user_id, income_type, amount, percentage, from_user_id, source_level, upgrade_id, transaction_ref, status, created_at)
                    VALUES (:uid, 'level', :amt, 50.00, :from_uid, :lvl, :upg, :txn, 'credited', NOW())
                ");
                $inLvl->execute([
                    'uid'      => $ancestorId,
                    'amt'      => $fiftyPercent,
                    'from_uid' => $userId,
                    'lvl'      => $toLevel,
                    'upg'      => $upgradeId,
                    'txn'      => $lvlTxnRef
                ]);
                $lvlIncId = (int)$pdo->lastInsertId();

                // Lock and Credit ancestor wallet
                $wLock = $pdo->prepare("SELECT id, balance, total_income FROM wallets WHERE user_id = :uid FOR UPDATE");
                $wLock->execute(['uid' => $ancestorId]);
                $wal = $wLock->fetch(PDO::FETCH_ASSOC);
                if (!$wal) {
                    $insW = $pdo->prepare("INSERT INTO wallets (user_id, balance, total_income, direct_income, level_income, sponsor_income, total_withdrawn, created_at, updated_at) VALUES (:uid, 0, 0, 0, 0, 0, 0, NOW(), NOW())");
                    $insW->execute(['uid' => $ancestorId]);
                    $wLock->execute(['uid' => $ancestorId]);
                    $wal = $wLock->fetch(PDO::FETCH_ASSOC);
                }
                $walletId = (int)$wal['id'];
                $balanceBefore = (float)$wal['balance'];
                $balanceAfter = $balanceBefore + $fiftyPercent;

                $wUpd = $pdo->prepare("
                    UPDATE wallets 
                    SET balance = :bal_after, 
                        total_income = total_income + :amt, 
                        level_income = level_income + :amt, 
                        total_credit = total_credit + :amt,
                        updated_at = NOW() 
                    WHERE id = :wid
                ");
                $wUpd->execute(['bal_after' => $balanceAfter, 'amt' => $fiftyPercent, 'wid' => $walletId]);

                // Transaction log with balance before and after
                $tIns = $pdo->prepare("
                    INSERT INTO transactions (wallet_id, user_id, transaction_type, category, amount, balance_before, balance_after, reference_type, reference_id, reference_no, description, status, created_at)
                    VALUES (:wid, :uid, 'credit', 'level_income', :amt, :b_before, :b_after, 'income', :ref, :ref_no, :desc, 'completed', NOW())
                ");
                $tIns->execute([
                    'wid'      => $walletId,
                    'uid'      => $ancestorId,
                    'amt'      => $fiftyPercent,
                    'b_before' => $balanceBefore,
                    'b_after'  => $balanceAfter,
                    'ref'      => $lvlTxnRef,
                    'ref_no'   => $lvlTxnRef,
                    'desc'     => "50% Level {$toLevel} Income from @" . $user['username'] . " (Package ₹" . number_format($packageAmount, 2) . ")"
                ]);

                // Notification
                $notif = $pdo->prepare("
                    INSERT INTO notifications (user_id, title, message, is_read, created_at)
                    VALUES (:uid, 'Level Income Received', :msg, 0, NOW())
                ");
                $notif->execute([
                    'uid' => $ancestorId,
                    'msg' => "Congratulations! You received ₹" . number_format($fiftyPercent, 2) . " Level {$toLevel} Income (50%) from downline member @" . $user['username'] . "."
                ]);

                $results['level_income'] = [
                    'income_id' => $lvlIncId,
                    'user_id'   => $ancestorId,
                    'username'  => $ancestor['username'],
                    'amount'    => $fiftyPercent
                ];
                $results['messages'][] = "Level {$toLevel} Income credited to ancestor @{$ancestor['username']}.";
            } else {
                $results['messages'][] = "Level {$toLevel} Income for upgrade #{$upgradeId} already credited.";
            }
        } else {
            // Ancestor not eligible because they have not upgraded to $toLevel!
            $results['messages'][] = "Binary ancestor @{$ancestor['username']} on Level {$ancestorLevel} is NOT eligible for Level {$toLevel} income. Member must upgrade to Level {$toLevel} first.";
        }
    } else {
        $results['messages'][] = "No active binary ancestor at depth {$toLevel} found.";
    }

    // =========================================================================
    // 2. SPONSOR INCOME (50%)
    // =========================================================================
    // Goes to the DIRECT SPONSOR of the upgrading member
    if (!empty($user['sponsor_id'])) {
        $sponsorId = (int)$user['sponsor_id'];

        $spStmt = $pdo->prepare("SELECT id, username, status FROM users WHERE id = :sid LIMIT 1");
        $spStmt->execute(['sid' => $sponsorId]);
        $sponsor = $spStmt->fetch(PDO::FETCH_ASSOC);

        if ($sponsor && $sponsor['status'] === 'active') {
            // Check duplicate
            $dupSpo = $pdo->prepare("
                SELECT id FROM incomes 
                WHERE upgrade_id = :upg AND income_type = 'sponsor' AND user_id = :uid 
                LIMIT 1
            ");
            $dupSpo->execute(['upg' => $upgradeId, 'uid' => $sponsorId]);

            if (!$dupSpo->fetch()) {
                $spoTxnRef = 'TXN-SPO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

                // Insert into incomes
                $inSpo = $pdo->prepare("
                    INSERT INTO incomes (user_id, income_type, amount, percentage, from_user_id, source_level, upgrade_id, transaction_ref, status, created_at)
                    VALUES (:uid, 'sponsor', :amt, 50.00, :from_uid, :lvl, :upg, :txn, 'credited', NOW())
                ");
                $inSpo->execute([
                    'uid'      => $sponsorId,
                    'amt'      => $fiftyPercent,
                    'from_uid' => $userId,
                    'lvl'      => $toLevel,
                    'upg'      => $upgradeId,
                    'txn'      => $spoTxnRef
                ]);
                $spoIncId = (int)$pdo->lastInsertId();

                // Lock and Credit sponsor wallet
                $wLock = $pdo->prepare("SELECT id, balance, total_income FROM wallets WHERE user_id = :uid FOR UPDATE");
                $wLock->execute(['uid' => $sponsorId]);
                $wal = $wLock->fetch(PDO::FETCH_ASSOC);
                if (!$wal) {
                    $insW = $pdo->prepare("INSERT INTO wallets (user_id, balance, total_income, direct_income, level_income, sponsor_income, total_withdrawn, created_at, updated_at) VALUES (:uid, 0, 0, 0, 0, 0, 0, NOW(), NOW())");
                    $insW->execute(['uid' => $sponsorId]);
                    $wLock->execute(['uid' => $sponsorId]);
                    $wal = $wLock->fetch(PDO::FETCH_ASSOC);
                }
                $walletId = (int)$wal['id'];
                $balanceBefore = (float)$wal['balance'];
                $balanceAfter = $balanceBefore + $fiftyPercent;

                $wSpd = $pdo->prepare("
                    UPDATE wallets 
                    SET balance = :bal_after, 
                        total_income = total_income + :amt, 
                        sponsor_income = sponsor_income + :amt, 
                        total_credit = total_credit + :amt,
                        updated_at = NOW() 
                    WHERE id = :wid
                ");
                $wSpd->execute(['bal_after' => $balanceAfter, 'amt' => $fiftyPercent, 'wid' => $walletId]);

                // Transaction log with balance before and after
                $tIns = $pdo->prepare("
                    INSERT INTO transactions (wallet_id, user_id, transaction_type, category, amount, balance_before, balance_after, reference_type, reference_id, reference_no, description, status, created_at)
                    VALUES (:wid, :uid, 'credit', 'sponsor_income', :amt, :b_before, :b_after, 'income', :ref, :ref_no, :desc, 'completed', NOW())
                ");
                $tIns->execute([
                    'wid'      => $walletId,
                    'uid'      => $sponsorId,
                    'amt'      => $fiftyPercent,
                    'b_before' => $balanceBefore,
                    'b_after'  => $balanceAfter,
                    'ref'      => $spoTxnRef,
                    'ref_no'   => $spoTxnRef,
                    'desc'     => "50% Sponsor Income from direct team member @" . $user['username'] . " (Upgraded to Level {$toLevel})"
                ]);

                // Notification
                $notif = $pdo->prepare("
                    INSERT INTO notifications (user_id, title, message, is_read, created_at)
                    VALUES (:uid, 'Sponsor Income Received', :msg, 0, NOW())
                ");
                $notif->execute([
                    'uid' => $sponsorId,
                    'msg' => "You have received ₹" . number_format($fiftyPercent, 2) . " Sponsor Income (50%) from your referral @" . $user['username'] . "'s Level {$toLevel} upgrade."
                ]);

                $results['sponsor_income'] = [
                    'income_id' => $spoIncId,
                    'user_id'   => $sponsorId,
                    'username'  => $sponsor['username'],
                    'amount'    => $fiftyPercent
                ];
                $results['messages'][] = "50% Sponsor Income credited to direct sponsor @{$sponsor['username']}.";
            } else {
                $results['messages'][] = "Sponsor Income for upgrade #{$upgradeId} already credited.";
            }
        }
    }

    return $results;
}

/**
 * -------------------------------------------------------------------------
 * USER INCOMES RETRIEVAL FUNCTIONS (FOR user/income.php)
 * -------------------------------------------------------------------------
 */
function getUserIncomeSummary(int $userId, ?PDO $db = null): array {
    $pdo = getIncomeEngineDb($db);

    $summary = [
        'total'   => 0.0,
        'direct'  => 0.0,
        'level'   => 0.0,
        'sponsor' => 0.0,
        'count'   => 0
    ];

    $stmt = $pdo->prepare("
        SELECT income_type, SUM(amount) as sum_amt, COUNT(*) as cnt 
        FROM incomes 
        WHERE user_id = :uid AND status = 'credited' 
        GROUP BY income_type
    ");
    $stmt->execute(['uid' => $userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        $amt = (float)$r['sum_amt'];
        $type = $r['income_type'];
        if (isset($summary[$type])) {
            $summary[$type] = $amt;
        }
        $summary['total'] += $amt;
        $summary['count'] += (int)$r['cnt'];
    }

    return $summary;
}

function getUserPaginatedIncomes(int $userId, int $page = 1, int $limit = 15, string $type = 'all', ?PDO $db = null): array {
    $pdo = getIncomeEngineDb($db);

    $allowedTypes = ['direct', 'level', 'sponsor'];
    $where = "WHERE i.user_id = :uid";
    $params = ['uid' => $userId];

    if (in_array($type, $allowedTypes, true)) {
        $where .= " AND i.income_type = :type";
        $params['type'] = $type;
    }

    // Count
    $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM incomes i {$where}");
    $cntStmt->execute($params);
    $totalRecords = (int)$cntStmt->fetchColumn();

    $totalPages = max(1, (int)ceil($totalRecords / $limit));
    $offset = max(0, ($page - 1) * $limit);

    // Records
    $sql = "
        SELECT i.*, 
               u.username as from_username, 
               u.full_name as from_name, 
               u.current_level as from_current_level,
               p.price as package_price
        FROM incomes i
        LEFT JOIN users u ON i.from_user_id = u.id
        LEFT JOIN packages p ON i.source_level = p.level
        {$where}
        ORDER BY i.id DESC
        LIMIT :lim OFFSET :off
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue(':' . $k, $v);
    }
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'records'       => $records,
        'total'         => $totalRecords,
        'page'          => $page,
        'total_pages'   => $totalPages
    ];
}

/**
 * -------------------------------------------------------------------------
 * ADMIN INCOMES RETRIEVAL FUNCTIONS (FOR admin/incomes.php)
 * -------------------------------------------------------------------------
 */
function getAdminIncomeMetrics(?PDO $db = null): array {
    $pdo = getIncomeEngineDb($db);

    $metrics = [
        'total_distributed' => 0.0,
        'total_direct'      => 0.0,
        'total_level'       => 0.0,
        'total_sponsor'     => 0.0,
        'total_count'       => 0
    ];

    $stmt = $pdo->query("
        SELECT income_type, SUM(amount) as sum_amt, COUNT(*) as cnt 
        FROM incomes 
        WHERE status = 'credited' 
        GROUP BY income_type
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        $amt = (float)$r['sum_amt'];
        $type = $r['income_type'];
        if ($type === 'direct') $metrics['total_direct'] = $amt;
        if ($type === 'level') $metrics['total_level'] = $amt;
        if ($type === 'sponsor') $metrics['total_sponsor'] = $amt;
        $metrics['total_distributed'] += $amt;
        $metrics['total_count'] += (int)$r['cnt'];
    }

    return $metrics;
}

function getAdminPaginatedIncomes(int $page = 1, int $limit = 20, string $type = 'all', string $search = '', ?PDO $db = null): array {
    $pdo = getIncomeEngineDb($db);

    $where = "WHERE 1=1";
    $params = [];

    if (in_array($type, ['direct', 'level', 'sponsor'], true)) {
        $where .= " AND i.income_type = :type";
        $params['type'] = $type;
    }

    if (!empty($search)) {
        $where .= " AND (u_to.username LIKE :s OR u_from.username LIKE :s OR i.transaction_ref LIKE :s)";
        $params['s'] = '%' . $search . '%';
    }

    $cntStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM incomes i
        LEFT JOIN users u_to ON i.user_id = u_to.id
        LEFT JOIN users u_from ON i.from_user_id = u_from.id
        {$where}
    ");
    $cntStmt->execute($params);
    $totalRecords = (int)$cntStmt->fetchColumn();

    $totalPages = max(1, (int)ceil($totalRecords / $limit));
    $offset = max(0, ($page - 1) * $limit);

    $sql = "
        SELECT i.*, 
               u_to.username as to_username,
               u_to.full_name as to_name,
               u_from.username as from_username,
               u_from.full_name as from_name,
               p.price as package_price
        FROM incomes i
        LEFT JOIN users u_to ON i.user_id = u_to.id
        LEFT JOIN users u_from ON i.from_user_id = u_from.id
        LEFT JOIN packages p ON i.source_level = p.level
        {$where}
        ORDER BY i.id DESC
        LIMIT :lim OFFSET :off
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue(':' . $k, $v);
    }
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'records'     => $records,
        'total'       => $totalRecords,
        'page'        => $page,
        'total_pages' => $totalPages
    ];
}

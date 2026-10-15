<?php
/**
 * Social Service Dream - User Sidebar Navigation
 * Target: C:\xampp\htdocs\ssdream\user\includes\sidebar.php
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$unreadNotifs = getUnreadNotificationCount((int)($_SESSION['user_id'] ?? 0));
?>
<aside class="user-sidebar d-none d-md-flex">
    <div class="user-sidebar-brand d-flex align-items-center gap-2">
        <span class="navbar-brand-badge">
            <i class="bi bi-shield-check me-1"></i> SS DREAM
        </span>
        <span class="text-white fw-bold small">Member Panel</span>
    </div>

    <!-- Active User Brief -->
    <div class="p-3 border-bottom border-secondary border-opacity-25 bg-black bg-opacity-20">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-primary bg-opacity-20 text-primary p-2 d-flex align-items-center justify-center">
                <i class="bi bi-person-fill fs-5"></i>
            </div>
            <div class="overflow-hidden">
                <div class="fw-semibold text-white text-truncate small"><?php echo e($_SESSION['user_fullname'] ?? 'Member'); ?></div>
                <div class="badge bg-info-subtle text-info font-monospace text-xxs">@<?php echo e($_SESSION['user_username'] ?? 'user'); ?></div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu (Strict to Part 2 Requirements) -->
    <nav class="mt-2 flex-grow-1 overflow-y-auto">
        <!-- 1. Dashboard -->
        <a href="<?php echo BASE_URL; ?>/user/dashboard.php" class="user-nav-link <?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <!-- 2. My Profile -->
        <a href="<?php echo BASE_URL; ?>/user/profile.php" class="user-nav-link <?php echo ($currentPage === 'profile.php') ? 'active' : ''; ?>">
            <i class="bi bi-person-badge"></i> My Profile
        </a>

        <!-- 3. My Team (Activated in Part 4) -->
        <a href="<?php echo BASE_URL; ?>/user/team.php" class="user-nav-link <?php echo ($currentPage === 'team.php') ? 'active' : ''; ?>">
            <i class="bi bi-people"></i> My Team
        </a>

        <!-- 4. Binary Tree (Activated in Part 4) -->
        <a href="<?php echo BASE_URL; ?>/user/binary-tree.php" class="user-nav-link <?php echo ($currentPage === 'binary-tree.php') ? 'active' : ''; ?>">
            <i class="bi bi-diagram-3"></i> Binary Tree
        </a>

        <!-- Sponsor Details (Part 4) -->
        <a href="<?php echo BASE_URL; ?>/user/sponsor.php" class="user-nav-link <?php echo ($currentPage === 'sponsor.php') ? 'active' : ''; ?>">
            <i class="bi bi-person-check"></i> My Sponsor
        </a>

        <!-- 5. Packages & Upgrades (Activated in Part 3) -->
        <a href="<?php echo BASE_URL; ?>/user/packages.php" class="user-nav-link <?php echo ($currentPage === 'packages.php') ? 'active' : ''; ?>">
            <i class="bi bi-layers"></i> Packages & Upgrade
        </a>
        <a href="<?php echo BASE_URL; ?>/user/upgrade-history.php" class="user-nav-link <?php echo ($currentPage === 'upgrade-history.php') ? 'active' : ''; ?>">
            <i class="bi bi-clock-history"></i> Upgrade History
        </a>

        <!-- 6. Income (Activated in Part 5) -->
        <a href="<?php echo BASE_URL; ?>/user/income.php" class="user-nav-link <?php echo ($currentPage === 'income.php') ? 'active' : ''; ?>">
            <i class="bi bi-graph-up-arrow"></i> Income &amp; Earnings
        </a>

        <!-- 7. Wallet -->
        <a href="<?php echo BASE_URL; ?>/user/wallet.php" class="user-nav-link <?php echo ($currentPage === 'wallet.php') ? 'active' : ''; ?>">
            <i class="bi bi-wallet2"></i> Wallet
        </a>

        <!-- 8. Transactions -->
        <a href="<?php echo BASE_URL; ?>/user/transactions.php" class="user-nav-link <?php echo ($currentPage === 'transactions.php') ? 'active' : ''; ?>">
            <i class="bi bi-receipt"></i> Transactions
        </a>

        <!-- 9. Withdraw (Activated in Part 6) -->
        <a href="<?php echo BASE_URL; ?>/user/withdraw.php" class="user-nav-link <?php echo ($currentPage === 'withdraw.php' || $currentPage === 'withdrawals.php') ? 'active' : ''; ?>">
            <i class="bi bi-cash-stack"></i> Withdrawals
        </a>

        <!-- 10. KYC -->
        <a href="<?php echo BASE_URL; ?>/user/kyc.php" class="user-nav-link <?php echo ($currentPage === 'kyc.php') ? 'active' : ''; ?>">
            <i class="bi bi-card-checklist"></i> KYC
        </a>

        <!-- 11. Support (Activated in Part 7) -->
        <a href="<?php echo BASE_URL; ?>/user/support.php" class="user-nav-link <?php echo ($currentPage === 'support.php') ? 'active' : ''; ?>">
            <i class="bi bi-headset"></i> Support
        </a>

        <!-- 12. Notifications (Activated in Part 7) -->
        <a href="<?php echo BASE_URL; ?>/user/notifications.php" class="user-nav-link <?php echo ($currentPage === 'notifications.php') ? 'active' : ''; ?>">
            <i class="bi bi-bell"></i> Notifications
            <?php if ($unreadNotifs > 0): ?>
                <span class="badge bg-danger ms-auto rounded-pill"><?php echo $unreadNotifs; ?></span>
            <?php endif; ?>
        </a>

        <!-- 13. Logout -->
        <a href="<?php echo BASE_URL; ?>/logout.php" class="user-nav-link text-danger mt-2 border-top border-secondary border-opacity-25">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>

    <div class="p-3 border-top border-secondary border-opacity-25 bg-black bg-opacity-30 text-center">
        <span class="text-white-50 text-xxs">Social Service Dream &bull; v1.0.0</span>
    </div>
</aside>

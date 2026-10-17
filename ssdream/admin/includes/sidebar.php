<?php
/**
 * Social Service Dream - Admin Sidebar Navigation
 */
require_once dirname(__DIR__, 2) . '/config/config.php';
$currentAdminPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<aside class="admin-sidebar d-none d-md-flex">
    <div class="admin-sidebar-brand d-flex align-items-center gap-2">
        <span class="navbar-brand-badge bg-danger text-white">
            <i class="bi bi-shield-lock-fill me-1"></i> ADMIN
        </span>
        <span class="text-white fw-bold small">SS Dream Console</span>
    </div>

    <div class="p-3 border-bottom border-secondary border-opacity-25">
        <div class="small text-white-50">Authorized Operator:</div>
        <div class="fw-semibold text-white"><?php echo e($_SESSION['admin_name'] ?? 'Administrator'); ?></div>
        <div class="badge bg-danger-subtle text-danger mt-1">Super Admin</div>
    </div>

    <nav class="mt-3 flex-grow-1">
        <a href="<?php echo BASE_URL; ?>/admin/index.php" class="admin-nav-link <?php echo ($currentAdminPage === 'index.php') ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i> Overview
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/packages.php" class="admin-nav-link <?php echo ($currentAdminPage === 'packages.php') ? 'active' : ''; ?>">
            <i class="bi bi-layers"></i> Packages & Levels
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/upgrades.php" class="admin-nav-link <?php echo ($currentAdminPage === 'upgrades.php') ? 'active' : ''; ?>">
            <i class="bi bi-arrow-up-square"></i> Upgrade Requests
            <?php 
                $pendingUpgradeCount = getPendingUpgradeCount();
                if ($pendingUpgradeCount > 0): 
            ?>
                <span class="badge bg-warning text-dark ms-auto"><?php echo $pendingUpgradeCount; ?></span>
            <?php endif; ?>
        </a>
        <!-- Manage Members (Activated in Part 7) -->
        <a href="<?php echo BASE_URL; ?>/admin/members.php" class="admin-nav-link <?php echo ($currentAdminPage === 'members.php' || $currentAdminPage === 'users.php') ? 'active' : ''; ?>">
            <i class="bi bi-people"></i> Manage Members
        </a>
        <!-- Binary Network (Part 4) -->
        <a href="<?php echo BASE_URL; ?>/admin/tree.php" class="admin-nav-link <?php echo ($currentAdminPage === 'tree.php') ? 'active' : ''; ?>">
            <i class="bi bi-diagram-3"></i> Binary Network
        </a>
        <!-- Income Engine & Distribution Ledger (Part 5) -->
        <a href="<?php echo BASE_URL; ?>/admin/incomes.php" class="admin-nav-link <?php echo ($currentAdminPage === 'incomes.php') ? 'active' : ''; ?>">
            <i class="bi bi-graph-up-arrow"></i> Income Engine &amp; Ledger
        </a>
        <!-- Transactions Ledger (Part 6) -->
        <a href="<?php echo BASE_URL; ?>/admin/transactions.php" class="admin-nav-link <?php echo ($currentAdminPage === 'transactions.php') ? 'active' : ''; ?>">
            <i class="bi bi-receipt"></i> Transactions Ledger
        </a>

        <!-- Withdrawals & Payouts (Part 6) -->
        <a href="<?php echo BASE_URL; ?>/admin/withdrawals.php" class="admin-nav-link <?php echo ($currentAdminPage === 'withdrawals.php') ? 'active' : ''; ?>">
            <i class="bi bi-cash-stack"></i> Withdrawals
            <?php 
                $pendingWithdrawCount = getPendingWithdrawalsCount();
                if ($pendingWithdrawCount > 0): 
            ?>
                <span class="badge bg-warning text-dark ms-auto"><?php echo $pendingWithdrawCount; ?></span>
            <?php endif; ?>
        </a>

        <!-- KYC Verification (Activated in Part 7) -->
        <a href="<?php echo BASE_URL; ?>/admin/kyc.php" class="admin-nav-link <?php echo ($currentAdminPage === 'kyc.php') ? 'active' : ''; ?>">
            <i class="bi bi-card-checklist"></i> KYC Verification
            <?php 
                $pendingKyc = getPendingKycCount();
                if ($pendingKyc > 0): 
            ?>
                <span class="badge bg-warning text-dark ms-auto"><?php echo $pendingKyc; ?></span>
            <?php endif; ?>
        </a>

        <!-- Support Tickets (Activated in Part 7) -->
        <a href="<?php echo BASE_URL; ?>/admin/support.php" class="admin-nav-link <?php echo ($currentAdminPage === 'support.php') ? 'active' : ''; ?>">
            <i class="bi bi-headset"></i> Support Tickets
            <?php 
                $openTkt = getOpenSupportTicketsCount();
                if ($openTkt > 0): 
            ?>
                <span class="badge bg-info text-dark ms-auto"><?php echo $openTkt; ?></span>
            <?php endif; ?>
        </a>

        <!-- Reports Portal (Activated in Part 8) -->
        <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="admin-nav-link <?php echo (strpos($currentAdminPage, 'report') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-collection"></i> Reports &amp; Analytics
        </a>

        <!-- Notifications Dispatcher (Activated in Part 8) -->
        <a href="<?php echo BASE_URL; ?>/admin/notifications.php" class="admin-nav-link <?php echo ($currentAdminPage === 'notifications.php') ? 'active' : ''; ?>">
            <i class="bi bi-bell"></i> Notifications
        </a>

        <!-- System Settings (Activated in Part 7) -->
        <a href="<?php echo BASE_URL; ?>/admin/settings.php" class="admin-nav-link <?php echo ($currentAdminPage === 'settings.php') ? 'active' : ''; ?>">
            <i class="bi bi-gear"></i> System Settings
        </a>

        <!-- Audit Logs (Activated in Part 8) -->
        <a href="<?php echo BASE_URL; ?>/admin/audit-logs.php" class="admin-nav-link <?php echo ($currentAdminPage === 'audit-logs.php') ? 'active' : ''; ?>">
            <i class="bi bi-shield-check"></i> Audit Logs
        </a>
    </nav>

    <div class="p-3 border-top border-secondary border-opacity-25">
        <a href="<?php echo BASE_URL; ?>/admin/login.php?action=logout" class="btn btn-outline-danger btn-sm w-100">
            <i class="bi bi-box-arrow-right me-1"></i> Sign Out
        </a>
    </div>
</aside>

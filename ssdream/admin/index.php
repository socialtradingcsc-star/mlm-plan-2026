<?php
/**
 * Social Service Dream - Administrator Control Dashboard
 * =========================================================================
 * PART 8: SYSTEM REPORTING, METRICS, NOTIFICATIONS & CONTROLS
 * Target: C:\xampp\htdocs\ssdream\admin\index.php
 * =========================================================================
 */

$pageTitle = "Admin Overview - Social Service Dream";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
Security::requireAdminAuth();

// Database connection check
$dbConnected = Database::isConnected();
$db = getDb();

// Calculate actual database summary metrics for Part 8 Section 11
$stats = [
    'total_users'         => 0,
    'active_users'        => 0,
    'blocked_users'       => 0,
    'pending_kyc'         => 0,
    'approved_kyc'        => 0,
    'pending_upgrades'    => 0,
    'pending_withdrawals' => 0,
    'total_income'        => 0.00,
    'total_withdrawn'     => 0.00
];

if ($db) {
    try {
        $stats['total_users']   = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['active_users']  = (int)$db->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
        $stats['blocked_users'] = (int)$db->query("SELECT COUNT(*) FROM users WHERE status = 'blocked'")->fetchColumn();

        $stats['pending_kyc']   = (int)$db->query("SELECT COUNT(*) FROM kyc WHERE status = 'pending'")->fetchColumn();
        $stats['approved_kyc']  = (int)$db->query("SELECT COUNT(*) FROM kyc WHERE status = 'approved'")->fetchColumn();

        $stats['pending_upgrades']    = (int)$db->query("SELECT COUNT(*) FROM upgrades WHERE status = 'pending'")->fetchColumn();
        $stats['pending_withdrawals'] = (int)$db->query("SELECT COUNT(*) FROM withdrawals WHERE status = 'pending'")->fetchColumn();

        $stats['total_income']    = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM incomes")->fetchColumn();
        $stats['total_withdrawn'] = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE status IN ('approved', 'paid')")->fetchColumn();
    } catch (PDOException $e) {
        // Fallback gracefully
    }
}

$packages = getPackages();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Welcome Banner -->
<div class="card bg-white border-0 shadow-sm mb-4">
    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill mb-2">
                Part 8 Complete Platform Live
            </span>
            <h3 class="fw-bold mb-1" style="color: #0b192c;">Platform Overview &amp; Control Desk</h3>
            <p class="text-muted small mb-0">
                Social Service Dream Administration &bull; 7-Level Binary Team Architecture
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($dbConnected): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                    <i class="bi bi-database-check me-1"></i> Database Live
                </span>
            <?php else: ?>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill">
                    <i class="bi bi-database-x me-1"></i> Database Offline
                </span>
            <?php endif; ?>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill">
                PHP <?php echo PHP_VERSION; ?>
            </span>
        </div>
    </div>
</div>

<!-- Pending Action Banner Alerts -->
<?php if ($stats['pending_upgrades'] > 0 || $stats['pending_withdrawals'] > 0 || $stats['pending_kyc'] > 0): ?>
    <div class="row g-3 mb-4">
        <?php if ($stats['pending_upgrades'] > 0): ?>
            <div class="col-md-4">
                <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-between p-3 mb-0 rounded-3" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-up-square fs-4 text-warning"></i>
                        <div>
                            <strong><?php echo $stats['pending_upgrades']; ?></strong> Pending Upgrade(s)
                        </div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/admin/upgrades.php?status=pending" class="btn btn-warning btn-sm text-dark fw-bold px-2 py-1">Review &rarr;</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($stats['pending_withdrawals'] > 0): ?>
            <div class="col-md-4">
                <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center justify-content-between p-3 mb-0 rounded-3" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-cash-stack fs-4 text-danger"></i>
                        <div>
                            <strong><?php echo $stats['pending_withdrawals']; ?></strong> Pending Payout(s)
                        </div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/admin/withdrawals.php?status=pending" class="btn btn-danger btn-sm text-white fw-bold px-2 py-1">Review &rarr;</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($stats['pending_kyc'] > 0): ?>
            <div class="col-md-4">
                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center justify-content-between p-3 mb-0 rounded-3" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-card-checklist fs-4 text-info"></i>
                        <div>
                            <strong><?php echo $stats['pending_kyc']; ?></strong> Pending KYC Review(s)
                        </div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/admin/kyc.php?status=pending" class="btn btn-info btn-sm text-dark fw-bold px-2 py-1">Review &rarr;</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- ====================================================
     SECTION 11 DASHBOARD SUMMARY CARDS (Actual DB Values)
==================================================== -->
<div class="row g-3 mb-4">
    <!-- 1. Total Users -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Total Users</span>
                <div class="admin-stat-icon icon-blue"><i class="bi bi-people-fill text-primary"></i></div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo number_format($stats['total_users']); ?></h2>
            <div class="text-muted small">Registered network members</div>
        </div>
    </div>

    <!-- 2. Active Users -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Active Users</span>
                <div class="admin-stat-icon icon-green"><i class="bi bi-person-check-fill text-success"></i></div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo number_format($stats['active_users']); ?></h2>
            <div class="text-success small">Activated network accounts</div>
        </div>
    </div>

    <!-- 3. Blocked Users -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Blocked Users</span>
                <div class="admin-stat-icon icon-amber"><i class="bi bi-person-x-fill text-danger"></i></div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo number_format($stats['blocked_users']); ?></h2>
            <div class="text-danger small">Suspended / blocked accounts</div>
        </div>
    </div>

    <!-- 4. Pending KYC -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Pending KYC</span>
                <div class="admin-stat-icon icon-amber"><i class="bi bi-card-checklist text-warning"></i></div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo number_format($stats['pending_kyc']); ?></h2>
            <div class="text-muted small">Documents awaiting review</div>
        </div>
    </div>

    <!-- 5. Approved KYC -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Approved KYC</span>
                <div class="admin-stat-icon icon-green"><i class="bi bi-shield-check text-success"></i></div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo number_format($stats['approved_kyc']); ?></h2>
            <div class="text-success small">Verified compliant accounts</div>
        </div>
    </div>

    <!-- 6. Pending Upgrades -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Pending Upgrades</span>
                <div class="admin-stat-icon icon-amber"><i class="bi bi-arrow-up-square text-warning"></i></div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo number_format($stats['pending_upgrades']); ?></h2>
            <div class="text-muted small">Level upgrade requests</div>
        </div>
    </div>

    <!-- 7. Pending Withdrawals -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Pending Withdrawals</span>
                <div class="admin-stat-icon icon-cyan"><i class="bi bi-hourglass-split text-danger"></i></div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo number_format($stats['pending_withdrawals']); ?></h2>
            <div class="text-muted small">Payouts awaiting approval</div>
        </div>
    </div>

    <!-- 8. Total Income Disbursed -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Total Income Disbursed</span>
                <div class="admin-stat-icon icon-blue"><i class="bi bi-graph-up text-primary"></i></div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo formatCurrency($stats['total_income'], true); ?></h2>
            <div class="text-muted small">Total earnings generated</div>
        </div>
    </div>

    <!-- 9. Total Withdrawn -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Total Withdrawn</span>
                <div class="admin-stat-icon icon-green"><i class="bi bi-cash-stack text-success"></i></div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo formatCurrency($stats['total_withdrawn'], true); ?></h2>
            <div class="text-muted small">Approved payout volume</div>
        </div>
    </div>
</div>

<!-- ====================================================
     QUICK LINKS NAVIGATION BAR
==================================================== -->
<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
            <i class="bi bi-lightning-charge-fill text-warning me-2"></i> Admin Action Desks &amp; Quick Shortcuts
        </h6>
    </div>
    <div class="card-body p-3">
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo BASE_URL; ?>/admin/members.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="bi bi-people me-1"></i> View Users
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/kyc.php" class="btn btn-outline-info btn-sm rounded-pill px-3">
                <i class="bi bi-card-checklist me-1"></i> Review KYC
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/upgrades.php" class="btn btn-outline-warning btn-sm text-dark rounded-pill px-3">
                <i class="bi bi-arrow-up-square me-1"></i> Review Upgrades
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/incomes.php" class="btn btn-outline-success btn-sm rounded-pill px-3">
                <i class="bi bi-graph-up-arrow me-1"></i> View Income
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/withdrawals.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                <i class="bi bi-cash-stack me-1"></i> Review Withdrawals
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-collection me-1"></i> System Reports
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/notifications.php" class="btn btn-dark btn-sm rounded-pill px-3">
                <i class="bi bi-bell me-1"></i> Notifications
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/support.php" class="btn btn-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-headset me-1"></i> Support Desk
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/audit-logs.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                <i class="bi bi-shield-check me-1"></i> Audit Logs
            </a>
        </div>
    </div>
</div>

<!-- ====================================================
     PRESERVED 7-LEVEL PACKAGE STRUCTURE
==================================================== -->
<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                <i class="bi bi-layers text-primary me-2"></i> Preserved 7-Level Package Structure
            </h6>
            <div class="text-muted text-xxs">LOCKED Rules: Direct 100% | Level 50% (L2-L7) | Sponsor 50% (L2-Unlimited)</div>
        </div>
        <a href="<?php echo BASE_URL; ?>/admin/packages.php" class="btn btn-outline-primary btn-sm rounded-pill">
            <i class="bi bi-layers me-1"></i> View Architecture
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Level</th>
                        <th>Package Name</th>
                        <th>Package Amount</th>
                        <th>Binary Team Size</th>
                        <th>Direct Income</th>
                        <th>Level Income</th>
                        <th>Sponsor Income</th>
                        <th class="pe-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $pkg): ?>
                    <tr>
                        <td class="ps-3 fw-bold text-primary">Level <?php echo (int)$pkg['level_number']; ?></td>
                        <td class="fw-semibold"><?php echo e($pkg['package_name']); ?></td>
                        <td class="fw-bold fs-6"><?php echo formatCurrency($pkg['price']); ?></td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                <?php echo (int)$pkg['team_size']; ?> Members
                            </span>
                        </td>
                        <td><?php echo (int)$pkg['level_number'] === 1 ? '<span class="badge bg-primary">100%</span>' : '<span class="text-muted">-</span>'; ?></td>
                        <td><?php echo (int)$pkg['level_number'] >= 2 ? '<span class="badge bg-info text-dark">50%</span>' : '<span class="text-muted">-</span>'; ?></td>
                        <td><?php echo (int)$pkg['level_number'] >= 2 ? '<span class="badge bg-success">50%</span>' : '<span class="text-muted">-</span>'; ?></td>
                        <td class="pe-3"><span class="badge bg-success-subtle text-success">Active</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

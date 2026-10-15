<?php
/**
 * Social Service Dream - Administrator Control Dashboard
 * Part 1 Foundation
 */

$pageTitle = "Admin Overview - Social Service Dream";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
Security::requireAdminAuth();

// Get stats for the 6 core metrics
$stats = getAdminStats();
$dbConnected = Database::isConnected();
$packages = getPackages();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Welcome Banner -->
<div class="card bg-white border-0 shadow-sm mb-4">
    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill mb-2">
                Part 1 Foundation Ready
            </span>
            <h3 class="fw-bold mb-1" style="color: #0b192c;">Platform Overview</h3>
            <p class="text-muted small mb-0">
                Social Service Dream Administration Console &bull; 7-Level Binary Team Architecture
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($dbConnected): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                    <i class="bi bi-database-check me-1"></i> Database Connected
                </span>
            <?php else: ?>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill">
                    <i class="bi bi-database-x me-1"></i> Database Disconnected
                </span>
            <?php endif; ?>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill">
                PHP <?php echo PHP_VERSION; ?>
            </span>
        </div>
    </div>
</div>

<?php if (!empty($stats['pending_upgrades']) && $stats['pending_upgrades'] > 0): ?>
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-between p-3 mb-4 rounded-3" role="alert">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-hourglass-split fs-3 text-warning"></i>
            <div>
                <strong>Action Required:</strong> You have <strong><?php echo $stats['pending_upgrades']; ?></strong> member package upgrade request(s) waiting for verification.
            </div>
        </div>
        <a href="<?php echo BASE_URL; ?>/admin/upgrades.php?status=pending" class="btn btn-warning btn-sm text-dark fw-bold px-3">
            Review Upgrades &rarr;
        </a>
    </div>
<?php endif; ?>

<!-- ====================================================
     THE ADMIN OVERVIEW CARDS
==================================================== -->
<div class="row g-3 mb-4">
    <!-- 1. Total Users -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Total Users</span>
                <div class="admin-stat-icon icon-blue">
                    <i class="bi bi-people-fill text-primary"></i>
                </div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo number_format($stats['total_users']); ?></h2>
            <div class="text-muted small d-flex align-items-center gap-1">
                <i class="bi bi-person-check text-primary"></i> Registered network members
            </div>
        </div>
    </div>

    <!-- 2. Active Users -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Active Users</span>
                <div class="admin-stat-icon icon-green">
                    <i class="bi bi-person-check-fill text-success"></i>
                </div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo number_format($stats['active_users']); ?></h2>
            <div class="text-success small d-flex align-items-center gap-1">
                <i class="bi bi-check-circle-fill"></i> Fully activated accounts
            </div>
        </div>
    </div>

    <!-- 3. Pending Users -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Pending Users</span>
                <div class="admin-stat-icon icon-amber">
                    <i class="bi bi-hourglass-split text-warning"></i>
                </div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo number_format($stats['pending_users']); ?></h2>
            <div class="text-warning small d-flex align-items-center gap-1">
                <i class="bi bi-clock"></i> Awaiting activation or payment
            </div>
        </div>
    </div>

    <!-- 4. Total Transactions -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Total Transactions</span>
                <div class="admin-stat-icon icon-cyan">
                    <i class="bi bi-receipt-cutoff text-info"></i>
                </div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo number_format($stats['total_transactions']); ?></h2>
            <div class="text-muted small d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left-right text-info"></i> Recorded credits & debits
            </div>
        </div>
    </div>

    <!-- 5. Total Withdrawals -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Total Withdrawals</span>
                <div class="admin-stat-icon icon-blue">
                    <i class="bi bi-cash-coin text-primary"></i>
                </div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo formatCurrency($stats['total_withdrawals'], true); ?></h2>
            <div class="text-muted small d-flex align-items-center gap-1">
                <i class="bi bi-check2-circle text-success"></i> Disbursed payout volume
            </div>
        </div>
    </div>

    <!-- 6. Pending KYC -->
    <div class="col-xl-4 col-md-6">
        <div class="admin-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Pending KYC</span>
                <div class="admin-stat-icon icon-amber">
                    <i class="bi bi-card-checklist text-warning"></i>
                </div>
            </div>
            <h2 class="fw-bold mb-1" style="color: #0b192c;"><?php echo number_format($stats['pending_kyc']); ?></h2>
            <div class="text-muted small d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-person text-secondary"></i> Submissions awaiting review
            </div>
        </div>
    </div>
</div>

<!-- ====================================================
     PLAN PACKAGES AUDIT TABLE (Preserved Source Figures)
==================================================== -->
<div class="card border shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                <i class="bi bi-layers text-primary me-2"></i> Preserved 7-Level Package Structure
            </h5>
            <div class="text-muted small">Official business plan figures locked into database</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle">
                7 Levels Defined
            </span>
            <a href="<?php echo BASE_URL; ?>/admin/packages.php" class="btn btn-outline-primary btn-sm rounded-pill">
                <i class="bi bi-layers me-1"></i> View Architecture
            </a>
        </div>
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

<?php
/**
 * Social Service Dream - Central Reports Hub
 * =========================================================================
 * PART 8: REPORTING SYSTEM & ANALYTICS DASHBOARD
 * Target: C:\xampp\htdocs\ssdream\admin\reports.php
 * =========================================================================
 */

$pageTitle = "Reports & Business Intelligence - SS Dream Admin";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication Guard
Security::requireAdminAuth();

// Fetch summary metrics
$userStats = getMembersSummaryStats();
$incomeStats = getReportIncomeSummary();
$pendingKyc = getPendingKycCount();
$pendingUpgrades = getPendingUpgradeCount();
$pendingWithdrawals = getPendingWithdrawalsCount();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div class="card border-0 shadow-sm mb-4 bg-white">
    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill">
                    <i class="bi bi-graph-up me-1"></i> Part 8 Reporting Hub
                </span>
            </div>
            <h3 class="fw-bold text-navy mb-1" style="color: #0b192c;">Reports &amp; System Intelligence</h3>
            <p class="text-muted small mb-0">Read-only audit reports, financial summaries, network stats and CSV export utility.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?php echo BASE_URL; ?>/admin/index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Dashboard Overview
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/audit-logs.php" class="btn btn-dark btn-sm rounded-pill px-3">
                <i class="bi bi-shield-check me-1"></i> Admin Audit Logs
            </a>
        </div>
    </div>
</div>

<!-- Financial Summary Banner Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">Total Direct Income</span>
                    <span class="badge bg-primary-subtle text-primary">100%</span>
                </div>
                <h3 class="fw-bold mb-1 text-primary"><?php echo formatCurrency($incomeStats['direct'], true); ?></h3>
                <div class="text-muted text-xxs">Direct referrals credited</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">Total Level Income</span>
                    <span class="badge bg-info-subtle text-info">50% L2–L7</span>
                </div>
                <h3 class="fw-bold mb-1 text-info"><?php echo formatCurrency($incomeStats['level'], true); ?></h3>
                <div class="text-muted text-xxs">Level 2 to Level 7 matrix earnings</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">Total Sponsor Income</span>
                    <span class="badge bg-success-subtle text-success">50% L2–Unlimited</span>
                </div>
                <h3 class="fw-bold mb-1 text-success"><?php echo formatCurrency($incomeStats['sponsor'], true); ?></h3>
                <div class="text-muted text-xxs">Sponsor level matching bonus</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">Grand Total Disbursed</span>
                    <span class="badge bg-dark text-white">Cumulative</span>
                </div>
                <h3 class="fw-bold mb-1 text-navy" style="color:#0b192c;"><?php echo formatCurrency($incomeStats['total'], true); ?></h3>
                <div class="text-muted text-xxs">Total network income generated</div>
            </div>
        </div>
    </div>
</div>

<!-- 8 Core Reports Navigation Cards -->
<h5 class="fw-bold mb-3 text-navy" style="color: #0b192c;">
    <i class="bi bi-collection me-2 text-primary"></i> Available System Reports
</h5>

<div class="row g-3 mb-4">
    <!-- 1. User Directory Report -->
    <div class="col-lg-4 col-md-6">
        <div class="card border shadow-sm h-100 card-hover bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary"><?php echo number_format($userStats['total']); ?> Members</span>
                    </div>
                    <h5 class="fw-bold mb-2">1. Users Directory Report</h5>
                    <p class="text-muted small mb-3">Full list of registered members, sponsors, current level status, and registration date filters.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/report-users.php" class="btn btn-primary btn-sm flex-grow-1 rounded-2">
                        <i class="bi bi-eye me-1"></i> View Report
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/export-users.php" class="btn btn-outline-success btn-sm rounded-2" title="Export CSV">
                        <i class="bi bi-download"></i> CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Income Earnings Report -->
    <div class="col-lg-4 col-md-6">
        <div class="card border shadow-sm h-100 card-hover bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success">
                            <i class="bi bi-cash-coin fs-4"></i>
                        </div>
                        <span class="badge bg-success-subtle text-success">Direct, Level, Sponsor</span>
                    </div>
                    <h5 class="fw-bold mb-2">2. Income Breakdown Report</h5>
                    <p class="text-muted small mb-3">Detailed earnings audit showing 100% Direct, 50% Level, and 50% Sponsor payouts.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/report-income.php" class="btn btn-primary btn-sm flex-grow-1 rounded-2">
                        <i class="bi bi-eye me-1"></i> View Report
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/export-income.php" class="btn btn-outline-success btn-sm rounded-2" title="Export CSV">
                        <i class="bi bi-download"></i> CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Sponsor Report -->
    <div class="col-lg-4 col-md-6">
        <div class="card border shadow-sm h-100 card-hover bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info">
                            <i class="bi bi-person-check fs-4"></i>
                        </div>
                        <span class="badge bg-info-subtle text-info">Sponsor Network</span>
                    </div>
                    <h5 class="fw-bold mb-2">3. Sponsor Performance Report</h5>
                    <p class="text-muted small mb-3">Analysis of top sponsors, direct referral totals, and active direct team metrics.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/report-sponsor.php" class="btn btn-primary btn-sm flex-grow-1 rounded-2">
                        <i class="bi bi-eye me-1"></i> View Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Team & Binary Hierarchy Report -->
    <div class="col-lg-4 col-md-6">
        <div class="card border shadow-sm h-100 card-hover bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-diagram-3 fs-4"></i>
                        </div>
                        <span class="badge bg-warning-subtle text-warning">2x2 Matrix</span>
                    </div>
                    <h5 class="fw-bold mb-2">4. Binary Team Structure Report</h5>
                    <p class="text-muted small mb-3">Detailed team hierarchy showing sponsor, parent, left/right positions, and levels.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/report-team.php" class="btn btn-primary btn-sm flex-grow-1 rounded-2">
                        <i class="bi bi-eye me-1"></i> View Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Package Upgrades Report -->
    <div class="col-lg-4 col-md-6">
        <div class="card border shadow-sm h-100 card-hover bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 p-3 bg-purple bg-opacity-10 text-purple" style="background: rgba(111,66,193,0.1); color:#6f42c1;">
                            <i class="bi bi-arrow-up-square fs-4"></i>
                        </div>
                        <?php if ($pendingUpgrades > 0): ?>
                            <span class="badge bg-warning text-dark"><?php echo $pendingUpgrades; ?> Pending</span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary">Upgrades</span>
                        <?php endif; ?>
                    </div>
                    <h5 class="fw-bold mb-2">5. Level Upgrades Report</h5>
                    <p class="text-muted small mb-3">Audit log of level progression requests, package amounts, and approval status.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/report-upgrades.php" class="btn btn-primary btn-sm flex-grow-1 rounded-2">
                        <i class="bi bi-eye me-1"></i> View Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Withdrawals & Payouts Report -->
    <div class="col-lg-4 col-md-6">
        <div class="card border shadow-sm h-100 card-hover bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                        <?php if ($pendingWithdrawals > 0): ?>
                            <span class="badge bg-danger text-white"><?php echo $pendingWithdrawals; ?> Pending</span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary">Payouts</span>
                        <?php endif; ?>
                    </div>
                    <h5 class="fw-bold mb-2">6. Withdrawals &amp; Payouts Report</h5>
                    <p class="text-muted small mb-3">Detailed record of requested payouts, 5% TDS, 5% Admin fee, net amount, and bank UTR.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/report-withdrawals.php" class="btn btn-primary btn-sm flex-grow-1 rounded-2">
                        <i class="bi bi-eye me-1"></i> View Report
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/export-withdrawals.php" class="btn btn-outline-success btn-sm rounded-2" title="Export CSV">
                        <i class="bi bi-download"></i> CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. KYC Verification Report -->
    <div class="col-lg-4 col-md-6">
        <div class="card border shadow-sm h-100 card-hover bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-card-checklist fs-4"></i>
                        </div>
                        <?php if ($pendingKyc > 0): ?>
                            <span class="badge bg-warning text-dark"><?php echo $pendingKyc; ?> Pending</span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary">KYC</span>
                        <?php endif; ?>
                    </div>
                    <h5 class="fw-bold mb-2">7. KYC Compliance Report</h5>
                    <p class="text-muted small mb-3">Compliance audit of member document verifications, review dates, and statuses.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/report-kyc.php" class="btn btn-primary btn-sm flex-grow-1 rounded-2">
                        <i class="bi bi-eye me-1"></i> View Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 8. Transactions Ledger Report -->
    <div class="col-lg-4 col-md-6">
        <div class="card border shadow-sm h-100 card-hover bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 p-3 bg-dark bg-opacity-10 text-dark">
                            <i class="bi bi-receipt fs-4"></i>
                        </div>
                        <span class="badge bg-dark text-white">Full Ledger</span>
                    </div>
                    <h5 class="fw-bold mb-2">8. Financial Transactions Ledger</h5>
                    <p class="text-muted small mb-3">Complete double-entry transaction history with before and after wallet balances.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/report-transactions.php" class="btn btn-primary btn-sm flex-grow-1 rounded-2">
                        <i class="bi bi-eye me-1"></i> View Report
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/export-transactions.php" class="btn btn-outline-success btn-sm rounded-2" title="Export CSV">
                        <i class="bi bi-download"></i> CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

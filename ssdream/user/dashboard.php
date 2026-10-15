<?php
/**
 * Social Service Dream - User Dashboard
 * Part 2: Complete User Authentication & Dashboard Foundation
 * Target: C:\xampp\htdocs\ssdream\user\dashboard.php
 */

$pageTitle = "Member Dashboard";
$pageHeading = "Dashboard Overview";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireUserLogin();

$userId = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_fullname'] ?? 'Member';
$userUsername = $_SESSION['user_username'] ?? 'user';

// Retrieve user data & metrics (Safe defaults: ₹0, 0 members, Level 1)
$userData = getUserDashboardData($userId);
$userKyc = getUserKyc($userId);
$referralLink = BASE_URL . "/register.php?ref=" . urlencode($userUsername);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Welcome Banner & Referral Link -->
<div class="referral-box mb-4 shadow-sm">
    <div class="row align-items-center g-3">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                    <i class="bi bi-patch-check-fill me-1"></i> Account Active
                </span>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 rounded-pill">
                    Level <?php echo (int)$userData['current_level']; ?> Member
                </span>
                <?php if ($userKyc && $userKyc['status'] === 'approved'): ?>
                    <span class="badge bg-success text-white px-2 py-1 rounded-pill small">KYC Verified</span>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/user/kyc.php" class="badge bg-warning text-dark text-decoration-none px-2 py-1 rounded-pill small">
                        <i class="bi bi-shield-exclamation me-1"></i> Complete KYC
                    </a>
                <?php endif; ?>
            </div>

            <h3 class="fw-bold mb-1 text-white">Welcome, <?php echo e($userName); ?>!</h3>
            <p class="text-white-50 small mb-3">
                Manage your binary team, track wallet balances, and share your personal referral link to introduce new partners to Social Service Dream.
            </p>

            <!-- Referral Link Sharing Box -->
            <div class="input-group input-group-sm" style="max-width: 580px;">
                <span class="input-group-text bg-dark border-secondary text-white-50">
                    <i class="bi bi-link-45deg"></i> Sponsor Link
                </span>
                <input type="text" class="form-control bg-dark text-white border-secondary font-monospace" id="ref-link-input" 
                       value="<?php echo e($referralLink); ?>" readonly>
                <button class="btn btn-primary" type="button" id="btn-copy-ref">
                    <i class="bi bi-clipboard me-1"></i> Copy Link
                </button>
            </div>
        </div>

        <div class="col-lg-4 text-lg-end">
            <div class="p-3 bg-white bg-opacity-10 rounded-3 text-center d-inline-block border border-white border-opacity-10 w-100" style="max-width: 280px;">
                <div class="small text-white-50 text-uppercase letter-spacing-1">Current Package</div>
                <div class="fs-4 fw-bold text-info">Level <?php echo (int)$userData['current_level']; ?></div>
                <div class="small text-white-50 mb-2">Team Required: <?php echo (int)(PLAN_PACKAGES[$userData['current_level']]['team_size'] ?? 2); ?></div>
                <a href="<?php echo BASE_URL; ?>/user/packages.php" class="btn btn-sm btn-info text-dark fw-semibold px-3 rounded-pill w-100">
                    <i class="bi bi-layers me-1"></i> <?php echo ((int)$userData['current_level'] < 7) ? 'Upgrade Package' : 'View Packages'; ?>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ====================================================
     THE 4 CORE DASHBOARD METRIC CARDS
==================================================== -->
<div class="row g-3 mb-4">
    <!-- Card 1: Wallet Balance -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Wallet Balance</span>
                <div class="stat-icon icon-blue">
                    <i class="bi bi-wallet2 text-primary"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1" style="color: #0b192c;"><?php echo formatCurrency($userData['wallet_balance'], true); ?></h3>
            <span class="text-muted small d-flex align-items-center gap-1">
                <i class="bi bi-arrow-down-circle text-primary"></i> Available for withdrawal
            </span>
        </div>
    </div>

    <!-- Card 2: Total Income -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Total Income</span>
                <div class="stat-icon icon-green">
                    <i class="bi bi-cash-stack text-success"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1" style="color: #0b192c;"><?php echo formatCurrency($userData['total_income'], true); ?></h3>
            <span class="text-success small d-flex align-items-center gap-1">
                <i class="bi bi-graph-up-arrow"></i> Cumulative earnings
            </span>
        </div>
    </div>

    <!-- Card 3: Team Members -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Team Members</span>
                <div class="stat-icon icon-cyan">
                    <i class="bi bi-people-fill text-info"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1" style="color: #0b192c;"><?php echo (int)$userData['team_members']; ?></h3>
            <span class="text-muted small d-flex align-items-center gap-1">
                <i class="bi bi-diagram-2 text-info"></i> Direct team referrals
            </span>
        </div>
    </div>

    <!-- Card 4: Current Level -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Current Level</span>
                <div class="stat-icon icon-amber">
                    <i class="bi bi-award-fill text-warning"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1" style="color: #0b192c;">Level <?php echo (int)$userData['current_level']; ?></h3>
            <span class="text-muted small d-flex align-items-center gap-1">
                <i class="bi bi-layers text-secondary"></i> Package: <?php echo formatCurrency(PLAN_PACKAGES[$userData['current_level']]['price'] ?? 1200); ?>
            </span>
        </div>
    </div>
</div>

<?php
$directTeamCount = getDirectTeamCount($userId);
$binaryTeamCount = getBinaryTeamCount($userId);
$userSponsor = getUserSponsor($userId);
?>

<!-- ====================================================
     PART 4: TEAM & SPONSOR SUMMARY SECTION
==================================================== -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-0" style="color: #0b192c;">
                <i class="bi bi-people-fill text-primary me-2"></i> Team &amp; Binary Network Summary
            </h6>
            <div class="text-muted text-xxs">Real-time dynamic network metrics strictly computed from database</div>
        </div>
        <a href="<?php echo BASE_URL; ?>/user/team.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            Manage Team &rarr;
        </a>
    </div>

    <div class="card-body p-4">
        <div class="row g-3 align-items-center">
            <!-- Metric 1: Direct Team -->
            <div class="col-md-3 col-6">
                <div class="p-3 bg-light rounded-3 border text-center">
                    <span class="text-muted small d-block mb-1 text-uppercase fw-semibold">Direct Team</span>
                    <div class="fs-4 fw-bold text-primary"><?php echo $directTeamCount; ?></div>
                    <span class="text-muted text-xxs">Personal Referrals</span>
                </div>
            </div>

            <!-- Metric 2: Binary Team -->
            <div class="col-md-3 col-6">
                <div class="p-3 bg-light rounded-3 border text-center">
                    <span class="text-muted small d-block mb-1 text-uppercase fw-semibold">Binary Team</span>
                    <div class="fs-4 fw-bold text-info"><?php echo $binaryTeamCount; ?></div>
                    <span class="text-muted text-xxs">2&times;2 Downline Matrix</span>
                </div>
            </div>

            <!-- Metric 3: Current Level -->
            <div class="col-md-3 col-6">
                <div class="p-3 bg-light rounded-3 border text-center">
                    <span class="text-muted small d-block mb-1 text-uppercase fw-semibold">Current Level</span>
                    <div class="fs-4 fw-bold text-warning">Level <?php echo (int)$userData['current_level']; ?></div>
                    <span class="text-muted text-xxs"><?php echo formatCurrency(PLAN_PACKAGES[$userData['current_level']]['price'] ?? 1200); ?> Package</span>
                </div>
            </div>

            <!-- Metric 4: Sponsor Info -->
            <div class="col-md-3 col-6">
                <div class="p-3 bg-light rounded-3 border text-center">
                    <span class="text-muted small d-block mb-1 text-uppercase fw-semibold">My Sponsor</span>
                    <?php if ($userSponsor): ?>
                        <div class="fw-bold text-dark text-truncate" title="<?php echo e($userSponsor['full_name']); ?>">
                            <?php echo e($userSponsor['full_name']); ?>
                        </div>
                        <span class="badge bg-light text-primary border font-monospace text-xxs">@<?php echo e($userSponsor['username']); ?></span>
                    <?php else: ?>
                        <div class="fw-semibold text-muted small">Root Member</div>
                        <span class="badge bg-light text-muted border text-xxs">No Sponsor</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ====================================================
     QUICK NAVIGATION SHORTCUTS (Team, Binary & Income)
==================================================== -->
<div class="row g-3 mb-4">
    <!-- Button 1: My Team -->
    <div class="col-lg col-md-4 col-6">
        <a href="<?php echo BASE_URL; ?>/user/team.php" class="card border p-3 text-decoration-none bg-white hover-shadow transition h-100">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark small">My Team</div>
                    <div class="text-muted text-xxs">Direct & Downline</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Button 2: Binary Tree -->
    <div class="col-lg col-md-4 col-6">
        <a href="<?php echo BASE_URL; ?>/user/binary-tree.php" class="card border p-3 text-decoration-none bg-white hover-shadow transition h-100">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-3 bg-info bg-opacity-10 text-info p-2">
                    <i class="bi bi-diagram-3-fill fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark small">Binary Tree</div>
                    <div class="text-muted text-xxs">Visual 2&times;2 Matrix</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Button 3: Income Center -->
    <div class="col-lg col-md-4 col-6">
        <a href="<?php echo BASE_URL; ?>/user/income.php" class="card border p-3 text-decoration-none bg-white hover-shadow transition h-100" style="border-left: 4px solid #198754 !important;">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-3 bg-success bg-opacity-10 text-success p-2">
                    <i class="bi bi-graph-up-arrow fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark small">Income Center</div>
                    <div class="text-muted text-xxs">3 Streams &bull; Live</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Button 4: My Sponsor -->
    <div class="col-lg col-md-4 col-6">
        <a href="<?php echo BASE_URL; ?>/user/sponsor.php" class="card border p-3 text-decoration-none bg-white hover-shadow transition h-100">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-3 bg-secondary bg-opacity-10 text-secondary p-2">
                    <i class="bi bi-person-check fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark small">My Sponsor</div>
                    <div class="text-muted text-xxs">Upline Partner</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Button 5: Upgrade Package -->
    <div class="col-lg col-md-4 col-6">
        <a href="<?php echo BASE_URL; ?>/user/packages.php" class="card border p-3 text-decoration-none bg-white hover-shadow transition h-100">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-2">
                    <i class="bi bi-layers fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark small">Upgrade</div>
                    <div class="text-muted text-xxs">Advance Package</div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- ====================================================
     RECENT TRANSACTIONS TABLE FOUNDATION
==================================================== -->
<div class="card border shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
            <i class="bi bi-clock-history me-2 text-primary"></i> Recent Transactions
        </h6>
        <a href="<?php echo BASE_URL; ?>/user/transactions.php" class="btn btn-outline-primary btn-sm py-1 px-2.5" style="font-size: 0.8rem;">
            View Full Ledger <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Reference No</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="pe-3">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($userData['recent_transactions'])): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <span class="fw-semibold text-secondary">No transactions yet</span>
                                <p class="text-muted small mb-0 mt-1">Transaction entries will appear here once financial activity is initiated.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($userData['recent_transactions'] as $tx): ?>
                            <tr>
                                <td class="ps-3 font-monospace small fw-bold text-dark"><?php echo e($tx['reference_no']); ?></td>
                                <td>
                                    <span class="badge bg-light text-secondary border">
                                        <?php echo e(ucwords(str_replace('_', ' ', $tx['category']))); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($tx['transaction_type'] === 'credit'): ?>
                                        <span class="text-success fw-bold small"><i class="bi bi-plus-circle me-1"></i>Credit</span>
                                    <?php else: ?>
                                        <span class="text-danger fw-bold small"><i class="bi bi-dash-circle me-1"></i>Debit</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?php echo formatCurrency($tx['amount'], true); ?></td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <?php echo e(ucfirst($tx['status'])); ?>
                                    </span>
                                </td>
                                <td class="pe-3 text-muted small"><?php echo date('d M Y, h:i A', strtotime($tx['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

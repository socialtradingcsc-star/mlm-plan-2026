<?php
/**
 * Social Service Dream - User Income Center & Ledger
 * =========================================================================
 * PART 5: Official SS Dream Income Stream Reports
 * Target: C:\xampp\htdocs\ssdream\user\income.php
 * =========================================================================
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/income-engine.php';

requireUserLogin();

$userId = (int)$_SESSION['user_id'];
$user = getUserById($userId);
if (!$user) {
    header('Location: ' . BASE_URL . '/logout.php');
    exit;
}

$currentLevel = (int)($user['current_level'] ?? 1);
$typeFilter = strtolower(trim($_GET['type'] ?? 'all'));
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 15;

$summary = getUserIncomeSummary($userId);
$ledgerData = getUserPaginatedIncomes($userId, $page, $limit, $typeFilter);
$incomes = $ledgerData['records'];
$totalPages = $ledgerData['total_pages'];
$totalCount = $ledgerData['total'];

$wallet = getUserWallet($userId);
$walletBalance = $wallet ? (float)$wallet['balance'] : 0.00;

$pageTitle = 'Income Center & Earnings Ledger';
include __DIR__ . '/includes/header.php';
?>

<div class="user-dashboard-wrapper">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="user-main-content">
        <!-- Breadcrumb & Title -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-xs">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/user/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Income Center</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold text-slate-800 mb-0">
                    <i class="bi bi-graph-up-arrow text-success me-2"></i>Income Center &amp; Earnings
                </h1>
                <p class="text-muted small mb-0">
                    Real-time breakdown of all earnings automatically credited across the 3 official SS Dream income streams.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?php echo BASE_URL; ?>/user/wallet.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-wallet2 me-1"></i> My Wallet (₹<?php echo number_format($walletBalance, 2); ?>)
                </a>
                <a href="<?php echo BASE_URL; ?>/user/packages.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-up-circle me-1"></i> Upgrade Level
                </a>
            </div>
        </div>

        <!-- Official Locked Business Rules Banner -->
        <div class="card border-0 shadow-xs rounded-3 mb-4 bg-light border-start border-4 border-warning">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-shield-lock text-warning fs-3 mt-1"></i>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-dark mb-1">Official SS Dream Income Rules &amp; Eligibility Status</h6>
                        <p class="text-muted small mb-2">
                            The system calculates and disburses exactly 3 official income streams in real-time upon verification:
                        </p>
                        <div class="row g-2 text-xs">
                            <div class="col-12 col-md-4">
                                <div class="p-2.5 rounded bg-white border border-slate-200">
                                    <div class="fw-bold text-success"><i class="bi bi-check-circle-fill me-1"></i>1. Direct Income: 100%</div>
                                    <span class="text-muted">On direct referral Level 1 activation (₹1,200).</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-2.5 rounded bg-white border border-slate-200">
                                    <div class="fw-bold text-primary"><i class="bi bi-layers-fill me-1"></i>2. Level Income: 50%</div>
                                    <span class="text-muted">Level 2 &rarr; Level 7 downline upgrades (Requires you to be on that level).</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-2.5 rounded bg-white border border-slate-200">
                                    <div class="fw-bold text-purple" style="color: #6f42c1;"><i class="bi bi-people-fill me-1"></i>3. Sponsor Income: 50%</div>
                                    <span class="text-muted">Level 2 &rarr; Unlimited Level on direct member upgrades.</span>
                                </div>
                            </div>
                        </div>

                        <!-- User's Progression Reminder -->
                        <div class="mt-3 p-2.5 rounded bg-white border border-warning-subtle d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="text-xs">
                                <strong>Your Current Active Tier:</strong> <span class="badge bg-primary">Level <?php echo $currentLevel; ?></span>
                                <?php if ($currentLevel < 7): ?>
                                    <span class="text-muted ms-2">&bull; To qualify for Level <?php echo ($currentLevel + 1); ?> Level Income when downlines upgrade, you must upgrade to <strong>Level <?php echo ($currentLevel + 1); ?></strong>.</span>
                                <?php else: ?>
                                    <span class="badge bg-success ms-2">Top Tier (Level 7) - All Levels Unlocked!</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($currentLevel < 7): ?>
                                <a href="<?php echo BASE_URL; ?>/user/packages.php" class="btn btn-warning btn-xs fw-bold">
                                    Upgrade to Level <?php echo ($currentLevel + 1); ?> &rarr;
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Metric Cards -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Total Earnings -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-xs rounded-3 h-100 bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between text-muted mb-2">
                            <span class="text-xs text-uppercase fw-semibold">Total Earnings</span>
                            <span class="badge bg-success-subtle text-success p-1.5"><i class="bi bi-cash-stack"></i></span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1 font-monospace">₹<?php echo number_format($summary['total'], 2); ?></h3>
                        <span class="text-xs text-success"><i class="bi bi-check-circle me-1"></i>Auto-Credited to Wallet</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Direct Income (100%) -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-xs rounded-3 h-100 bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between text-muted mb-2">
                            <span class="text-xs text-uppercase fw-semibold">Direct Income (100%)</span>
                            <span class="badge bg-success-subtle text-success p-1.5"><i class="bi bi-person-check"></i></span>
                        </div>
                        <h3 class="fw-bold text-success mb-1 font-monospace">₹<?php echo number_format($summary['direct'], 2); ?></h3>
                        <span class="text-xs text-muted">Level 1 Direct Referrals</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Level Income (50%) -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-xs rounded-3 h-100 bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between text-muted mb-2">
                            <span class="text-xs text-uppercase fw-semibold">Level Income (50%)</span>
                            <span class="badge bg-primary-subtle text-primary p-1.5"><i class="bi bi-diagram-3"></i></span>
                        </div>
                        <h3 class="fw-bold text-primary mb-1 font-monospace">₹<?php echo number_format($summary['level'], 2); ?></h3>
                        <span class="text-xs text-muted">Level 2 to Level 7 Downlines</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Sponsor Income (50%) -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-xs rounded-3 h-100 bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between text-muted mb-2">
                            <span class="text-xs text-uppercase fw-semibold">Sponsor Income (50%)</span>
                            <span class="badge bg-info-subtle text-info p-1.5" style="color: #6f42c1 !important;"><i class="bi bi-people"></i></span>
                        </div>
                        <h3 class="fw-bold mb-1 font-monospace" style="color: #6f42c1;">₹<?php echo number_format($summary['sponsor'], 2); ?></h3>
                        <span class="text-xs text-muted">Level 2 to Unlimited Level</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ledger Table & Filter Navigation -->
        <div class="card border-0 shadow-xs rounded-3 bg-white">
            <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-journal-text text-primary"></i>
                    <h6 class="fw-bold mb-0">Detailed Earnings Ledger</h6>
                    <span class="badge bg-light text-dark border"><?php echo $totalCount; ?> entries</span>
                </div>

                <!-- Stream Filter Pills -->
                <div class="btn-group btn-group-sm" role="group">
                    <a href="<?php echo BASE_URL; ?>/user/income.php?type=all" 
                       class="btn <?php echo ($typeFilter === 'all') ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                        All Streams
                    </a>
                    <a href="<?php echo BASE_URL; ?>/user/income.php?type=direct" 
                       class="btn <?php echo ($typeFilter === 'direct') ? 'btn-success' : 'btn-outline-secondary'; ?>">
                        Direct (100%)
                    </a>
                    <a href="<?php echo BASE_URL; ?>/user/income.php?type=level" 
                       class="btn <?php echo ($typeFilter === 'level') ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                        Level (50%)
                    </a>
                    <a href="<?php echo BASE_URL; ?>/user/income.php?type=sponsor" 
                       class="btn <?php echo ($typeFilter === 'sponsor') ? 'btn-dark' : 'btn-outline-secondary'; ?>">
                        Sponsor (50%)
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <?php if (empty($incomes)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-300"></i>
                        <p class="mb-1 fw-semibold">No income transactions recorded under this stream yet.</p>
                        <p class="text-xs mb-3">Earnings will appear automatically as your direct team registers and downline upgrades are approved.</p>
                        <a href="<?php echo BASE_URL; ?>/user/team.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-people me-1"></i> View My Team
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-xs">
                            <thead class="table-light text-muted text-uppercase text-xxs">
                                <tr>
                                    <th class="ps-3">ID / Reference</th>
                                    <th>Income Stream</th>
                                    <th>From Member</th>
                                    <th>Base Package</th>
                                    <th>Rate</th>
                                    <th>Amount Credited</th>
                                    <th>Date &amp; Time</th>
                                    <th class="pe-3">Wallet Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($incomes as $inc): ?>
                                    <tr>
                                        <td class="ps-3 font-monospace fw-bold text-dark">
                                            #INC-<?php echo str_pad($inc['id'], 6, '0', STR_PAD_LEFT); ?>
                                            <div class="text-xxs text-muted font-monospace"><?php echo htmlspecialchars($inc['transaction_ref'] ?? 'N/A'); ?></div>
                                        </td>
                                        <td>
                                            <?php if ($inc['income_type'] === 'direct'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <i class="bi bi-person-check me-1"></i> Direct Income
                                                </span>
                                            <?php elseif ($inc['income_type'] === 'level'): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                    <i class="bi bi-layers me-1"></i> Level <?php echo (int)$inc['source_level']; ?> Income
                                                </span>
                                            <?php elseif ($inc['income_type'] === 'sponsor'): ?>
                                                <span class="badge bg-dark-subtle text-dark border">
                                                    <i class="bi bi-people me-1"></i> Sponsor Income (L<?php echo (int)$inc['source_level']; ?>)
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($inc['from_name'] ?? 'Member'); ?></div>
                                            <div class="text-muted font-monospace text-xxs">@<?php echo htmlspecialchars($inc['from_username'] ?? 'user'); ?> &bull; L<?php echo (int)($inc['from_current_level'] ?? 1); ?></div>
                                        </td>
                                        <td class="font-monospace">
                                            ₹<?php echo number_format((float)($inc['package_price'] ?? 1200), 2); ?>
                                        </td>
                                        <td class="fw-bold text-slate-700">
                                            <?php echo number_format((float)$inc['percentage'], 0); ?>%
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success font-monospace fs-6">
                                                +₹<?php echo number_format((float)$inc['amount'], 2); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted">
                                            <?php echo date('d M Y, h:i A', strtotime($inc['created_at'])); ?>
                                        </td>
                                        <td class="pe-3">
                                            <span class="badge bg-success text-white">
                                                <i class="bi bi-check2-circle me-1"></i> Credited
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination Footer -->
            <?php if ($totalPages > 1): ?>
                <div class="card-footer bg-white border-top p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span class="text-xs text-muted">
                        Page <?php echo $page; ?> of <?php echo $totalPages; ?> &bull; Total <?php echo $totalCount; ?> entries
                    </span>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>/user/income.php?type=<?php echo urlencode($typeFilter); ?>&page=<?php echo ($page - 1); ?>">Previous</a>
                            </li>
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <li class="page-item <?php echo ($p === $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo BASE_URL; ?>/user/income.php?type=<?php echo urlencode($typeFilter); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>/user/income.php?type=<?php echo urlencode($typeFilter); ?>&page=<?php echo ($page + 1); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

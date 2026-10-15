<?php
/**
 * Social Service Dream - User Wallet & Financial Hub
 * =========================================================================
 * PART 6: WALLET + TRANSACTIONS + WITHDRAWAL ENGINE
 * Target: C:\xampp\htdocs\ssdream\user\wallet.php
 * =========================================================================
 */

$pageTitle = "My Wallet - Social Service Dream";
$pageHeading = "My Financial Wallet";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireUserLogin();

$userId = (int)$_SESSION['user_id'];
$user = getUserById($userId);
$wallet = getUserWallet($userId);
$kyc = getUserKyc($userId);
$profile = getUserProfile($userId);

// Fetch recent 10 transactions with before and after balance
$recentTransactions = getUserTransactions($userId, 10);

require_once __DIR__ . '/includes/header.php';
?>

<!-- 5 Professional Cards as requested in Part 6 -->
<div class="row g-3 mb-4">
    <!-- 1. Current Balance -->
    <div class="col-xl col-md-4 col-sm-6">
        <div class="stat-card border-top border-primary border-4 h-100 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Current Balance</span>
                <div class="stat-icon icon-blue">
                    <i class="bi bi-wallet2 text-primary"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-navy" style="color: #0b192c;"><?php echo formatCurrency($wallet['balance'], true); ?></h3>
            <span class="text-muted text-xxs">Current net ledger balance</span>
        </div>
    </div>

    <!-- 2. Total Credit -->
    <div class="col-xl col-md-4 col-sm-6">
        <div class="stat-card border-top border-success border-4 h-100 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Total Credit</span>
                <div class="stat-icon icon-green">
                    <i class="bi bi-arrow-down-left-circle text-success"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-success"><?php echo formatCurrency($wallet['total_credit'], true); ?></h3>
            <span class="text-success text-xxs"><i class="bi bi-plus-circle me-1"></i>All incomes & refunds</span>
        </div>
    </div>

    <!-- 3. Total Debit -->
    <div class="col-xl col-md-4 col-sm-6">
        <div class="stat-card border-top border-danger border-4 h-100 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Total Debit</span>
                <div class="stat-icon icon-amber">
                    <i class="bi bi-arrow-up-right-circle text-danger"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-danger"><?php echo formatCurrency($wallet['total_debit'], true); ?></h3>
            <span class="text-danger text-xxs"><i class="bi bi-dash-circle me-1"></i>Withdrawals & investments</span>
        </div>
    </div>

    <!-- 4. Available for Withdrawal -->
    <div class="col-xl col-md-6 col-sm-6">
        <div class="stat-card border-top border-info border-4 h-100 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Available for Withdrawal</span>
                <div class="stat-icon bg-info-subtle">
                    <i class="bi bi-cash-coin text-info"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-info"><?php echo formatCurrency($wallet['available_for_withdrawal'], true); ?></h3>
            <span class="text-muted text-xxs">Spendable payout capacity</span>
        </div>
    </div>

    <!-- 5. Pending Withdrawal -->
    <div class="col-xl col-md-6 col-sm-6">
        <div class="stat-card border-top border-warning border-4 h-100 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Pending Withdrawal</span>
                <div class="stat-icon bg-warning-subtle">
                    <i class="bi bi-hourglass-split text-warning"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-warning"><?php echo formatCurrency($wallet['pending_withdrawal'], true); ?></h3>
            <span class="text-muted text-xxs">Under admin verification</span>
        </div>
    </div>
</div>

<!-- Quick Action & Breakdown Row -->
<div class="row g-4 mb-4">
    <!-- Income Stream Sub-Accounts -->
    <div class="col-lg-7">
        <div class="card border shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-pie-chart-fill text-primary me-2"></i> Income Stream Distribution (Locked Rules)
                </h6>
                <a href="<?php echo BASE_URL; ?>/user/income.php" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Full Breakdown
                </a>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-3">
                    Earnings generated through the 3 official SS Dream income streams automatically verified and credited:
                </p>

                <div class="list-group list-group-flush border rounded-3 mb-3">
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <span class="badge bg-primary text-white me-2">100%</span>
                            <strong class="text-dark">1. Direct Income</strong>
                            <div class="text-muted text-xxs mt-0.5">Direct referral entry package (₹1,200)</div>
                        </div>
                        <span class="fw-bold fs-6 text-primary"><?php echo formatCurrency($wallet['direct_income'], true); ?></span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <span class="badge bg-info text-dark me-2">50%</span>
                            <strong class="text-dark">2. Level Income</strong>
                            <div class="text-muted text-xxs mt-0.5">Level 2 → Level 7 (User must upgrade to that level)</div>
                        </div>
                        <span class="fw-bold fs-6 text-info"><?php echo formatCurrency($wallet['level_income'], true); ?></span>
                    </div>

                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <span class="badge bg-success text-white me-2">50%</span>
                            <strong class="text-dark">3. Direct Sponsor Income</strong>
                            <div class="text-muted text-xxs mt-0.5">Level 2 → Unlimited Level (Sponsor relationship)</div>
                        </div>
                        <span class="fw-bold fs-6 text-success"><?php echo formatCurrency($wallet['sponsor_income'], true); ?></span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 pt-2">
                    <a href="<?php echo BASE_URL; ?>/user/withdraw.php" class="btn btn-success btn-sm">
                        <i class="bi bi-cash-stack me-1"></i> Request Payout / Withdrawal
                    </a>
                    <a href="<?php echo BASE_URL; ?>/user/transactions.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-receipt me-1"></i> View Full Transaction Ledger
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Withdrawal & Payout Readiness Panel -->
    <div class="col-lg-5">
        <div class="card border shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-bank text-success me-2"></i> Withdrawal Status &amp; Readiness
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">KYC Compliance:</span>
                        <?php if (($kyc['status'] ?? '') === 'approved'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-check-circle-fill me-1"></i> KYC Approved
                            </span>
                        <?php elseif (($kyc['status'] ?? '') === 'pending'): ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                <i class="bi bi-clock-fill me-1"></i> KYC Under Review
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> KYC Required
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Bank Account:</span>
                        <span class="small fw-bold text-dark font-monospace">
                            <?php echo !empty($profile['account_number']) ? e(maskAccountNumber($profile['account_number'])) : 'Not Provided'; ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">UPI VPA:</span>
                        <span class="small fw-bold text-dark font-monospace">
                            <?php echo !empty($profile['upi_id']) ? e($profile['upi_id']) : 'Not Provided'; ?>
                        </span>
                    </div>
                </div>

                <div class="border-top pt-3">
                    <h6 class="small fw-bold text-dark mb-2">Withdrawal Terms &amp; Deductions:</h6>
                    <ul class="list-unstyled text-muted small space-y-1 mb-3">
                        <li class="d-flex justify-content-between py-1 border-bottom border-light">
                            <span>Minimum Withdrawal:</span>
                            <strong class="text-dark">₹500.00</strong>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom border-light">
                            <span>TDS Deduction (Govt):</span>
                            <span class="text-dark">5%</span>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom border-light">
                            <span>Admin Handling Fee:</span>
                            <span class="text-dark">5%</span>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span>Total Net Payout:</span>
                            <strong class="text-success">90% of requested sum</strong>
                        </li>
                    </ul>

                    <?php if (($kyc['status'] ?? '') === 'approved' && $wallet['balance'] >= 500): ?>
                        <a href="<?php echo BASE_URL; ?>/user/withdraw.php" class="btn btn-success w-100 py-2">
                            <i class="bi bi-cash-stack me-1"></i> Request Withdrawal (₹500 - <?php echo formatCurrency($wallet['balance'], false); ?>)
                        </a>
                    <?php elseif (($kyc['status'] ?? '') !== 'approved'): ?>
                        <a href="<?php echo BASE_URL; ?>/user/kyc.php" class="btn btn-warning w-100 py-2">
                            <i class="bi bi-shield-exclamation me-1"></i> Complete KYC To Enable Withdrawals
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100 py-2" disabled>
                            <i class="bi bi-lock me-1"></i> Insufficient Balance (Min ₹500 required)
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions Ledger with Balance Before & Balance After -->
<div class="card border shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                <i class="bi bi-clock-history text-primary me-2"></i> Recent Wallet Transactions Ledger
            </h6>
            <span class="text-muted small">Real-time ledger audit with exact balance before and after each transaction.</span>
        </div>
        <a href="<?php echo BASE_URL; ?>/user/transactions.php" class="btn btn-outline-secondary btn-sm">
            View All Transactions <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date &amp; Time</th>
                        <th>Reference / ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance Before</th>
                        <th>Balance After</th>
                        <th>Status</th>
                        <th class="pe-4">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentTransactions)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <h6 class="fw-bold text-dark">No wallet transactions recorded yet</h6>
                                <p class="text-muted small mb-0">Incomes, investments, and payouts will appear here automatically.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentTransactions as $tx): ?>
                            <tr>
                                <td class="ps-4 small text-secondary">
                                    <?php echo date('d M Y, h:i A', strtotime($tx['created_at'])); ?>
                                </td>
                                <td class="font-monospace small fw-bold text-dark">
                                    <?php echo e($tx['reference_no'] ?? $tx['reference_id'] ?? ('TXN-' . $tx['id'])); ?>
                                </td>
                                <td>
                                    <?php if ($tx['transaction_type'] === 'credit'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-arrow-down-left me-1"></i> Credit
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-arrow-up-right me-1"></i> Debit
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold <?php echo $tx['transaction_type'] === 'credit' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($tx['transaction_type'] === 'credit' ? '+' : '-') . formatCurrency($tx['amount'], true); ?>
                                </td>
                                <td class="small font-monospace text-muted">
                                    <?php echo formatCurrency($tx['balance_before'] ?? 0, true); ?>
                                </td>
                                <td class="small font-monospace fw-bold text-dark">
                                    <?php echo formatCurrency($tx['balance_after'] ?? $tx['amount'], true); ?>
                                </td>
                                <td>
                                    <?php if ($tx['status'] === 'completed'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check-circle me-1"></i> Completed
                                        </span>
                                    <?php elseif ($tx['status'] === 'pending'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            <i class="bi bi-clock me-1"></i> Pending
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-x-circle me-1"></i> Failed
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-secondary small">
                                    <?php echo e($tx['description']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

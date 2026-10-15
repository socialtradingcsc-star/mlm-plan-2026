<?php
/**
 * Social Service Dream - User Withdrawal Request & History
 * =========================================================================
 * PART 6: WALLET + TRANSACTIONS + WITHDRAWAL ENGINE
 * Target: C:\xampp\htdocs\ssdream\user\withdraw.php
 * =========================================================================
 */

$pageTitle = "Withdrawal - Social Service Dream";
$pageHeading = "Payout & Withdrawals";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireUserLogin();

$userId = (int)$_SESSION['user_id'];
$wallet = getUserWallet($userId);
$kyc = getUserKyc($userId);
$profile = getUserProfile($userId);
$user = getUserById($userId);

$alert = null;

// Handle Withdrawal Request Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_withdrawal') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $alert = ['type' => 'danger', 'msg' => 'Security token expired. Please refresh and try again.'];
    } else {
        $amount = (float)($_POST['amount'] ?? 0);
        $paymentMode = trim($_POST['payment_mode'] ?? 'Bank Transfer');
        
        // Prepare payment details
        $paymentDetails = [];
        if ($paymentMode === 'UPI') {
            $paymentDetails = [
                'type'   => 'UPI',
                'upi_id' => trim($_POST['upi_id'] ?? $profile['upi_id'] ?? '')
            ];
            if (empty($paymentDetails['upi_id'])) {
                $alert = ['type' => 'danger', 'msg' => 'Please provide a valid UPI ID (VPA).'];
            }
        } else {
            $paymentDetails = [
                'type'                => 'Bank Transfer',
                'bank_name'           => trim($_POST['bank_name'] ?? $profile['bank_name'] ?? ''),
                'account_number'      => trim($_POST['account_number'] ?? $profile['account_number'] ?? ''),
                'ifsc_code'           => strtoupper(trim($_POST['ifsc_code'] ?? $profile['ifsc_code'] ?? '')),
                'account_holder_name' => trim($_POST['account_holder_name'] ?? $profile['account_holder_name'] ?? $user['full_name'] ?? '')
            ];
            if (empty($paymentDetails['account_number']) || empty($paymentDetails['ifsc_code'])) {
                $alert = ['type' => 'danger', 'msg' => 'Please provide complete Bank Account and IFSC details.'];
            }
        }

        if (!$alert) {
            $res = requestUserWithdrawal($userId, $amount, $paymentMode, $paymentDetails);
            if ($res['success']) {
                $alert = ['type' => 'success', 'msg' => $res['message']];
                // Refresh wallet stats
                $wallet = getUserWallet($userId);
            } else {
                $alert = ['type' => 'danger', 'msg' => $res['message']];
            }
        }
    }
}

// Fetch Withdrawal History
$withdrawals = getUserWithdrawals($userId, 50);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Alerts -->
<?php if ($alert): ?>
    <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="bi <?php echo $alert['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
        <?php echo e($alert['msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Quick Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card border-top border-primary border-4 shadow-sm h-100">
            <span class="text-muted small fw-semibold text-uppercase">Available Balance</span>
            <h3 class="fw-bold mb-1 text-navy" style="color: #0b192c;"><?php echo formatCurrency($wallet['balance'], true); ?></h3>
            <span class="text-muted text-xxs">Spendable payout balance</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card border-top border-warning border-4 shadow-sm h-100">
            <span class="text-muted small fw-semibold text-uppercase">Pending Payouts</span>
            <h3 class="fw-bold mb-1 text-warning"><?php echo formatCurrency($wallet['pending_withdrawal'], true); ?></h3>
            <span class="text-muted text-xxs">Under admin approval</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card border-top border-success border-4 shadow-sm h-100">
            <span class="text-muted small fw-semibold text-uppercase">Total Withdrawn</span>
            <h3 class="fw-bold mb-1 text-success"><?php echo formatCurrency($wallet['total_withdrawn'], true); ?></h3>
            <span class="text-muted text-xxs">Successfully disbursed</span>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card border-top border-info border-4 shadow-sm h-100">
            <span class="text-muted small fw-semibold text-uppercase">KYC Verification</span>
            <div class="mt-2">
                <?php if (($kyc['status'] ?? '') === 'approved'): ?>
                    <span class="badge bg-success text-white px-2 py-1"><i class="bi bi-shield-check me-1"></i> Approved</span>
                <?php elseif (($kyc['status'] ?? '') === 'pending'): ?>
                    <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-clock-history me-1"></i> Under Review</span>
                <?php else: ?>
                    <span class="badge bg-danger text-white px-2 py-1"><i class="bi bi-shield-x me-1"></i> Not Verified</span>
                <?php endif; ?>
            </div>
            <span class="text-muted text-xxs d-block mt-1">Mandatory for withdrawal</span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Withdrawal Form -->
    <div class="col-lg-6">
        <div class="card border shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-cash-stack text-success me-2"></i> Submit New Withdrawal Request
                </h6>
            </div>
            <div class="card-body p-4">
                <?php if (($kyc['status'] ?? '') !== 'approved'): ?>
                    <div class="alert alert-warning border-warning border-opacity-25 mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-exclamation fs-3 text-warning"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Approved KYC Required</h6>
                                <p class="mb-0 small text-secondary">
                                    According to RBI and SS Dream compliance, you must verify your KYC documents before requesting withdrawals.
                                </p>
                            </div>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/user/kyc.php" class="btn btn-warning btn-sm mt-3 fw-semibold">
                            <i class="bi bi-upload me-1"></i> Go To KYC Verification
                        </a>
                    </div>
                <?php elseif ($wallet['balance'] < 500): ?>
                    <div class="alert alert-info border-info border-opacity-25 mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        Minimum withdrawal limit is <strong>₹500.00</strong>. Your current available balance is <strong><?php echo formatCurrency($wallet['balance'], true); ?></strong>.
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="withdrawalForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="action" value="request_withdrawal">

                    <!-- Amount -->
                    <div class="mb-3">
                        <label for="withdrawAmount" class="form-label small fw-bold">Withdrawal Amount (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">₹</span>
                            <input type="number" step="1" min="500" max="<?php echo (int)$wallet['balance']; ?>" class="form-control font-monospace fs-5 fw-bold" id="withdrawAmount" name="amount" placeholder="500" required <?php echo (($kyc['status'] ?? '') !== 'approved' || $wallet['balance'] < 500) ? 'disabled' : ''; ?>>
                        </div>
                        <div class="form-text small d-flex justify-content-between">
                            <span>Min: ₹500.00</span>
                            <span>Max Spendable: <?php echo formatCurrency($wallet['balance'], true); ?></span>
                        </div>
                    </div>

                    <!-- Payment Mode -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Disbursement Mode <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_mode" id="modeBank" value="Bank Transfer" checked onchange="togglePaymentFields('bank')">
                                <label class="form-check-label small fw-semibold" for="modeBank">
                                    <i class="bi bi-bank text-primary me-1"></i> Direct Bank Transfer
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_mode" id="modeUPI" value="UPI" onchange="togglePaymentFields('upi')">
                                <label class="form-check-label small fw-semibold" for="modeUPI">
                                    <i class="bi bi-qr-code text-success me-1"></i> UPI Transfer
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Details Section -->
                    <div id="bankFieldsSection">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control form-control-sm" value="<?php echo e($profile['bank_name'] ?? ''); ?>" placeholder="e.g. State Bank of India">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-7">
                                <label class="form-label small fw-bold">Account Number</label>
                                <input type="text" name="account_number" class="form-control form-control-sm font-monospace" value="<?php echo e($profile['account_number'] ?? ''); ?>" placeholder="Account number">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold">IFSC Code</label>
                                <input type="text" name="ifsc_code" class="form-control form-control-sm font-monospace text-uppercase" value="<?php echo e($profile['ifsc_code'] ?? ''); ?>" placeholder="e.g. SBIN0001234">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Account Holder Name</label>
                            <input type="text" name="account_holder_name" class="form-control form-control-sm" value="<?php echo e($profile['account_holder_name'] ?? $user['full_name'] ?? ''); ?>" placeholder="Full name as in bank">
                        </div>
                    </div>

                    <!-- UPI Details Section -->
                    <div id="upiFieldsSection" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">UPI ID / VPA</label>
                            <input type="text" name="upi_id" class="form-control form-control-sm font-monospace" value="<?php echo e($profile['upi_id'] ?? ''); ?>" placeholder="e.g. username@okhdfcbank">
                            <span class="text-muted text-xxs">Ensure your VPA is linked and active.</span>
                        </div>
                    </div>

                    <!-- Live Breakdown Summary Box -->
                    <div class="p-3 bg-light rounded-3 mb-4 border">
                        <div class="d-flex justify-content-between align-items-center mb-1 small">
                            <span>Requested Amount:</span>
                            <span class="fw-bold font-monospace" id="calcRequested">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1 small text-muted">
                            <span>TDS Deduction (5%):</span>
                            <span class="font-monospace text-danger" id="calcTds">-₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 small text-muted border-bottom pb-1">
                            <span>Admin Handling (5%):</span>
                            <span class="font-monospace text-danger" id="calcAdmin">-₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark">Estimated Net Payable:</span>
                            <span class="fw-bold font-monospace fs-5 text-success" id="calcNet">₹0.00</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-semibold" <?php echo (($kyc['status'] ?? '') !== 'approved' || $wallet['balance'] < 500) ? 'disabled' : ''; ?>>
                        <i class="bi bi-arrow-up-right-circle me-1"></i> Submit Withdrawal Request
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Official Payout Policies & Guidelines -->
    <div class="col-lg-6">
        <div class="card border shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-shield-check text-primary me-2"></i> SS Dream Payout Rules &amp; Schedule
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <h6 class="fw-bold text-dark small text-uppercase">1. Locked Payout Rules</h6>
                    <p class="text-muted small mb-2">
                        All withdrawals are processed securely from verified income generated under the 3 official income streams:
                    </p>
                    <ul class="text-secondary small ps-3 space-y-1 mb-0">
                        <li><strong>Direct Income (100%):</strong> Credited on Level 1 referral activation.</li>
                        <li><strong>Level Income (50%):</strong> Credited from Level 2 to Level 7 downline upon user upgrade.</li>
                        <li><strong>Sponsor Income (50%):</strong> Credited from Level 2 to Unlimited Level sponsor team.</li>
                    </ul>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-dark small text-uppercase">2. Deductions &amp; Taxes</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light text-center">
                                <span class="d-block text-xxs text-muted text-uppercase">TDS (Tax Deducted at Source)</span>
                                <strong class="fs-5 text-dark">5%</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light text-center">
                                <span class="d-block text-xxs text-muted text-uppercase">Admin Handling Charge</span>
                                <strong class="fs-5 text-dark">5%</strong>
                            </div>
                        </div>
                    </div>
                    <span class="text-muted text-xxs d-block mt-1">Total combined deduction is exactly 10% (User receives 90% net).</span>
                </div>

                <div>
                    <h6 class="fw-bold text-dark small text-uppercase">3. Anti-Fraud &amp; Row-Locking Security</h6>
                    <p class="text-muted small mb-0">
                        When you submit a withdrawal, the system safely reserves the requested amount via database row locking. If a withdrawal request is rejected by admin for invalid details, the reserved funds are <strong>automatically refunded</strong> back to your wallet with an official refund transaction record.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Withdrawal History Table -->
<div class="card border shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                <i class="bi bi-clock-history text-primary me-2"></i> My Withdrawal History
            </h6>
            <span class="text-muted small">Complete record of your payout requests, deductions, and payment UTRs.</span>
        </div>
        <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill">
            Total Requests: <?php echo count($withdrawals); ?>
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Request Date</th>
                        <th>Reference ID</th>
                        <th>Requested</th>
                        <th>TDS (5%)</th>
                        <th>Admin (5%)</th>
                        <th>Net Payable</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th class="pe-4">Remarks / Payout Ref</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($withdrawals)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <h6 class="fw-bold text-dark">No withdrawal requests yet</h6>
                                <p class="text-muted small mb-0">Your withdrawal requests will appear here with live tracking status.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($withdrawals as $w): ?>
                            <tr>
                                <td class="ps-4 small text-secondary">
                                    <?php echo date('d M Y, h:i A', strtotime($w['created_at'])); ?>
                                </td>
                                <td class="font-monospace small fw-bold text-dark">
                                    <?php echo e($w['transaction_ref'] ?? ('WTH-' . $w['id'])); ?>
                                </td>
                                <td class="fw-bold text-dark">
                                    <?php echo formatCurrency($w['amount'], true); ?>
                                </td>
                                <td class="small font-monospace text-danger">
                                    -<?php echo formatCurrency($w['tds_deduction'], true); ?>
                                </td>
                                <td class="small font-monospace text-danger">
                                    -<?php echo formatCurrency($w['admin_charge'], true); ?>
                                </td>
                                <td class="fw-bold fs-6 text-success font-monospace">
                                    <?php echo formatCurrency($w['net_amount'], true); ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">
                                        <?php echo e($w['payment_mode']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($w['status'] === 'approved'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check-circle me-1"></i> Approved &amp; Paid
                                        </span>
                                    <?php elseif ($w['status'] === 'pending'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            <i class="bi bi-hourglass-split me-1"></i> Pending
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-x-circle me-1"></i> Rejected (Refunded)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-secondary small">
                                    <?php if (!empty($w['admin_remarks'])): ?>
                                        <span class="d-block"><?php echo e($w['admin_remarks']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function togglePaymentFields(mode) {
    var bankSection = document.getElementById('bankFieldsSection');
    var upiSection = document.getElementById('upiFieldsSection');
    if (mode === 'upi') {
        bankSection.style.display = 'none';
        upiSection.style.display = 'block';
    } else {
        bankSection.style.display = 'block';
        upiSection.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var amtInput = document.getElementById('withdrawAmount');
    var calcReq = document.getElementById('calcRequested');
    var calcTds = document.getElementById('calcTds');
    var calcAdm = document.getElementById('calcAdmin');
    var calcNet = document.getElementById('calcNet');

    function updateCalculations() {
        var val = parseFloat(amtInput.value) || 0;
        var tds = val * 0.05;
        var adm = val * 0.05;
        var net = Math.max(0, val - (tds + adm));

        calcReq.innerText = '₹' + val.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        calcTds.innerText = '-₹' + tds.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        calcAdm.innerText = '-₹' + adm.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        calcNet.innerText = '₹' + net.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    if (amtInput) {
        amtInput.addEventListener('input', updateCalculations);
        amtInput.addEventListener('change', updateCalculations);
        updateCalculations();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

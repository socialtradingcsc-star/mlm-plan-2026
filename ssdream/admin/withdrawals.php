<?php
/**
 * Social Service Dream - Admin Withdrawal Management Console
 * =========================================================================
 * PART 6: WALLET + TRANSACTIONS + WITHDRAWAL ENGINE
 * Target: C:\xampp\htdocs\ssdream\admin\withdrawals.php
 * =========================================================================
 */

$pageTitle = "Withdrawal Management - SS Dream Admin";
$pageHeading = "Member Withdrawals & Payouts";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard for Admin
requireAdminLogin();

$db = getDb();
$alert = null;

// Handle Admin Actions (Approve / Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $alert = ['type' => 'danger', 'msg' => 'Security token expired. Please refresh and try again.'];
    } else {
        $action = trim($_POST['action']);
        $withdrawalId = (int)($_POST['withdrawal_id'] ?? 0);
        $remarks = trim($_POST['admin_remarks'] ?? '');
        $txnRef = trim($_POST['transaction_ref'] ?? '');

        if ($action === 'approve') {
            $res = adminApproveWithdrawal($withdrawalId, $remarks, $txnRef);
            $alert = ['type' => $res['success'] ? 'success' : 'danger', 'msg' => $res['message']];
        } elseif ($action === 'reject') {
            if (empty($remarks)) {
                $alert = ['type' => 'danger', 'msg' => 'Please provide a reason for rejecting this withdrawal request.'];
            } else {
                $res = adminRejectWithdrawal($withdrawalId, $remarks);
                $alert = ['type' => $res['success'] ? 'success' : 'danger', 'msg' => $res['message']];
            }
        }
    }
}

// Filters & Pagination
$currentStatus = strtolower(trim($_GET['status'] ?? 'pending'));
if (!in_array($currentStatus, ['all', 'pending', 'approved', 'rejected'])) {
    $currentStatus = 'all';
}
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 15;

$withdrawalData = getAllWithdrawals($currentStatus, $page, $limit, $search);
$withdrawals = $withdrawalData['records'];
$totalRecords = $withdrawalData['total'];
$totalPages = $withdrawalData['total_pages'];

// Summary Statistics
$stats = [
    'pending_count'   => 0,
    'pending_amount'  => 0.00,
    'approved_count'  => 0,
    'approved_amount' => 0.00,
    'rejected_count'  => 0,
    'rejected_amount' => 0.00,
];

if ($db) {
    try {
        $stStmt = $db->query("
            SELECT status, COUNT(*) as cnt, COALESCE(SUM(amount), 0) as total_amt, COALESCE(SUM(net_amount), 0) as net_amt 
            FROM withdrawals 
            GROUP BY status
        ");
        while ($row = $stStmt->fetch(PDO::FETCH_ASSOC)) {
            $st = $row['status'];
            if ($st === 'pending') {
                $stats['pending_count'] = (int)$row['cnt'];
                $stats['pending_amount'] = (float)$row['total_amt'];
            } elseif ($st === 'approved') {
                $stats['approved_count'] = (int)$row['cnt'];
                $stats['approved_amount'] = (float)$row['net_amt'];
            } elseif ($st === 'rejected') {
                $stats['rejected_count'] = (int)$row['cnt'];
                $stats['rejected_amount'] = (float)$row['total_amt'];
            }
        }
    } catch (PDOException $e) {
        // Fallback to zeros
    }
}

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

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <!-- Pending -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card border-top border-warning border-4 shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Pending Payouts</span>
                <span class="badge bg-warning text-dark"><?php echo $stats['pending_count']; ?> requests</span>
            </div>
            <h3 class="fw-bold mb-1 text-warning"><?php echo formatCurrency($stats['pending_amount'], true); ?></h3>
            <span class="text-muted text-xxs">Requires administrator review &amp; disbursement</span>
        </div>
    </div>

    <!-- Approved -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card border-top border-success border-4 shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Total Disbursed</span>
                <span class="badge bg-success text-white"><?php echo $stats['approved_count']; ?> paid</span>
            </div>
            <h3 class="fw-bold mb-1 text-success"><?php echo formatCurrency($stats['approved_amount'], true); ?></h3>
            <span class="text-success text-xxs"><i class="bi bi-check-all me-1"></i>Net payouts disbursed</span>
        </div>
    </div>

    <!-- Rejected / Refunded -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card border-top border-danger border-4 shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Rejected &amp; Refunded</span>
                <span class="badge bg-danger text-white"><?php echo $stats['rejected_count']; ?> refunded</span>
            </div>
            <h3 class="fw-bold mb-1 text-danger"><?php echo formatCurrency($stats['rejected_amount'], true); ?></h3>
            <span class="text-danger text-xxs"><i class="bi bi-arrow-counterclockwise me-1"></i>Returned to member wallets</span>
        </div>
    </div>

    <!-- Policy Compliance -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card border-top border-primary border-4 shadow-sm h-100">
            <span class="text-muted small fw-semibold text-uppercase">Standard Deductions</span>
            <div class="d-flex gap-2 mt-2">
                <span class="badge bg-light text-dark border">TDS: 5%</span>
                <span class="badge bg-light text-dark border">Admin: 5%</span>
                <span class="badge bg-success text-white">Net: 90%</span>
            </div>
            <span class="text-muted text-xxs d-block mt-2">Rule enforcement locked</span>
        </div>
    </div>
</div>

<!-- Main Table Card with Search & Filters -->
<div class="card border shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-cash-stack text-primary me-2"></i> Member Withdrawal Requests Ledger
                </h6>
                <span class="text-muted small">Verify member bank/UPI credentials, check KYC, approve disbursement or refund on rejection.</span>
            </div>

            <!-- Search Form -->
            <form method="GET" action="" class="d-flex gap-2">
                <input type="hidden" name="status" value="<?php echo e($currentStatus); ?>">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="q" class="form-control" placeholder="Search user, mobile, ref..." value="<?php echo e($search); ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                </div>
                <?php if (!empty($search)): ?>
                    <a href="?status=<?php echo urlencode($currentStatus); ?>" class="btn btn-outline-secondary btn-sm" title="Clear search">
                        <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Filter Tabs -->
        <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top">
            <a href="?status=pending<?php echo $search ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm <?php echo ($currentStatus === 'pending') ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary'; ?>">
                <i class="bi bi-hourglass-split me-1"></i> Pending Requests (<?php echo $stats['pending_count']; ?>)
            </a>
            <a href="?status=approved<?php echo $search ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm <?php echo ($currentStatus === 'approved') ? 'btn-success fw-bold' : 'btn-outline-secondary'; ?>">
                <i class="bi bi-check-circle me-1"></i> Approved / Paid (<?php echo $stats['approved_count']; ?>)
            </a>
            <a href="?status=rejected<?php echo $search ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm <?php echo ($currentStatus === 'rejected') ? 'btn-danger fw-bold' : 'btn-outline-secondary'; ?>">
                <i class="bi bi-x-circle me-1"></i> Rejected (<?php echo $stats['rejected_count']; ?>)
            </a>
            <a href="?status=all<?php echo $search ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm <?php echo ($currentStatus === 'all') ? 'btn-primary fw-bold' : 'btn-outline-secondary'; ?>">
                All Requests (<?php echo $totalRecords; ?>)
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Request Ref / Date</th>
                        <th>Member Details</th>
                        <th>KYC</th>
                        <th>Gross Amount</th>
                        <th>Deductions</th>
                        <th>Net Payable</th>
                        <th>Payment Credentials</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($withdrawals)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                <h6 class="fw-bold text-dark">No withdrawal requests found</h6>
                                <p class="text-muted small mb-0">No records match the current status filter (<?php echo e(strtoupper($currentStatus)); ?>).</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($withdrawals as $w): 
                            $details = json_decode($w['payment_details'] ?? '{}', true) ?: [];
                        ?>
                            <tr>
                                <!-- Ref / Date -->
                                <td class="ps-4">
                                    <span class="font-monospace fw-bold text-dark small d-block">
                                        <?php echo e($w['transaction_ref'] ?? ('WTH-' . $w['id'])); ?>
                                    </span>
                                    <span class="text-muted text-xxs">
                                        <?php echo date('d M Y, h:i A', strtotime($w['created_at'])); ?>
                                    </span>
                                </td>

                                <!-- Member Details -->
                                <td>
                                    <div class="fw-semibold text-dark small"><?php echo e($w['full_name']); ?></div>
                                    <div class="font-monospace text-xxs text-primary">@<?php echo e($w['username']); ?> • <?php echo e($w['mobile']); ?></div>
                                    <span class="badge bg-light text-secondary border text-xxs mt-0.5">
                                        Level <?php echo (int)($w['current_level'] ?? 1); ?>
                                    </span>
                                </td>

                                <!-- KYC Status -->
                                <td>
                                    <?php if (($w['kyc_status'] ?? '') === 'approved'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle text-xxs">
                                            <i class="bi bi-check-circle me-1"></i> Approved
                                        </span>
                                    <?php elseif (($w['kyc_status'] ?? '') === 'pending'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-xxs">
                                            <i class="bi bi-clock me-1"></i> Review
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-xxs">
                                            <i class="bi bi-x-circle me-1"></i> Unverified
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Gross Amount -->
                                <td class="fw-bold text-dark">
                                    <?php echo formatCurrency($w['amount'], true); ?>
                                </td>

                                <!-- Deductions -->
                                <td>
                                    <div class="text-danger text-xxs font-monospace">TDS: -<?php echo formatCurrency($w['tds_deduction'], true); ?></div>
                                    <div class="text-danger text-xxs font-monospace">Adm: -<?php echo formatCurrency($w['admin_charge'], true); ?></div>
                                </td>

                                <!-- Net Payable -->
                                <td>
                                    <span class="fw-bold fs-6 text-success font-monospace">
                                        <?php echo formatCurrency($w['net_amount'], true); ?>
                                    </span>
                                    <span class="text-muted text-xxs d-block">(90% Net)</span>
                                </td>

                                <!-- Payment Credentials -->
                                <td>
                                    <span class="badge bg-light text-dark border mb-1"><?php echo e($w['payment_mode']); ?></span>
                                    <?php if ($w['payment_mode'] === 'UPI'): ?>
                                        <div class="small font-monospace text-dark fw-bold">
                                            <?php echo e($details['upi_id'] ?? $w['upi_id'] ?? 'Not set'); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="small font-monospace text-dark fw-bold">
                                            A/C: <?php echo e($details['account_number'] ?? $w['account_number'] ?? 'Not set'); ?>
                                        </div>
                                        <div class="text-xxs font-monospace text-muted">
                                            IFSC: <?php echo e($details['ifsc_code'] ?? $w['ifsc_code'] ?? '—'); ?> • <?php echo e($details['bank_name'] ?? $w['bank_name'] ?? ''); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Status -->
                                <td>
                                    <?php if ($w['status'] === 'approved'): ?>
                                        <span class="badge bg-success text-white">
                                            <i class="bi bi-check2-circle me-1"></i> Approved &amp; Paid
                                        </span>
                                        <?php if (!empty($w['admin_remarks'])): ?>
                                            <div class="text-xxs text-muted mt-1"><?php echo e($w['admin_remarks']); ?></div>
                                        <?php endif; ?>
                                    <?php elseif ($w['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-hourglass-split me-1"></i> Pending Action
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger text-white">
                                            <i class="bi bi-x-circle me-1"></i> Rejected &amp; Refunded
                                        </span>
                                        <?php if (!empty($w['admin_remarks'])): ?>
                                            <div class="text-xxs text-danger mt-1"><?php echo e($w['admin_remarks']); ?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>

                                <!-- Action Buttons -->
                                <td class="pe-4 text-end">
                                    <?php if ($w['status'] === 'pending'): ?>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $w['id']; ?>" title="Approve & Mark Paid">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $w['id']; ?>" title="Reject & Refund to Wallet">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        </div>

                                        <!-- Approve Modal -->
                                        <div class="modal fade" id="approveModal<?php echo $w['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog text-start">
                                                <div class="modal-content">
                                                    <form method="POST" action="">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <input type="hidden" name="withdrawal_id" value="<?php echo $w['id']; ?>">

                                                        <div class="modal-header bg-success text-white">
                                                            <h6 class="modal-title fw-bold">
                                                                <i class="bi bi-check2-circle me-1"></i> Approve &amp; Disburse Payout #<?php echo $w['id']; ?>
                                                            </h6>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                                                <div class="d-flex justify-content-between mb-1 small">
                                                                    <span>Member:</span>
                                                                    <strong><?php echo e($w['full_name']); ?> (@<?php echo e($w['username']); ?>)</strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1 small">
                                                                    <span>Requested Gross:</span>
                                                                    <strong class="font-monospace"><?php echo formatCurrency($w['amount'], true); ?></strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between mb-1 small text-danger">
                                                                    <span>TDS (5%) + Admin (5%):</span>
                                                                    <strong class="font-monospace">-<?php echo formatCurrency($w['tds_deduction'] + $w['admin_charge'], true); ?></strong>
                                                                </div>
                                                                <div class="d-flex justify-content-between border-top pt-1">
                                                                    <span class="fw-bold">Net Payout Amount:</span>
                                                                    <strong class="text-success fs-5 font-monospace"><?php echo formatCurrency($w['net_amount'], true); ?></strong>
                                                                </div>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">Bank UTR / Transaction Reference</label>
                                                                <input type="text" name="transaction_ref" class="form-control font-monospace" placeholder="e.g. UTR1234567890" required>
                                                                <span class="text-muted text-xxs">Bank reference number proving disbursement.</span>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">Admin Remarks</label>
                                                                <textarea name="admin_remarks" class="form-control form-control-sm" rows="2" placeholder="e.g. Disbursed successfully via NEFT/UPI."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success btn-sm fw-bold">
                                                                <i class="bi bi-check-lg me-1"></i> Confirm &amp; Mark Paid
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal<?php echo $w['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog text-start">
                                                <div class="modal-content">
                                                    <form method="POST" action="">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <input type="hidden" name="withdrawal_id" value="<?php echo $w['id']; ?>">

                                                        <div class="modal-header bg-danger text-white">
                                                            <h6 class="modal-title fw-bold">
                                                                <i class="bi bi-x-circle me-1"></i> Reject &amp; Refund Payout #<?php echo $w['id']; ?>
                                                            </h6>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-warning small mb-3">
                                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                                Rejecting this request will <strong>automatically refund <?php echo formatCurrency($w['amount'], true); ?></strong> back to the member's wallet via a safe row-locked transaction ledger record.
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">Reason for Rejection <span class="text-danger">*</span></label>
                                                                <textarea name="admin_remarks" class="form-control" rows="3" placeholder="e.g. Invalid bank account number or mismatching IFSC code. Please update profile." required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger btn-sm fw-bold">
                                                                <i class="bi bi-arrow-counterclockwise me-1"></i> Confirm Rejection &amp; Refund
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted text-xxs font-monospace">
                                            Processed: <?php echo !empty($w['processed_at']) ? date('d M Y', strtotime($w['processed_at'])) : date('d M Y', strtotime($w['updated_at'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
            <span class="text-muted small">
                Showing page <strong><?php echo $page; ?></strong> of <strong><?php echo $totalPages; ?></strong> (<?php echo $totalRecords; ?> records)
            </span>
            <nav aria-label="Withdrawal pages">
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?status=<?php echo urlencode($currentStatus); ?>&q=<?php echo urlencode($search); ?>&page=<?php echo ($page - 1); ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?status=<?php echo urlencode($currentStatus); ?>&q=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?status=<?php echo urlencode($currentStatus); ?>&q=<?php echo urlencode($search); ?>&page=<?php echo ($page + 1); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

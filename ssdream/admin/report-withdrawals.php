<?php
/**
 * Social Service Dream - Withdrawals & Payouts Report
 * Target: C:\xampp\htdocs\ssdream\admin\report-withdrawals.php
 */

$pageTitle = "Withdrawals & Payouts Report - SS Dream Admin";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

$filters = [
    'user'      => trim($_GET['user'] ?? ''),
    'status'    => trim($_GET['status'] ?? ''),
    'date_from' => trim($_GET['date_from'] ?? ''),
    'date_to'   => trim($_GET['date_to'] ?? '')
];

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

$reportData = getReportWithdrawals($filters, $limit, $offset);
$withdrawals = $reportData['records'];
$total = $reportData['total'];
$totalPages = max(1, (int)ceil($total / $limit));

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="text-decoration-none text-muted small">
            &larr; Back to Reports Portal
        </a>
        <h3 class="fw-bold text-navy mb-0 mt-1" style="color: #0b192c;">6. Withdrawals &amp; Payouts Report</h3>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/admin/export-withdrawals.php?<?php echo http_build_query($filters); ?>" class="btn btn-success btn-sm rounded-pill px-3">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Member</label>
                <input type="text" name="user" class="form-control form-control-sm" placeholder="Username / Name..." value="<?php echo e($filters['user']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses (Pending, Approved, Paid, Rejected)</option>
                    <option value="pending" <?php echo strtolower($filters['status']) === 'pending' ? 'selected' : ''; ?>>PENDING</option>
                    <option value="approved" <?php echo strtolower($filters['status']) === 'approved' ? 'selected' : ''; ?>>APPROVED / PAID</option>
                    <option value="rejected" <?php echo strtolower($filters['status']) === 'rejected' ? 'selected' : ''; ?>>REJECTED</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo e($filters['date_from']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo e($filters['date_to']); ?>">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-filter"></i> Filter</button>
                <a href="<?php echo BASE_URL; ?>/admin/report-withdrawals.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Withdrawal Payout Log (<?php echo number_format($total); ?> Records)</h6>
        <span class="badge bg-secondary-subtle text-secondary">5% TDS + 5% Admin Fee</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Member</th>
                        <th>Gross Request</th>
                        <th>Deductions (10%)</th>
                        <th>Net Payout</th>
                        <th>Status</th>
                        <th>Payment Mode</th>
                        <th>Ref / UTR</th>
                        <th class="pe-3">Requested Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($withdrawals)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">No withdrawal records found matching filters.</td></tr>
                    <?php else: ?>
                        <?php foreach ($withdrawals as $w): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#<?php echo (int)$w['id']; ?></td>
                                <td>
                                    <div class="fw-bold text-navy"><?php echo e($w['full_name']); ?></div>
                                    <div class="badge bg-light text-dark border text-xxs">@<?php echo e($w['username']); ?></div>
                                </td>
                                <td class="fw-bold text-dark"><?php echo formatCurrency($w['amount'], true); ?></td>
                                <td class="text-danger small">
                                    -<?php echo formatCurrency(($w['tds_deduction'] ?? 0) + ($w['admin_charge'] ?? 0), true); ?>
                                </td>
                                <td class="fw-bold text-success fs-6"><?php echo formatCurrency($w['net_amount'], true); ?></td>
                                <td>
                                    <?php if ($w['status'] === 'approved' || $w['status'] === 'paid'): ?>
                                        <span class="badge bg-success-subtle text-success border">PAID / APPROVED</span>
                                    <?php elseif ($w['status'] === 'rejected'): ?>
                                        <span class="badge bg-danger-subtle text-danger border">REJECTED</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border">PENDING</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted"><?php echo e($w['payment_mode'] ?? 'Bank/UPI'); ?></td>
                                <td><code class="small text-muted"><?php echo e($w['transaction_ref'] ?? ('WTH-' . $w['id'])); ?></code></td>
                                <td class="pe-3 small text-muted"><?php echo date('d M Y, h:i A', strtotime($w['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white py-2 d-flex justify-content-between align-items-center">
            <div class="small text-muted">Page <?php echo $page; ?> of <?php echo $totalPages; ?></div>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($filters, ['page' => $p])); ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

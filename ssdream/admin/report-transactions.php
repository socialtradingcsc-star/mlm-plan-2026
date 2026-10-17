<?php
/**
 * Social Service Dream - Transactions Ledger Audit Report
 * Target: C:\xampp\htdocs\ssdream\admin\report-transactions.php
 */

$pageTitle = "Transactions Ledger Report - SS Dream Admin";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

$filters = [
    'user'      => trim($_GET['user'] ?? ''),
    'type'      => trim($_GET['type'] ?? ''),
    'category'  => trim($_GET['category'] ?? ''),
    'date_from' => trim($_GET['date_from'] ?? ''),
    'date_to'   => trim($_GET['date_to'] ?? '')
];

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

$reportData = getReportTransactions($filters, $limit, $offset);
$txs = $reportData['records'];
$total = $reportData['total'];
$totalPages = max(1, (int)ceil($total / $limit));

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="text-decoration-none text-muted small">
            &larr; Back to Reports Portal
        </a>
        <h3 class="fw-bold text-navy mb-0 mt-1" style="color: #0b192c;">8. Financial Transactions Ledger Report</h3>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/admin/export-transactions.php?<?php echo http_build_query($filters); ?>" class="btn btn-success btn-sm rounded-pill px-3">
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
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="credit" <?php echo strtolower($filters['type']) === 'credit' ? 'selected' : ''; ?>>Credit</option>
                    <option value="debit" <?php echo strtolower($filters['type']) === 'debit' ? 'selected' : ''; ?>>Debit</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Category</label>
                <input type="text" name="category" class="form-control form-control-sm" placeholder="direct_income, withdrawal..." value="<?php echo e($filters['category']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo e($filters['date_from']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo e($filters['date_to']); ?>">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-filter"></i></button>
                <a href="<?php echo BASE_URL; ?>/admin/report-transactions.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Ledger Entry Audit Records (<?php echo number_format($total); ?> Total)</h6>
        <span class="badge bg-secondary-subtle text-secondary">Immutable Ledger</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Member</th>
                        <th>Type / Category</th>
                        <th>Amount</th>
                        <th>Balance Before</th>
                        <th>Balance After</th>
                        <th>Reference</th>
                        <th class="pe-3">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($txs)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No transaction records found matching filters.</td></tr>
                    <?php else: ?>
                        <?php foreach ($txs as $t): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#<?php echo (int)$t['id']; ?></td>
                                <td>
                                    <div class="fw-bold text-navy"><?php echo e($t['full_name']); ?></div>
                                    <div class="badge bg-light text-dark border text-xxs">@<?php echo e($t['username']); ?></div>
                                </td>
                                <td>
                                    <?php if ($t['transaction_type'] === 'credit'): ?>
                                        <span class="badge bg-success-subtle text-success border">CREDIT</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border">DEBIT</span>
                                    <?php endif; ?>
                                    <span class="badge bg-secondary-subtle text-secondary border text-xxs ms-1"><?php echo e($t['category']); ?></span>
                                </td>
                                <td class="fw-bold fs-6 <?php echo $t['transaction_type'] === 'credit' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $t['transaction_type'] === 'credit' ? '+' : '-'; ?><?php echo formatCurrency($t['amount'], true); ?>
                                </td>
                                <td class="small text-muted"><?php echo formatCurrency($t['balance_before'] ?? 0, true); ?></td>
                                <td class="small fw-semibold text-dark"><?php echo formatCurrency($t['balance_after'] ?? 0, true); ?></td>
                                <td><code class="small text-muted"><?php echo e($t['reference_no'] ?? ('TXN-' . $t['id'])); ?></code></td>
                                <td class="pe-3 small text-muted"><?php echo date('d M Y, h:i A', strtotime($t['created_at'])); ?></td>
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

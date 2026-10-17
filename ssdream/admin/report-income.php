<?php
/**
 * Social Service Dream - Income Earnings Breakdown Report
 * Target: C:\xampp\htdocs\ssdream\admin\report-income.php
 * Strictly enforces LOCKED rules: Direct 100%, Level 50% (L2-L7), Sponsor 50% (L2-Unlimited)
 */

$pageTitle = "Income Breakdown Report - SS Dream Admin";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

$filters = [
    'user'      => trim($_GET['user'] ?? ''),
    'type'      => trim($_GET['type'] ?? ''),
    'level'     => trim($_GET['level'] ?? ''),
    'date_from' => trim($_GET['date_from'] ?? ''),
    'date_to'   => trim($_GET['date_to'] ?? '')
];

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

$reportData = getReportIncomes($filters, $limit, $offset);
$incomes = $reportData['records'];
$total = $reportData['total'];
$totalPages = max(1, (int)ceil($total / $limit));

$summary = getReportIncomeSummary($filters);

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="text-decoration-none text-muted small">
            &larr; Back to Reports Portal
        </a>
        <h3 class="fw-bold text-navy mb-0 mt-1" style="color: #0b192c;">2. Income Breakdown Report</h3>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/admin/export-income.php?<?php echo http_build_query($filters); ?>" class="btn btn-success btn-sm rounded-pill px-3">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body p-3">
                <div class="small text-white-50 text-uppercase fw-semibold">Direct Income (100%)</div>
                <h4 class="fw-bold mb-0 mt-1"><?php echo formatCurrency($summary['direct'], true); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-dark">
            <div class="card-body p-3">
                <div class="small text-dark-50 text-uppercase fw-semibold">Level Income (50% L2–L7)</div>
                <h4 class="fw-bold mb-0 mt-1"><?php echo formatCurrency($summary['level'], true); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body p-3">
                <div class="small text-white-50 text-uppercase fw-semibold">Sponsor Income (50% L2–Unlimited)</div>
                <h4 class="fw-bold mb-0 mt-1"><?php echo formatCurrency($summary['sponsor'], true); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-dark text-white">
            <div class="card-body p-3">
                <div class="small text-white-50 text-uppercase fw-semibold">Grand Total Income</div>
                <h4 class="fw-bold mb-0 mt-1"><?php echo formatCurrency($summary['total'], true); ?></h4>
            </div>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="card border-0 shadow-sm mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Beneficiary Member</label>
                <input type="text" name="user" class="form-control form-control-sm" placeholder="Username, Name, User ID..." value="<?php echo e($filters['user']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Income Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types (Direct, Level, Sponsor)</option>
                    <option value="DIRECT" <?php echo strtoupper($filters['type']) === 'DIRECT' ? 'selected' : ''; ?>>DIRECT (100%)</option>
                    <option value="LEVEL" <?php echo strtoupper($filters['type']) === 'LEVEL' ? 'selected' : ''; ?>>LEVEL (50%)</option>
                    <option value="SPONSOR" <?php echo strtoupper($filters['type']) === 'SPONSOR' ? 'selected' : ''; ?>>SPONSOR (50%)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Level</label>
                <select name="level" class="form-select form-select-sm">
                    <option value="">All Levels (1–7)</option>
                    <?php for ($i=1; $i<=7; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $filters['level'] == $i ? 'selected' : ''; ?>>Level <?php echo $i; ?></option>
                    <?php endfor; ?>
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
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-filter"></i></button>
                <a href="<?php echo BASE_URL; ?>/admin/report-income.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Income Ledger Table -->
<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Income Ledger Entries (<?php echo number_format($total); ?> Records)</h6>
        <span class="badge bg-secondary-subtle text-secondary">READ-ONLY AUDIT</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Income ID</th>
                        <th>Beneficiary User</th>
                        <th>Income Type</th>
                        <th>Source Member</th>
                        <th>Level</th>
                        <th>Amount</th>
                        <th>Transaction Ref</th>
                        <th class="pe-3">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($incomes)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No income records found matching specified criteria.</td></tr>
                    <?php else: ?>
                        <?php foreach ($incomes as $inc): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#<?php echo (int)$inc['id']; ?></td>
                                <td>
                                    <div class="fw-bold text-navy"><?php echo e($inc['full_name']); ?></div>
                                    <div class="badge bg-light text-dark border text-xxs">@<?php echo e($inc['username']); ?></div>
                                </td>
                                <td>
                                    <?php if ($inc['income_type'] === 'DIRECT'): ?>
                                        <span class="badge bg-primary">DIRECT (100%)</span>
                                    <?php elseif ($inc['income_type'] === 'LEVEL'): ?>
                                        <span class="badge bg-info text-dark">LEVEL (50%)</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">SPONSOR (50%)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($inc['source_username']): ?>
                                        <div class="fw-semibold text-dark"><?php echo e($inc['source_full_name']); ?></div>
                                        <div class="text-muted text-xxs">@<?php echo e($inc['source_username']); ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary">Level <?php echo (int)$inc['level']; ?></span></td>
                                <td class="fw-bold text-success fs-6"><?php echo formatCurrency($inc['amount'], true); ?></td>
                                <td><code class="small text-muted"><?php echo e($inc['transaction_ref'] ?? ('INC-' . $inc['id'])); ?></code></td>
                                <td class="pe-3 small text-muted"><?php echo date('d M Y, h:i A', strtotime($inc['created_at'])); ?></td>
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

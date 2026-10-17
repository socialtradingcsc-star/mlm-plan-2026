<?php
/**
 * Social Service Dream - Package Upgrade Requests Report
 * Target: C:\xampp\htdocs\ssdream\admin\report-upgrades.php
 */

$pageTitle = "Upgrades Audit Report - SS Dream Admin";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

$filters = [
    'user'            => trim($_GET['user'] ?? ''),
    'level'           => trim($_GET['level'] ?? ''),
    'requested_level' => trim($_GET['requested_level'] ?? ''),
    'status'          => trim($_GET['status'] ?? ''),
    'date_from'       => trim($_GET['date_from'] ?? ''),
    'date_to'         => trim($_GET['date_to'] ?? '')
];

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

$reportData = getReportUpgrades($filters, $limit, $offset);
$upgrades = $reportData['records'];
$total = $reportData['total'];
$totalPages = max(1, (int)ceil($total / $limit));

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="text-decoration-none text-muted small">
            &larr; Back to Reports Portal
        </a>
        <h3 class="fw-bold text-navy mb-0 mt-1" style="color: #0b192c;">5. Package Upgrades Audit Report</h3>
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
                <label class="form-label text-muted small fw-semibold">Target Level</label>
                <select name="requested_level" class="form-select form-select-sm">
                    <option value="">All Requested Levels</option>
                    <?php for ($i=2; $i<=7; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $filters['requested_level'] == $i ? 'selected' : ''; ?>>Level <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $filters['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $filters['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
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
                <a href="<?php echo BASE_URL; ?>/admin/report-upgrades.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Upgrade Log Records (<?php echo number_format($total); ?> Total)</h6>
        <span class="badge bg-secondary-subtle text-secondary">Read-Only</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Member</th>
                        <th>Progression</th>
                        <th>Package Amount</th>
                        <th>Payment Mode</th>
                        <th>Status</th>
                        <th>Requested Date</th>
                        <th class="pe-3">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($upgrades)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No upgrade records found matching filters.</td></tr>
                    <?php else: ?>
                        <?php foreach ($upgrades as $upg): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#<?php echo (int)$upg['id']; ?></td>
                                <td>
                                    <div class="fw-bold text-navy"><?php echo e($upg['full_name']); ?></div>
                                    <div class="badge bg-light text-dark border text-xxs">@<?php echo e($upg['username']); ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">Level <?php echo (int)$upg['from_level']; ?></span>
                                    <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                    <span class="badge bg-primary">Level <?php echo (int)$upg['to_level']; ?></span>
                                </td>
                                <td class="fw-bold text-dark"><?php echo formatCurrency($upg['amount']); ?></td>
                                <td class="small text-muted"><?php echo e($upg['payment_mode'] ?? 'Wallet'); ?></td>
                                <td>
                                    <?php if ($upg['status'] === 'approved'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>
                                    <?php elseif ($upg['status'] === 'rejected'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted"><?php echo date('d M Y, h:i A', strtotime($upg['created_at'])); ?></td>
                                <td class="pe-3 text-muted small"><?php echo e($upg['admin_remarks'] ?? '&mdash;'); ?></td>
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

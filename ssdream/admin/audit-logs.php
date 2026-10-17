<?php
/**
 * Social Service Dream - Admin Activity Audit Trail
 * Target: C:\xampp\htdocs\ssdream\admin\audit-logs.php
 */

$pageTitle = "System Audit Trail - SS Dream Admin";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

$filters = [
    'action'   => trim($_GET['action'] ?? ''),
    'admin_id' => trim($_GET['admin_id'] ?? '')
];

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 30;
$offset = ($page - 1) * $limit;

$logData = getAdminActivityLogs($filters, $limit, $offset);
$logs = $logData['records'];
$total = $logData['total'];
$totalPages = max(1, (int)ceil($total / $limit));

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="text-decoration-none text-muted small">
            &larr; Back to Reports
        </a>
        <h3 class="fw-bold text-navy mb-0 mt-1" style="color: #0b192c;">Administrator Activity Audit Log</h3>
        <p class="text-muted small mb-0">Security log recording operator actions, approval timestamps, and system configurations.</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-end">
            <div class="col-md-9">
                <label class="form-label text-muted small fw-semibold">Action Keyword Filter</label>
                <input type="text" name="action" class="form-control form-control-sm" placeholder="e.g., APPROVE_UPGRADE, UPDATE_SETTINGS, BROADCAST..." value="<?php echo e($filters['action']); ?>">
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-filter"></i> Filter</button>
                <a href="<?php echo BASE_URL; ?>/admin/audit-logs.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
            <i class="bi bi-shield-check text-primary me-2"></i> Audit Records Log (<?php echo number_format($total); ?> Records)
        </h6>
        <span class="badge bg-dark text-white">Immutable Security Log</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Operator</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>Details</th>
                        <th>IP Address</th>
                        <th class="pe-3">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No activity logs found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#<?php echo (int)$l['id']; ?></td>
                                <td>
                                    <div class="fw-bold text-navy"><?php echo e($l['admin_username']); ?></div>
                                    <div class="text-muted text-xxs">ID: <?php echo (int)$l['admin_id']; ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                        <?php echo e($l['action']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($l['target_type'])): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border text-xxs">
                                            <?php echo e($l['target_type']); ?> <?php echo !empty($l['target_id']) ? ('#' . $l['target_id']) : ''; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small text-dark"><?php echo e($l['details'] ?? '&mdash;'); ?></div>
                                </td>
                                <td><code class="small text-muted"><?php echo e($l['ip_address'] ?? '127.0.0.1'); ?></code></td>
                                <td class="pe-3 small text-muted"><?php echo date('d M Y, h:i A', strtotime($l['created_at'])); ?></td>
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

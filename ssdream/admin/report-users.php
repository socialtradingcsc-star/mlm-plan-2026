<?php
/**
 * Social Service Dream - Member Directory Report
 * Target: C:\xampp\htdocs\ssdream\admin\report-users.php
 */

$pageTitle = "User Directory Report - SS Dream Admin";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

$filters = [
    'search'    => trim($_GET['search'] ?? ''),
    'level'     => trim($_GET['level'] ?? ''),
    'status'    => trim($_GET['status'] ?? ''),
    'sponsor'   => trim($_GET['sponsor'] ?? ''),
    'date_from' => trim($_GET['date_from'] ?? ''),
    'date_to'   => trim($_GET['date_to'] ?? '')
];

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

$reportData = getReportUsers($filters, $limit, $offset);
$users = $reportData['records'];
$total = $reportData['total'];
$totalPages = max(1, (int)ceil($total / $limit));

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="text-decoration-none text-muted small">
            &larr; Back to Reports Portal
        </a>
        <h3 class="fw-bold text-navy mb-0 mt-1" style="color: #0b192c;">1. Users Directory Report</h3>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/admin/export-users.php?<?php echo http_build_query($filters); ?>" class="btn btn-success btn-sm rounded-pill px-3">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
    </div>
</div>

<!-- Filters Form -->
<div class="card border-0 shadow-sm mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Search Member</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Username, Name, Email, Phone..." value="<?php echo e($filters['search']); ?>">
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
                <label class="form-label text-muted small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="blocked" <?php echo $filters['status'] === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
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
                <button type="submit" class="btn btn-primary btn-sm w-100" title="Apply Filters">
                    <i class="bi bi-filter"></i>
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/report-users.php" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Filtered User Records (<?php echo number_format($total); ?> Total)</h6>
        <span class="badge bg-secondary-subtle text-secondary">Read-Only Report</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">User ID</th>
                        <th>Member Details</th>
                        <th>Contact Info</th>
                        <th>Sponsor</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th class="pe-3">Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No member records match the specified filters.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#<?php echo (int)$u['id']; ?></td>
                                <td>
                                    <div class="fw-bold text-navy"><?php echo e($u['full_name']); ?></div>
                                    <div class="badge bg-light text-dark border font-monospace text-xxs">@<?php echo e($u['username']); ?></div>
                                </td>
                                <td>
                                    <div class="small"><i class="bi bi-telephone text-muted me-1"></i><?php echo e($u['mobile']); ?></div>
                                    <div class="text-muted text-xxs"><i class="bi bi-envelope text-muted me-1"></i><?php echo e($u['email']); ?></div>
                                </td>
                                <td>
                                    <?php if ($u['sponsor_username']): ?>
                                        <div class="fw-semibold text-dark"><?php echo e($u['sponsor_name']); ?></div>
                                        <div class="text-muted text-xxs">@<?php echo e($u['sponsor_username']); ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">&mdash; Root &mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-primary">Level <?php echo (int)$u['current_level']; ?></span></td>
                                <td>
                                    <?php if ($u['status'] === 'active'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                    <?php elseif ($u['status'] === 'blocked'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Blocked</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3 small text-muted"><?php echo date('d M Y, h:i A', strtotime($u['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination Footer -->
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

<?php
/**
 * Social Service Dream - Binary Team Structure Report
 * Target: C:\xampp\htdocs\ssdream\admin\report-team.php
 */

$pageTitle = "Binary Team Report - SS Dream Admin";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

$filters = [
    'user'     => trim($_GET['user'] ?? ''),
    'sponsor'  => trim($_GET['sponsor'] ?? ''),
    'level'    => trim($_GET['level'] ?? ''),
    'position' => trim($_GET['position'] ?? ''),
    'status'   => trim($_GET['status'] ?? '')
];

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

$reportData = getReportTeam($filters, $limit, $offset);
$team = $reportData['records'];
$total = $reportData['total'];
$totalPages = max(1, (int)ceil($total / $limit));

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="text-decoration-none text-muted small">
            &larr; Back to Reports Portal
        </a>
        <h3 class="fw-bold text-navy mb-0 mt-1" style="color: #0b192c;">4. Binary Team Structure Report</h3>
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
                <label class="form-label text-muted small fw-semibold">Sponsor</label>
                <input type="text" name="sponsor" class="form-control form-control-sm" placeholder="Sponsor Username..." value="<?php echo e($filters['sponsor']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Level</label>
                <select name="level" class="form-select form-select-sm">
                    <option value="">All Levels</option>
                    <?php for ($i=1; $i<=7; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $filters['level'] == $i ? 'selected' : ''; ?>>Level <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Position</label>
                <select name="position" class="form-select form-select-sm">
                    <option value="">All Positions</option>
                    <option value="left" <?php echo strtolower($filters['position']) === 'left' ? 'selected' : ''; ?>>LEFT</option>
                    <option value="right" <?php echo strtolower($filters['position']) === 'right' ? 'selected' : ''; ?>>RIGHT</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label text-muted small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-filter"></i></button>
                <a href="<?php echo BASE_URL; ?>/admin/report-team.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Binary Team Records (<?php echo number_format($total); ?> Total)</h6>
        <span class="badge bg-secondary-subtle text-secondary">Read-Only</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">User</th>
                        <th>Sponsor</th>
                        <th>Binary Parent</th>
                        <th>Position</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th class="pe-3">Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($team)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No team records found matching filters.</td></tr>
                    <?php else: ?>
                        <?php foreach ($team as $tm): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-navy"><?php echo e($tm['full_name']); ?></div>
                                    <div class="badge bg-light text-dark border text-xxs">@<?php echo e($tm['username']); ?></div>
                                </td>
                                <td>
                                    <?php if ($tm['sponsor_username']): ?>
                                        <div class="fw-semibold text-dark"><?php echo e($tm['sponsor_name']); ?></div>
                                        <div class="text-muted text-xxs">@<?php echo e($tm['sponsor_username']); ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">&mdash; Root &mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($tm['parent_username']): ?>
                                        <div class="fw-semibold text-dark"><?php echo e($tm['parent_name']); ?></div>
                                        <div class="text-muted text-xxs">@<?php echo e($tm['parent_username']); ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">&mdash; Root &mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (strtolower($tm['position'] ?? '') === 'left'): ?>
                                        <span class="badge bg-primary-subtle text-primary border px-2">LEFT</span>
                                    <?php elseif (strtolower($tm['position'] ?? '') === 'right'): ?>
                                        <span class="badge bg-info-subtle text-info border px-2">RIGHT</span>
                                    <?php else: ?>
                                        <span class="text-muted small">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-primary">Level <?php echo (int)$tm['current_level']; ?></span></td>
                                <td>
                                    <?php if ($tm['status'] === 'active'): ?>
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3 small text-muted"><?php echo date('d M Y, h:i A', strtotime($tm['created_at'])); ?></td>
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

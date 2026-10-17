<?php
/**
 * Social Service Dream - Sponsor Performance Report
 * Target: C:\xampp\htdocs\ssdream\admin\report-sponsor.php
 */

$pageTitle = "Sponsor Performance Report - SS Dream Admin";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

$filters = [
    'search' => trim($_GET['search'] ?? '')
];

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

$reportData = getReportSponsor($filters, $limit, $offset);
$sponsors = $reportData['records'];
$total = $reportData['total'];
$totalPages = max(1, (int)ceil($total / $limit));

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="text-decoration-none text-muted small">
            &larr; Back to Reports Portal
        </a>
        <h3 class="fw-bold text-navy mb-0 mt-1" style="color: #0b192c;">3. Sponsor Performance Report</h3>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-end">
            <div class="col-md-9">
                <label class="form-label text-muted small fw-semibold">Search Sponsor</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Sponsor Username, Name, Email..." value="<?php echo e($filters['search']); ?>">
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-search me-1"></i> Search</button>
                <a href="<?php echo BASE_URL; ?>/admin/report-sponsor.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Active Sponsors Summary (<?php echo number_format($total); ?> Records)</h6>
        <span class="badge bg-secondary-subtle text-secondary">Sponsor Network</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Sponsor ID</th>
                        <th>Sponsor Details</th>
                        <th>Current Level</th>
                        <th>Total Direct Referrals</th>
                        <th>Active Direct Referrals</th>
                        <th class="pe-3">Joined Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sponsors)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No sponsor records found matching filters.</td></tr>
                    <?php else: ?>
                        <?php foreach ($sponsors as $sp): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#<?php echo (int)$sp['sponsor_id']; ?></td>
                                <td>
                                    <div class="fw-bold text-navy"><?php echo e($sp['sponsor_name']); ?></div>
                                    <div class="badge bg-light text-dark border text-xxs">@<?php echo e($sp['sponsor_username']); ?></div>
                                </td>
                                <td><span class="badge bg-primary">Level <?php echo (int)$sp['current_level']; ?></span></td>
                                <td class="fw-bold fs-6"><?php echo (int)$sp['total_direct_members']; ?> Directs</td>
                                <td><span class="badge bg-success-subtle text-success border px-2 py-1"><?php echo (int)$sp['active_direct_members']; ?> Active</span></td>
                                <td class="pe-3 small text-muted"><?php echo date('d M Y', strtotime($sp['registration_date'])); ?></td>
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

<?php
/**
 * Social Service Dream - KYC Verification Audit Report
 * Target: C:\xampp\htdocs\ssdream\admin\report-kyc.php
 */

$pageTitle = "KYC Audit Report - SS Dream Admin";
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

$reportData = getReportKyc($filters, $limit, $offset);
$kycRecords = $reportData['records'];
$total = $reportData['total'];
$totalPages = max(1, (int)ceil($total / $limit));

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="text-decoration-none text-muted small">
            &larr; Back to Reports Portal
        </a>
        <h3 class="fw-bold text-navy mb-0 mt-1" style="color: #0b192c;">7. KYC Compliance Audit Report</h3>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/admin/kyc.php" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-shield-check me-1"></i> Review Verification Desk
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
                    <option value="">All Verification Statuses</option>
                    <option value="pending" <?php echo strtolower($filters['status']) === 'pending' ? 'selected' : ''; ?>>PENDING</option>
                    <option value="approved" <?php echo strtolower($filters['status']) === 'approved' ? 'selected' : ''; ?>>APPROVED</option>
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
                <a href="<?php echo BASE_URL; ?>/admin/report-kyc.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">KYC Verification Audit Log (<?php echo number_format($total); ?> Records)</h6>
        <span class="badge bg-secondary-subtle text-secondary">Secure Private Review</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">KYC ID</th>
                        <th>Member</th>
                        <th>Document Type</th>
                        <th>Aadhaar / PAN Info</th>
                        <th>Status</th>
                        <th>Submitted Date</th>
                        <th>Reviewed Date</th>
                        <th class="pe-3">Reviewer Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kycRecords)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No KYC audit records found matching filters.</td></tr>
                    <?php else: ?>
                        <?php foreach ($kycRecords as $k): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#<?php echo (int)$k['id']; ?></td>
                                <td>
                                    <div class="fw-bold text-navy"><?php echo e($k['full_name']); ?></div>
                                    <div class="badge bg-light text-dark border text-xxs">@<?php echo e($k['username']); ?></div>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-dark border"><?php echo e($k['document_type'] ?? 'Aadhaar / PAN'); ?></span></td>
                                <td class="small font-monospace text-muted">
                                    <?php echo !empty($k['aadhaar_number']) ? ('Aadhaar: ' . e(substr($k['aadhaar_number'], 0, 4)) . '-XXXX-XXXX') : ''; ?>
                                    <?php echo !empty($k['pan_number']) ? (' | PAN: ' . e(substr($k['pan_number'], 0, 2)) . 'XXXXX' . e(substr($k['pan_number'], -1))) : ''; ?>
                                </td>
                                <td>
                                    <?php if ($k['status'] === 'approved'): ?>
                                        <span class="badge bg-success-subtle text-success border">APPROVED</span>
                                    <?php elseif ($k['status'] === 'rejected'): ?>
                                        <span class="badge bg-danger-subtle text-danger border">REJECTED</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border">PENDING</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted"><?php echo date('d M Y, h:i A', strtotime($k['created_at'])); ?></td>
                                <td class="small text-muted"><?php echo !empty($k['reviewed_at']) ? date('d M Y, h:i A', strtotime($k['reviewed_at'])) : '&mdash;'; ?></td>
                                <td class="pe-3 small text-muted"><?php echo e($k['admin_notes'] ?? '&mdash;'); ?></td>
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

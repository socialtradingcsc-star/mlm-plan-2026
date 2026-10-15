<?php
/**
 * Social Service Dream - User Upgrade History
 * Part 3/4: Complete Upgrade History Ledger with Pagination & Status Tracking
 * Target: C:\xampp\htdocs\ssdream\user\upgrade-history.php
 */

$pageTitle = "Upgrade History - Social Service Dream";
$pageHeading = "Upgrade History";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireUserLogin();

$userId = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_fullname'] ?? 'Member';
$userUsername = $_SESSION['user_username'] ?? 'user';

// Current level and pending request status
$currentLevel = getUserCurrentLevel($userId);
$currentPkg = getPackageByLevel($currentLevel);
$nextLevel = ($currentLevel < 7) ? ($currentLevel + 1) : null;
$nextPkg = $nextLevel ? getPackageByLevel($nextLevel) : null;
$pendingUpgrade = getUserPendingUpgrade($userId);

// Pagination & Filtering
$statusFilter = strtolower(trim($_GET['status'] ?? 'all'));
if (!in_array($statusFilter, ['all', 'pending', 'approved', 'rejected'], true)) {
    $statusFilter = 'all';
}
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$upgradeData = getUserPaginatedUpgradeHistory($userId, $page, $perPage, $statusFilter);
$upgrades = $upgradeData['records'];
$totalPages = $upgradeData['pages'];
$currentPage = $upgradeData['current_page'];
$totalRecords = $upgradeData['total'];
$counts = $upgradeData['counts'];

require_once __DIR__ . '/includes/header.php';
?>

<!-- ========================================================
     HEADER BANNER
======================================================== -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0b192c 0%, #1e3e62 100%); color: #ffffff; border-radius: 14px;">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-primary text-white px-3 py-1 rounded-pill small">
                        <i class="bi bi-shield-check me-1"></i> SS DREAM Architecture
                    </span>
                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill small fw-bold">
                        Current: Level <?php echo $currentLevel; ?> (<?php echo formatCurrency($currentPkg['amount'] ?? ($currentLevel === 1 ? 1200 : 2000)); ?>)
                    </span>
                    <?php if ($pendingUpgrade): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-1 rounded-pill small">
                            <i class="bi bi-hourglass-split me-1"></i> Upgrade to L<?php echo $pendingUpgrade['to_level']; ?> In Review
                        </span>
                    <?php endif; ?>
                </div>
                <h3 class="fw-bold mb-1 text-white">Package Upgrade Requests Ledger</h3>
                <p class="text-white-50 small mb-0">
                    Track all your membership level advancement applications, verification status, transaction references, and administrative review remarks.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <?php if ($pendingUpgrade): ?>
                    <a href="<?php echo BASE_URL; ?>/user/packages.php" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-eye me-1"></i> View Pending Request
                    </a>
                <?php elseif ($nextLevel): ?>
                    <a href="<?php echo BASE_URL; ?>/user/packages.php" class="btn btn-light text-primary fw-bold px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-arrow-up-circle me-1"></i> Upgrade to Level <?php echo $nextLevel; ?> &rarr;
                    </a>
                <?php else: ?>
                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill fs-7">
                        <i class="bi bi-award-fill me-1"></i> Maximum Level 7 Achieved
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================
     PENDING UPGRADE NOTICE
======================================================== -->
<?php if ($pendingUpgrade): ?>
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-between p-3 mb-4 rounded-3" role="alert">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning text-dark p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-hourglass-split fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-dark">Active Upgrade Application in Progress</h6>
                <p class="small text-muted mb-0">
                    Your request to upgrade from <strong>Level <?php echo $pendingUpgrade['from_level']; ?></strong> to <strong>Level <?php echo $pendingUpgrade['to_level']; ?></strong> (<?php echo formatCurrency($pendingUpgrade['amount']); ?>) submitted on <strong><?php echo date('d M Y, h:i A', strtotime($pendingUpgrade['created_at'])); ?></strong> is currently under administrative verification.
                </p>
            </div>
        </div>
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">
            Status: Pending Admin Review
        </span>
    </div>
<?php endif; ?>

<!-- ========================================================
     SUMMARY METRICS CARDS
======================================================== -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <a href="?status=all" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 <?php echo ($statusFilter === 'all') ? 'border-primary border-2 ring-1 ring-primary' : ''; ?>" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted text-uppercase fw-semibold">Total Requests</span>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <i class="bi bi-layers-fill"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo $counts['all']; ?></h3>
                    <span class="text-muted text-xxs">All submitted applications</span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-6">
        <a href="?status=approved" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 <?php echo ($statusFilter === 'approved') ? 'border-success border-2' : ''; ?>" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted text-uppercase fw-semibold">Approved</span>
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-success"><?php echo $counts['approved']; ?></h3>
                    <span class="text-muted text-xxs">Successfully activated upgrades</span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-6">
        <a href="?status=pending" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 <?php echo ($statusFilter === 'pending') ? 'border-warning border-2' : ''; ?>" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted text-uppercase fw-semibold">Pending Review</span>
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-warning"><?php echo $counts['pending']; ?></h3>
                    <span class="text-muted text-xxs">Awaiting admin verification</span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3 col-6">
        <a href="?status=rejected" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 <?php echo ($statusFilter === 'rejected') ? 'border-danger border-2' : ''; ?>" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted text-uppercase fw-semibold">Rejected</span>
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-danger"><?php echo $counts['rejected']; ?></h3>
                    <span class="text-muted text-xxs">Declined with feedback</span>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- ========================================================
     FILTER TABS & UPGRADE LEDGER TABLE
======================================================== -->
<div class="card border-0 shadow-sm" style="border-radius: 14px;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-primary fs-5"></i>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Upgrade Requests History</h6>
                <span class="text-muted small">Paginated audit log of all package upgrade submissions</span>
            </div>
        </div>

        <!-- Filter Pill Buttons -->
        <div class="btn-group btn-group-sm" role="group" aria-label="Status Filter">
            <a href="?status=all" class="btn <?php echo ($statusFilter === 'all') ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                All <span class="badge bg-light text-dark ms-1"><?php echo $counts['all']; ?></span>
            </a>
            <a href="?status=pending" class="btn <?php echo ($statusFilter === 'pending') ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary'; ?>">
                Pending <span class="badge bg-warning text-dark ms-1"><?php echo $counts['pending']; ?></span>
            </a>
            <a href="?status=approved" class="btn <?php echo ($statusFilter === 'approved') ? 'btn-success' : 'btn-outline-secondary'; ?>">
                Approved <span class="badge bg-success ms-1"><?php echo $counts['approved']; ?></span>
            </a>
            <a href="?status=rejected" class="btn <?php echo ($statusFilter === 'rejected') ? 'btn-danger' : 'btn-outline-secondary'; ?>">
                Rejected <span class="badge bg-danger ms-1"><?php echo $counts['rejected']; ?></span>
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Request ID</th>
                        <th>Level Progression</th>
                        <th>Target Package &amp; Amount</th>
                        <th>Payment Mode / Reference</th>
                        <th>Submission Date</th>
                        <th>Status</th>
                        <th>Admin Remarks</th>
                        <th class="pe-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($upgrades)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <h6 class="fw-bold text-dark">No upgrade requests found</h6>
                                    <p class="text-muted small mb-3">
                                        <?php if ($statusFilter !== 'all'): ?>
                                            There are no upgrade applications with status "<strong><?php echo ucfirst($statusFilter); ?></strong>".
                                        <?php else: ?>
                                            You have not submitted any package upgrade requests yet.
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($nextLevel && !$pendingUpgrade): ?>
                                        <a href="<?php echo BASE_URL; ?>/user/packages.php" class="btn btn-primary btn-sm px-3 rounded-pill">
                                            <i class="bi bi-arrow-up-circle me-1"></i> Apply for Level <?php echo $nextLevel; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($upgrades as $upg): 
                            $status = strtolower($upg['status'] ?? 'pending');
                            $statusBadge = match($status) {
                                'approved' => 'bg-success text-white',
                                'rejected' => 'bg-danger text-white',
                                default    => 'bg-warning text-dark fw-bold'
                            };
                            $statusIcon = match($status) {
                                'approved' => 'bi-check-circle-fill',
                                'rejected' => 'bi-x-circle-fill',
                                default    => 'bi-hourglass-split'
                            };
                            $toLevel = (int)$upg['to_level'];
                            $fromLevel = (int)$upg['from_level'];
                            $amount = (float)$upg['amount'];
                        ?>
                            <tr>
                                <td class="ps-4 font-monospace small fw-bold text-dark">
                                    #UPG-<?php echo str_pad((string)$upg['id'], 5, '0', STR_PAD_LEFT); ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-secondary border px-2 py-1">
                                            L<?php echo $fromLevel; ?>
                                        </span>
                                        <i class="bi bi-arrow-right text-muted small"></i>
                                        <span class="badge bg-primary text-white px-2 py-1 fw-bold">
                                            L<?php echo $toLevel; ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        Level <?php echo $toLevel; ?> Package
                                    </div>
                                    <div class="text-success small fw-semibold font-monospace">
                                        <?php echo formatCurrency($amount); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">
                                        <i class="bi bi-wallet2 text-muted me-1"></i>
                                        <?php echo e($upg['payment_mode'] ?? 'Wallet / Direct'); ?>
                                    </div>
                                    <?php if (!empty($upg['transaction_ref'])): ?>
                                        <div class="text-muted text-xxs font-monospace">
                                            Ref: <?php echo e($upg['transaction_ref']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small text-dark">
                                        <?php echo date('d M Y', strtotime($upg['created_at'])); ?>
                                    </div>
                                    <div class="text-muted text-xxs">
                                        <?php echo date('h:i A', strtotime($upg['created_at'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $statusBadge; ?> px-2.5 py-1.5 rounded-pill d-inline-flex align-items-center gap-1">
                                        <i class="bi <?php echo $statusIcon; ?>"></i>
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($upg['admin_remarks'])): ?>
                                        <span class="small text-muted d-inline-block text-truncate" style="max-width: 180px;" title="<?php echo e($upg['admin_remarks']); ?>">
                                            <?php echo e($upg['admin_remarks']); ?>
                                        </span>
                                    <?php elseif ($status === 'approved'): ?>
                                        <span class="text-success text-xxs fw-semibold">
                                            <i class="bi bi-check2"></i> Verified &amp; Activated
                                        </span>
                                    <?php elseif ($status === 'pending'): ?>
                                        <span class="text-muted text-xxs fst-italic">
                                            Pending admin action
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted text-xxs">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalUpg<?php echo $upg['id']; ?>">
                                        <i class="bi bi-eye me-1"></i> View
                                    </button>

                                    <!-- Detail Modal -->
                                    <div class="modal fade" id="modalUpg<?php echo $upg['id']; ?>" tabindex="-1" aria-labelledby="modalLabel<?php echo $upg['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header bg-dark text-white py-3">
                                                    <h6 class="modal-title fw-bold" id="modalLabel<?php echo $upg['id']; ?>">
                                                        <i class="bi bi-receipt me-1"></i> Upgrade Request #UPG-<?php echo str_pad((string)$upg['id'], 5, '0', STR_PAD_LEFT); ?>
                                                    </h6>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start p-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                                        <div>
                                                            <span class="text-muted small d-block">Application Status</span>
                                                            <span class="badge <?php echo $statusBadge; ?> px-3 py-1.5 rounded-pill fs-7 mt-1">
                                                                <i class="bi <?php echo $statusIcon; ?> me-1"></i>
                                                                <?php echo ucfirst($status); ?>
                                                            </span>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="text-muted small d-block">Official Package Amount</span>
                                                            <span class="fs-5 fw-bold text-success font-monospace">
                                                                <?php echo formatCurrency($amount); ?>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 mb-3">
                                                        <div class="col-6">
                                                            <span class="text-muted small d-block">Previous Level</span>
                                                            <span class="fw-bold text-dark">Level <?php echo $fromLevel; ?></span>
                                                        </div>
                                                        <div class="col-6">
                                                            <span class="text-muted small d-block">Target Level</span>
                                                            <span class="fw-bold text-primary">Level <?php echo $toLevel; ?></span>
                                                        </div>
                                                        <div class="col-6">
                                                            <span class="text-muted small d-block">Payment Mode</span>
                                                            <span class="fw-semibold text-dark"><?php echo e($upg['payment_mode'] ?? 'Wallet / Direct'); ?></span>
                                                        </div>
                                                        <div class="col-6">
                                                            <span class="text-muted small d-block">Transaction Ref</span>
                                                            <span class="font-monospace small text-dark"><?php echo e($upg['transaction_ref'] ?? 'N/A'); ?></span>
                                                        </div>
                                                        <div class="col-6">
                                                            <span class="text-muted small d-block">Submitted At</span>
                                                            <span class="small text-dark"><?php echo date('d M Y, h:i A', strtotime($upg['created_at'])); ?></span>
                                                        </div>
                                                        <div class="col-6">
                                                            <span class="text-muted small d-block">Reviewed At</span>
                                                            <span class="small text-dark">
                                                                <?php echo !empty($upg['approved_at']) ? date('d M Y, h:i A', strtotime($upg['approved_at'])) : 'Pending review'; ?>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <?php if (!empty($upg['admin_remarks'])): ?>
                                                        <div class="p-3 bg-light rounded-3 border">
                                                            <span class="small fw-bold text-dark d-block mb-1">
                                                                <i class="bi bi-chat-left-quote text-primary me-1"></i> Admin Remarks:
                                                            </span>
                                                            <p class="small text-muted mb-0"><?php echo nl2br(e($upg['admin_remarks'])); ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer bg-light py-2">
                                                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                                                    <a href="<?php echo BASE_URL; ?>/user/packages.php" class="btn btn-primary btn-sm px-3">
                                                        View All Packages
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Footer -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="small text-muted">
                Showing <strong><?php echo (($currentPage - 1) * $perPage) + 1; ?></strong> to 
                <strong><?php echo min($totalRecords, $currentPage * $perPage); ?></strong> of 
                <strong><?php echo $totalRecords; ?></strong> upgrade applications
            </span>

            <nav aria-label="Upgrade history pagination">
                <ul class="pagination pagination-sm mb-0">
                    <!-- Prev Button -->
                    <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?status=<?php echo urlencode($statusFilter); ?>&page=<?php echo ($currentPage - 1); ?>" tabindex="-1">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                            <a class="page-link" href="?status=<?php echo urlencode($statusFilter); ?>&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?status=<?php echo urlencode($statusFilter); ?>&page=<?php echo ($currentPage + 1); ?>">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

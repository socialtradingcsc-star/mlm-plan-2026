<?php
/**
 * Social Service Dream - Admin KYC Document Verification Center
 * =========================================================================
 * PART 7: KYC COMPLIANCE & APPROVAL ENGINE
 * Target: C:\xampp\htdocs\ssdream\admin\kyc.php
 * =========================================================================
 */

$pageTitle = "KYC Verification - SS Dream Admin";
$pageHeading = "Member KYC Document Verification";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireAdminLogin();

$alert = null;
$adminId = (int)($_SESSION['admin_id'] ?? 1);

// Handle KYC Action (Approve / Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $alert = ['type' => 'danger', 'msg' => 'Security session expired. Please refresh and try again.'];
    } else {
        $action = trim($_POST['action']);
        $kycId = (int)($_POST['kyc_id'] ?? 0);
        $adminRemarks = trim($_POST['admin_notes'] ?? '');

        if ($action === 'approve') {
            $res = processAdminKycAction($kycId, 'approve', $adminRemarks, $adminId);
            $alert = ['type' => $res['success'] ? 'success' : 'danger', 'msg' => $res['message']];
        } elseif ($action === 'reject') {
            if (empty($adminRemarks)) {
                $alert = ['type' => 'danger', 'msg' => 'Please provide a reason for rejecting the KYC document.'];
            } else {
                $res = processAdminKycAction($kycId, 'reject', $adminRemarks, $adminId);
                $alert = ['type' => $res['success'] ? 'success' : 'danger', 'msg' => $res['message']];
            }
        }
    }
}

// Filter by Status & Pagination
$currentStatus = strtolower(trim($_GET['status'] ?? 'pending'));
if (!in_array($currentStatus, ['all', 'pending', 'approved', 'rejected'])) {
    $currentStatus = 'all';
}
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

$kycStats = getKycCountStats();
$kycData = getAdminKycRequests($currentStatus, $limit, $offset);
$requests = $kycData['records'];
$totalRecords = $kycData['total'];
$totalPages = ceil($totalRecords / $limit);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-white-50 small">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin/index.php" class="text-decoration-none text-gold">Dashboard</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">KYC Management</li>
                </ol>
            </nav>
            <h2 class="h4 text-white fw-bold mb-0">
                <i class="bi bi-card-checklist text-gold me-2"></i> Member KYC Verification
            </h2>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-25 px-3 py-2">
                Pending Reviews: <strong><?php echo $kycStats['pending']; ?></strong>
            </span>
        </div>
    </div>

    <?php if ($alert): ?>
        <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-<?php echo ($alert['type'] === 'success') ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> me-2"></i>
            <?php echo e($alert['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-white-50 small">Pending KYC</span>
                        <div class="p-2 rounded bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-hourglass-split fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fs-4 text-warning fw-bold mb-0"><?php echo number_format($kycStats['pending']); ?></h3>
                    <small class="text-white-50">Requires admin action</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-white-50 small">Approved KYC</span>
                        <div class="p-2 rounded bg-success bg-opacity-10 text-success">
                            <i class="bi bi-patch-check-fill fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fs-4 text-success fw-bold mb-0"><?php echo number_format($kycStats['approved']); ?></h3>
                    <small class="text-white-50">Eligible for payouts</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-white-50 small">Rejected KYC</span>
                        <div class="p-2 rounded bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-x-octagon-fill fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fs-4 text-danger fw-bold mb-0"><?php echo number_format($kycStats['rejected']); ?></h3>
                    <small class="text-white-50">Need re-upload</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-white-50 small">Total Submitted</span>
                        <div class="p-2 rounded bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-files fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fs-4 text-white fw-bold mb-0"><?php echo number_format($kycStats['total']); ?></h3>
                    <small class="text-white-50">All submissions</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Navigation Tabs -->
    <div class="card bg-dark border-secondary border-opacity-25 shadow-sm mb-4">
        <div class="card-body p-2">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentStatus === 'pending') ? 'active bg-gold text-dark fw-bold' : 'text-white-50'; ?>" href="?status=pending">
                        <i class="bi bi-hourglass-split me-1"></i> Pending Verifications
                        <span class="badge bg-warning text-dark ms-1"><?php echo $kycStats['pending']; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentStatus === 'approved') ? 'active bg-gold text-dark fw-bold' : 'text-white-50'; ?>" href="?status=approved">
                        <i class="bi bi-check-circle me-1"></i> Approved (<?php echo $kycStats['approved']; ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentStatus === 'rejected') ? 'active bg-gold text-dark fw-bold' : 'text-white-50'; ?>" href="?status=rejected">
                        <i class="bi bi-x-circle me-1"></i> Rejected (<?php echo $kycStats['rejected']; ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentStatus === 'all') ? 'active bg-gold text-dark fw-bold' : 'text-white-50'; ?>" href="?status=all">
                        <i class="bi bi-list-ul me-1"></i> All Submissions (<?php echo $kycStats['total']; ?>)
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- KYC Table -->
    <div class="card bg-dark border-secondary border-opacity-25 shadow-sm">
        <div class="card-header bg-black bg-opacity-40 border-secondary border-opacity-25 py-3">
            <h5 class="card-title text-white mb-0 fs-6">
                <i class="bi bi-table me-2 text-gold"></i> Submissions List (<?php echo number_format($totalRecords); ?>)
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0 border-secondary border-opacity-25">
                <thead class="text-white-50 text-uppercase small bg-black bg-opacity-50">
                    <tr>
                        <th class="ps-3 py-3">ID / Member</th>
                        <th>Document Details</th>
                        <th>Bank / Payout Info</th>
                        <th>Document Proof</th>
                        <th>Status</th>
                        <th>Submission Date</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-white-50">
                                <i class="bi bi-card-checklist fs-1 d-block mb-2 text-secondary"></i>
                                No KYC requests found under "<?php echo strtoupper($currentStatus); ?>".
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $k): ?>
                            <tr>
                                <td class="ps-3 py-3">
                                    <div class="fw-semibold text-white"><?php echo e($k['full_name']); ?></div>
                                    <div class="text-gold font-monospace small">@<?php echo e($k['username']); ?></div>
                                    <div class="text-white-50 small"><i class="bi bi-telephone text-white-50 me-1"></i> <?php echo e($k['mobile']); ?></div>
                                    <span class="badge bg-secondary bg-opacity-50 text-xxs">Level <?php echo $k['current_level']; ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-white">
                                        <i class="bi bi-file-earmark-person text-info me-1"></i>
                                        <?php echo e($k['document_type'] ?? 'Aadhar Card / PAN'); ?>
                                    </div>
                                    <?php if (!empty($k['document_number'])): ?>
                                        <div class="font-monospace text-warning small mt-1">
                                            No: <?php echo e($k['document_number']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($k['bank_name']) || !empty($k['upi_id'])): ?>
                                        <div class="small text-white">
                                            <strong><?php echo e($k['bank_name'] ?? 'Bank Account'); ?></strong>
                                        </div>
                                        <?php if (!empty($k['account_number'])): ?>
                                            <div class="font-monospace text-white-50 text-xxs">
                                                A/C: <?php echo 'XXXX' . substr($k['account_number'], -4); ?> (IFSC: <?php echo e($k['ifsc_code'] ?? ''); ?>)
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($k['upi_id'])): ?>
                                            <div class="font-monospace text-info text-xxs mt-1">
                                                UPI: <?php echo e($k['upi_id']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-white-50 small fst-italic">No bank saved yet</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($k['pan_card_path'])): ?>
                                        <a href="<?php echo BASE_URL . '/' . e($k['pan_card_path']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-file-earmark-arrow-down me-1"></i> View Document
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary text-white-50">No file uploaded</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($k['status'] === 'approved'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i> Approved
                                        </span>
                                    <?php elseif ($k['status'] === 'rejected'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 px-2 py-1">
                                            <i class="bi bi-x-circle me-1"></i> Rejected
                                        </span>
                                        <?php if (!empty($k['admin_notes'])): ?>
                                            <div class="text-xxs text-danger text-truncate mt-1" style="max-width: 160px;" title="<?php echo e($k['admin_notes']); ?>">
                                                Reason: <?php echo e($k['admin_notes']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning border-opacity-25 px-2 py-1">
                                            <i class="bi bi-hourglass me-1"></i> Pending Review
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small text-white"><?php echo date('d M Y', strtotime($k['submitted_at'])); ?></div>
                                    <div class="text-xxs text-white-50"><?php echo date('h:i A', strtotime($k['submitted_at'])); ?></div>
                                </td>
                                <td class="text-end pe-3">
                                    <?php if ($k['status'] === 'pending'): ?>
                                        <div class="btn-group">
                                            <!-- Approve Button Modal -->
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $k['id']; ?>" title="Approve KYC">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>

                                            <!-- Reject Button Modal -->
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $k['id']; ?>" title="Reject KYC">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        </div>

                                        <!-- Approve Modal -->
                                        <div class="modal fade" id="approveModal<?php echo $k['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content bg-dark text-white border-secondary">
                                                    <form method="POST">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <input type="hidden" name="kyc_id" value="<?php echo $k['id']; ?>">

                                                        <div class="modal-header border-secondary">
                                                            <h5 class="modal-title fs-6">
                                                                <i class="bi bi-patch-check-fill text-success me-2"></i>
                                                                Approve KYC: @<?php echo e($k['username']); ?>
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="text-white-50 mb-3">
                                                                Are you sure the documents submitted by <strong><?php echo e($k['full_name']); ?></strong> are verified and authentic?
                                                            </p>
                                                            <label class="form-label text-white-50 small">Optional Admin Note</label>
                                                            <input type="text" name="admin_notes" class="form-control bg-black text-white border-secondary" placeholder="e.g. Verified with Aadhar portal">
                                                        </div>
                                                        <div class="modal-footer border-secondary">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success btn-sm">Yes, Approve KYC</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal<?php echo $k['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content bg-dark text-white border-secondary">
                                                    <form method="POST">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <input type="hidden" name="kyc_id" value="<?php echo $k['id']; ?>">

                                                        <div class="modal-header border-secondary">
                                                            <h5 class="modal-title fs-6 text-danger">
                                                                <i class="bi bi-x-octagon-fill me-2"></i>
                                                                Reject KYC: @<?php echo e($k['username']); ?>
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="text-white-50 mb-3">
                                                                Please specify why this KYC submission is being rejected so the user can re-upload correct documents.
                                                            </p>
                                                            <label class="form-label text-white-50 small">Rejection Reason *</label>
                                                            <textarea name="admin_notes" class="form-control bg-black text-white border-secondary mb-2" rows="3" required placeholder="e.g. Image blurry / Name on bank account does not match member profile."></textarea>
                                                        </div>
                                                        <div class="modal-footer border-secondary">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger btn-sm">Reject Submission</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-white-50 small">Reviewed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-black bg-opacity-40 border-secondary border-opacity-25 py-3 d-flex justify-content-between align-items-center">
                <span class="text-white-50 small">
                    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                </span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link bg-dark text-white border-secondary" href="?status=<?php echo urlencode($currentStatus); ?>&page=<?php echo $page - 1; ?>">Previous</a>
                        </li>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?php echo ($p === $page) ? 'active' : ''; ?>">
                                <a class="page-link <?php echo ($p === $page) ? 'bg-gold text-dark border-gold fw-bold' : 'bg-dark text-white border-secondary'; ?>" href="?status=<?php echo urlencode($currentStatus); ?>&page=<?php echo $p; ?>">
                                    <?php echo $p; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link bg-dark text-white border-secondary" href="?status=<?php echo urlencode($currentStatus); ?>&page=<?php echo $page + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

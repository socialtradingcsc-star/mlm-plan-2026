<?php
/**
 * Social Service Dream - Admin Upgrade Requests Management
 * Part 3: Package + Level + Upgrade System
 * Target: C:\xampp\htdocs\ssdream\admin\upgrades.php
 */

$pageTitle = "Package Upgrade Requests";
$pageHeading = "Upgrade Management";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireAdminLogin();

// Handle Actions (Approve / Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        setFlashMessage('error', 'Security session token expired. Please refresh and try again.');
        header('Location: ' . BASE_URL . '/admin/upgrades.php');
        exit;
    }

    $upgradeId = (int)($_POST['upgrade_id'] ?? 0);
    $remarks = sanitizeInput($_POST['remarks'] ?? '');

    if ($action === 'approve') {
        if ($upgradeId > 0 && approveUpgradeRequest($upgradeId, $remarks)) {
            setFlashMessage('success', "Upgrade request #{$upgradeId} has been successfully approved and user level has been upgraded.");
        } else {
            setFlashMessage('error', "Failed to approve upgrade request #{$upgradeId}. Please verify request status.");
        }
    } elseif ($action === 'reject') {
        if ($upgradeId > 0 && rejectUpgradeRequest($upgradeId, $remarks)) {
            setFlashMessage('success', "Upgrade request #{$upgradeId} has been rejected. Notification sent to member.");
        } else {
            setFlashMessage('error', "Failed to reject upgrade request #{$upgradeId}.");
        }
    }

    header('Location: ' . BASE_URL . '/admin/upgrades.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
    exit;
}

// Filter status
$statusFilter = isset($_GET['status']) && in_array($_GET['status'], ['pending', 'approved', 'rejected']) ? $_GET['status'] : null;

// Retrieve upgrades
$upgrades = getAllUpgrades($statusFilter);
$pendingCount = getPendingUpgradeCount();

// Stats calculation
$db = getDb();
$totalRequests = 0;
$approvedCount = 0;
$rejectedCount = 0;
$totalUpgradeVolume = 0.00;

if ($db) {
    try {
        $totalRequests = (int)$db->query("SELECT COUNT(*) FROM upgrades")->fetchColumn();
        $approvedCount = (int)$db->query("SELECT COUNT(*) FROM upgrades WHERE status = 'approved'")->fetchColumn();
        $rejectedCount = (int)$db->query("SELECT COUNT(*) FROM upgrades WHERE status = 'rejected'")->fetchColumn();
        $totalUpgradeVolume = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM upgrades WHERE status = 'approved'")->fetchColumn();
    } catch (PDOException $e) {
        // Fallback
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Metric Statistics Cards -->
<div class="row g-3 mb-4">
    <!-- Card 1: Pending Upgrades -->
    <div class="col-xl-3 col-sm-6">
        <a href="<?php echo BASE_URL; ?>/admin/upgrades.php?status=pending" class="text-decoration-none">
            <div class="admin-stat-card <?php echo ($statusFilter === 'pending') ? 'border-warning' : ''; ?>" style="border-left: 4px solid #ffc107;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Pending Review</span>
                    <div class="admin-stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 text-dark"><?php echo $pendingCount; ?></h3>
                <span class="text-warning small d-flex align-items-center gap-1">
                    <i class="bi bi-clock-history"></i> Awaiting verification
                </span>
            </div>
        </a>
    </div>

    <!-- Card 2: Approved Upgrades -->
    <div class="col-xl-3 col-sm-6">
        <a href="<?php echo BASE_URL; ?>/admin/upgrades.php?status=approved" class="text-decoration-none">
            <div class="admin-stat-card <?php echo ($statusFilter === 'approved') ? 'border-success' : ''; ?>" style="border-left: 4px solid #198754;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Approved Upgrades</span>
                    <div class="admin-stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 text-dark"><?php echo $approvedCount; ?></h3>
                <span class="text-success small d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-up-circle"></i> Successfully advanced
                </span>
            </div>
        </a>
    </div>

    <!-- Card 3: Rejected Upgrades -->
    <div class="col-xl-3 col-sm-6">
        <a href="<?php echo BASE_URL; ?>/admin/upgrades.php?status=rejected" class="text-decoration-none">
            <div class="admin-stat-card <?php echo ($statusFilter === 'rejected') ? 'border-danger' : ''; ?>" style="border-left: 4px solid #dc3545;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Rejected Requests</span>
                    <div class="admin-stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 text-dark"><?php echo $rejectedCount; ?></h3>
                <span class="text-danger small d-flex align-items-center gap-1">
                    <i class="bi bi-shield-x"></i> Invalid / unverified
                </span>
            </div>
        </a>
    </div>

    <!-- Card 4: Total Volume -->
    <div class="col-xl-3 col-sm-6">
        <div class="admin-stat-card" style="border-left: 4px solid #0066cc;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Approved Volume</span>
                <div class="admin-stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-1 text-dark"><?php echo formatCurrency($totalUpgradeVolume, true); ?></h3>
            <span class="text-muted small d-flex align-items-center gap-1">
                <i class="bi bi-layers"></i> Across all levels
            </span>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php echo renderFlashMessages(); ?>

<!-- Upgrades Management Table -->
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-arrow-up-square text-primary me-2"></i> Member Upgrade Requests
            </h5>
            <div class="text-muted small">Verify payments and authorize level advancements from Level 1 to Level 7</div>
        </div>

        <!-- Filter Buttons -->
        <div class="btn-group btn-group-sm">
            <a href="<?php echo BASE_URL; ?>/admin/upgrades.php" class="btn <?php echo ($statusFilter === null) ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                All (<?php echo $totalRequests; ?>)
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/upgrades.php?status=pending" class="btn <?php echo ($statusFilter === 'pending') ? 'btn-warning text-dark' : 'btn-outline-warning text-dark'; ?>">
                Pending (<?php echo $pendingCount; ?>)
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/upgrades.php?status=approved" class="btn <?php echo ($statusFilter === 'approved') ? 'btn-success' : 'btn-outline-success'; ?>">
                Approved (<?php echo $approvedCount; ?>)
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/upgrades.php?status=rejected" class="btn <?php echo ($statusFilter === 'rejected') ? 'btn-danger' : 'btn-outline-danger'; ?>">
                Rejected (<?php echo $rejectedCount; ?>)
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Member</th>
                        <th>Progression</th>
                        <th>Target Package</th>
                        <th>Amount</th>
                        <th>Payment / Ref</th>
                        <th>Requested On</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($upgrades)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No upgrade requests found matching the selected filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($upgrades as $upg): 
                            $status = $upg['status'];
                            $badgeClass = match($status) {
                                'approved' => 'bg-success',
                                'rejected' => 'bg-danger',
                                default    => 'bg-warning text-dark'
                            };
                        ?>
                            <tr>
                                <td class="ps-4 font-monospace fw-semibold">
                                    #UPG-<?php echo str_pad((string)$upg['id'], 5, '0', STR_PAD_LEFT); ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo e($upg['full_name']); ?></div>
                                    <div class="small text-muted">@<?php echo e($upg['username']); ?> &bull; <?php echo e($upg['mobile']); ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        Level <?php echo (int)$upg['from_level']; ?> &rarr; <strong class="text-primary">Level <?php echo (int)$upg['to_level']; ?></strong>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?php echo e($upg['package_name'] ?? ('Level ' . $upg['to_level'])); ?></div>
                                    <div class="text-muted text-xxs">Team: <?php echo (int)($upg['team_size'] ?? 0); ?> Members</div>
                                </td>
                                <td class="fw-bold text-primary">
                                    <?php echo formatCurrency($upg['amount']); ?>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark"><?php echo e($upg['payment_mode'] ?? 'Wallet / Direct'); ?></div>
                                    <?php if (!empty($upg['transaction_ref'])): ?>
                                        <div class="text-muted text-xxs font-monospace"><?php echo e($upg['transaction_ref']); ?></div>
                                    <?php else: ?>
                                        <div class="text-muted text-xxs">No Ref</div>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <?php echo date('d M Y, h:i A', strtotime($upg['created_at'])); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badgeClass; ?> text-capitalize px-2 py-1 rounded-pill">
                                        <?php echo e($status); ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <?php if ($status === 'pending'): ?>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $upg['id']; ?>">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $upg['id']; ?>">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        </div>

                                        <!-- Approve Modal -->
                                        <div class="modal fade" id="approveModal<?php echo $upg['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title fw-bold"><i class="bi bi-check-circle me-1"></i> Approve Package Upgrade</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/upgrades.php">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <input type="hidden" name="upgrade_id" value="<?php echo $upg['id']; ?>">

                                                        <div class="modal-body p-4">
                                                            <p class="mb-3">
                                                                Are you sure you want to approve upgrade request <strong>#UPG-<?php echo str_pad((string)$upg['id'], 5, '0', STR_PAD_LEFT); ?></strong>?
                                                            </p>
                                                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                                                <div><strong>Member:</strong> <?php echo e($upg['full_name']); ?> (@<?php echo e($upg['username']); ?>)</div>
                                                                <div><strong>Upgrade:</strong> Level <?php echo (int)$upg['from_level']; ?> &rarr; Level <?php echo (int)$upg['to_level']; ?></div>
                                                                <div><strong>Official Amount:</strong> <?php echo formatCurrency($upg['amount']); ?></div>
                                                                <div><strong>Payment Ref:</strong> <?php echo e($upg['transaction_ref'] ?: 'N/A'); ?></div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Admin Remarks (Optional)</label>
                                                                <input type="text" class="form-control" name="remarks" value="Payment verified and package authorized.">
                                                            </div>
                                                            <div class="alert alert-info py-2 small mb-0">
                                                                <i class="bi bi-info-circle me-1"></i> Approving will immediately advance the member's level to Level <?php echo (int)$upg['to_level']; ?>.
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success px-4">
                                                                <i class="bi bi-check2-circle me-1"></i> Confirm & Authorize
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal<?php echo $upg['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title fw-bold"><i class="bi bi-x-circle me-1"></i> Reject Upgrade Request</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/upgrades.php">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <input type="hidden" name="upgrade_id" value="<?php echo $upg['id']; ?>">

                                                        <div class="modal-body p-4">
                                                            <p class="mb-3">
                                                                Specify reason for rejecting upgrade request <strong>#UPG-<?php echo str_pad((string)$upg['id'], 5, '0', STR_PAD_LEFT); ?></strong>.
                                                            </p>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold">Rejection Reason / Remarks <span class="text-danger">*</span></label>
                                                                <textarea class="form-control" name="remarks" rows="3" required placeholder="e.g. Payment reference could not be verified on company bank statement."></textarea>
                                                            </div>
                                                            <div class="small text-muted">
                                                                The member will receive a notification with these remarks and their current level will remain unchanged.
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger px-4">
                                                                <i class="bi bi-x-circle me-1"></i> Confirm Rejection
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="small text-muted">
                                            <?php echo e($upg['admin_remarks'] ?: 'Completed'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Plan Data Lock Warning & Audit -->
<div class="card border-0 shadow-sm mt-4 bg-light" style="border-radius: 12px;">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-shield-check text-primary me-2"></i> Official Plan Protection Notice</h6>
        <p class="text-muted small mb-0">
            Social Service Dream operates strictly on the 7 official packages (₹1,200, ₹2,000, ₹3,000, ₹8,000, ₹40,000, ₹2,00,000, ₹5,00,000).
            Per the system rules, sequential progression is enforced and no arbitrary packages or levels beyond Level 7 can be added.
        </p>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

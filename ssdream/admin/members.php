<?php
/**
 * Social Service Dream - Admin Member Management Console
 * =========================================================================
 * PART 7: MEMBER DIRECTORY, CONTROLS & SECURITY
 * Target: C:\xampp\htdocs\ssdream\admin\members.php
 * =========================================================================
 */

$pageTitle = "Member Management - SS Dream Admin";
$pageHeading = "Member Directory & Account Control";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireAdminLogin();

$db = getDb();
$alert = null;

// Handle Actions (Block, Activate, Reset Password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $alert = ['type' => 'danger', 'msg' => 'Security session expired. Please refresh and try again.'];
    } else {
        $action = trim($_POST['action']);
        $targetUserId = (int)($_POST['user_id'] ?? 0);

        if ($action === 'change_status') {
            $newStatus = trim($_POST['new_status'] ?? '');
            $res = updateMemberAccountStatus($targetUserId, $newStatus);
            $alert = ['type' => $res['success'] ? 'success' : 'danger', 'msg' => $res['message']];
        } elseif ($action === 'reset_password') {
            $newPass = trim($_POST['new_password'] ?? '');
            if (empty($newPass)) {
                $alert = ['type' => 'danger', 'msg' => 'Please provide a new password.'];
            } else {
                $res = adminResetMemberPassword($targetUserId, $newPass);
                $alert = ['type' => $res['success'] ? 'success' : 'danger', 'msg' => $res['message']];
            }
        }
    }
}

// Filters & Pagination
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$levelFilter = trim($_GET['level'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

$filters = [
    'search' => $search,
    'status' => $statusFilter,
    'level'  => $levelFilter
];

$summary = getMembersSummaryStats();
$memberData = getAdminMemberList($filters, $limit, $offset);
$members = $memberData['records'];
$totalRecords = $memberData['total'];
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
                    <li class="breadcrumb-item active text-white" aria-current="page">Member Management</li>
                </ol>
            </nav>
            <h2 class="h4 text-white fw-bold mb-0">
                <i class="bi bi-people-fill text-gold me-2"></i> Member Directory &amp; Control
            </h2>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-dark border border-secondary px-3 py-2 text-white-50">
                Total Members: <strong class="text-white"><?php echo number_format($summary['total']); ?></strong>
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
        <div class="col-6 col-lg-3">
            <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-white-50 small">All Registered</span>
                        <div class="p-2 rounded bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fs-4 text-white fw-bold mb-0"><?php echo number_format($summary['total']); ?></h3>
                    <small class="text-white-50">Total database accounts</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-white-50 small">Active Members</span>
                        <div class="p-2 rounded bg-success bg-opacity-10 text-success">
                            <i class="bi bi-person-check fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fs-4 text-success fw-bold mb-0"><?php echo number_format($summary['active']); ?></h3>
                    <small class="text-white-50">Normal trading &amp; earning</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-white-50 small">Blocked Accounts</span>
                        <div class="p-2 rounded bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-person-slash fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fs-4 text-danger fw-bold mb-0"><?php echo number_format($summary['blocked']); ?></h3>
                    <small class="text-white-50">Login &amp; payouts restricted</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-white-50 small">Pending Verification</span>
                        <div class="p-2 rounded bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-hourglass-split fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fs-4 text-warning fw-bold mb-0"><?php echo number_format($summary['pending']); ?></h3>
                    <small class="text-white-50">Activation pending</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card bg-dark border-secondary border-opacity-25 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-black border-secondary text-white-50">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-black text-white border-secondary" 
                               placeholder="Search username, name, email, mobile..." 
                               value="<?php echo e($search); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select bg-black text-white border-secondary">
                        <option value="">-- All Statuses --</option>
                        <option value="active" <?php echo ($statusFilter === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="blocked" <?php echo ($statusFilter === 'blocked') ? 'selected' : ''; ?>>Blocked</option>
                        <option value="pending" <?php echo ($statusFilter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="level" class="form-select bg-black text-white border-secondary">
                        <option value="">-- All Levels --</option>
                        <?php for ($i = 1; $i <= 7; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($levelFilter === (string)$i) ? 'selected' : ''; ?>>Level <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-gold w-100 fw-semibold">
                        Filter
                    </button>
                    <?php if ($search !== '' || $statusFilter !== '' || $levelFilter !== ''): ?>
                        <a href="members.php" class="btn btn-outline-secondary" title="Reset Filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Members Table -->
    <div class="card bg-dark border-secondary border-opacity-25 shadow-sm">
        <div class="card-header bg-black bg-opacity-40 border-secondary border-opacity-25 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title text-white mb-0 fs-6">
                <i class="bi bi-table me-2 text-gold"></i> Registered Members (<?php echo number_format($totalRecords); ?>)
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0 border-secondary border-opacity-25">
                <thead class="text-white-50 text-uppercase small bg-black bg-opacity-50">
                    <tr>
                        <th class="ps-3 py-3">ID / Member</th>
                        <th>Contact</th>
                        <th>Sponsor / Placement</th>
                        <th>Level</th>
                        <th>Wallet Balance</th>
                        <th>KYC</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($members)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-white-50">
                                <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                                No members match the specified filters.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($members as $m): ?>
                            <tr>
                                <td class="ps-3 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle bg-secondary bg-opacity-25 text-gold fw-bold rounded-circle p-2 text-center" style="width: 38px; height: 38px; line-height: 22px;">
                                            <?php echo strtoupper(substr($m['full_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-white"><?php echo e($m['full_name']); ?></div>
                                            <div class="text-gold font-monospace small">@<?php echo e($m['username']); ?></div>
                                            <span class="badge bg-secondary bg-opacity-50 text-xxs">ID: #<?php echo $m['id']; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-white"><i class="bi bi-telephone text-white-50 me-1"></i> <?php echo e($m['mobile']); ?></div>
                                    <div class="text-white-50 small"><i class="bi bi-envelope text-white-50 me-1"></i> <?php echo e($m['email']); ?></div>
                                    <div class="text-xxs text-white-50 mt-1">Joined: <?php echo date('d M Y', strtotime($m['created_at'])); ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($m['sponsor_username'])): ?>
                                        <div class="text-white small">
                                            Sponsor: <span class="text-info font-monospace">@<?php echo e($m['sponsor_username']); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-white-50 small">System Root</span>
                                    <?php endif; ?>
                                    <?php if (!empty($m['position'])): ?>
                                        <span class="badge <?php echo ($m['position'] === 'left') ? 'bg-primary' : 'bg-info text-dark'; ?> text-uppercase text-xxs mt-1">
                                            <?php echo $m['position']; ?> Leg
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-25 px-2 py-1">
                                        Level <?php echo $m['current_level']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-success font-monospace">
                                        ₹<?php echo number_format((float)($m['wallet_balance'] ?? 0), 2); ?>
                                    </div>
                                    <small class="text-white-50">Earned: ₹<?php echo number_format((float)($m['total_income'] ?? 0), 2); ?></small>
                                </td>
                                <td>
                                    <?php 
                                        $kStatus = $m['kyc_status'] ?? 'pending';
                                        $kBadge = match($kStatus) {
                                            'approved' => 'bg-success text-white',
                                            'rejected' => 'bg-danger text-white',
                                            default    => 'bg-warning text-dark'
                                        };
                                    ?>
                                    <span class="badge <?php echo $kBadge; ?> text-capitalize text-xxs">
                                        <?php echo $kStatus; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($m['status'] === 'active'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25">
                                            <i class="bi bi-check-circle me-1"></i> Active
                                        </span>
                                    <?php elseif ($m['status'] === 'blocked'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25">
                                            <i class="bi bi-slash-circle me-1"></i> Blocked
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning border-opacity-25">
                                            <i class="bi bi-hourglass me-1"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group">
                                        <!-- View Downline Tree -->
                                        <a href="<?php echo BASE_URL; ?>/admin/tree.php?user_id=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-info" title="View in Binary Network">
                                            <i class="bi bi-diagram-3"></i>
                                        </a>

                                        <!-- Toggle Status (Block / Unblock) -->
                                        <button type="button" class="btn btn-sm <?php echo ($m['status'] === 'blocked') ? 'btn-outline-success' : 'btn-outline-danger'; ?>" 
                                                data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $m['id']; ?>"
                                                title="<?php echo ($m['status'] === 'blocked') ? 'Activate Account' : 'Block Account'; ?>">
                                            <i class="bi <?php echo ($m['status'] === 'blocked') ? 'bi-unlock' : 'bi-lock'; ?>"></i>
                                        </button>

                                        <!-- Reset Password -->
                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                data-bs-toggle="modal" data-bs-target="#passwordModal<?php echo $m['id']; ?>"
                                                title="Reset Member Password">
                                            <i class="bi bi-key"></i>
                                        </button>
                                    </div>

                                    <!-- Status Modal -->
                                    <div class="modal fade" id="statusModal<?php echo $m['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content bg-dark text-white border-secondary">
                                                <form method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                    <input type="hidden" name="action" value="change_status">
                                                    <input type="hidden" name="user_id" value="<?php echo $m['id']; ?>">
                                                    <input type="hidden" name="new_status" value="<?php echo ($m['status'] === 'blocked') ? 'active' : 'blocked'; ?>">

                                                    <div class="modal-header border-secondary">
                                                        <h5 class="modal-title fs-6">
                                                            <i class="bi bi-shield-exclamation text-warning me-2"></i>
                                                            Confirm Status Change: @<?php echo e($m['username']); ?>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php if ($m['status'] === 'blocked'): ?>
                                                            <p class="text-white-50 mb-0">
                                                                Are you sure you want to <strong class="text-success">ACTIVATE</strong> member 
                                                                <strong><?php echo e($m['full_name']); ?></strong>? 
                                                                They will be able to log in, access their wallet, and earn bonuses.
                                                            </p>
                                                        <?php else: ?>
                                                            <p class="text-white-50 mb-0">
                                                                Are you sure you want to <strong class="text-danger">BLOCK</strong> member 
                                                                <strong><?php echo e($m['full_name']); ?></strong>? 
                                                                This will suspend their login and disable payouts immediately.
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer border-secondary">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn <?php echo ($m['status'] === 'blocked') ? 'btn-success' : 'btn-danger'; ?> btn-sm">
                                                            Yes, <?php echo ($m['status'] === 'blocked') ? 'Activate Account' : 'Block Account'; ?>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reset Password Modal -->
                                    <div class="modal fade" id="passwordModal<?php echo $m['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content bg-dark text-white border-secondary">
                                                <form method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                    <input type="hidden" name="action" value="reset_password">
                                                    <input type="hidden" name="user_id" value="<?php echo $m['id']; ?>">

                                                    <div class="modal-header border-secondary">
                                                        <h5 class="modal-title fs-6">
                                                            <i class="bi bi-key text-warning me-2"></i>
                                                            Reset Password for @<?php echo e($m['username']); ?>
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <label class="form-label text-white-50 small">Enter New Password</label>
                                                        <input type="text" name="new_password" class="form-control bg-black text-white border-secondary mb-2" 
                                                               placeholder="At least 6 characters" minlength="6" required>
                                                        <small class="text-white-50 d-block">
                                                            The user will be required to use this new password on their next login attempt.
                                                        </small>
                                                    </div>
                                                    <div class="modal-footer border-secondary">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-warning btn-sm fw-semibold">Save New Password</button>
                                                    </div>
                                                </form>
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

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-black bg-opacity-40 border-secondary border-opacity-25 py-3 d-flex justify-content-between align-items-center">
                <span class="text-white-50 small">
                    Page <?php echo $page; ?> of <?php echo $totalPages; ?> (<?php echo number_format($totalRecords); ?> members total)
                </span>
                <nav aria-label="Members pagination">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link bg-dark text-white border-secondary" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&level=<?php echo urlencode($levelFilter); ?>">Previous</a>
                        </li>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?php echo ($p === $page) ? 'active' : ''; ?>">
                                <a class="page-link <?php echo ($p === $page) ? 'bg-gold text-dark border-gold fw-bold' : 'bg-dark text-white border-secondary'; ?>" href="?page=<?php echo $p; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&level=<?php echo urlencode($levelFilter); ?>">
                                    <?php echo $p; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link bg-dark text-white border-secondary" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&level=<?php echo urlencode($levelFilter); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

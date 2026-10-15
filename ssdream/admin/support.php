<?php
/**
 * Social Service Dream - Admin Support Ticket Management
 * =========================================================================
 * PART 7: MEMBER SUPPORT HELPDESK & TICKETING CONSOLE
 * Target: C:\xampp\htdocs\ssdream\admin\support.php
 * =========================================================================
 */

$pageTitle = "Support Tickets - SS Dream Admin";
$pageHeading = "Member Inquiries & Support Helpdesk";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireAdminLogin();

$alert = null;
$adminId = (int)($_SESSION['admin_id'] ?? 1);
$adminName = $_SESSION['admin_name'] ?? 'Support Admin';

// Handle Reply / Status Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $alert = ['type' => 'danger', 'msg' => 'Security session expired. Please refresh and try again.'];
    } else {
        $action = trim($_POST['action']);
        $ticketId = (int)($_POST['ticket_id'] ?? 0);

        if ($action === 'reply_ticket') {
            $replyMsg = trim($_POST['reply_message'] ?? '');
            $newStatus = trim($_POST['ticket_status'] ?? 'in_progress');

            if (empty($replyMsg)) {
                $alert = ['type' => 'danger', 'msg' => 'Reply message cannot be empty.'];
            } else {
                $res = addSupportTicketReply($ticketId, 'admin', $adminId, $adminName, $replyMsg, $newStatus);
                $alert = ['type' => $res['success'] ? 'success' : 'danger', 'msg' => $res['message']];
            }
        } elseif ($action === 'change_status_only') {
            $newStatus = trim($_POST['ticket_status'] ?? '');
            if (in_array($newStatus, ['open', 'in_progress', 'resolved', 'closed'], true)) {
                $db = getDb();
                $uStmt = $db->prepare("UPDATE support_tickets SET status = :st, updated_at = NOW() WHERE id = :tid");
                $uStmt->execute(['st' => $newStatus, 'tid' => $ticketId]);
                $alert = ['type' => 'success', 'msg' => "Ticket status changed to " . strtoupper($newStatus)];
            }
        }
    }
}

// Filters & Pagination
$currentStatus = strtolower(trim($_GET['status'] ?? 'all'));
if (!in_array($currentStatus, ['all', 'open', 'in_progress', 'resolved', 'closed'])) {
    $currentStatus = 'all';
}
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

$ticketData = getSupportTicketsList(0, $currentStatus, $limit, $offset);
$tickets = $ticketData['records'];
$totalRecords = $ticketData['total'];
$totalPages = ceil($totalRecords / $limit);

// Active thread view if ID is passed
$activeTicketId = (int)($_GET['view_ticket'] ?? 0);
$activeThread = null;
if ($activeTicketId > 0) {
    $activeThread = getSupportTicketThread($activeTicketId);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-white-50 small">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/admin/index.php" class="text-decoration-none text-gold">Dashboard</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Support Helpdesk</li>
                </ol>
            </nav>
            <h2 class="h4 text-white fw-bold mb-0">
                <i class="bi bi-headset text-gold me-2"></i> Member Support Tickets
            </h2>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-25 px-3 py-2">
                Open Inquiries: <strong><?php echo getOpenSupportTicketsCount(); ?></strong>
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

    <!-- Filter Pills -->
    <div class="card bg-dark border-secondary border-opacity-25 shadow-sm mb-4">
        <div class="card-body p-2">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentStatus === 'all') ? 'active bg-gold text-dark fw-bold' : 'text-white-50'; ?>" href="?status=all">
                        <i class="bi bi-list-task me-1"></i> All Tickets
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentStatus === 'open') ? 'active bg-gold text-dark fw-bold' : 'text-white-50'; ?>" href="?status=open">
                        <i class="bi bi-envelope-open me-1"></i> Open
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentStatus === 'in_progress') ? 'active bg-gold text-dark fw-bold' : 'text-white-50'; ?>" href="?status=in_progress">
                        <i class="bi bi-hourglass-split me-1"></i> In Progress
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentStatus === 'resolved') ? 'active bg-gold text-dark fw-bold' : 'text-white-50'; ?>" href="?status=resolved">
                        <i class="bi bi-check-circle me-1"></i> Resolved
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentStatus === 'closed') ? 'active bg-gold text-dark fw-bold' : 'text-white-50'; ?>" href="?status=closed">
                        <i class="bi bi-slash-circle me-1"></i> Closed
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- If thread is selected, show detail card -->
    <?php if ($activeThread): ?>
        <?php 
            $t = $activeThread['ticket']; 
            $messages = $activeThread['messages'];
        ?>
        <div class="card bg-dark border-gold border-opacity-50 shadow mb-4">
            <div class="card-header bg-black bg-opacity-60 border-secondary border-opacity-25 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <span class="badge bg-gold text-dark fw-bold me-2"><?php echo e($t['ticket_no']); ?></span>
                    <strong class="text-white fs-6"><?php echo e($t['subject']); ?></strong>
                    <span class="badge bg-secondary ms-2 text-xxs"><?php echo e($t['category']); ?></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-white-50 small">Raised by: <strong class="text-white"><?php echo e($t['full_name']); ?></strong> (@<?php echo e($t['username']); ?>)</span>
                    <a href="support.php" class="btn btn-sm btn-outline-secondary ms-2">Close Thread View</a>
                </div>
            </div>
            <div class="card-body p-4" style="max-height: 500px; overflow-y: auto;">
                <?php foreach ($messages as $msg): ?>
                    <div class="d-flex mb-3 <?php echo ($msg['sender_type'] === 'admin') ? 'justify-content-end' : 'justify-content-start'; ?>">
                        <div class="p-3 rounded-3 <?php echo ($msg['sender_type'] === 'admin') ? 'bg-primary bg-opacity-20 border border-primary border-opacity-50 text-white' : 'bg-secondary bg-opacity-20 border border-secondary border-opacity-50 text-white'; ?>" style="max-width: 75%;">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                                <span class="fw-bold small <?php echo ($msg['sender_type'] === 'admin') ? 'text-info' : 'text-gold'; ?>">
                                    <i class="bi bi-<?php echo ($msg['sender_type'] === 'admin') ? 'shield-check' : 'person'; ?> me-1"></i>
                                    <?php echo e($msg['sender_name']); ?>
                                </span>
                                <span class="text-white-50 text-xxs"><?php echo date('d M Y, h:i A', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <div class="small" style="white-space: pre-wrap;"><?php echo nl2br(e($msg['message'])); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Reply Form -->
            <div class="card-footer bg-black bg-opacity-40 border-secondary border-opacity-25 p-3">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="action" value="reply_ticket">
                    <input type="hidden" name="ticket_id" value="<?php echo $t['id']; ?>">

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-md-3">
                            <label class="form-label text-white-50 text-xxs mb-1">Update Status On Reply</label>
                            <select name="ticket_status" class="form-select form-select-sm bg-black text-white border-secondary">
                                <option value="in_progress" <?php echo ($t['status'] === 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo ($t['status'] === 'resolved') ? 'selected' : ''; ?>>Mark Resolved</option>
                                <option value="closed" <?php echo ($t['status'] === 'closed') ? 'selected' : ''; ?>>Close Ticket</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <textarea name="reply_message" class="form-control bg-black text-white border-secondary" rows="2" placeholder="Write reply to member..." required></textarea>
                        <button type="submit" class="btn btn-gold px-4 fw-bold">
                            <i class="bi bi-send-fill me-1"></i> Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tickets List Table -->
    <div class="card bg-dark border-secondary border-opacity-25 shadow-sm">
        <div class="card-header bg-black bg-opacity-40 border-secondary border-opacity-25 py-3">
            <h5 class="card-title text-white mb-0 fs-6">
                <i class="bi bi-table me-2 text-gold"></i> Support Tickets (<?php echo number_format($totalRecords); ?>)
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0 border-secondary border-opacity-25">
                <thead class="text-white-50 text-uppercase small bg-black bg-opacity-50">
                    <tr>
                        <th class="ps-3 py-3">Ticket ID</th>
                        <th>Member</th>
                        <th>Subject &amp; Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-white-50">
                                <i class="bi bi-headset fs-1 d-block mb-2 text-secondary"></i>
                                No support tickets found under this status.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td class="ps-3 py-3">
                                    <span class="badge bg-secondary bg-opacity-50 font-monospace text-gold">
                                        <?php echo e($t['ticket_no']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-white"><?php echo e($t['full_name']); ?></div>
                                    <div class="text-white-50 font-monospace small">@<?php echo e($t['username']); ?></div>
                                    <small class="text-white-50"><?php echo e($t['mobile']); ?></small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-white"><?php echo e($t['subject']); ?></div>
                                    <span class="badge bg-dark border border-secondary text-white-50 text-xxs">
                                        <?php echo e($t['category'] ?? 'General'); ?>
                                    </span>
                                    <span class="badge bg-info-subtle text-info text-xxs ms-1">
                                        <?php echo $t['message_count']; ?> messages
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $priBadge = match($t['priority']) {
                                            'high'   => 'bg-danger-subtle text-danger border-danger',
                                            'medium' => 'bg-warning-subtle text-warning border-warning',
                                            default  => 'bg-info-subtle text-info border-info'
                                        };
                                    ?>
                                    <span class="badge <?php echo $priBadge; ?> border border-opacity-25 text-capitalize text-xxs">
                                        <?php echo $t['priority']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $statusBadge = match($t['status']) {
                                            'open'        => 'bg-warning text-dark',
                                            'in_progress' => 'bg-info text-dark',
                                            'resolved'    => 'bg-success text-white',
                                            default       => 'bg-secondary text-white'
                                        };
                                    ?>
                                    <span class="badge <?php echo $statusBadge; ?> text-uppercase text-xxs">
                                        <?php echo str_replace('_', ' ', $t['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-white"><?php echo date('d M Y', strtotime($t['updated_at'])); ?></div>
                                    <div class="text-xxs text-white-50"><?php echo date('h:i A', strtotime($t['updated_at'])); ?></div>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="?view_ticket=<?php echo $t['id']; ?>&status=<?php echo urlencode($currentStatus); ?>" class="btn btn-sm btn-gold">
                                        <i class="bi bi-chat-dots-fill me-1"></i> View Thread
                                    </a>
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
                <span class="text-white-50 small">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
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

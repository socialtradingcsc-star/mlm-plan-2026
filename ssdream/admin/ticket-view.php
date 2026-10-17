<?php
/**
 * Social Service Dream - Admin Support Ticket Conversation View
 * Target: C:\xampp\htdocs\ssdream\admin\ticket-view.php
 */

$pageTitle = "Ticket Discussion - SS Dream Admin";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

$ticketId = (int)($_GET['id'] ?? 0);
if ($ticketId <= 0) {
    header('Location: ' . BASE_URL . '/admin/support.php');
    exit();
}

// Handle Admin Reply Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply_ticket') {
    if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'CSRF security token verification failed.');
    } else {
        $replyMsg = trim($_POST['reply_message'] ?? '');
        $newStatus = trim($_POST['status'] ?? 'in_progress');
        $adminId = (int)($_SESSION['admin_id'] ?? 1);
        $adminName = $_SESSION['admin_name'] ?? 'Administrator';

        if (empty($replyMsg)) {
            setFlashMessage('error', 'Please enter a reply message.');
        } else {
            $res = addSupportTicketReply($ticketId, 'admin', $adminId, $adminName, $replyMsg, $newStatus);
            if ($res['success']) {
                setFlashMessage('success', 'Your reply has been posted to the member!');
                logAdminActivity($adminId, 'REPLY_SUPPORT_TICKET', 'ticket', $ticketId, "Replied to ticket #{$ticketId} setting status to {$newStatus}.");
            } else {
                setFlashMessage('error', $res['message']);
            }
        }
    }
    header('Location: ' . BASE_URL . '/admin/ticket-view.php?id=' . $ticketId);
    exit();
}

$thread = getSupportTicketThread($ticketId, 0); // 0 = admin fetch all
if (!$thread) {
    setFlashMessage('error', 'Support ticket record not found.');
    header('Location: ' . BASE_URL . '/admin/support.php');
    exit();
}

$ticket = $thread['ticket'];
$messages = $thread['messages'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>/admin/support.php" class="text-decoration-none text-muted small">
            &larr; Back to Helpdesk Console
        </a>
        <h3 class="fw-bold text-navy mb-0 mt-1" style="color: #0b192c;">
            Support Ticket #<?php echo e($ticket['ticket_no']); ?>
        </h3>
    </div>
    <div>
        <?php if ($ticket['status'] === 'open'): ?>
            <span class="badge bg-danger fs-6 px-3 py-2">OPEN</span>
        <?php elseif ($ticket['status'] === 'in_progress'): ?>
            <span class="badge bg-warning text-dark fs-6 px-3 py-2">IN PROGRESS</span>
        <?php elseif ($ticket['status'] === 'resolved'): ?>
            <span class="badge bg-success fs-6 px-3 py-2">RESOLVED</span>
        <?php else: ?>
            <span class="badge bg-secondary fs-6 px-3 py-2">CLOSED</span>
        <?php endif; ?>
    </div>
</div>

<?php echo renderFlashMessages(); ?>

<div class="row g-4 mb-4">
    <!-- Main Thread Conversation -->
    <div class="col-lg-8">
        <div class="card border shadow-sm bg-white mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-1 text-navy" style="color: #0b192c;"><?php echo e($ticket['subject']); ?></h5>
                <div class="text-muted small">
                    Category: <span class="badge bg-secondary-subtle text-dark border"><?php echo e($ticket['category'] ?? 'General'); ?></span>
                    &bull; Created: <?php echo date('d M Y, h:i A', strtotime($ticket['created_at'])); ?>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Messages Timeline -->
                <div class="d-flex flex-column gap-3 mb-4">
                    <?php foreach ($messages as $msg): ?>
                        <?php $isAdmin = ($msg['sender_type'] === 'admin'); ?>
                        <div class="d-flex <?php echo $isAdmin ? 'justify-content-end' : 'justify-content-start'; ?>">
                            <div class="card border-0 shadow-sm p-3 style-bubble" style="max-width: 80%; background-color: <?php echo $isAdmin ? '#f0f7ff' : '#f8f9fa'; ?>; border-left: 4px solid <?php echo $isAdmin ? '#0d6efd' : '#6c757d'; ?> !important;">
                                <div class="d-flex justify-content-between align-items-center mb-1 gap-3">
                                    <span class="fw-bold small <?php echo $isAdmin ? 'text-primary' : 'text-navy'; ?>">
                                        <i class="bi bi-<?php echo $isAdmin ? 'shield-lock-fill' : 'person-circle'; ?> me-1"></i>
                                        <?php echo e($msg['sender_name']); ?> <?php echo $isAdmin ? '(Admin)' : ''; ?>
                                    </span>
                                    <span class="text-muted text-xxs"><?php echo date('d M, h:i A', strtotime($msg['created_at'])); ?></span>
                                </div>
                                <div class="text-dark small lh-base" style="white-space: pre-wrap;"><?php echo e($msg['message']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Admin Reply Form -->
                <div class="border-top pt-4">
                    <h6 class="fw-bold mb-3 text-navy" style="color: #0b192c;">
                        <i class="bi bi-reply-fill text-primary me-1"></i> Post Administrator Reply
                    </h6>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="reply_ticket">
                        <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Update Ticket Status</label>
                            <select name="status" class="form-select">
                                <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>IN PROGRESS &bull; Working on issue</option>
                                <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>OPEN &bull; Awaiting resolution</option>
                                <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>RESOLVED &bull; Mark issue as resolved</option>
                                <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>CLOSED &bull; Close ticket thread</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Reply Message</label>
                            <textarea name="reply_message" class="form-control" rows="4" placeholder="Type your response to the member..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-send-fill me-1"></i> Submit Reply &amp; Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side Member Profile Summary -->
    <div class="col-lg-4">
        <div class="card border shadow-sm bg-white mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-person-badge text-primary me-2"></i> Member Profile
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-3 border-bottom pb-2">
                    <div class="text-muted text-xxs text-uppercase fw-semibold">Member Name</div>
                    <div class="fw-bold text-navy fs-6"><?php echo e($ticket['full_name']); ?></div>
                    <div class="badge bg-light text-dark border text-xxs">@<?php echo e($ticket['username']); ?></div>
                </div>

                <div class="mb-3 border-bottom pb-2">
                    <div class="text-muted text-xxs text-uppercase fw-semibold">Current Package Level</div>
                    <div class="fw-bold text-primary">Level <?php echo (int)($ticket['current_level'] ?? 1); ?></div>
                </div>

                <div class="mb-3 border-bottom pb-2">
                    <div class="text-muted text-xxs text-uppercase fw-semibold">Contact Details</div>
                    <div class="small fw-semibold"><i class="bi bi-telephone text-muted me-1"></i><?php echo e($ticket['mobile']); ?></div>
                    <div class="small text-muted"><i class="bi bi-envelope text-muted me-1"></i><?php echo e($ticket['email']); ?></div>
                </div>

                <div>
                    <a href="<?php echo BASE_URL; ?>/admin/members.php?search=<?php echo urlencode($ticket['username']); ?>" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-search me-1"></i> Inspect Full Member Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * Social Service Dream - User Support Ticket Conversation Thread
 * Target: C:\xampp\htdocs\ssdream\user\ticket-view.php
 */

$pageTitle = "Ticket Thread - SS Dream";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireUserAuth();
$userId = (int)$_SESSION['user_id'];

$ticketId = (int)($_GET['id'] ?? 0);
if ($ticketId <= 0) {
    header('Location: ' . BASE_URL . '/user/support.php');
    exit();
}

// Handle Member Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply_ticket') {
    if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'CSRF verification failed.');
    } else {
        $replyMsg = trim($_POST['reply_message'] ?? '');
        $userName = $_SESSION['user_fullname'] ?? 'Member';

        if (empty($replyMsg)) {
            setFlashMessage('error', 'Reply message cannot be empty.');
        } else {
            $res = addSupportTicketReply($ticketId, 'user', $userId, $userName, $replyMsg, 'open');
            if ($res['success']) {
                setFlashMessage('success', 'Your reply has been sent to our support team.');
            } else {
                setFlashMessage('error', $res['message']);
            }
        }
    }
    header('Location: ' . BASE_URL . '/user/ticket-view.php?id=' . $ticketId);
    exit();
}

// Fetch ticket thread WITH strict user access control
$thread = getSupportTicketThread($ticketId, $userId);
if (!$thread) {
    setFlashMessage('error', 'Support ticket not found or access denied.');
    header('Location: ' . BASE_URL . '/user/support.php');
    exit();
}

$ticket = $thread['ticket'];
$messages = $thread['messages'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>/user/support.php" class="text-decoration-none text-muted small">
            &larr; Back to My Support Tickets
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

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border shadow-sm bg-white mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-1 text-navy" style="color: #0b192c;"><?php echo e($ticket['subject']); ?></h5>
                <div class="text-muted small">
                    Category: <span class="badge bg-secondary-subtle text-dark border"><?php echo e($ticket['category'] ?? 'General'); ?></span>
                    &bull; Created: <?php echo date('d M Y, h:i A', strtotime($ticket['created_at'])); ?>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Message Thread Timeline -->
                <div class="d-flex flex-column gap-3 mb-4">
                    <?php foreach ($messages as $msg): ?>
                        <?php $isAdmin = ($msg['sender_type'] === 'admin'); ?>
                        <div class="d-flex <?php echo $isAdmin ? 'justify-content-start' : 'justify-content-end'; ?>">
                            <div class="card border-0 shadow-sm p-3 style-bubble" style="max-width: 80%; background-color: <?php echo $isAdmin ? '#eef2ff' : '#f8f9fa'; ?>; border-left: 4px solid <?php echo $isAdmin ? '#0d6efd' : '#20c997'; ?> !important;">
                                <div class="d-flex justify-content-between align-items-center mb-1 gap-3">
                                    <span class="fw-bold small <?php echo $isAdmin ? 'text-primary' : 'text-success'; ?>">
                                        <i class="bi bi-<?php echo $isAdmin ? 'headset' : 'person-fill'; ?> me-1"></i>
                                        <?php echo e($msg['sender_name']); ?> <?php echo $isAdmin ? '(Support Agent)' : '(You)'; ?>
                                    </span>
                                    <span class="text-muted text-xxs"><?php echo date('d M, h:i A', strtotime($msg['created_at'])); ?></span>
                                </div>
                                <div class="text-dark small lh-base" style="white-space: pre-wrap;"><?php echo e($msg['message']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Reply Form (if ticket is not closed) -->
                <?php if ($ticket['status'] !== 'closed'): ?>
                    <div class="border-top pt-4">
                        <h6 class="fw-bold mb-3 text-navy" style="color: #0b192c;">
                            <i class="bi bi-reply-fill text-primary me-1"></i> Reply to Ticket Thread
                        </h6>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="reply_ticket">
                            <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">

                            <div class="mb-3">
                                <textarea name="reply_message" class="form-control" rows="4" placeholder="Type your response to support team..." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="bi bi-send-fill me-1"></i> Send Reply
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary border-0 mb-0">
                        <i class="bi bi-lock-fill me-1"></i> This support ticket thread has been closed by support agents. If you require further assistance, please create a new ticket.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

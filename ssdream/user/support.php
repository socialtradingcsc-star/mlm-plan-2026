<?php
/**
 * Social Service Dream - User Support Helpdesk
 * =========================================================================
 * PART 7: MEMBER TICKETING & INQUIRY SYSTEM
 * Target: C:\xampp\htdocs\ssdream\user\support.php
 * =========================================================================
 */

$pageTitle = "Support Helpdesk - Social Service Dream";
$pageHeading = "Customer Support & Inquiries";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireUserLogin();

$userId = (int)$_SESSION['user_id'];
$user = getUserById($userId);
$userName = $user['full_name'] ?? 'Member';

$alert = null;

// Handle New Ticket Creation or Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $alert = ['type' => 'danger', 'msg' => 'Security session expired. Please refresh and try again.'];
    } else {
        $action = trim($_POST['action']);

        if ($action === 'create_ticket') {
            $subj = trim($_POST['subject'] ?? '');
            $cat = trim($_POST['category'] ?? 'General');
            $pri = trim($_POST['priority'] ?? 'medium');
            $msg = trim($_POST['message'] ?? '');

            $res = createSupportTicket($userId, $subj, $cat, $pri, $msg);
            $alert = ['type' => $res['success'] ? 'success' : 'danger', 'msg' => $res['message']];
        } elseif ($action === 'reply_ticket') {
            $ticketId = (int)($_POST['ticket_id'] ?? 0);
            $replyMsg = trim($_POST['reply_message'] ?? '');

            if (empty($replyMsg)) {
                $alert = ['type' => 'danger', 'msg' => 'Please enter your message before sending.'];
            } else {
                $res = addSupportTicketReply($ticketId, 'user', $userId, $userName, $replyMsg);
                $alert = ['type' => $res['success'] ? 'success' : 'danger', 'msg' => $res['message']];
            }
        }
    }
}

// Fetch user's tickets
$myTicketsData = getSupportTicketsList($userId, 'all', 20, 0);
$myTickets = $myTicketsData['records'];

// Active thread view if requested
$activeTicketId = (int)($_GET['ticket_id'] ?? 0);
$activeThread = null;
if ($activeTicketId > 0) {
    $activeThread = getSupportTicketThread($activeTicketId, $userId);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="fw-bold mb-1" style="color: #0b192c;">Support Helpdesk &amp; Inquiries</h4>
                <p class="text-muted small mb-0">Need help with withdrawals, packages, or bonuses? Create a ticket and our administrative team will assist you.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTicketModal">
                    <i class="bi bi-plus-circle me-1"></i> Open New Ticket
                </button>
            </div>
        </div>
    </div>
</div>

<?php if ($alert): ?>
    <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-<?php echo ($alert['type'] === 'success') ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> me-2"></i>
        <?php echo e($alert['msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Active Thread View -->
<?php if ($activeThread): ?>
    <?php 
        $t = $activeThread['ticket']; 
        $messages = $activeThread['messages'];
    ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #0b192c;">
            <div>
                <span class="badge bg-warning text-dark font-monospace me-2"><?php echo e($t['ticket_no']); ?></span>
                <strong class="fs-6"><?php echo e($t['subject']); ?></strong>
                <span class="badge bg-secondary ms-2 text-xxs"><?php echo e($t['category']); ?></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark text-capitalize"><?php echo str_replace('_', ' ', $t['status']); ?></span>
                <a href="support.php" class="btn btn-sm btn-outline-light">Close Thread</a>
            </div>
        </div>
        <div class="card-body p-4" style="max-height: 480px; overflow-y: auto; background-color: #f8fafc;">
            <?php foreach ($messages as $msg): ?>
                <div class="d-flex mb-3 <?php echo ($msg['sender_type'] === 'user') ? 'justify-content-end' : 'justify-content-start'; ?>">
                    <div class="p-3 rounded-3 shadow-sm <?php echo ($msg['sender_type'] === 'user') ? 'bg-primary text-white' : 'bg-white text-dark border'; ?>" style="max-width: 75%;">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                            <span class="fw-bold small <?php echo ($msg['sender_type'] === 'user') ? 'text-warning' : 'text-primary'; ?>">
                                <i class="bi bi-<?php echo ($msg['sender_type'] === 'user') ? 'person-circle' : 'shield-check'; ?> me-1"></i>
                                <?php echo e($msg['sender_name']); ?>
                            </span>
                            <span class="<?php echo ($msg['sender_type'] === 'user') ? 'text-white-50' : 'text-muted'; ?> text-xxs">
                                <?php echo date('d M Y, h:i A', strtotime($msg['created_at'])); ?>
                            </span>
                        </div>
                        <div class="small" style="white-space: pre-wrap;"><?php echo nl2br(e($msg['message'])); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($t['status'] !== 'closed'): ?>
            <div class="card-footer bg-white p-3 border-top">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="action" value="reply_ticket">
                    <input type="hidden" name="ticket_id" value="<?php echo $t['id']; ?>">

                    <div class="input-group">
                        <textarea name="reply_message" class="form-control" rows="2" placeholder="Type your reply to the administration team..." required></textarea>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-send me-1"></i> Send Reply
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="card-footer bg-light p-3 text-center text-muted small">
                <i class="bi bi-lock me-1"></i> This support ticket has been closed. If you have another query, please open a new ticket.
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Tickets List Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fs-6 fw-bold" style="color: #0b192c;">
            <i class="bi bi-ticket-perforated text-primary me-2"></i> My Support Tickets
        </h5>
        <span class="badge bg-secondary-subtle text-secondary"><?php echo count($myTickets); ?> Total</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase small text-muted">
                <tr>
                    <th class="ps-3 py-3">Ticket ID</th>
                    <th>Category</th>
                    <th>Subject</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Last Activity</th>
                    <th class="text-end pe-3">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($myTickets)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-headset fs-1 d-block mb-2 text-secondary"></i>
                            You have not raised any support tickets yet.
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newTicketModal">
                                    Open First Ticket
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($myTickets as $t): ?>
                        <tr>
                            <td class="ps-3 py-3">
                                <span class="badge bg-light text-dark border font-monospace"><?php echo e($t['ticket_no']); ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary"><?php echo e($t['category'] ?? 'General'); ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?php echo e($t['subject']); ?></div>
                                <small class="text-muted"><?php echo $t['message_count']; ?> messages exchanged</small>
                            </td>
                            <td>
                                <?php 
                                    $priBadge = match($t['priority']) {
                                        'high'   => 'bg-danger-subtle text-danger',
                                        'medium' => 'bg-warning-subtle text-warning',
                                        default  => 'bg-info-subtle text-info'
                                    };
                                ?>
                                <span class="badge <?php echo $priBadge; ?> text-capitalize text-xxs">
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
                                <div class="small"><?php echo date('d M Y', strtotime($t['updated_at'])); ?></div>
                                <div class="text-muted text-xxs"><?php echo date('h:i A', strtotime($t['updated_at'])); ?></div>
                            </td>
                            <td class="text-end pe-3">
                                <a href="?ticket_id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-chat-dots me-1"></i> View Thread
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create New Ticket -->
<div class="modal fade" id="newTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="action" value="create_ticket">

                <div class="modal-header bg-navy text-white" style="background-color: #0b192c;">
                    <h5 class="modal-title fs-6">
                        <i class="bi bi-headset me-2 text-warning"></i> Open New Support Ticket
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Inquiry Category *</label>
                        <select name="category" class="form-select" required>
                            <option value="Withdrawal / Payout">Withdrawal / Payout</option>
                            <option value="Level Upgrade / Package">Level Upgrade / Package</option>
                            <option value="Direct / Sponsor Income">Direct / Sponsor Income</option>
                            <option value="Binary Tree Placement">Binary Tree Placement</option>
                            <option value="KYC Verification">KYC Document Verification</option>
                            <option value="General Inquiry" selected>General Inquiry</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label small fw-semibold">Subject / Title *</label>
                            <input type="text" name="subject" class="form-control" placeholder="Brief summary of your inquiry" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Describe Your Issue In Detail *</label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Provide complete details (e.g. transaction reference, user IDs, or description)..." required></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                        <i class="bi bi-send me-1"></i> Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

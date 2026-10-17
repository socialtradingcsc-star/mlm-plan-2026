<?php
/**
 * Social Service Dream - User Create Support Ticket
 * Target: C:\xampp\htdocs\ssdream\user\create-ticket.php
 */

$pageTitle = "Create Support Ticket - SS Dream";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireUserAuth();
$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_ticket') {
    if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'CSRF token verification failed.');
    } else {
        $subject = trim($_POST['subject'] ?? '');
        $category = trim($_POST['category'] ?? 'OTHER');
        $priority = trim($_POST['priority'] ?? 'medium');
        $message = trim($_POST['message'] ?? '');

        if (empty($subject) || empty($message)) {
            setFlashMessage('error', 'Please fill in both the ticket subject and inquiry message.');
        } else {
            $res = createSupportTicket($userId, $subject, $category, $priority, $message);
            if ($res['success']) {
                sendNotification($userId, 'Support Ticket Created', "Your support ticket #{$res['ticket_no']} has been created successfully. Our team will review and reply shortly.", 'SUPPORT');
                setFlashMessage('success', $res['message']);
                header('Location: ' . BASE_URL . '/user/ticket-view.php?id=' . $res['ticket_id']);
                exit();
            } else {
                setFlashMessage('error', $res['message']);
            }
        }
    }
    header('Location: ' . BASE_URL . '/user/create-ticket.php');
    exit();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo BASE_URL; ?>/user/support.php" class="text-decoration-none text-muted small">
            &larr; Back to Support Tickets
        </a>
        <h3 class="fw-bold text-navy mb-0 mt-1" style="color: #0b192c;">Create New Support Ticket</h3>
    </div>
</div>

<?php echo renderFlashMessages(); ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border shadow-sm bg-white">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-headset text-primary me-2"></i> Submit Inquiry to Helpdesk
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create_ticket">
                    <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Inquiry Category</label>
                            <select name="category" class="form-select" required>
                                <option value="ACCOUNT">ACCOUNT &bull; Profile &amp; Login</option>
                                <option value="KYC">KYC &bull; Document Verification</option>
                                <option value="UPGRADE">UPGRADE &bull; Package Level Upgrade</option>
                                <option value="WITHDRAWAL">WITHDRAWAL &bull; Wallet &amp; Payouts</option>
                                <option value="WALLET">WALLET &bull; Balance &amp; Transactions</option>
                                <option value="OTHER">OTHER &bull; General Question</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Priority Level</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low Priority</option>
                                <option value="medium" selected>Medium Priority</option>
                                <option value="high">High Priority</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Subject / Overview</label>
                        <input type="text" name="subject" class="form-control" placeholder="Brief summary of your request..." required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Detailed Message</label>
                        <textarea name="message" class="form-control" rows="6" placeholder="Describe your question or issue in detail..." required></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?php echo BASE_URL; ?>/user/support.php" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-send-fill me-1"></i> Submit Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

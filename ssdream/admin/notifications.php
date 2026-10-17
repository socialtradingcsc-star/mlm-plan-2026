<?php
/**
 * Social Service Dream - Admin Notification Dispatcher
 * Target: C:\xampp\htdocs\ssdream\admin\notifications.php
 */

$pageTitle = "Broadcast & System Notifications - SS Dream Admin";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Security::requireAdminAuth();

// Handle Notification Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_notification') {
    if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'CSRF security token verification failed.');
    } else {
        $recipientType = trim($_POST['recipient_type'] ?? 'one'); // 'one', 'multiple', 'all_active'
        $targetUser = trim($_POST['target_user'] ?? '');
        $targetUserIds = $_POST['target_user_ids'] ?? [];
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $type = strtoupper(trim($_POST['type'] ?? 'GENERAL'));

        if (empty($title) || empty($message)) {
            setFlashMessage('error', 'Please provide both a notification title and message body.');
        } else {
            $sentCount = 0;
            if ($recipientType === 'all_active') {
                $sentCount = sendBroadcastNotificationToAllActive($title, $message, $type);
                setFlashMessage('success', "Broadcast notification successfully sent to all {$sentCount} active members!");
                logAdminActivity((int)$_SESSION['admin_id'], 'BROADCAST_NOTIFICATION', 'system', null, "Sent broadcast '{$title}' to {$sentCount} active members.");
            } elseif ($recipientType === 'one') {
                $user = getSponsorByIdentifier($targetUser);
                if (!$user) {
                    setFlashMessage('error', "Recipient user '{$targetUser}' not found.");
                } else {
                    if (sendNotification((int)$user['id'], $title, $message, $type)) {
                        setFlashMessage('success', "Notification successfully sent to member @{$user['username']} ({$user['full_name']}).");
                        logAdminActivity((int)$_SESSION['admin_id'], 'SEND_NOTIFICATION', 'user', (int)$user['id'], "Sent notification '{$title}' to @{$user['username']}.");
                    } else {
                        setFlashMessage('error', 'Failed to dispatch notification.');
                    }
                }
            } elseif ($recipientType === 'multiple') {
                if (empty($targetUserIds) || !is_array($targetUserIds)) {
                    setFlashMessage('error', 'Please select at least one recipient member.');
                } else {
                    $sentCount = sendBulkNotifications($targetUserIds, $title, $message, $type);
                    setFlashMessage('success', "Notification successfully sent to {$sentCount} selected members.");
                    logAdminActivity((int)$_SESSION['admin_id'], 'BULK_NOTIFICATION', 'users', null, "Sent bulk notification '{$title}' to {$sentCount} members.");
                }
            }
        }
    }
    header('Location: ' . BASE_URL . '/admin/notifications.php');
    exit();
}

// Fetch notification history
$db = getDb();
$history = [];
$totalNotifs = 0;
if ($db) {
    try {
        $cStmt = $db->query("SELECT COUNT(*) FROM notifications");
        $totalNotifs = (int)$cStmt->fetchColumn();

        $stmt = $db->query("
            SELECT n.*, u.username, u.full_name 
            FROM notifications n
            JOIN users u ON n.user_id = u.id
            ORDER BY n.id DESC
            LIMIT 50
        ");
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        $history = [];
    }
}

// Fetch active members for multi-select dropdown
$activeMembers = [];
if ($db) {
    try {
        $mStmt = $db->query("SELECT id, username, full_name, mobile FROM users WHERE status = 'active' ORDER BY username ASC LIMIT 200");
        $activeMembers = $mStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        $activeMembers = [];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-navy mb-0" style="color: #0b192c;">Broadcast &amp; Notifications Dispatcher</h3>
        <p class="text-muted small mb-0">Send account alerts, system announcements, and targeted broadcast messages to network members.</p>
    </div>
</div>

<?php echo renderFlashMessages(); ?>

<div class="row g-4 mb-4">
    <!-- Dispatch Form Card -->
    <div class="col-lg-5">
        <div class="card border shadow-sm bg-white">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-send text-primary me-2"></i> Create &amp; Send Notification
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="send_notification">
                    <input type="hidden" name="csrf_token" value="<?php echo Security::generateCsrfToken(); ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Recipient Target Group</label>
                        <select name="recipient_type" id="recipientTypeSelect" class="form-select">
                            <option value="one">Single Member (by Username or ID)</option>
                            <option value="all_active">All Active Network Members (Broadcast)</option>
                            <option value="multiple">Selected Multiple Members</option>
                        </select>
                    </div>

                    <!-- Single Member Target Field -->
                    <div class="mb-3" id="singleUserBox">
                        <label class="form-label fw-semibold small">Target Member Username or ID</label>
                        <input type="text" name="target_user" class="form-control" placeholder="e.g., user123 or Member ID">
                    </div>

                    <!-- Multiple Members Target Field -->
                    <div class="mb-3 d-none" id="multipleUserBox">
                        <label class="form-label fw-semibold small">Select Target Members</label>
                        <select name="target_user_ids[]" class="form-select" multiple size="5">
                            <?php foreach ($activeMembers as $m): ?>
                                <option value="<?php echo $m['id']; ?>">@<?php echo e($m['username']); ?> &bull; <?php echo e($m['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-xxs">Hold Ctrl (Windows) or Cmd (Mac) to select multiple members.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Notification Category / Type</label>
                        <select name="type" class="form-select">
                            <option value="GENERAL">GENERAL &bull; General Update</option>
                            <option value="SYSTEM">SYSTEM &bull; Platform Announcement</option>
                            <option value="ACCOUNT">ACCOUNT &bull; Account Status Alert</option>
                            <option value="KYC">KYC &bull; KYC Verification Notice</option>
                            <option value="UPGRADE">UPGRADE &bull; Package Upgrade Notice</option>
                            <option value="WITHDRAWAL">WITHDRAWAL &bull; Withdrawal Notice</option>
                            <option value="SUPPORT">SUPPORT &bull; Support Desk Notice</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Notification Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g., Important System Maintenance Update" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Message Body</label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Enter clear, concise notification details..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="bi bi-send-fill me-1"></i> Dispatch Notification Now
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification History Card -->
    <div class="col-lg-7">
        <div class="card border shadow-sm bg-white">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-navy" style="color: #0b192c;">
                    <i class="bi bi-clock-history text-primary me-2"></i> Notification History Log (<?php echo number_format($totalNotifs); ?>)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 520px;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3">Recipient</th>
                                <th>Category</th>
                                <th>Title &amp; Message</th>
                                <th>Status</th>
                                <th class="pe-3">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($history)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No notification logs recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($history as $h): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold text-dark"><?php echo e($h['full_name']); ?></div>
                                            <div class="text-muted text-xxs">@<?php echo e($h['username']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary border text-xxs">
                                                <?php echo e($h['type'] ?? 'GENERAL'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-navy small"><?php echo e($h['title']); ?></div>
                                            <div class="text-muted text-xxs text-truncate" style="max-width: 240px;"><?php echo e($h['message']); ?></div>
                                        </td>
                                        <td>
                                            <?php if (!empty($h['is_read'])): ?>
                                                <span class="badge bg-success-subtle text-success">Read</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning">Unread</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-3 small text-muted"><?php echo date('d M Y, h:i A', strtotime($h['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('recipientTypeSelect').addEventListener('change', function() {
    var val = this.value;
    var singleBox = document.getElementById('singleUserBox');
    var multiBox = document.getElementById('multipleUserBox');
    
    if (val === 'one') {
        singleBox.classList.remove('d-none');
        multiBox.classList.add('d-none');
    } else if (val === 'multiple') {
        singleBox.classList.add('d-none');
        multiBox.classList.remove('d-none');
    } else {
        singleBox.classList.add('d-none');
        multiBox.classList.add('d-none');
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * Social Service Dream - User Notifications Hub
 * =========================================================================
 * PART 7: MEMBER NOTIFICATIONS & BROADCAST ALERTS
 * Target: C:\xampp\htdocs\ssdream\user\notifications.php
 * =========================================================================
 */

$pageTitle = "Notifications - Social Service Dream";
$pageHeading = "Notifications & System Alerts";
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Authentication guard
requireUserLogin();

$userId = (int)$_SESSION['user_id'];
$db = getDb();
$alert = null;

// Handle Mark All As Read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_all_read') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        try {
            $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :uid OR user_id IS NULL");
            $stmt->execute(['uid' => $userId]);
            $alert = ['type' => 'success', 'msg' => 'All notifications marked as read.'];
        } catch (PDOException $e) {
            $alert = ['type' => 'danger', 'msg' => 'Failed to update notifications.'];
        }
    }
}

// Fetch notifications
$notifications = [];
try {
    $stmt = $db->prepare("
        SELECT * FROM notifications 
        WHERE user_id = :uid OR user_id IS NULL 
        ORDER BY id DESC 
        LIMIT 50
    ");
    $stmt->execute(['uid' => $userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    $notifications = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="fw-bold mb-1" style="color: #0b192c;">Notifications &amp; Activity Log</h4>
                <p class="text-muted small mb-0">System updates, bonus payout alerts, and administrative messages.</p>
            </div>
            <?php if (!empty($notifications)): ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-check2-all me-1"></i> Mark All as Read
                    </button>
                </form>
            <?php endif; ?>
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

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($notifications)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bell-slash fs-1 d-block mb-2 text-secondary"></i>
                You do not have any notifications yet.
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $n): ?>
                    <div class="list-group-item p-3 <?php echo empty($n['is_read']) ? 'bg-light border-start border-primary border-4' : ''; ?>">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="fw-bold text-dark mb-1">
                                    <?php if (empty($n['is_read'])): ?>
                                        <span class="badge bg-primary text-xxs me-1">NEW</span>
                                    <?php endif; ?>
                                    <?php echo e($n['title']); ?>
                                </div>
                                <div class="small text-muted" style="white-space: pre-wrap;"><?php echo e($n['message']); ?></div>
                            </div>
                            <span class="text-muted text-xxs text-nowrap">
                                <?php echo date('d M Y, h:i A', strtotime($n['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

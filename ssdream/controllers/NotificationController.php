<?php
/**
 * Social Service Dream - NotificationController
 * Path: ssdream/controllers/NotificationController.php
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/models/Notification.php';

class NotificationController {
    /**
     * Handle Admin Send Notification POST
     */
    public static function handleAdminSendNotification(): array {
        requireAdminLogin();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'CSRF security token expired. Please refresh and try again.'];
        }

        $targetMode = trim($_POST['target_mode'] ?? 'all'); // 'all', 'single', 'multi'
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $type = trim($_POST['type'] ?? 'GENERAL');

        if (empty($title) || empty($message)) {
            return ['success' => false, 'message' => 'Notification title and message body are required.'];
        }

        $adminId = (int)($_SESSION['admin_id'] ?? 1);

        if ($targetMode === 'single') {
            $userId = (int)($_POST['user_id'] ?? 0);
            if ($userId <= 0) {
                return ['success' => false, 'message' => 'Please select a valid member recipient.'];
            }
            $res = Notification::create($userId, $title, $message, $type);
            logAdminActivity($adminId, 'SEND_NOTIFICATION_SINGLE', 'user', $userId, "Title: {$title}");
            return ['success' => $res, 'message' => $res ? "Notification sent to User #{$userId}." : 'Failed to send notification.'];
        } elseif ($targetMode === 'multi') {
            $userIds = $_POST['user_ids'] ?? [];
            if (!is_array($userIds) || empty($userIds)) {
                return ['success' => false, 'message' => 'Please select at least one recipient user.'];
            }
            $count = 0;
            foreach ($userIds as $uid) {
                $uidInt = (int)$uid;
                if ($uidInt > 0 && Notification::create($uidInt, $title, $message, $type)) {
                    $count++;
                }
            }
            logAdminActivity($adminId, 'SEND_NOTIFICATION_MULTI', 'user', null, "Sent to {$count} users. Title: {$title}");
            return ['success' => true, 'message' => "Notification dispatched to {$count} selected members."];
        } else {
            // Broadcast to All
            $res = Notification::broadcast($title, $message, $type);
            logAdminActivity($adminId, 'SEND_NOTIFICATION_BROADCAST', 'all_users', null, "Title: {$title}");
            return ['success' => $res, 'message' => $res ? 'Broadcast notification dispatched to all registered members!' : 'Broadcast failed.'];
        }
    }
}

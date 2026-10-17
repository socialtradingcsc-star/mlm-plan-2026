<?php
/**
 * Social Service Dream - SupportController
 * Path: ssdream/controllers/SupportController.php
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/models/SupportTicket.php';
require_once dirname(__DIR__) . '/models/SupportMessage.php';

class SupportController {
    /**
     * Create Support Ticket for User
     */
    public static function handleUserCreateTicket(): array {
        requireUserLogin();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'CSRF security token expired. Please refresh and try again.'];
        }

        $userId = (int)$_SESSION['user_id'];
        $subject = trim($_POST['subject'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $priority = trim($_POST['priority'] ?? 'medium');
        $message = trim($_POST['message'] ?? '');

        if (empty($subject) || empty($message)) {
            return ['success' => false, 'message' => 'Ticket subject and message cannot be empty.'];
        }

        return SupportTicket::create($userId, $subject, $category, $priority, $message);
    }

    /**
     * Reply to Support Ticket for Admin or User
     */
    public static function handleReply(): array {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'CSRF token verification failed.'];
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $newStatus = trim($_POST['status'] ?? '');

        if ($ticketId <= 0 || empty($message)) {
            return ['success' => false, 'message' => 'Invalid ticket ID or empty message.'];
        }

        $isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

        if ($isAdmin) {
            $senderType = 'admin';
            $senderId = (int)($_SESSION['admin_id'] ?? 1);
            $senderName = $_SESSION['admin_name'] ?? 'Support Admin';
            logAdminActivity($senderId, 'REPLY_SUPPORT_TICKET', 'ticket', $ticketId, "Status set to: " . ($newStatus ?: 'in_progress'));
        } else {
            requireUserLogin();
            $senderType = 'user';
            $senderId = (int)$_SESSION['user_id'];
            $senderName = $_SESSION['user_fullname'] ?? 'Member';

            // Verify user owns the ticket
            $t = SupportTicket::getById($ticketId);
            if (!$t || (int)$t['user_id'] !== $senderId) {
                return ['success' => false, 'message' => 'Unauthorized ticket access.'];
            }
        }

        $res = SupportMessage::addReply($ticketId, $senderType, $senderId, $senderName, $message, $newStatus ?: null);
        return ['success' => $res, 'message' => $res ? 'Reply posted successfully.' : 'Failed to post reply.'];
    }
}

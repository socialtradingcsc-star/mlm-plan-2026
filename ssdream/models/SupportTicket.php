<?php
/**
 * Social Service Dream - SupportTicket Model
 * Path: ssdream/models/SupportTicket.php
 */

class SupportTicket {
    /**
     * Create a new support ticket
     */
    public static function create(int $userId, string $subject, string $category, string $priority, string $initialMessage): array {
        $db = Database::getConnection();
        if (!$db) return ['success' => false, 'message' => 'Database connection unavailable.'];

        $ticketNo = 'TKT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO support_tickets (user_id, ticket_no, subject, category, priority, status, created_at, updated_at)
                VALUES (:uid, :tno, :subj, :cat, :pri, 'open', NOW(), NOW())
            ");
            $stmt->execute([
                'uid'  => $userId,
                'tno'  => $ticketNo,
                'subj' => $subject,
                'cat'  => $category,
                'pri'  => strtolower($priority)
            ]);
            $ticketId = (int)$db->lastInsertId();

            // Insert initial message
            $stmtMsg = $db->prepare("
                INSERT INTO support_messages (ticket_id, sender_type, sender_id, sender_name, message, created_at)
                VALUES (:tid, 'user', :sid, :sname, :msg, NOW())
            ");

            // Fetch user name
            $uStmt = $db->prepare("SELECT full_name FROM users WHERE id = :uid LIMIT 1");
            $uStmt->execute(['uid' => $userId]);
            $userName = $uStmt->fetchColumn() ?: 'Member';

            $stmtMsg->execute([
                'tid'   => $ticketId,
                'sid'   => $userId,
                'sname' => $userName,
                'msg'   => $initialMessage
            ]);

            // Notify User
            Notification::create($userId, 'Support Ticket Created', "Your ticket #{$ticketNo} has been submitted successfully.", 'SUPPORT');

            $db->commit();
            return ['success' => true, 'ticket_id' => $ticketId, 'ticket_no' => $ticketNo, 'message' => "Ticket #{$ticketNo} created successfully!"];
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            return ['success' => false, 'message' => 'Failed to create ticket: ' . $e->getMessage()];
        }
    }

    /**
     * Get ticket by ID
     */
    public static function getById(int $ticketId): ?array {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("
                SELECT t.*, u.username, u.full_name, u.email, u.mobile 
                FROM support_tickets t
                JOIN users u ON t.user_id = u.id
                WHERE t.id = :tid LIMIT 1
            ");
            $stmt->execute(['tid' => $ticketId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}

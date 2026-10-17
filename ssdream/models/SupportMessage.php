<?php
/**
 * Social Service Dream - SupportMessage Model
 * Path: ssdream/models/SupportMessage.php
 */

class SupportMessage {
    /**
     * Add reply message to ticket
     */
    public static function addReply(int $ticketId, string $senderType, int $senderId, string $senderName, string $message, ?string $newStatus = null): bool {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO support_messages (ticket_id, sender_type, sender_id, sender_name, message, created_at)
                VALUES (:tid, :stype, :sid, :sname, :msg, NOW())
            ");
            $stmt->execute([
                'tid'   => $ticketId,
                'stype' => $senderType,
                'sid'   => $senderId,
                'sname' => $senderName,
                'msg'   => $message
            ]);

            $st = $newStatus ?: (($senderType === 'admin') ? 'in_progress' : 'open');
            $uStmt = $db->prepare("UPDATE support_tickets SET status = :st, updated_at = NOW() WHERE id = :tid");
            $uStmt->execute(['st' => $st, 'tid' => $ticketId]);

            // If admin replied, send notification to user
            if ($senderType === 'admin') {
                $tStmt = $db->prepare("SELECT user_id, ticket_no FROM support_tickets WHERE id = :tid LIMIT 1");
                $tStmt->execute(['tid' => $ticketId]);
                $t = $tStmt->fetch(PDO::FETCH_ASSOC);
                if ($t) {
                    Notification::create($t['user_id'], 'Support Ticket Reply', "New response on ticket #{$t['ticket_no']}.", 'SUPPORT');
                }
            }

            $db->commit();
            return true;
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            return false;
        }
    }
}

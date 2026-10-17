<?php
/**
 * Social Service Dream - Notification Model
 * Path: ssdream/models/Notification.php
 */

class Notification {
    /**
     * Create a notification entry in database
     */
    public static function create(?int $userId, string $title, string $message, string $type = 'GENERAL'): bool {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, title, message, type, is_read, created_at)
                VALUES (:uid, :title, :message, :type, 0, NOW())
            ");
            return $stmt->execute([
                'uid'     => $userId,
                'title'   => $title,
                'message' => $message,
                'type'    => strtoupper($type)
            ]);
        } catch (PDOException $e) {
            error_log("Notification Create Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send broadcast notification to all active users
     */
    public static function broadcast(string $title, string $message, string $type = 'GENERAL'): bool {
        return self::create(null, $title, $message, $type);
    }

    /**
     * Get user notifications
     */
    public static function getForUser(int $userId, int $limit = 20, int $offset = 0): array {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->prepare("
                SELECT * FROM notifications 
                WHERE user_id = :uid OR user_id IS NULL
                ORDER BY id DESC
                LIMIT :offset, :limit
            ");
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Mark all notifications read for user
     */
    public static function markAllRead(int $userId): bool {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                UPDATE notifications SET is_read = 1 
                WHERE user_id = :uid OR user_id IS NULL
            ");
            return $stmt->execute(['uid' => $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

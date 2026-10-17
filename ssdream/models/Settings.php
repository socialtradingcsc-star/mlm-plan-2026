<?php
/**
 * Social Service Dream - Settings Model
 * Path: ssdream/models/Settings.php
 */

class Settings {
    /**
     * Get all settings
     */
    public static function getAll(): array {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $stmt = $db->query("SELECT setting_key, setting_value, description FROM settings");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $settings = [];
            foreach ($rows as $r) {
                $settings[$r['setting_key']] = $r['setting_value'];
            }
            return $settings;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get single setting by key
     */
    public static function get(string $key, $default = ''): string {
        $db = Database::getConnection();
        if (!$db) return (string)$default;

        try {
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = :k LIMIT 1");
            $stmt->execute(['k' => $key]);
            $val = $stmt->fetchColumn();
            return ($val !== false) ? (string)$val : (string)$default;
        } catch (PDOException $e) {
            return (string)$default;
        }
    }

    /**
     * Save multiple settings safely while protecting core business plan rules
     */
    public static function saveBatch(array $keyValuePairs): bool {
        $db = Database::getConnection();
        if (!$db) return false;

        // PROTECTED core business plan rules that CANNOT be altered
        $protectedKeys = ['direct_income_percent', 'level_income_percent', 'sponsor_income_percent', 'joining_package'];

        try {
            $db->beginTransaction();
            $stmt = $db->prepare("
                INSERT INTO settings (setting_key, setting_value, updated_at)
                VALUES (:k, :v, NOW())
                ON DUPLICATE KEY UPDATE setting_value = :v, updated_at = NOW()
            ");

            foreach ($keyValuePairs as $key => $value) {
                if (in_array($key, $protectedKeys, true)) {
                    continue; // Safeguard core business rules
                }
                $stmt->execute(['k' => $key, 'v' => (string)$value]);
            }

            $db->commit();
            return true;
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            return false;
        }
    }
}

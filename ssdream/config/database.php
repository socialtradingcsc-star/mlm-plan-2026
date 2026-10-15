<?php
/**
 * Social Service Dream - Database Connection Handler
 * Uses PHP Data Objects (PDO) with prepared statements
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;
    private static ?string $connectionError = null;

    /**
     * Get singleton PDO connection instance
     * 
     * @return PDO|null
     */
    public static function getConnection(): ?PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET . ";port=" . DB_PORT;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
                ];

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                self::$connectionError = null;
            } catch (PDOException $e) {
                self::$connectionError = $e->getMessage();
                // Do not die immediately in production to allow graceful UI messaging
                error_log("SS Dream DB Connection Error: " . $e->getMessage());
                return null;
            }
        }

        return self::$instance;
    }

    /**
     * Check if database connection is active
     * 
     * @return bool
     */
    public static function isConnected(): bool {
        return self::getConnection() !== null;
    }

    /**
     * Return connection error string if any
     * 
     * @return string|null
     */
    public static function getError(): ?string {
        return self::$connectionError;
    }
}

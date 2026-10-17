<?php
/**
 * Social Service Dream - SettingsController
 * Path: ssdream/controllers/SettingsController.php
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/security.php';
require_once dirname(__DIR__) . '/models/Settings.php';

class SettingsController {
    /**
     * Save Admin Settings POST with core rule protection & Audit Logging
     */
    public static function handleAdminSaveSettings(): array {
        requireAdminLogin();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'CSRF security token expired. Please refresh and try again.'];
        }

        $adminId = (int)($_SESSION['admin_id'] ?? 1);

        $allowedData = [
            'site_name'                 => trim($_POST['site_name'] ?? 'Social Service Dream'),
            'site_email'                => trim($_POST['site_email'] ?? 'support@ssdream.com'),
            'site_phone'                => trim($_POST['site_phone'] ?? '+91 98765 43210'),
            'site_address'              => trim($_POST['site_address'] ?? ''),
            'timezone'                  => trim($_POST['timezone'] ?? 'Asia/Kolkata'),
            'date_format'               => trim($_POST['date_format'] ?? 'd M Y, h:i A'),
            'maintenance_mode'          => ($_POST['maintenance_mode'] ?? '0') === '1' ? '1' : '0',
            'min_withdrawal'            => max(1, (float)($_POST['min_withdrawal'] ?? 500)),
            'withdrawal_fee'            => max(0, (float)($_POST['withdrawal_fee'] ?? 0)),
            'company_bank_name'         => trim($_POST['company_bank_name'] ?? ''),
            'company_account_holder'    => trim($_POST['company_account_holder'] ?? ''),
            'company_account_number'    => trim($_POST['company_account_number'] ?? ''),
            'company_ifsc_code'         => strtoupper(trim($_POST['company_ifsc_code'] ?? '')),
            'company_upi_id'            => trim($_POST['company_upi_id'] ?? ''),
            'notice_board_text'         => trim($_POST['notice_board_text'] ?? ''),
            'notice_board_status'       => in_array($_POST['notice_board_status'] ?? '', ['active', 'inactive'], true) ? $_POST['notice_board_status'] : 'active'
        ];

        $res = Settings::saveBatch($allowedData);
        if ($res) {
            logAdminActivity($adminId, 'UPDATE_SETTINGS', 'settings', null, "Updated system technical settings.");
            return ['success' => true, 'message' => 'System settings updated successfully!'];
        }
        return ['success' => false, 'message' => 'Failed to save settings to database.'];
    }

    /**
     * Handle User Password Change in user/settings.php
     */
    public static function handleUserPasswordChange(): array {
        requireUserLogin();

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'CSRF security token expired. Please refresh and try again.'];
        }

        $userId = (int)$_SESSION['user_id'];
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
            return ['success' => false, 'message' => 'Please fill in all password fields.'];
        }

        if ($newPass !== $confirmPass) {
            return ['success' => false, 'message' => 'New password and confirmation password do not match.'];
        }

        if (strlen($newPass) < 6) {
            return ['success' => false, 'message' => 'New password must be at least 6 characters long.'];
        }

        $db = Database::getConnection();
        if (!$db) return ['success' => false, 'message' => 'Database connection failed.'];

        try {
            $stmt = $db->prepare("SELECT password FROM users WHERE id = :uid LIMIT 1");
            $stmt->execute(['uid' => $userId]);
            $hash = $stmt->fetchColumn();

            if (!$hash || !password_verify($currentPass, $hash)) {
                return ['success' => false, 'message' => 'Your current password is incorrect.'];
            }

            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            $uStmt = $db->prepare("UPDATE users SET password = :p, updated_at = NOW() WHERE id = :uid");
            $uStmt->execute(['p' => $newHash, 'uid' => $userId]);

            Notification::create($userId, 'Security Alert: Password Changed', 'Your account password was changed successfully.', 'ACCOUNT');

            return ['success' => true, 'message' => 'Your password has been changed successfully!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error changing password: ' . $e->getMessage()];
        }
    }
}

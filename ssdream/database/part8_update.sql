-- =======================================================
-- Social Service Dream - Part 8 Database Schema Updates
-- Reports, Notifications, Audit Logs & Support Enhancement
-- =======================================================

USE `ssdream`;

-- 1. Table for Admin Activity Audit Logs
CREATE TABLE IF NOT EXISTS `admin_activity_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT UNSIGNED NOT NULL,
  `admin_username` VARCHAR(50) NOT NULL DEFAULT 'admin',
  `action` VARCHAR(100) NOT NULL,
  `target_type` VARCHAR(50) DEFAULT NULL,
  `target_id` INT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `details` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_log_admin` (`admin_id`),
  INDEX `idx_log_action` (`action`),
  INDEX `idx_log_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add type column to notifications table if not existing
ALTER TABLE `notifications`
  ADD COLUMN IF NOT EXISTS `type` VARCHAR(30) NOT NULL DEFAULT 'GENERAL' AFTER `message`;

-- 3. Add Category and Priority to support_tickets if not present
ALTER TABLE `support_tickets`
  ADD COLUMN IF NOT EXISTS `category` VARCHAR(50) NOT NULL DEFAULT 'General' AFTER `subject`,
  ADD COLUMN IF NOT EXISTS `last_reply_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `updated_at`;

-- 4. Insert default system settings keys
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('site_email', 'support@ssdream.com', 'Official platform email address'),
('site_phone', '+91 98765 43210', 'Official platform customer support phone'),
('site_address', '101, Dream Plaza, Financial District, New Delhi, India', 'Corporate office address'),
('timezone', 'Asia/Kolkata', 'Platform default operational timezone'),
('date_format', 'd M Y, h:i A', 'Platform default display date format'),
('maintenance_mode', '0', 'System maintenance mode toggle (0 = Live, 1 = Maintenance)')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

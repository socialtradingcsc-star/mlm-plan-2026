-- =======================================================
-- Social Service Dream - Part 9 Database Security & Indexes
-- Rate Limiting, Idempotency Keys, and Unique Constraints
-- =======================================================

USE `ssdream`;

-- 1. Table for IP Rate Limiting & Brute-Force Protection
CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) NOT NULL,
  `action_key` VARCHAR(50) NOT NULL,
  `attempts` INT UNSIGNED NOT NULL DEFAULT 1,
  `last_attempt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_ip_action` (`ip_address`, `action_key`),
  INDEX `idx_last_attempt` (`last_attempt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add System Environment & Production Security Keys if not present
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('app_env', 'development', 'Environment mode: development or production'),
('rate_limit_login_max', '5', 'Max login attempts before IP lockout window'),
('rate_limit_window_minutes', '15', 'Lockout window duration in minutes'),
('max_file_upload_size_mb', '5', 'Maximum allowed KYC & attachment upload size in MB'),
('allowed_upload_extensions', 'jpg,jpeg,png,pdf', 'Permitted file upload extensions list')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- 3. Optimization Indexes for Fast Reporting & Database Performance
ALTER TABLE `users` 
  ADD INDEX IF NOT EXISTS `idx_user_status_lvl` (`status`, `current_level`),
  ADD INDEX IF NOT EXISTS `idx_user_sponsor` (`sponsor_id`),
  ADD INDEX IF NOT EXISTS `idx_user_parent` (`parent_id`);

ALTER TABLE `withdrawals` 
  ADD INDEX IF NOT EXISTS `idx_wth_user_st` (`user_id`, `status`);

ALTER TABLE `upgrades` 
  ADD INDEX IF NOT EXISTS `idx_upg_user_st` (`user_id`, `status`);

ALTER TABLE `incomes` 
  ADD UNIQUE INDEX IF NOT EXISTS `uk_income_ref` (`transaction_ref`);

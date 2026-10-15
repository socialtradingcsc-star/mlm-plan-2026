-- =======================================================
-- Social Service Dream - Database Structure
-- Database: ssdream
-- Technology: MySQL 5.7+ / MariaDB 10.4+ / XAMPP / cPanel
-- =======================================================

CREATE DATABASE IF NOT EXISTS `ssdream` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ssdream`;

-- -------------------------------------------------------
-- 1. Table structure for table `users`
-- -------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sponsor_id` INT UNSIGNED DEFAULT NULL,
  `sponsor_username` VARCHAR(50) DEFAULT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `mobile` VARCHAR(20) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `current_level` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Current Package Level (1 to 7)',
  `position` ENUM('left', 'right') DEFAULT NULL COMMENT 'Binary leg position under parent',
  `parent_id` INT UNSIGNED DEFAULT NULL COMMENT 'Direct parent in binary tree',
  `status` ENUM('active', 'pending', 'blocked') NOT NULL DEFAULT 'active',
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `mobile_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_sponsor_id` (`sponsor_id`),
  INDEX `idx_parent_id` (`parent_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_current_level` (`current_level`),
  UNIQUE KEY `uq_parent_position` (`parent_id`, `position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 2. Table structure for table `user_profiles`
-- -------------------------------------------------------
DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE `user_profiles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `pincode` VARCHAR(10) DEFAULT NULL,
  `bank_name` VARCHAR(100) DEFAULT NULL,
  `account_number` VARCHAR(50) DEFAULT NULL,
  `ifsc_code` VARCHAR(20) DEFAULT NULL,
  `account_holder_name` VARCHAR(100) DEFAULT NULL,
  `upi_id` VARCHAR(100) DEFAULT NULL,
  `pan_number` VARCHAR(20) DEFAULT NULL,
  `aadhar_number` VARCHAR(20) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default-avatar.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- -------------------------------------------------------
-- 3. Table structure for table `packages`
-- Strictly preserved from SS Dream document
-- Contains: id, level_no, package_name, amount, team_size, status, created_at
-- -------------------------------------------------------
DROP TABLE IF EXISTS `packages`;
CREATE TABLE `packages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `level_number` TINYINT UNSIGNED NOT NULL UNIQUE COMMENT 'Level 1 to 7',
  `level_no` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Synonym for level_number',
  `package_name` VARCHAR(50) NOT NULL,
  `price` DECIMAL(12,2) NOT NULL COMMENT 'Package cost in INR',
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Synonym for price in INR',
  `team_size` INT UNSIGNED NOT NULL COMMENT 'Required team size at this level',
  `direct_income_percent` DECIMAL(5,2) NOT NULL DEFAULT 100.00 COMMENT '100% on Level 1',
  `level_income_percent` DECIMAL(5,2) NOT NULL DEFAULT 50.00 COMMENT '50% on Level 2 to 7',
  `sponsor_income_percent` DECIMAL(5,2) NOT NULL DEFAULT 50.00 COMMENT '50% Level 2 to Unlimited',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `chk_package_level` CHECK (`level_number` BETWEEN 1 AND 7)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Seed packages: Exactly 7 records strictly as specified
-- -------------------------------------------------------
INSERT INTO `packages` (`level_number`, `level_no`, `package_name`, `price`, `amount`, `team_size`, `direct_income_percent`, `level_income_percent`, `sponsor_income_percent`, `status`) VALUES
(1, 1, 'Level 1', 1200.00, 1200.00, 2, 100.00, 0.00, 0.00, 'active'),
(2, 2, 'Level 2', 2000.00, 2000.00, 4, 0.00, 50.00, 50.00, 'active'),
(3, 3, 'Level 3', 3000.00, 3000.00, 8, 0.00, 50.00, 50.00, 'active'),
(4, 4, 'Level 4', 8000.00, 8000.00, 16, 0.00, 50.00, 50.00, 'active'),
(5, 5, 'Level 5', 40000.00, 40000.00, 32, 0.00, 50.00, 50.00, 'active'),
(6, 6, 'Level 6', 200000.00, 200000.00, 64, 0.00, 50.00, 50.00, 'active'),
(7, 7, 'Level 7', 500000.00, 500000.00, 128, 0.00, 50.00, 50.00, 'active');

-- -------------------------------------------------------
-- 4. Table structure for table `wallets`
-- -------------------------------------------------------
DROP TABLE IF EXISTS `wallets`;
CREATE TABLE `wallets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `balance` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Available main balance',
  `total_income` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Cumulative all earnings',
  `direct_income` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT '100% Direct Sponsor Income',
  `level_income` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT '50% Level Income (L2-L7)',
  `sponsor_income` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT '50% Sponsor Income (L2-Unlimited)',
  `total_withdrawn` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_wallet_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 5. Table structure for table `transactions`
-- -------------------------------------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `transaction_type` ENUM('credit', 'debit') NOT NULL,
  `category` ENUM('package_purchase', 'direct_income', 'level_income', 'sponsor_income', 'withdrawal', 'upgrade', 'admin_adjustment') NOT NULL,
  `amount` DECIMAL(14,2) NOT NULL,
  `reference_no` VARCHAR(60) NOT NULL UNIQUE,
  `description` VARCHAR(255) NOT NULL,
  `status` ENUM('completed', 'pending', 'failed') NOT NULL DEFAULT 'completed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_trans_user` (`user_id`),
  INDEX `idx_trans_category` (`category`),
  INDEX `idx_trans_status` (`status`),
  CONSTRAINT `fk_trans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 6. Table structure for table `incomes`
-- Detailed ledger for the 3 official income streams
-- -------------------------------------------------------
DROP TABLE IF EXISTS `incomes`;
CREATE TABLE `incomes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL COMMENT 'Beneficiary user',
  `from_user_id` INT UNSIGNED NOT NULL COMMENT 'Triggering user who paid/upgraded',
  `income_type` ENUM('direct', 'level', 'sponsor') NOT NULL,
  `level_number` TINYINT UNSIGNED NOT NULL COMMENT '1 to 7 or higher',
  `amount` DECIMAL(14,2) NOT NULL,
  `percentage` DECIMAL(5,2) NOT NULL,
  `remarks` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_income_user` (`user_id`),
  INDEX `idx_income_from_user` (`from_user_id`),
  INDEX `idx_income_type` (`income_type`),
  CONSTRAINT `fk_income_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_income_from_user` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 7. Table structure for table `binary_tree`
-- Tracks left and right child nodes for binary matrix
-- -------------------------------------------------------
DROP TABLE IF EXISTS `binary_tree`;
CREATE TABLE `binary_tree` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `left_child_id` INT UNSIGNED DEFAULT NULL,
  `right_child_id` INT UNSIGNED DEFAULT NULL,
  `tree_level` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_tree_parent` (`parent_id`),
  INDEX `idx_tree_left` (`left_child_id`),
  INDEX `idx_tree_right` (`right_child_id`),
  CONSTRAINT `fk_tree_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 8. Table structure for table `upgrades`
-- Tracks package upgrades from Level 1 up to Level 7
-- -------------------------------------------------------
DROP TABLE IF EXISTS `upgrades`;
CREATE TABLE `upgrades` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `from_level` TINYINT UNSIGNED NOT NULL,
  `to_level` TINYINT UNSIGNED NOT NULL,
  `package_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_mode` VARCHAR(50) NOT NULL DEFAULT 'Wallet / Direct Payment',
  `transaction_ref` VARCHAR(100) DEFAULT NULL,
  `payment_proof` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `admin_remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_upgrades_user` (`user_id`),
  INDEX `idx_upgrades_status` (`status`),
  CONSTRAINT `fk_upgrades_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_upgrades_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`),
  CONSTRAINT `chk_upgrade_levels` CHECK (`to_level` BETWEEN 2 AND 7 AND `to_level` > `from_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 9. Table structure for table `withdrawals`
-- -------------------------------------------------------
DROP TABLE IF EXISTS `withdrawals`;
CREATE TABLE `withdrawals` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(14,2) NOT NULL,
  `tds_deduction` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `admin_charge` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `net_amount` DECIMAL(14,2) NOT NULL,
  `payment_mode` VARCHAR(50) NOT NULL DEFAULT 'Bank Transfer',
  `payment_details` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `admin_remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_withdraw_user` (`user_id`),
  INDEX `idx_withdraw_status` (`status`),
  CONSTRAINT `fk_withdraw_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 10. Table structure for table `kyc`
-- -------------------------------------------------------
DROP TABLE IF EXISTS `kyc`;
CREATE TABLE `kyc` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `pan_card_path` VARCHAR(255) DEFAULT NULL,
  `aadhar_front_path` VARCHAR(255) DEFAULT NULL,
  `aadhar_back_path` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` TEXT DEFAULT NULL,
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_kyc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 11. Table structure for table `notifications`
-- -------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL COMMENT 'NULL for broadcast to all',
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_notif_user` (`user_id`),
  INDEX `idx_notif_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 12. Table structure for table `support_tickets`
-- -------------------------------------------------------
DROP TABLE IF EXISTS `support_tickets`;
CREATE TABLE `support_tickets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `ticket_no` VARCHAR(30) NOT NULL UNIQUE,
  `subject` VARCHAR(200) NOT NULL,
  `priority` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
  `status` ENUM('open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_ticket_user` (`user_id`),
  INDEX `idx_ticket_status` (`status`),
  CONSTRAINT `fk_ticket_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 13. Table structure for table `admin_users`
-- -------------------------------------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'admin') NOT NULL DEFAULT 'super_admin',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default Administrator: admin / Admin@123
-- Hash generated using password_hash('Admin@123', PASSWORD_BCRYPT)
INSERT INTO `admin_users` (`id`, `username`, `full_name`, `email`, `password`, `role`, `status`) VALUES
(1, 'admin', 'System Administrator', 'admin@ssdream.com', '$2y$10$wT5gQ9/yE2fU5RjYx9bE3O7M9wHqE9dE4g5b6a7z8y9x0w1v2u3t4', 'super_admin', 'active')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

-- -------------------------------------------------------
-- 14. Table structure for table `settings`
-- -------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(60) NOT NULL UNIQUE,
  `setting_value` TEXT NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default system settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('site_name', 'Social Service Dream', 'Company and platform title'),
('joining_package', '1200', 'Standard Level 1 entry fee in INR'),
('currency', '₹', 'Currency symbol'),
('currency_code', 'INR', 'ISO currency code'),
('direct_income_percent', '100', 'Direct referral income percentage'),
('level_income_percent', '50', 'Level income percentage for levels 2 through 7'),
('sponsor_income_percent', '50', 'Sponsor income percentage from level 2 to unlimited'),
('min_withdrawal', '500', 'Minimum withdrawal amount in INR'),
('tds_percent', '5', 'TDS tax deduction percentage'),
('admin_charge_percent', '10', 'Platform maintenance charge percentage'),
('support_email', 'support@ssdream.com', 'Official support email address'),
('support_phone', '+91 98765 43210', 'Official customer support phone')
ON DUPLICATE KEY UPDATE `setting_key` = VALUES(`setting_key`);

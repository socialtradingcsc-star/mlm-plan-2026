-- =======================================================
-- Social Service Dream - Part 6 Database Migration
-- Target: C:\xampp\htdocs\ssdream\database\part6_update.sql
-- Modules: Wallet Ledger, Transactions & Withdrawal Engine
-- =======================================================

USE ssdream;

-- 1. Ensure wallets table has total_credit and total_debit columns
ALTER TABLE `wallets` 
  ADD COLUMN IF NOT EXISTS `total_credit` DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER `balance`,
  ADD COLUMN IF NOT EXISTS `total_debit` DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER `total_credit`;

-- Sync existing figures if total_credit is 0 and total_income > 0
UPDATE `wallets` SET `total_credit` = `total_income` WHERE `total_credit` = 0 AND `total_income` > 0;
UPDATE `wallets` SET `total_debit` = `total_withdrawn` WHERE `total_debit` = 0 AND `total_withdrawn` > 0;

-- 2. Enhance transactions table with ledger audit columns (balance_before, balance_after, reference details)
ALTER TABLE `transactions`
  ADD COLUMN IF NOT EXISTS `wallet_id` INT UNSIGNED NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `balance_before` DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER `amount`,
  ADD COLUMN IF NOT EXISTS `balance_after` DECIMAL(14,2) NOT NULL DEFAULT 0.00 AFTER `balance_before`,
  ADD COLUMN IF NOT EXISTS `reference_type` VARCHAR(50) NULL DEFAULT 'system' AFTER `balance_after`,
  ADD COLUMN IF NOT EXISTS `reference_id` VARCHAR(100) NULL AFTER `reference_type`;

ALTER TABLE `transactions` ADD INDEX IF NOT EXISTS `idx_trans_type` (`transaction_type`);
ALTER TABLE `transactions` ADD INDEX IF NOT EXISTS `idx_trans_created` (`created_at`);

-- 3. Ensure withdrawals table has all necessary fields
CREATE TABLE IF NOT EXISTS `withdrawals` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(14,2) NOT NULL,
  `tds_deduction` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `admin_charge` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `net_amount` DECIMAL(14,2) NOT NULL,
  `payment_mode` VARCHAR(50) NOT NULL DEFAULT 'Bank Transfer',
  `payment_details` TEXT DEFAULT NULL,
  `transaction_ref` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `admin_remarks` TEXT DEFAULT NULL,
  `processed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_withdraw_user` (`user_id`),
  INDEX `idx_withdraw_status` (`status`),
  CONSTRAINT `fk_withdraw_user_p6` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `withdrawals` 
  ADD COLUMN IF NOT EXISTS `transaction_ref` VARCHAR(100) NULL DEFAULT NULL AFTER `payment_details`,
  ADD COLUMN IF NOT EXISTS `processed_at` TIMESTAMP NULL DEFAULT NULL AFTER `admin_remarks`;

-- =======================================================
-- Social Service Dream - Part 2 Database Schema Updates
-- Database: ssdream
-- =======================================================

USE `ssdream`;

-- 1. Ensure `kyc` table has document_type, document_number, and document_path
ALTER TABLE `kyc`
  ADD COLUMN IF NOT EXISTS `document_type` VARCHAR(50) DEFAULT 'Aadhar Card' AFTER `user_id`,
  ADD COLUMN IF NOT EXISTS `document_number` VARCHAR(100) DEFAULT NULL AFTER `document_type`,
  ADD COLUMN IF NOT EXISTS `document_path` VARCHAR(255) DEFAULT NULL AFTER `document_number`;

-- 2. Create password_resets table for Forgot Password module foundation
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL,
  `token` VARCHAR(100) NOT NULL UNIQUE,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_reset_email` (`email`),
  INDEX `idx_reset_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

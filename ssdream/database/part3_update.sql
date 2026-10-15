-- =======================================================
-- Social Service Dream - Part 3 Database Schema Updates
-- Database: ssdream
-- Modules: Package, Level & Upgrade System
-- =======================================================

USE `ssdream`;

-- 1. Ensure `users` table has current_level with default 1
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `current_level` TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER `password`;

-- 2. Ensure `upgrades` table has pending default and admin remarks
ALTER TABLE `upgrades`
  MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  ADD COLUMN IF NOT EXISTS `admin_remarks` TEXT DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- 3. Verify the 7 Official Packages exist with strictly preserved figures
INSERT INTO `packages` (`level_number`, `package_name`, `price`, `team_size`, `direct_income_percent`, `level_income_percent`, `sponsor_income_percent`, `status`) VALUES
(1, 'Level 1', 1200.00, 2, 100.00, 0.00, 0.00, 'active'),
(2, 'Level 2', 2000.00, 4, 0.00, 50.00, 50.00, 'active'),
(3, 'Level 3', 3000.00, 8, 0.00, 50.00, 50.00, 'active'),
(4, 'Level 4', 8000.00, 16, 0.00, 50.00, 50.00, 'active'),
(5, 'Level 5', 40000.00, 32, 0.00, 50.00, 50.00, 'active'),
(6, 'Level 6', 200000.00, 64, 0.00, 50.00, 50.00, 'active'),
(7, 'Level 7', 500000.00, 128, 0.00, 50.00, 50.00, 'active')
ON DUPLICATE KEY UPDATE 
  `price` = VALUES(`price`),
  `team_size` = VALUES(`team_size`),
  `package_name` = VALUES(`package_name`);

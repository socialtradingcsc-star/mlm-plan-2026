-- =======================================================
-- Social Service Dream - Part 7 Database Updates
-- Member Management, KYC Verification, Settings & Support
-- =======================================================

USE `ssdream`;

-- 1. Table for Support Ticket Thread Messages / Replies
CREATE TABLE IF NOT EXISTS `support_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT UNSIGNED NOT NULL,
  `sender_type` ENUM('user', 'admin') NOT NULL,
  `sender_id` INT UNSIGNED NOT NULL,
  `sender_name` VARCHAR(100) NOT NULL DEFAULT 'User',
  `message` TEXT NOT NULL,
  `attachment_path` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_msg_ticket` (`ticket_id`),
  INDEX `idx_msg_sender` (`sender_type`, `sender_id`),
  CONSTRAINT `fk_msg_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Ensure support_tickets has category column if not present
ALTER TABLE `support_tickets` 
  ADD COLUMN IF NOT EXISTS `category` VARCHAR(50) NOT NULL DEFAULT 'General' AFTER `subject`,
  ADD COLUMN IF NOT EXISTS `last_reply_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `updated_at`;

-- 3. Ensure kyc table has document_type and document_number
ALTER TABLE `kyc`
  ADD COLUMN IF NOT EXISTS `document_type` VARCHAR(50) NOT NULL DEFAULT 'Aadhar Card' AFTER `user_id`,
  ADD COLUMN IF NOT EXISTS `document_number` VARCHAR(50) DEFAULT NULL AFTER `document_type`,
  ADD COLUMN IF NOT EXISTS `reviewed_by` INT UNSIGNED DEFAULT NULL AFTER `admin_notes`,
  ADD COLUMN IF NOT EXISTS `reviewed_at` DATETIME DEFAULT NULL AFTER `reviewed_by`;

-- 4. Insert / Update default company payment & platform settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('company_bank_name', 'State Bank of India', 'Company receiving bank name for package upgrades'),
('company_account_holder', 'Social Service Dream Welfare Foundation', 'Beneficiary name for bank transfers'),
('company_account_number', '39820100049281', 'Company account number for direct bank deposit'),
('company_ifsc_code', 'SBIN0001234', 'Company bank IFSC code'),
('company_upi_id', 'ssdream@sbi', 'Company official UPI ID for instant payments'),
('company_contact_whatsapp', '+91 98765 43210', 'Official member support WhatsApp number'),
('notice_board_text', 'Welcome to SS Dream! Ensure your KYC is completed to enable instant withdrawals. Direct income 100%, Level & Sponsor income 50%.', 'Active notification banner on user dashboard'),
('notice_board_status', 'active', 'Notice board active or inactive status')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

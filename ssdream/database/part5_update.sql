-- =======================================================
-- Social Service Dream - Part 5 Database Migration
-- Target: C:\xampp\htdocs\ssdream\database\part5_update.sql
-- Modules: Income Calculation Engine & Payout Ledger
-- =======================================================

USE ssdream;

-- 1. Create Incomes Table (Idempotent and Transaction-Safe)
CREATE TABLE IF NOT EXISTS incomes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    income_type ENUM('direct', 'level', 'sponsor') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    percentage DECIMAL(5, 2) NOT NULL,
    from_user_id INT NOT NULL,
    source_level INT NOT NULL DEFAULT 1,
    upgrade_id INT NULL,
    transaction_ref VARCHAR(100) NULL,
    status ENUM('credited', 'pending', 'cancelled') NOT NULL DEFAULT 'credited',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_income_user (user_id),
    KEY idx_income_type (income_type),
    KEY idx_from_user (from_user_id),
    KEY idx_upgrade_id (upgrade_id),
    UNIQUE KEY uq_upgrade_distribution (upgrade_id, income_type, user_id),
    CONSTRAINT fk_income_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_income_from_user FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Ensure users table has current_level index for high-performance ancestry checks
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_current_level (current_level);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_sponsor_id (sponsor_id);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_parent_pos (parent_id, position);

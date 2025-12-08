-- Create system_logs table for tracking system events and activities
CREATE TABLE IF NOT EXISTS `system_logs` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `level` ENUM('error', 'warning', 'info', 'success', 'critical') NOT NULL DEFAULT 'info',
    `source` VARCHAR(100) NOT NULL DEFAULT 'system',
    `message` TEXT NOT NULL,
    `details` TEXT NULL,
    `user_id` INT(11) UNSIGNED NULL,
    `user_role` VARCHAR(50) NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_level` (`level`),
    INDEX `idx_source` (`source`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_user_role` (`user_role`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Create reports table for storing generated reports
CREATE TABLE IF NOT EXISTS `reports` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `type` ENUM('academic', 'attendance', 'financial', 'system', 'grade', 'enrollment') NOT NULL DEFAULT 'academic',
    `period` ENUM('daily', 'weekly', 'monthly', 'yearly', 'custom') NOT NULL DEFAULT 'monthly',
    `status` ENUM('completed', 'generating', 'scheduled', 'failed', 'cancelled') NOT NULL DEFAULT 'generating',
    `file_path` VARCHAR(500) NULL,
    `file_format` VARCHAR(50) NULL DEFAULT 'pdf',
    `generated_by` INT(11) UNSIGNED NULL,
    `scheduled_at` DATETIME NULL,
    `generated_at` DATETIME NULL,
    `download_count` INT(11) UNSIGNED DEFAULT 0,
    `parameters` TEXT NULL COMMENT 'JSON string for report filters/parameters',
    `error_message` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_type` (`type`),
    INDEX `idx_period` (`period`),
    INDEX `idx_status` (`status`),
    INDEX `idx_generated_by` (`generated_by`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Create sections table for semester schedule management
CREATE TABLE IF NOT EXISTS `sections` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT(11) UNSIGNED NOT NULL,
    `section_code` VARCHAR(50) NOT NULL,
    `type` ENUM('lecture', 'lab', 'tutorial') NOT NULL DEFAULT 'lecture',
    `doctor_id` INT(11) UNSIGNED NULL,
    `room` VARCHAR(100) NULL,
    `days` VARCHAR(50) NULL,
    `time` VARCHAR(100) NULL,
    `capacity` INT(4) DEFAULT 30,
    `semester` VARCHAR(50) NULL,
    `notes` TEXT NULL,
    `status` ENUM('active', 'pending', 'closed') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_course_id` (`course_id`),
    INDEX `idx_doctor_id` (`doctor_id`),
    INDEX `idx_semester` (`semester`),
    INDEX `idx_status` (`status`),
    INDEX `idx_type` (`type`),
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Create courses table
CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `course_code` VARCHAR(50) NOT NULL UNIQUE,
    `course_name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `department` VARCHAR(255) NOT NULL,
    `credits` INT(2) DEFAULT 3,
    `level` INT(3) NULL,
    `doctor_id` INT(11) UNSIGNED NULL,
    `max_students` INT(4) DEFAULT 50,
    `status` ENUM('active','inactive','pending','archived') DEFAULT 'active',
    `semester` VARCHAR(50) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_course_code` (`course_code`),
    INDEX `idx_department` (`department`),
    INDEX `idx_doctor_id` (`doctor_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraint separately (only if doctors table exists)
-- ALTER TABLE `courses` ADD CONSTRAINT `fk_courses_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE SET NULL;


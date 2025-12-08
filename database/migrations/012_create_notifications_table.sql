-- Create notifications table for storing student, doctor, and system notifications
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT(11) UNSIGNED NULL,
    `doctor_id` INT(11) UNSIGNED NULL,
    `course_id` INT(11) UNSIGNED NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `notification_type` ENUM('assignment', 'exam', 'announcement', 'grade', 'system', 'schedule', 'reminder') NOT NULL DEFAULT 'announcement',
    `priority` ENUM('normal', 'urgent', 'high', 'low') DEFAULT 'normal',
    `is_read` BOOLEAN DEFAULT FALSE,
    `read_at` TIMESTAMP NULL,
    `related_event_id` INT(11) UNSIGNED NULL COMMENT 'Reference to calendar_events or other related records',
    `created_by` INT(11) UNSIGNED NULL COMMENT 'User ID who created the notification',
    `created_by_role` VARCHAR(50) NULL COMMENT 'Role of the creator (doctor, admin, system)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_student_id` (`student_id`),
    INDEX `idx_doctor_id` (`doctor_id`),
    INDEX `idx_course_id` (`course_id`),
    INDEX `idx_notification_type` (`notification_type`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Create separate tables for each role
-- This migration creates dedicated tables for students, doctors, IT officers, advisors, admins, and default users

-- Update users table to only handle default 'user' role
-- Note: DROP COLUMN IF EXISTS is not supported in MySQL, so we'll handle this manually
-- First, check and drop columns if they exist (run these separately if needed):
-- ALTER TABLE `users` DROP COLUMN `student_number`;
-- ALTER TABLE `users` DROP COLUMN `gpa`;
-- ALTER TABLE `users` DROP COLUMN `major`;
-- ALTER TABLE `users` DROP COLUMN `minor`;
-- ALTER TABLE `users` DROP COLUMN `last_activity`;
-- ALTER TABLE `users` DROP COLUMN `status`;

-- Modify role column to only allow 'user'
ALTER TABLE `users` MODIFY COLUMN `role` ENUM('user') DEFAULT 'user';

-- Add phone field to users table
ALTER TABLE `users`
  ADD COLUMN `phone` VARCHAR(20) NULL AFTER `email`;

-- Create students table
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `student_number` VARCHAR(50) UNIQUE,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NULL,
    `password` VARCHAR(255) NOT NULL,
    `year_enrolled` YEAR NULL,
    `major` VARCHAR(150) NULL,
    `minor` VARCHAR(150) NULL,
    `gpa` DECIMAL(4,2) NULL,
    `transcript` TEXT NULL,
    `status` ENUM('active','inactive','suspended') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_student_number` (`student_number`),
    INDEX `idx_email` (`email`),
    INDEX `idx_major` (`major`),
    INDEX `idx_status` (`status`),
    INDEX `idx_year_enrolled` (`year_enrolled`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create student_courses table (courses taken)
CREATE TABLE IF NOT EXISTS `student_courses` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT(11) UNSIGNED NOT NULL,
    `course_id` INT(11) UNSIGNED NOT NULL,
    `status` ENUM('taken','taking') DEFAULT 'taking',
    `grade` VARCHAR(10) NULL,
    `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_student_id` (`student_id`),
    INDEX `idx_course_id` (`course_id`),
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create student_attendance table
CREATE TABLE IF NOT EXISTS `student_attendance` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT(11) UNSIGNED NOT NULL,
    `course_id` INT(11) UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `status` ENUM('present','absent','late','excused') DEFAULT 'absent',
    `notes` TEXT NULL,
    `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_student_id` (`student_id`),
    INDEX `idx_course_id` (`course_id`),
    INDEX `idx_date` (`date`),
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create doctors table
CREATE TABLE IF NOT EXISTS `doctors` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NULL,
    `password` VARCHAR(255) NOT NULL,
    `employee_id` VARCHAR(50) UNIQUE,
    `department` VARCHAR(255) NOT NULL,
    `bio` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_employee_id` (`employee_id`),
    INDEX `idx_department` (`department`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create doctor_courses table (courses they are teaching)
CREATE TABLE IF NOT EXISTS `doctor_courses` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `doctor_id` INT(11) UNSIGNED NOT NULL,
    `course_id` INT(11) UNSIGNED NOT NULL,
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_doctor_id` (`doctor_id`),
    INDEX `idx_course_id` (`course_id`),
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create it_officers table
CREATE TABLE IF NOT EXISTS `it_officers` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NULL,
    `password` VARCHAR(255) NOT NULL,
    `employee_id` VARCHAR(50) UNIQUE,
    `department` VARCHAR(255) NULL,
    `specialization` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_employee_id` (`employee_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create advisors table
CREATE TABLE IF NOT EXISTS `advisors` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NULL,
    `password` VARCHAR(255) NOT NULL,
    `employee_id` VARCHAR(50) UNIQUE,
    `department` VARCHAR(255) NULL,
    `specialization` VARCHAR(255) NULL,
    `max_students` INT(11) DEFAULT 50,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_employee_id` (`employee_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create advisor_students table (students assigned to advisors)
CREATE TABLE IF NOT EXISTS `advisor_students` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `advisor_id` INT(11) UNSIGNED NOT NULL,
    `student_id` INT(11) UNSIGNED NOT NULL,
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_advisor_id` (`advisor_id`),
    INDEX `idx_student_id` (`student_id`),
    FOREIGN KEY (`advisor_id`) REFERENCES `advisors`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admins table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NULL,
    `password` VARCHAR(255) NOT NULL,
    `employee_id` VARCHAR(50) UNIQUE,
    `department` VARCHAR(255) NULL,
    `permissions` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_employee_id` (`employee_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


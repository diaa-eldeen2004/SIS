-- Add student-specific fields to users table
ALTER TABLE `users`
  ADD COLUMN `student_number` VARCHAR(50) NULL AFTER `id`,
  ADD COLUMN `gpa` DECIMAL(4,2) NULL AFTER `password`,
  ADD COLUMN `major` VARCHAR(150) NULL AFTER `gpa`,
  ADD COLUMN `minor` VARCHAR(150) NULL AFTER `major`,
  ADD COLUMN `last_activity` DATETIME NULL AFTER `updated_at`,
  ADD COLUMN `status` ENUM('active','not_active') NOT NULL DEFAULT 'not_active' AFTER `last_activity`,
  ADD INDEX `idx_student_number` (`student_number`),
  ADD INDEX `idx_major` (`major`),
  ADD INDEX `idx_status` (`status`);

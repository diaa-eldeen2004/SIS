-- Add 'pending' status to student_courses table
-- This migration adds 'pending' to the status ENUM to support enrollment requests

-- First, check if the status column exists and modify it
ALTER TABLE `student_courses` 
MODIFY COLUMN `status` ENUM('pending','taking','taken','approved','rejected') DEFAULT 'pending';

-- If the status column doesn't exist, add it
-- ALTER TABLE `student_courses` 
-- ADD COLUMN `status` ENUM('pending','taking','taken','approved','rejected') DEFAULT 'pending' AFTER `course_id`;


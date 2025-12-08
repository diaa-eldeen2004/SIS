-- Add schedule_group_id to sections table for grouping weekly schedules
ALTER TABLE `sections` 
ADD COLUMN `schedule_group_id` VARCHAR(100) NULL AFTER `id`,
ADD INDEX `idx_schedule_group_id` (`schedule_group_id`);


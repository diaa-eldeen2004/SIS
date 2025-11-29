-- Add password reset columns to users table
ALTER TABLE `users`
ADD COLUMN `password_reset_code` VARCHAR(255) DEFAULT NULL AFTER `password`,
ADD COLUMN `password_reset_expires` DATETIME DEFAULT NULL AFTER `password_reset_code`;

-- Create index for reset code lookups
CREATE INDEX `idx_reset_code` ON `users`(`password_reset_code`);

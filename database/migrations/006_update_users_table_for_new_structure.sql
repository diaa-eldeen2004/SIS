-- Update existing users table to match new structure
-- Add phone column and password reset columns

-- Add phone column (run this manually if column already exists, it will error but that's okay)
ALTER TABLE `users` 
ADD COLUMN `phone` VARCHAR(20) NULL AFTER `email`;

-- Add password reset columns (run manually if columns already exist)
ALTER TABLE `users` 
ADD COLUMN `password_reset_code` VARCHAR(255) DEFAULT NULL AFTER `password`,
ADD COLUMN `password_reset_expires` DATETIME DEFAULT NULL AFTER `password_reset_code`;

-- Create index for reset code
CREATE INDEX `idx_reset_code` ON `users`(`password_reset_code`);

-- Note: If you get "Duplicate column" errors, the columns already exist and you can skip those ALTER TABLE statements


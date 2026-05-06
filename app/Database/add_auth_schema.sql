-- Run this once in phpMyAdmin (or MySQL) on your `corso` database
-- to add auth columns and create refresh_tokens so login/signup work.

-- 1) Add auth columns to users (skip any line if that column already exists)
ALTER TABLE `users` ADD COLUMN `role` VARCHAR(20) DEFAULT 'student';
ALTER TABLE `users` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'active';
ALTER TABLE `users` ADD COLUMN `email_verified` TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE `users` ADD COLUMN `email_verified_at` DATETIME NULL;
ALTER TABLE `users` ADD COLUMN `verification_token` VARCHAR(64) NULL;
ALTER TABLE `users` ADD COLUMN `last_login_at` DATETIME NULL;
ALTER TABLE `users` ADD COLUMN `reset_token` VARCHAR(64) NULL;
ALTER TABLE `users` ADD COLUMN `reset_expires` DATETIME NULL;

-- 2) Create refresh_tokens table if it does not exist
CREATE TABLE IF NOT EXISTS `refresh_tokens` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) Super Admin: role_permissions table (for controlling admin/hr access)
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_slug` VARCHAR(32) NOT NULL,
  `permission_slug` VARCHAR(64) NOT NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_perm` (`role_slug`, `permission_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) Make your first super admin (run after creating a user, replace email with that user's email):
-- UPDATE `users` SET `role` = 'super_admin' WHERE `email` = 'your@email.com';

-- =============================================================================
-- Corso / CI4 — full schema (MySQL / MariaDB, utf8mb4)
-- =============================================================================
-- Generated to match THIS codebase (Models + Services), not only old migrations.
--
-- Usage (phpMyAdmin):
--   1) Select database `corso` from left sidebar (or create it first).
--   2) Open SQL tab, paste this whole file, Go.
--
-- WARNING: Drops existing app tables in `corso` (data loss for those tables).
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Optional: uncomment if database does not exist yet
-- CREATE DATABASE IF NOT EXISTS `corso` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `corso`;

DROP TABLE IF EXISTS
  `certificate_log`,
  `certificate_logs`,
  `quiz_attempt_answers`,
  `quiz_attempts`,
  `quiz_questions`,
  `quizzes`,
  `payments`,
  `enrollments`,
  `certificates`,
  `courses`,
  `categories`,
  `announcements`,
  `role_permissions`,
  `refresh_tokens`,
  `users`;

-- -----------------------------------------------------------------------------
-- users  (AuthController + UserModel + super_admin / hr roles)
-- -----------------------------------------------------------------------------
CREATE TABLE `users` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `password` VARCHAR(255) NULL DEFAULT NULL,
  `password_hash` VARCHAR(255) NULL DEFAULT NULL,
  `role` VARCHAR(32) NULL DEFAULT 'student',
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `email_verified` TINYINT(1) NOT NULL DEFAULT 1,
  `email_verified_at` DATETIME NULL DEFAULT NULL,
  `verification_token` VARCHAR(64) NULL DEFAULT NULL,
  `last_login_at` DATETIME NULL DEFAULT NULL,
  `reset_token` VARCHAR(64) NULL DEFAULT NULL,
  `reset_expires` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- refresh_tokens
-- -----------------------------------------------------------------------------
CREATE TABLE `refresh_tokens` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `refresh_tokens_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- role_permissions
-- -----------------------------------------------------------------------------
CREATE TABLE `role_permissions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_slug` VARCHAR(32) NOT NULL,
  `permission_slug` VARCHAR(64) NOT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_perm` (`role_slug`, `permission_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- announcements
-- -----------------------------------------------------------------------------
CREATE TABLE `announcements` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT NULL DEFAULT NULL,
  `target_roles` VARCHAR(255) NULL DEFAULT NULL,
  `starts_at` DATETIME NULL DEFAULT NULL,
  `ends_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- categories
-- -----------------------------------------------------------------------------
CREATE TABLE `categories` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- courses
-- -----------------------------------------------------------------------------
CREATE TABLE `courses` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT(20) UNSIGNED NOT NULL,
  `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NULL DEFAULT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `thumbnail` VARCHAR(255) NULL DEFAULT NULL,
  `price` DECIMAL(10,2) NULL DEFAULT NULL,
  `level` ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  `quiz_duration_minutes` INT(11) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- enrollments  (EnrollmentService uses pending / completed / cancelled / active)
-- -----------------------------------------------------------------------------
CREATE TABLE `enrollments` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `course_id` BIGINT(20) UNSIGNED NOT NULL,
  `enrolled_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `progress_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `enrollments_user_course` (`user_id`, `course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- payments  (PaymentService + PaymentController enrollment_id)
-- -----------------------------------------------------------------------------
CREATE TABLE `payments` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `course_id` BIGINT(20) UNSIGNED NOT NULL,
  `enrollment_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `transaction_id` VARCHAR(100) NULL DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `paid_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_user_id` (`user_id`),
  KEY `payments_enrollment_id` (`enrollment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- quizzes  (QuizModel allowedFields — after RemoveNegative migration state)
-- -----------------------------------------------------------------------------
CREATE TABLE `quizzes` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` BIGINT(20) UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NULL DEFAULT NULL,
  `total_marks` INT(11) NOT NULL,
  `passing_marks` INT(11) NOT NULL,
  `duration` INT(11) NOT NULL DEFAULT 30,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quizzes_course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- quiz_questions
-- -----------------------------------------------------------------------------
CREATE TABLE `quiz_questions` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quiz_id` BIGINT(20) UNSIGNED NOT NULL,
  `question` TEXT NOT NULL,
  `option_a` VARCHAR(255) NOT NULL,
  `option_b` VARCHAR(255) NOT NULL,
  `option_c` VARCHAR(255) NOT NULL,
  `option_d` VARCHAR(255) NOT NULL,
  `correct_option` ENUM('A','B','C','D') NOT NULL,
  `marks` INT(11) NOT NULL,
  `position` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_questions_quiz_id` (`quiz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- quiz_attempts  (QuizAttemptModel + Admin/Analytics + DashboardService)
-- -----------------------------------------------------------------------------
CREATE TABLE `quiz_attempts` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quiz_id` BIGINT(20) UNSIGNED NOT NULL,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `score` INT(11) NOT NULL DEFAULT 0,
  `passed` TINYINT(1) NOT NULL DEFAULT 0,
  `result` VARCHAR(10) NULL DEFAULT NULL,
  `obtained_marks` INT(11) NOT NULL DEFAULT 0,
  `total_marks` INT(11) NOT NULL DEFAULT 0,
  `attempted_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `best_attempt` TINYINT(1) NOT NULL DEFAULT 0,
  `started_at` DATETIME NULL DEFAULT NULL,
  `total_questions` INT(11) NOT NULL DEFAULT 0,
  `completed_at` DATETIME NULL DEFAULT NULL,
  `time_taken` INT(11) NULL DEFAULT 0,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `quiz_attempts_quiz_user` (`quiz_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- quiz_attempt_answers
-- -----------------------------------------------------------------------------
CREATE TABLE `quiz_attempt_answers` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `attempt_id` BIGINT(20) UNSIGNED NOT NULL,
  `question_id` BIGINT(20) UNSIGNED NOT NULL,
  `selected_option` ENUM('A','B','C','D') NULL DEFAULT NULL,
  `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
  `marks_awarded` INT(11) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `qaa_attempt` (`attempt_id`),
  KEY `qaa_question` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- certificates  (CertificateModel)
-- -----------------------------------------------------------------------------
CREATE TABLE `certificates` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `certificate_number` VARCHAR(100) NOT NULL,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `course_id` BIGINT(20) UNSIGNED NOT NULL,
  `issued_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `certificate_path` VARCHAR(255) NULL DEFAULT NULL,
  `status` ENUM('active','revoked') NOT NULL DEFAULT 'active',
  `revoked_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cert_user_course` (`user_id`, `course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------------------
-- certificate_log  (CertificateLogModel — table name is singular)
-- -----------------------------------------------------------------------------
CREATE TABLE `certificate_log` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `certificate_id` BIGINT(20) UNSIGNED NOT NULL,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `score` INT(11) NOT NULL DEFAULT 0,
  `issued_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `certlog_cert` (`certificate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- After first signup:
-- UPDATE users SET role = 'super_admin' WHERE email = 'you@example.com';

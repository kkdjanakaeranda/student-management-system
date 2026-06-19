-- ============================================================
-- MariaDB/XAMPP Compatible LMS Fixes
-- Safe to run after older installs that do not yet have the
-- user profile columns expected by the current PHP code.
-- ============================================================

ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `display_name` VARCHAR(150) NULL AFTER `username`;

ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `student_id` INT NULL AFTER `display_name`;

ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `teacher_id` INT NULL AFTER `student_id`;

CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NULL,
    `action` VARCHAR(50) NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT NULL,
    `details` TEXT,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE `enrollments`
SET `status` = 'enrolled'
WHERE `status` = 'active';

ALTER TABLE `enrollments`
MODIFY COLUMN `status` ENUM('enrolled','completed','dropped') NOT NULL DEFAULT 'enrolled';

UPDATE `users`
SET `display_name` = `username`
WHERE `display_name` IS NULL;

UPDATE `users`
SET `display_name` = 'Administrator'
WHERE `role` = 'admin';

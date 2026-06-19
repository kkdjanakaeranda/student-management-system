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

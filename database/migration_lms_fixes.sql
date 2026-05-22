
-- ============================================================
-- MariaDB/XAMPP Compatible LMS Fixes
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `users`
ADD COLUMN `display_name` VARCHAR(150) NULL AFTER `username`;

ALTER TABLE `users`
ADD COLUMN `student_id` INT NULL AFTER `display_name`;

ALTER TABLE `users`
ADD COLUMN `teacher_id` INT NULL AFTER `student_id`;

ALTER TABLE `classes`
ADD COLUMN `teacher_id` INT NULL AFTER `section`;

ALTER TABLE `classes`
ADD COLUMN `course_id` INT NULL AFTER `teacher_id`;

ALTER TABLE `subjects`
ADD COLUMN `teacher_id` INT NULL AFTER `class_id`;

ALTER TABLE `exams`
ADD COLUMN `subject_id` INT NULL AFTER `class_id`;

ALTER TABLE `classes`
ADD CONSTRAINT `fk_classes_teacher_new`
FOREIGN KEY (`teacher_id`)
REFERENCES `teachers`(`id`)
ON DELETE SET NULL;

ALTER TABLE `classes`
ADD CONSTRAINT `fk_classes_course_new`
FOREIGN KEY (`course_id`)
REFERENCES `courses`(`id`)
ON DELETE SET NULL;

ALTER TABLE `subjects`
ADD CONSTRAINT `fk_subjects_teacher_new`
FOREIGN KEY (`teacher_id`)
REFERENCES `teachers`(`id`)
ON DELETE SET NULL;

ALTER TABLE `exams`
ADD CONSTRAINT `fk_exams_subject_new`
FOREIGN KEY (`subject_id`)
REFERENCES `subjects`(`id`)
ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS `csrf_tokens` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `session_id` VARCHAR(128) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `enrollments`
ADD COLUMN `enrolled_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `enrollments`
ADD COLUMN `status`
ENUM('active','dropped','completed')
NOT NULL DEFAULT 'active';

CREATE INDEX `idx_enroll_student_class`
ON `enrollments` (`student_id`, `class_id`, `status`);

UPDATE `users`
SET `display_name` = `username`
WHERE `display_name` IS NULL;

UPDATE `users`
SET `display_name` = 'Administrator'
WHERE `role` = 'admin';

SET FOREIGN_KEY_CHECKS=1;

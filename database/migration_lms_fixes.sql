-- ============================================================
-- LMS FIXES — migration_lms_fixes.sql
-- Run once against your student_management_system database.
-- Safe to run on the existing schema — uses IF NOT EXISTS
-- and ALTER TABLE ADD COLUMN IF NOT EXISTS (MySQL 8) guards.
-- For MySQL 5.7 the conditional guards are replaced with plain
-- ALTER TABLE — check column existence first if re-running.
-- ============================================================

-- ------------------------------------------------------------
-- 1. Add display_name to users so dashboards show real names
-- ------------------------------------------------------------
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `display_name` VARCHAR(150) NULL AFTER `username`,
    ADD COLUMN IF NOT EXISTS `student_id`   INT          NULL AFTER `display_name`,  -- link user → students row
    ADD COLUMN IF NOT EXISTS `teacher_id`   INT          NULL AFTER `student_id`;    -- link user → teachers row

-- ------------------------------------------------------------
-- 2. Link teachers to classes (teacher ownership)
-- ------------------------------------------------------------
ALTER TABLE `classes`
    ADD COLUMN IF NOT EXISTS `teacher_id` INT NULL AFTER `section`;

ALTER TABLE `classes`
    ADD CONSTRAINT IF NOT EXISTS `fk_classes_teacher`
    FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE SET NULL;

-- ------------------------------------------------------------
-- 3. Link subjects to teachers (who teaches the subject)
-- ------------------------------------------------------------
ALTER TABLE `subjects`
    ADD COLUMN IF NOT EXISTS `teacher_id` INT NULL AFTER `class_id`;

ALTER TABLE `subjects`
    ADD CONSTRAINT IF NOT EXISTS `fk_subjects_teacher`
    FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE SET NULL;

-- ------------------------------------------------------------
-- 4. Link exams to subjects (so grades are per-subject)
-- ------------------------------------------------------------
ALTER TABLE `exams`
    ADD COLUMN IF NOT EXISTS `subject_id` INT NULL AFTER `class_id`;

ALTER TABLE `exams`
    ADD CONSTRAINT IF NOT EXISTS `fk_exams_subject`
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE SET NULL;

-- ------------------------------------------------------------
-- 5. Link courses to classes (which class runs which course)
-- ------------------------------------------------------------
ALTER TABLE `classes`
    ADD COLUMN IF NOT EXISTS `course_id` INT NULL AFTER `teacher_id`;

ALTER TABLE `classes`
    ADD CONSTRAINT IF NOT EXISTS `fk_classes_course`
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL;

-- ------------------------------------------------------------
-- 6. Add CSRF token table (session-based, auto-expires)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `csrf_tokens` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `session_id` VARCHAR(128) NOT NULL,
    `token`      VARCHAR(64)  NOT NULL,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_session` (`session_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Auto-clean tokens older than 2 hours (run via cron or on login)
-- DELETE FROM csrf_tokens WHERE created_at < NOW() - INTERVAL 2 HOUR;

-- ------------------------------------------------------------
-- 7. Ensure enrollments has the right columns
--    (original schema had basic structure; make it explicit)
-- ------------------------------------------------------------
ALTER TABLE `enrollments`
    ADD COLUMN IF NOT EXISTS `enrolled_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS `status`      ENUM('active','dropped','completed') NOT NULL DEFAULT 'active';

-- Index used by attendance enrollment check
CREATE INDEX IF NOT EXISTS `idx_enroll_student_class`
    ON `enrollments` (`student_id`, `class_id`, `status`);

-- ------------------------------------------------------------
-- 8. Backfill display_name from existing users
-- ------------------------------------------------------------
UPDATE `users` SET `display_name` = `username` WHERE `display_name` IS NULL;

-- Link existing admin user to display name
UPDATE `users` SET `display_name` = 'Administrator' WHERE `role` = 'admin' AND `display_name` = `username`;

-- ============================================================
-- Done. Verify with:
--   DESCRIBE classes;
--   DESCRIBE subjects;
--   DESCRIBE exams;
--   DESCRIBE users;
--   SHOW INDEX FROM enrollments;
-- ============================================================

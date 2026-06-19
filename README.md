# Student Management System

A PHP and MySQL student management application for a local XAMPP-style environment. It manages students, teachers, courses, classes, subjects, attendance, exams, grades, and announcements with role-based access for admins, teachers, and students.

## Current Modules

### Login and Dashboard
- Session-based login and logout.
- Role-aware dashboard for admin, teacher, and student users.
- Admin dashboard shows active students, teachers, classes, and courses.
- Teacher dashboard shows assigned classes, subjects, students, upcoming exams, and pending attendance.
- Student dashboard shows enrolled classes, average grade, recent attendance percentage, upcoming exams, and quick links to personal pages.

### Students
- Admins can add, edit, view, and deactivate students.
- Teachers can view students assigned through their classes.
- Students can access personal attendance, grade, and timetable pages.
- Student detail pages include profile information, guardian details, admission data, and enrollments.
- Admins can enroll students into active classes from the student detail page.

### Teachers
- Admin-only module.
- Admins can add, edit, view, and deactivate teachers.
- Teacher records include profile, contact, qualification, specialization, joining date, and status.

### Courses, Classes, and Subjects
- Admin-only management pages.
- Courses include code, name, credits, duration, description, and status.
- Classes link courses, teachers, academic year, room number, section, and status.
- Subjects link a class and teacher with subject code, name, and description.

### Attendance
- Admins and teachers can mark attendance by class and date.
- Attendance statuses: `present`, `absent`, `late`, and `excused`.
- Teachers are scoped to their assigned classes.
- Students have a personal attendance summary and recent attendance records page.
- The main attendance listing shows today's attendance records, scoped by role.

### Exams and Grades
- Admins and teachers can create and edit exams.
- Exams are scoped by assigned classes for teachers and by enrolled classes for students.
- Admins and teachers can enter grades.
- Students can view their own grades and average percentage.

### Announcements
- Admins and teachers can create announcements.
- Admins can delete announcements.
- Authors and admins can edit announcements.
- Announcements can target all users, students, or teachers.
- Announcements support low, medium, and high priority.

## Technology Stack

- PHP 7.4+
- MySQL or MariaDB
- Apache through XAMPP or a similar PHP web server
- HTML, CSS, and JavaScript
- PDO database access
- Custom CSS in `assets/css/style.css`

## Requirements

- XAMPP, WAMP, MAMP, or an equivalent local PHP/MySQL stack
- PHP 7.4 or newer
- MySQL 5.7+ or MariaDB
- A modern browser such as Chrome, Edge, Firefox, or Safari

## Installation

1. Put the project in your web server root.

   For XAMPP on Windows:

   ```text
   C:\xampp\htdocs\student-management-system
   ```

2. Start Apache and MySQL.

3. Import a database file.

   Base schema:

   ```bash
   mysql -u root -p < database/schema.sql
   ```

   Sample/mock data:

   ```bash
   mysql -u root -p student_management_system < database/student_management_system_with_mock_data.sql
   ```

4. For an older database, run the compatibility migration:

   ```bash
   mysql -u root -p student_management_system < database/migration_lms_fixes.sql
   ```

5. Check `config/config.php`.

   Current defaults:

   ```php
   define('SITE_NAME', 'Student Management System');
   define('BASE_URL', 'http://localhost/student-management-system/');
   ```

   Current database settings inside `config/config.php`:

   ```text
   host: localhost
   database: student_management_system
   username: root
   password: empty
   ```

6. Open the app:

   ```text
   http://localhost/student-management-system/
   ```

## Verified Seed Login Notes

The seed data currently contains different password hashes depending on which SQL file you import.

### `database/schema.sql`

The inserted admin user is:

```text
username: admin
password: password
role: admin
```

Note: the SQL comment says `admin123`, but the actual hash in `schema.sql` verifies as `password`.

### `database/student_management_system_with_mock_data.sql`

Verified examples from the mock data:

```text
username: admin
password: admin123
role: admin

username: admin1
password: admin1
role: admin

username: teacher1
password: teacher1
role: teacher

username: student1
password: admin1
role: student
```

Several mock users use `admin1` as the password.

## Project Structure

```text
student-management-system/
|-- announcements/
|   |-- add.php
|   |-- edit.php
|   `-- index.php
|-- assets/
|   |-- css/
|   |   |-- style.css
|   |   `-- style.css.backup
|   |-- images/
|   |   |-- create-default-avatar.php
|   |   `-- default-avatar.svg
|   `-- js/
|       `-- main.js
|-- attendance/
|   |-- index.php
|   `-- mark.php
|-- classes/
|   |-- add.php
|   |-- delete.php
|   |-- edit.php
|   `-- index.php
|-- config/
|   |-- config.php
|   `-- database.php
|-- courses/
|   |-- add.php
|   |-- delete.php
|   |-- edit.php
|   |-- index.php
|   `-- view.php
|-- database/
|   |-- migration_lms_fixes.sql
|   |-- schema.sql
|   `-- student_management_system_with_mock_data.sql
|-- exams/
|   |-- add.php
|   |-- edit.php
|   `-- index.php
|-- grades/
|   |-- add.php
|   `-- index.php
|-- includes/
|   |-- header.php
|   `-- sidebar.php
|-- students/
|   |-- add.php
|   |-- delete.php
|   |-- edit.php
|   |-- index.php
|   |-- my_attendance.php
|   |-- my_grades.php
|   |-- my_timetable.php
|   `-- view.php
|-- subjects/
|   |-- add.php
|   |-- delete.php
|   |-- edit.php
|   `-- index.php
|-- teachers/
|   |-- add.php
|   |-- delete.php
|   |-- edit.php
|   |-- index.php
|   `-- view.php
|-- uploads/
|   |-- students/
|   `-- teachers/
|-- dashboard.php
|-- index.php
|-- login.php
|-- logout.php
`-- README.md
```

## Database Tables

- `users` - login accounts, roles, display names, and linked student/teacher IDs
- `students` - student profile and guardian details
- `teachers` - teacher profile and professional details
- `courses` - course catalog
- `classes` - class sections linked to courses and teachers
- `enrollments` - student-to-class enrollment records
- `subjects` - class subjects linked to teachers
- `attendance` - daily attendance records
- `exams` - exam schedules and marks setup
- `grades` - student exam results
- `announcements` - system announcements

## Role Access Summary

### Admin
- Dashboard
- Students
- Teachers
- Classes
- Courses
- Subjects
- Attendance
- Exams
- Grades
- Announcements

### Teacher
- Dashboard
- Assigned students
- Attendance for assigned classes
- Exams for assigned classes
- Grades for assigned classes
- Announcements

### Student
- Dashboard
- Personal attendance page
- Personal grades page
- Personal timetable page
- Announcements

The sidebar currently shows students only the dashboard and announcements links. Student-specific attendance, grades, and timetable pages are linked from the student dashboard.

## Security and Data Handling

- Uses PDO prepared statements for database queries.
- Uses `password_verify()` for login.
- Uses session regeneration on successful login.
- Uses CSRF tokens for protected forms.
- Uses role-check helpers such as `requireAdmin()`, `requireTeacher()`, and `requireStudent()`.
- Uses output escaping through `e()` and `htmlspecialchars()`.
- Photo uploads are checked by MIME type and limited to 2 MB in `handlePhotoUpload()`.
- Delete actions generally deactivate records instead of removing business data permanently.

## Important Development Notes

- Current pages load `config/config.php`, which contains app settings, database connection, auth helpers, CSRF helpers, and upload helpers.
- `config/database.php` still exists as an older standalone database connection class, but the active pages use `config/config.php`.
- Shared layout files are `includes/header.php` and `includes/sidebar.php`.
- Main styling is in `assets/css/style.css`.
- Main JavaScript is in `assets/js/main.js`.
- The app assumes the `BASE_URL` folder name is `student-management-system`.
- If you rename the folder, update `BASE_URL` in `config/config.php`.

## Common Troubleshooting

### Login fails
- Confirm which SQL file was imported.
- Use the verified seed credentials above.
- Confirm `config/config.php` points to the correct database.
- If necessary, reset a password using PHP's `password_hash()` and update the `users` table.

### Page redirects to dashboard
- The logged-in role probably does not have access to that page.
- Admin-only pages use `requireAdmin()`.
- Student personal pages require the `student` role.

### CSS or layout looks old
- Hard refresh with `Ctrl + F5`.
- Confirm `BASE_URL` is correct.
- Confirm `assets/css/style.css` is the file being loaded.

### Blank page or PHP error
- Enable PHP error reporting locally.
- Check the Apache/PHP error log.
- Confirm the database schema matches the current PHP files.
- Run `database/migration_lms_fixes.sql` if the database was created before the current user-linking fields were added.

### Image uploads fail
- Confirm `uploads/students/` and `uploads/teachers/` exist.
- Confirm Apache can write to the `uploads/` directory.
- Use JPEG, PNG, GIF, or WebP files under 2 MB.

## Known Gaps and Future Improvements

- Password reset workflow
- PDF reports
- CSV export
- Email notifications
- Parent portal
- Fee management
- Timetable management UI for admins
- Stronger audit logging
- Automated tests
- Cleaner removal of old backup or legacy files

## Author

GitHub: [@kkdjanakaeranda](https://github.com/kkdjanakaeranda)

# Student Management System

A PHP and MySQL based Student Management System for managing students, teachers, classes, courses, attendance, exams, grades, and announcements. The project is designed for a local XAMPP-style environment and uses a simple role-based dashboard for admins, teachers, and students.

## Features

### Authentication and Roles
- Login and logout with PHP sessions
- Role-based access for admin, teacher, and student users
- CSRF protection on form submissions
- Password verification with PHP password hashes
- Access helpers for student-owned and teacher-assigned records

### Dashboard
- Role-aware dashboard after login
- Quick access navigation through the shared sidebar
- Responsive layout for desktop and smaller screens

### Student Management
- Add, edit, view, and deactivate student records
- Student profile details with photo support
- Guardian information
- Class enrollment tracking
- Student self-service pages for attendance, grades, and timetable

### Teacher Management
- Add, edit, view, and deactivate teacher records
- Teacher profile details with photo support
- Qualification, specialization, joining date, and contact information

### Academic Management
- Manage courses with code, name, credits, duration, description, and status
- Manage classes with course, teacher, academic year, room, and section
- Manage subjects assigned to classes and teachers
- View individual course details

### Attendance
- View attendance records
- Mark attendance by class and date
- Supports present, absent, late, and excused statuses
- Teachers can mark attendance for assigned classes
- Admins can manage attendance across classes
- CSV export support from the attendance listing

### Exams and Grades
- Create and manage exams
- Enter student grades
- Track marks, grade values, and remarks
- CSV export support from the grades listing

### Announcements
- Create, edit, and view announcements
- Target announcements to all users, students, or teachers
- Priority levels for low, medium, and high importance

### User Interface
- Shared header and sidebar layout
- Responsive tables and cards
- Detail pages for students, teachers, and courses
- Profile summary cards for student and teacher views
- Consistent form, button, badge, alert, and table styling

## Technology Stack

- PHP 7.4+
- MySQL or MariaDB
- Apache through XAMPP or another PHP web server
- HTML, CSS, and JavaScript
- PDO for database access

## Requirements

- XAMPP, WAMP, MAMP, or equivalent PHP/MySQL stack
- PHP 7.4 or newer
- MySQL 5.7+ or MariaDB
- A modern browser such as Chrome, Edge, Firefox, or Safari

## Installation

1. Place the project folder inside your web server root.

   For XAMPP on Windows, the expected path is:

   ```text
   C:\xampp\htdocs\student-management-system
   ```

2. Start Apache and MySQL from the XAMPP Control Panel.

3. Create and import the database.

   Option A: base schema only:

   ```bash
   mysql -u root -p < database/schema.sql
   ```

   Option B: schema with sample/mock data:

   ```bash
   mysql -u root -p student_management_system < database/student_management_system_with_mock_data.sql
   ```

4. If you are updating an older install, run the migration file:

   ```bash
   mysql -u root -p student_management_system < database/migration_lms_fixes.sql
   ```

5. Check the database settings in `config/config.php`.

   Current default settings:

   ```php
   define('SITE_NAME', 'Student Management System');
   define('BASE_URL', 'http://localhost/student-management-system/');
   ```

   Database connection defaults:

   ```php
   host: localhost
   database: student_management_system
   username: root
   password: empty
   ```

6. Open the app in your browser:

   ```text
   http://localhost/student-management-system/
   ```

## Login Notes

The base `database/schema.sql` creates an admin user named `admin`. The SQL comment lists the intended password as `admin123`.

The mock-data SQL file includes additional users such as `admin1`, `teacher1`, and `student1`. If login fails after importing sample data, check the hashed passwords in the imported SQL or reset the password using PHP's `password_hash()`.

## Project Structure

```text
student-management-system/
|-- announcements/
|   |-- add.php
|   |-- edit.php
|   `-- index.php
|-- assets/
|   |-- css/style.css
|   |-- images/
|   `-- js/main.js
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

## Main Database Tables

- `users` - login accounts, roles, display names, and linked profile IDs
- `students` - student profile and guardian details
- `teachers` - teacher profile and professional details
- `courses` - course catalog
- `classes` - class sections linked to courses and teachers
- `enrollments` - student class enrollment records
- `subjects` - class subjects linked to teachers
- `attendance` - daily attendance records
- `exams` - exam schedules and marks configuration
- `grades` - student exam results
- `announcements` - system announcements

## Role Permissions

### Admin
- Full access to students, teachers, classes, courses, subjects, exams, grades, attendance, and announcements
- Can enroll students in classes
- Can deactivate students, teachers, classes, courses, and subjects

### Teacher
- Can view students assigned through their classes
- Can mark attendance for assigned classes
- Can work with grade and announcement modules where allowed by the page logic

### Student
- Can view their own profile-related information
- Can access personal attendance, grades, timetable, and announcements

## Security Notes

- Uses PDO prepared statements for database queries
- Uses `password_verify()` for login
- Uses CSRF tokens for protected forms
- Uses role-check helpers such as `requireAdmin()`, `requireTeacher()`, and `requireStudent()`
- Uses HTML escaping through `e()` and `htmlspecialchars()`
- Validates photo uploads by MIME type and size

## Common Troubleshooting

### Login fails
- Confirm the database was imported successfully.
- Confirm `config/config.php` points to the correct database.
- If using mock data, reset the user password if you are unsure of the imported hash.

### CSS or layout looks old
- Hard refresh the browser with `Ctrl + F5`.
- Confirm `assets/css/style.css` is loading from the correct `BASE_URL`.

### Blank page or PHP error
- Enable PHP error reporting in your local environment.
- Check the Apache/PHP error log.
- Confirm the database schema matches the current PHP files.
- Run `database/migration_lms_fixes.sql` if this is an older database.

### Image uploads fail
- Confirm `uploads/students/` and `uploads/teachers/` exist.
- Confirm the web server can write to the `uploads/` directory.
- Use supported image types: JPEG, PNG, GIF, or WebP.

## Development Notes

- Shared UI styles live in `assets/css/style.css`.
- Shared navigation lives in `includes/sidebar.php`.
- Shared page header and user controls live in `includes/header.php`.
- Main auth, database, CSRF, and helper functions live in `config/config.php`.
- Older `config/database.php` exists, but current pages load `config/config.php`.

## Future Improvements

- PDF reports
- Email notifications
- Parent portal
- Fee management
- Timetable management UI
- Stronger audit logging
- More automated tests
- Cleaner password reset workflow

## Author

Project repository: `student-management-system`

GitHub: [@kkdjanakaeranda](https://github.com/kkdjanakaeranda)

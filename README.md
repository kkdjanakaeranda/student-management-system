# 🎓 Student Management System

A complete, modern, and beautiful Student Management System built with PHP, MySQL, HTML, CSS, and JavaScript.

![Version](https://img.shields.io/badge/version-1.0. 0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange. svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## ✨ Features

### 🔐 Authentication & Authorization
- User login/logout system
- Role-based access control (Admin, Teacher, Student)
- Secure password hashing

### 👨‍🎓 Student Management
- Add, edit, view, and delete students
- Student profile with photo upload
- Guardian information
- Student enrollment tracking
- Status management (Active, Inactive, Graduated)

### 👨‍🏫 Teacher Management
- Complete teacher profiles
- Qualification and specialization tracking
- Photo upload capability
- Active/Inactive status

### 📚 Academic Management
- **Classes**:  Create and manage classes with sections
- **Courses**: Course catalog with credits and duration
- **Subjects**: Subject assignment to classes and teachers
- **Enrollments**: Student class enrollment tracking

### ✅ Attendance System
- Daily attendance marking
- Multiple status options (Present, Absent, Late, Excused)
- Class-wise attendance tracking
- Date-based attendance reports

### 📋 Examination & Grading
- Exam scheduling (Midterm, Final, Quiz, Assignment)
- Grade entry and calculation
- Automatic grade assignment based on percentage
- Performance tracking

### 📢 Announcements
- Post announcements with priority levels
- Target-specific audience (All, Students, Teachers)
- Edit and manage announcements

### 🎨 Modern UI/UX
- Beautiful gradient design
- Responsive layout for all devices
- Smooth animations and transitions
- Icon-based navigation
- Empty state designs
- Loading indicators

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser (Chrome, Firefox, Safari, Edge)

### Step-by-Step Installation

1. **Download/Clone the Project**
   ```bash
   git clone https://github.com/kkdjanakaeranda/student-management-system.git
   cd student-management-system
   ```

2. **Create Database**
   - Open phpMyAdmin or MySQL command line
   - Create a new database: 
     ```sql
     CREATE DATABASE student_management_system;
     ```
   - Import the schema: 
     ```bash
     mysql -u root -p student_management_system < database/schema.sql
     ```

3. **Configure Database Connection**
   - Open `config/database.php`
   - Update your database credentials:
     ```php
     private $host = "localhost";
     private $db_name = "student_management_system";
     private $username = "root";  // Your MySQL username
     private $password = "";      // Your MySQL password
     ```

4. **Set File Permissions**
   ```bash
   chmod -R 755 uploads/
   chmod -R 755 uploads/students/
   chmod -R 755 uploads/teachers/
   ```

5. **Access the Application**
   - Open your browser
   - Navigate to:  `http://localhost/student-management-system/`

6. **Login**
   - **Username**: `admin`
   - **Password**: `admin123`
  
   Default password for all users:
      ```text
         admin1
      ```


## 📁 Project Structure

```
student-management-system/
│
├── assets/
│   ├── css/
│   │   └── style.css          # Complete styling
│   ├── js/
│   │   └── main.js            # JavaScript functionality
│   └── images/
│       └── default-avatar.png # Default user avatar
│
├── config/
│   ├── config.php             # Application configuration
│   └── database.php           # Database connection
│
├── database/
│   └── schema.sql             # Database schema
│
├── includes/
│   ├── header.php             # Common header
│   └── sidebar.php            # Navigation sidebar
│
├── students/
│   ├── index.php              # List students
│   ├── add.php                # Add student
│   ├── edit.php               # Edit student
│   ├── view.php               # View student details
│   └── delete.php             # Delete student
│
├── teachers/
│   ├── index.php              # List teachers
│   ├── add.php                # Add teacher
│   ├── edit.php               # Edit teacher
│   ├── view.php               # View teacher details
│   └── delete.php             # Delete teacher
│
├── classes/
│   ├── index.php              # List classes
│   ├── add.php                # Add class
│   ├── edit.php               # Edit class
│   └── delete.php             # Delete class
│
├── courses/
│   ├── index.php              # List courses
│   ├── add.php                # Add course
│   ├── edit.php               # Edit course
│   └── delete.php             # Delete course
│
├── subjects/
│   ├── index.php              # List subjects
│   ├── add. php                # Add subject
│   ├── edit.php               # Edit subject
│   └── delete.php             # Delete subject
│
├── attendance/
│   ├── index.php              # View attendance
│   └── mark.php               # Mark attendance
│
├── exams/
│   ├── index.php              # List exams
│   └── add.php                # Add exam
│
├── grades/
│   ├── index.php              # View grades
│   └── add.php                # Add grades
│
├── announcements/
│   ├── index. php              # View announcements
│   ├── add.php                # Add announcement
│   └── edit. php               # Edit announcement
│
├── uploads/
│   ├── students/              # Student photos
│   └── teachers/              # Teacher photos
│
├── index.php                  # Entry point
├── login.php                  # Login page
├── logout.php                 # Logout handler
├── dashboard.php              # Main dashboard
└── README.md                  # Documentation
```

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- PDO prepared statements to prevent SQL injection
- Session-based authentication
- Role-based access control
- Input validation and sanitization
- XSS protection with `htmlspecialchars()`
- File upload validation

## 🎯 User Roles & Permissions

### Admin
- Full access to all modules
- Manage students, teachers, classes, courses, subjects
- Mark attendance and enter grades
- Post and manage announcements
- View all reports

### Teacher
- View students
- Mark attendance for assigned classes
- Enter grades for exams
- Post announcements
- Limited administrative access

### Student
- View personal dashboard
- View announcements
- View grades and attendance (if implemented)

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Design**: Custom CSS with modern gradients
- **Architecture**: MVC-inspired structure
- **Security**: PDO, Password Hashing, Session Management

## 📱 Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Opera (latest)

## 🔧 Configuration Options

### Database Settings
Edit `config/database.php`:
```php
private $host = "localhost";      // Database host
private $db_name = "your_db_name"; // Database name
private $username = "your_username"; // MySQL username
private $password = "your_password"; // MySQL password
```

### Site Settings
Edit `config/config.php`:
```php
define('SITE_NAME', 'Your School Name');
define('BASE_URL', 'http://localhost/your-folder/');
```

## 📊 Database Schema

### Main Tables
- **users** - User authentication
- **students** - Student information
- **teachers** - Teacher information
- **classes** - Class management
- **courses** - Course catalog
- **subjects** - Subject details
- **enrollments** - Student enrollments
- **attendance** - Attendance records
- **exams** - Examination schedule
- **grades** - Student grades
- **announcements** - System announcements

## 🐛 Troubleshooting

### Common Issues

**Issue**: Login not working
- **Solution**: Check database connection in `config/database.php`
- Verify default admin user exists in database

**Issue**: Photos not uploading
- **Solution**: Check folder permissions (chmod 755 or 777)
- Verify `uploads/` directory exists

**Issue**: Blank page after form submission
- **Solution**: Enable error reporting in PHP
- Check PHP error logs
- Verify database table structure

**Issue**: CSS/JS not loading
- **Solution**: Check `BASE_URL` in `config/config.php`
- Verify file paths are correct

## 🚀 Future Enhancements

- [ ] Email notifications
- [ ] PDF report generation
- [ ] Student/Parent portal
- [ ] Fee management system
- [ ] Library management
- [ ] Hostel management
- [ ] Transport management
- [ ] Online examination module
- [ ] Chat/messaging system
- [ ] Mobile app

## 📝 License

This project is licensed under the MIT License. 

## 👨‍💻 Author

**Your Name**
- GitHub: [@kkdjanakaeranda](https://github.com/kkdjanakaeranda)
- Email: kkdjanakaeranda@gmail.com

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! 

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 💖 Support

Give a ⭐️ if this project helped you! 

## 📞 Contact

For support or queries: 
- Create an issue on GitHub
- Email: support@example.com

---

**Made with ❤️ for education**

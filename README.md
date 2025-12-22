# 🎓 Student Management System

A complete, modern, secure, and production-ready Student Management System built with PHP, MySQL, HTML, CSS, and JavaScript.

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Security](https://img.shields.io/badge/security-enhanced-green.svg)
![Docker](https://img.shields.io/badge/docker-ready-blue.svg)

> 🎯 **New in v2.0**: Enhanced security features, Docker support, CI/CD pipeline, comprehensive documentation

## 📑 Table of Contents

- [Features](#-features)
- [Security Features](#-security-features)
- [Quick Start](#-quick-start)
- [Installation](#-installation)
- [Docker Deployment](#-docker-deployment)
- [Configuration](#-configuration)
- [Documentation](#-documentation)
- [Screenshots](#-screenshots)
- [Contributing](#-contributing)
- [License](#-license)

## ✨ Features

### 🔐 Authentication & Authorization
- ✅ User login/logout system with rate limiting
- ✅ Role-based access control (Admin, Teacher, Student)
- ✅ Secure password hashing (bcrypt)
- ✅ Session security (HTTPOnly, SameSite cookies)
- ✅ CSRF protection on all forms
- ✅ Login attempt rate limiting

### 👨‍🎓 Student Management
- ✅ Add, edit, view, and delete students
- ✅ Student profile with photo upload
- ✅ Guardian information
- ✅ Student enrollment tracking
- ✅ Status management (Active, Inactive, Graduated)
- ✅ Secure file upload with validation

### 👨‍🏫 Teacher Management
- ✅ Complete teacher profiles
- ✅ Qualification and specialization tracking
- ✅ Photo upload capability
- ✅ Active/Inactive status
- ✅ Teacher assignment to subjects

### 📚 Academic Management
- ✅ **Classes**: Create and manage classes with sections
- ✅ **Courses**: Course catalog with credits and duration
- ✅ **Subjects**: Subject assignment to classes and teachers
- ✅ **Enrollments**: Student class enrollment tracking

### ✅ Attendance System
- ✅ Daily attendance marking
- ✅ Multiple status options (Present, Absent, Late, Excused)
- ✅ Class-wise attendance tracking
- ✅ Date-based attendance reports
- ✅ Bulk attendance operations

### 📋 Examination & Grading
- ✅ Exam scheduling (Midterm, Final, Quiz, Assignment)
- ✅ Grade entry and editing
- ✅ Automatic grade calculation based on percentage
- ✅ Performance tracking
- ✅ Exam deletion with dependency checks

### 📢 Announcements
- ✅ Post announcements with priority levels
- ✅ Target-specific audience (All, Students, Teachers)
- ✅ Edit and delete announcements
- ✅ Date and user tracking

### 🎨 Modern UI/UX
- ✅ Beautiful gradient design
- ✅ Fully responsive layout for all devices
- ✅ Smooth animations and transitions
- ✅ Icon-based navigation
- ✅ Empty state designs
- ✅ Loading indicators
- ✅ Toast notifications
- ✅ Form validation with visual feedback

## 🔒 Security Features

### Comprehensive Security Implementation

- ✅ **CSRF Protection**: All forms protected with CSRF tokens
- ✅ **SQL Injection Prevention**: PDO prepared statements throughout
- ✅ **XSS Protection**: Input sanitization and output escaping
- ✅ **Session Security**: Secure session configuration with regeneration
- ✅ **Password Security**: bcrypt hashing with strength validation
- ✅ **Rate Limiting**: Login attempt limiting to prevent brute force
- ✅ **Input Validation**: Comprehensive validation for all inputs
- ✅ **File Upload Security**: MIME type, size, and extension validation
- ✅ **Security Headers**: X-Frame-Options, CSP, X-XSS-Protection, etc.
- ✅ **Environment Configuration**: Sensitive data in .env files
- ✅ **Error Handling**: Secure error messages in production
- ✅ **Access Control**: Role-based authorization

📖 For detailed security documentation, see [SECURITY.md](SECURITY.md)

## 🚀 Quick Start

### Option 1: Docker (Recommended)

Get started in 3 commands:

```bash
git clone https://github.com/yourusername/student-management-system.git
cd student-management-system
cp .env.example .env
docker-compose up -d
```

Then access:
- **Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **Login**: admin / admin123

### Option 2: Manual Installation

See [INSTALLATION.md](INSTALLATION.md) for detailed instructions.

## 📦 Installation

### Prerequisites

**Required:**
- PHP 7.4+ (8.0+ recommended)
- MySQL 5.7+ or MariaDB 10.3+
- Apache 2.4+ or Nginx 1.18+
- Composer (optional, for dependencies)

**PHP Extensions:**
- pdo, pdo_mysql
- mbstring
- gd (for image processing)
- fileinfo
- json

### Quick Setup

1. **Clone Repository**
   ```bash
   git clone https://github.com/yourusername/student-management-system.git
   cd student-management-system
   ```

2. **Configure Environment**
   ```bash
   cp .env.example .env
   nano .env  # Edit with your settings
   ```

3. **Create Database**
   ```bash
   mysql -u root -p
   ```
   ```sql
   CREATE DATABASE student_management_system;
   EXIT;
   ```
   ```bash
   mysql -u root -p student_management_system < database/schema.sql
   ```

4. **Set Permissions**
   ```bash
   chmod -R 755 uploads/ logs/
   chmod 640 .env
   ```

5. **Access Application**
   Navigate to: `http://localhost/student-management-system/`

📖 For complete installation guide, see [INSTALLATION.md](INSTALLATION.md)

## 🐳 Docker Deployment

### Using Docker Compose

The application includes a complete Docker setup with PHP, MySQL, and phpMyAdmin.

**Start All Services:**
```bash
docker-compose up -d
```

**View Logs:**
```bash
docker-compose logs -f web
```

**Stop Services:**
```bash
docker-compose down
```

**Rebuild After Changes:**
```bash
docker-compose up -d --build
```

### Access Points

- **Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Server: db
  - Username: root
  - Password: rootpassword

### Docker Configuration

The `docker-compose.yml` includes:
- PHP 8.1 with Apache
- MySQL 8.0
- phpMyAdmin
- Persistent data volumes
- Network configuration
- Environment variables

## ⚙️ Configuration

### Environment Variables

All configuration is managed through `.env` file:

```env
# Database
DB_HOST=localhost
DB_NAME=student_management_system
DB_USER=root
DB_PASS=

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost/sms/
SITE_NAME="Student Management System"

# Security
SESSION_LIFETIME=7200
CSRF_TOKEN_EXPIRE=3600
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_TIME=900
```

### Production Recommendations

1. **Enable HTTPS**
   ```env
   APP_URL=https://yourdomain.com/
   SESSION_SECURE=true
   ```

2. **Disable Debug Mode**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

3. **Strong Database Password**
   ```env
   DB_PASS=your_very_strong_password_here
   ```

4. **Secure File Permissions**
   ```bash
   chmod 640 .env
   chmod 755 uploads/ logs/
   ```

## 📚 Documentation

Comprehensive documentation is available:

- **[INSTALLATION.md](INSTALLATION.md)** - Complete installation guide
  - System requirements
  - Step-by-step installation
  - Docker setup
  - Troubleshooting
  - Performance optimization

- **[SECURITY.md](SECURITY.md)** - Security features documentation
  - Implemented security features
  - Security best practices
  - Configuration guidelines
  - Security audit checklist

- **[CONTRIBUTING.md](CONTRIBUTING.md)** - Contributing guidelines (coming soon)

## 📁 Project Structure
   chmod -R 755 uploads/students/
   chmod -R 755 uploads/teachers/
   ```

5. **Access the Application**
   - Open your browser
   - Navigate to:  `http://localhost/student-management-system/`

6. **Login**
   - **Username**: `admin`
   - **Password**: `admin123`

```
student-management-system/
│
├── .github/
│   └── workflows/
│       └── ci.yml              # CI/CD pipeline configuration
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
│   ├── database.php           # Database connection
│   └── security.php           # Security helper functions
│
├── database/
│   └── schema.sql             # Database schema
│
├── includes/
│   ├── header.php             # Common header
│   └── sidebar.php            # Navigation sidebar
│
├── students/                  # Student module
│   ├── index.php              # List students
│   ├── add.php                # Add student
│   ├── edit.php               # Edit student
│   ├── view.php               # View student details
│   └── delete.php             # Delete student
│
├── teachers/                  # Teacher module
│   ├── index.php              # List teachers
│   ├── add.php                # Add teacher
│   ├── edit.php               # Edit teacher
│   ├── view.php               # View teacher details
│   └── delete.php             # Delete teacher
│
├── classes/                   # Classes module
├── courses/                   # Courses module
├── subjects/                  # Subjects module
├── attendance/                # Attendance module
├── exams/                     # Exams module
├── grades/                    # Grades module
├── announcements/             # Announcements module
│
├── uploads/                   # User uploads (not in git)
│   ├── students/              # Student photos
│   └── teachers/              # Teacher photos
│
├── logs/                      # Application logs (not in git)
│
├── .env.example               # Environment configuration template
├── .gitignore                 # Git exclusions
├── composer.json              # PHP dependencies
├── docker-compose.yml         # Docker configuration
├── Dockerfile                 # Docker image definition
├── phpunit.xml.dist           # PHPUnit configuration
├── phpcs.xml                  # Code standards configuration
├── index.php                  # Entry point
├── login.php                  # Login page
├── logout.php                 # Logout handler
├── dashboard.php              # Main dashboard
├── README.md                  # This file
├── INSTALLATION.md            # Installation guide
└── SECURITY.md                # Security documentation
```

## 🎯 User Roles & Permissions

### Admin
- ✅ Full access to all modules
- ✅ Manage students, teachers, classes, courses, subjects
- ✅ Mark attendance and enter grades
- ✅ Post and manage announcements
- ✅ View all reports
- ✅ System configuration

### Teacher
- ✅ View students
- ✅ Mark attendance for assigned classes
- ✅ Enter grades for exams
- ✅ Post announcements
- ⊗ Limited administrative access

### Student
- ✅ View personal dashboard
- ✅ View announcements
- ✅ View grades and attendance
- ⊗ No administrative access

## 🛠️ Technologies Used

### Backend
- **PHP 7.4+**: Core application logic
- **MySQL 5.7+**: Database management
- **PDO**: Database abstraction layer
- **Composer**: Dependency management

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Modern styling with gradients
- **JavaScript (ES6+)**: Interactive features
- **No frameworks**: Vanilla JS for simplicity

### DevOps
- **Docker**: Containerization
- **Docker Compose**: Multi-container orchestration
- **GitHub Actions**: CI/CD pipeline

### Testing & Quality
- **PHPUnit**: Unit testing framework
- **PHP_CodeSniffer**: Code standards (PSR-12)
- **PHP CS Fixer**: Code formatting

## 📸 Screenshots

### Dashboard
![Dashboard](docs/screenshots/dashboard.png)
*Modern dashboard with statistics and recent activities*

### Student Management
![Students](docs/screenshots/students.png)
*Complete student management with photo uploads*

### Attendance Marking
![Attendance](docs/screenshots/attendance.png)
*Easy attendance marking with bulk operations*

### Grade Management
![Grades](docs/screenshots/grades.png)
*Grade entry with automatic calculation*

> 📝 Note: Screenshots coming soon

## 🧪 Testing

### Running Tests

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run specific test suite
vendor/bin/phpunit --testsuite Unit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/
```

### Code Quality

```bash
# Check code standards
composer cs

# Fix code standards automatically
composer cbf
```

## 🔧 Troubleshooting

### Common Issues

**Problem**: CSRF token validation failed
- **Solution**: Clear browser cookies and refresh

**Problem**: Cannot upload photos
- **Solution**: Check permissions on `uploads/` directory
  ```bash
  chmod -R 775 uploads/
  ```

**Problem**: Database connection error
- **Solution**: Verify `.env` configuration and MySQL service status

**Problem**: Login rate limiting
- **Solution**: Wait 15 minutes or clear sessions

📖 For more troubleshooting, see [INSTALLATION.md](INSTALLATION.md#troubleshooting)

## 📊 CI/CD Pipeline

The project includes a GitHub Actions workflow that:

- ✅ Tests on PHP 7.4, 8.0, 8.1
- ✅ Validates composer.json
- ✅ Runs PHPUnit tests
- ✅ Checks code standards (PSR-12)
- ✅ Validates PHP syntax
- ✅ Checks for hardcoded secrets
- ✅ Builds Docker image
- ✅ Validates Docker Compose configuration

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write unit tests for new features
- Update documentation as needed
- Add CSRF protection to all forms
- Use prepared statements for database queries
- Sanitize all user inputs
- Validate all user inputs

## 🐛 Known Issues

- [ ] Pagination not yet implemented for large lists
- [ ] Export to CSV not yet implemented
- [ ] Email notifications not implemented
- [ ] Two-factor authentication not implemented

See [GitHub Issues](https://github.com/yourusername/student-management-system/issues) for full list.

## 🗺️ Roadmap

### Version 2.1 (Next Release)
- [ ] Pagination for all listings
- [ ] Export to CSV/Excel
- [ ] Advanced search and filtering
- [ ] Attendance reports and analytics
- [ ] Grade reports and transcripts

### Version 2.2 (Future)
- [ ] Email notification system
- [ ] SMS notifications
- [ ] Parent portal
- [ ] Fee management module
- [ ] Library management
- [ ] Timetable management

### Version 3.0 (Long-term)
- [ ] REST API
- [ ] Mobile app (React Native)
- [ ] Advanced analytics dashboard
- [ ] Machine learning insights
- [ ] Multi-language support

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2024 Student Management System

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## 👨‍💻 Author

**Student Management System Team**
- GitHub: [@yourusername](https://github.com/yourusername)
- Email: contact@example.com
- Website: https://example.com

## 💖 Support

If this project helped you, please consider:
- ⭐ Starring the repository
- 🐛 Reporting bugs
- 💡 Suggesting new features
- 🤝 Contributing code
- 📢 Sharing with others

## 📞 Contact & Support

- **Issues**: [GitHub Issues](https://github.com/yourusername/student-management-system/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/student-management-system/discussions)
- **Email**: support@example.com
- **Security**: security@example.com (for security issues only)

## 🙏 Acknowledgments

- Icons: Emoji icons for beautiful UI
- Fonts: Google Fonts (Inter)
- Inspiration: Modern web design trends
- Community: Open source contributors

## 📈 Project Stats

![GitHub stars](https://img.shields.io/github/stars/yourusername/student-management-system?style=social)
![GitHub forks](https://img.shields.io/github/forks/yourusername/student-management-system?style=social)
![GitHub issues](https://img.shields.io/github/issues/yourusername/student-management-system)
![GitHub pull requests](https://img.shields.io/github/issues-pr/yourusername/student-management-system)
![GitHub last commit](https://img.shields.io/github/last-commit/yourusername/student-management-system)

---

**Made with ❤️ for education • Built with modern PHP • Secured by design • Deployed with Docker**


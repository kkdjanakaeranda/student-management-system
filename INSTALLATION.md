# Installation Guide

## Table of Contents
1. [System Requirements](#system-requirements)
2. [Installation Methods](#installation-methods)
3. [Configuration](#configuration)
4. [Troubleshooting](#troubleshooting)

## System Requirements

### Minimum Requirements
- **PHP**: 7.4 or higher (8.0+ recommended)
- **MySQL**: 5.7 or higher / MariaDB 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **RAM**: 512 MB minimum, 1 GB recommended
- **Disk Space**: 500 MB for application and database

### PHP Extensions Required
- pdo
- pdo_mysql
- mbstring
- gd (for image processing)
- fileinfo
- json

## Installation Methods

### Method 1: Docker Installation (Recommended)

Docker provides the easiest way to get started with the Student Management System.

#### Step 1: Install Docker
```bash
# Install Docker and Docker Compose
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

#### Step 2: Clone Repository
```bash
git clone https://github.com/yourusername/student-management-system.git
cd student-management-system
```

#### Step 3: Configure Environment
```bash
cp .env.example .env
# Edit .env file with your settings
nano .env
```

#### Step 4: Start Containers
```bash
docker-compose up -d
```

#### Step 5: Access Application
- **Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **Default Login**: admin / admin123

### Method 2: Manual Installation

#### Step 1: Install LAMP Stack

**For Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install apache2 php php-mysql php-mbstring php-gd mysql-server
sudo systemctl start apache2
sudo systemctl start mysql
```

**For CentOS/RHEL:**
```bash
sudo yum install httpd php php-mysqlnd php-mbstring php-gd mysql-server
sudo systemctl start httpd
sudo systemctl start mysqld
```

#### Step 2: Download Application
```bash
cd /var/www/html
git clone https://github.com/yourusername/student-management-system.git
cd student-management-system
```

#### Step 3: Set Permissions
```bash
sudo chown -R www-data:www-data /var/www/html/student-management-system
sudo chmod -R 755 /var/www/html/student-management-system
sudo mkdir -p uploads/students uploads/teachers logs
sudo chmod -R 775 uploads logs
```

#### Step 4: Create Database
```bash
mysql -u root -p
```

```sql
CREATE DATABASE student_management_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sms_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON student_management_system.* TO 'sms_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Step 5: Import Database Schema
```bash
mysql -u root -p student_management_system < database/schema.sql
```

#### Step 6: Configure Environment
```bash
cp .env.example .env
nano .env
```

Update the following settings:
```env
DB_HOST=localhost
DB_NAME=student_management_system
DB_USER=sms_user
DB_PASS=your_secure_password
APP_URL=http://your-domain.com/
```

#### Step 7: Configure Apache (if using Apache)
Create virtual host configuration:
```bash
sudo nano /etc/apache2/sites-available/sms.conf
```

Add:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/student-management-system
    
    <Directory /var/www/html/student-management-system>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/sms_error.log
    CustomLog ${APACHE_LOG_DIR}/sms_access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite sms.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

#### Step 8: Access Application
Open browser and navigate to: http://your-domain.com

## Configuration

### Environment Variables

All configuration is managed through the `.env` file:

```env
# Database Configuration
DB_HOST=localhost                    # Database server hostname
DB_NAME=student_management_system    # Database name
DB_USER=root                         # Database username
DB_PASS=                             # Database password

# Application Configuration
APP_ENV=production                   # Environment: development/production
APP_DEBUG=false                      # Enable/disable debug mode
APP_URL=http://localhost/sms/        # Base URL of application

# Site Configuration
SITE_NAME="Student Management System"  # Site title

# Security Configuration
SESSION_LIFETIME=7200                # Session lifetime in seconds
CSRF_TOKEN_EXPIRE=3600              # CSRF token expiry in seconds
LOGIN_MAX_ATTEMPTS=5                # Maximum login attempts
LOGIN_LOCKOUT_TIME=900              # Lockout time in seconds
```

### Production Settings

For production environments, ensure:

1. **Set APP_ENV to production**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Use HTTPS**
   ```env
   APP_URL=https://yourdomain.com/
   SESSION_SECURE=true
   ```

3. **Strong Database Credentials**
   ```env
   DB_USER=sms_user
   DB_PASS=StrongRandomPassword123!
   ```

4. **File Permissions**
   ```bash
   chmod 640 .env
   chmod 755 uploads/
   chmod 755 logs/
   ```

## Troubleshooting

### Common Issues and Solutions

#### Issue: White/Blank Page

**Cause**: PHP errors not displayed or missing configuration.

**Solution**:
```bash
# Check PHP error log
tail -f /var/log/apache2/error.log
# Or application error log
tail -f logs/error.log
```

Enable error display temporarily:
```env
APP_ENV=development
APP_DEBUG=true
```

#### Issue: Database Connection Error

**Cause**: Incorrect database credentials or MySQL not running.

**Solution**:
1. Verify MySQL is running:
   ```bash
   sudo systemctl status mysql
   ```

2. Test database connection:
   ```bash
   mysql -u [DB_USER] -p[DB_PASS] -h [DB_HOST] [DB_NAME]
   ```

3. Check `.env` file credentials

#### Issue: Cannot Upload Photos

**Cause**: Insufficient permissions on upload directories.

**Solution**:
```bash
sudo chown -R www-data:www-data uploads/
sudo chmod -R 775 uploads/
```

#### Issue: CSRF Token Validation Failed

**Cause**: Session issues or token expiry.

**Solution**:
1. Clear browser cookies and cache
2. Check session configuration:
   ```bash
   php -i | grep session.save_path
   ```
3. Ensure session directory is writable:
   ```bash
   sudo chmod 777 /var/lib/php/sessions
   ```

#### Issue: Login Rate Limiting

**Cause**: Too many failed login attempts.

**Solution**:
Wait for the lockout period (default 15 minutes) or clear sessions:
```bash
rm -rf /var/lib/php/sessions/sess_*
```

#### Issue: 404 Not Found

**Cause**: Apache mod_rewrite not enabled or incorrect BASE_URL.

**Solution**:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Update `.env`:
```env
APP_URL=http://localhost/student-management-system/
```

#### Issue: Composer Dependencies Missing

**Cause**: Composer not installed or dependencies not installed.

**Solution**:
```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install dependencies
composer install --no-dev --optimize-autoloader
```

### Performance Optimization

#### Enable OPcache
```bash
sudo nano /etc/php/7.4/apache2/php.ini
```

Add:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

#### MySQL Optimization
```sql
-- Add indexes for better performance
ALTER TABLE students ADD INDEX idx_status (status);
ALTER TABLE attendance ADD INDEX idx_date (attendance_date);
ALTER TABLE grades ADD INDEX idx_exam (exam_id);
```

### Security Hardening

1. **Disable Directory Listing**
   ```apache
   <Directory /var/www/html/student-management-system>
       Options -Indexes +FollowSymLinks
   </Directory>
   ```

2. **Hide PHP Version**
   ```ini
   expose_php = Off
   ```

3. **Restrict File Uploads**
   ```ini
   file_uploads = On
   upload_max_filesize = 5M
   post_max_size = 8M
   ```

4. **Enable HTTPS**
   ```bash
   sudo apt install certbot python3-certbot-apache
   sudo certbot --apache -d yourdomain.com
   ```

## Getting Help

If you encounter issues not covered here:

1. Check the [GitHub Issues](https://github.com/yourusername/student-management-system/issues)
2. Review application logs in `logs/error.log`
3. Enable debug mode temporarily for detailed errors
4. Contact support: support@example.com

## Next Steps

After successful installation:

1. Change default admin password
2. Configure site settings
3. Add your first teacher
4. Create classes and subjects
5. Enroll students
6. Start managing your institution!

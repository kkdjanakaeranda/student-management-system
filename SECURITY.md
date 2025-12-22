# Security Features

## Overview

The Student Management System implements comprehensive security measures to protect against common web vulnerabilities and ensure data safety.

## Implemented Security Features

### 1. CSRF Protection ✅

**Cross-Site Request Forgery (CSRF)** protection is implemented across all forms.

#### How It Works
- Unique CSRF tokens generated per session
- Tokens expire after 1 hour
- All POST requests validate tokens
- Uses `hash_equals()` to prevent timing attacks

#### Implementation
```php
// Generate token
$token = generateCSRFToken();

// Add to form
echo csrfField();

// Validate on submission
requireCSRF();
```

#### Files
- `config/security.php`: CSRF functions
- All forms: CSRF field included
- All POST handlers: CSRF validation

### 2. SQL Injection Prevention ✅

**PDO Prepared Statements** are used throughout the application.

#### Example
```php
$query = "SELECT * FROM students WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
```

#### Features
- All queries use prepared statements
- Parameter binding with type hints
- No raw SQL concatenation
- PDO::ATTR_EMULATE_PREPARES disabled

### 3. XSS Protection ✅

**Cross-Site Scripting (XSS)** prevention through sanitization.

#### Input Sanitization
```php
$username = sanitizeString($_POST['username']);
$email = sanitizeEmail($_POST['email']);
$html = sanitizeHtml($_POST['content']);
```

#### Output Escaping
```php
echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
```

#### Available Sanitization Functions
- `sanitizeString()`: General text input
- `sanitizeEmail()`: Email addresses
- `sanitizeInt()`: Integer values
- `sanitizeFloat()`: Decimal numbers
- `sanitizeUrl()`: URLs
- `sanitizeFilename()`: File names
- `sanitizeHtml()`: HTML content with allowed tags

### 4. Session Security ✅

**Secure session configuration** to prevent session hijacking.

#### Features
- HTTPOnly cookies (not accessible via JavaScript)
- SameSite cookie attribute (Lax)
- Session regeneration every 30 minutes
- Session timeout after 2 hours
- Secure session initialization

#### Configuration
```php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 7200);
```

### 5. Password Security ✅

**Strong password hashing** using PHP's built-in functions.

#### Implementation
```php
// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Verify password
if (password_verify($inputPassword, $hashedPassword)) {
    // Login successful
}
```

#### Password Strength Validation
```php
$errors = validatePasswordStrength($password);
// Checks for:
// - Minimum 8 characters
// - At least one uppercase letter
// - At least one lowercase letter
// - At least one number
```

### 6. Rate Limiting ✅

**Login rate limiting** to prevent brute force attacks.

#### Configuration
```env
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_TIME=900  # 15 minutes
```

#### Features
- Tracks failed login attempts per username
- Temporary lockout after max attempts
- Session-based tracking
- Automatic reset on successful login

### 7. Input Validation ✅

**Comprehensive validation** for all user inputs.

#### Available Validators
- `validateRequired()`: Required field check
- `validateEmail()`: Email format validation
- `validateMinLength()`: Minimum length check
- `validateMaxLength()`: Maximum length check
- `validateNumeric()`: Numeric value check
- `validateInteger()`: Integer value check
- `validateDate()`: Date format validation
- `validateFileUpload()`: File upload validation

#### Example
```php
$errors = [];
if ($err = validateRequired($name, 'Name')) $errors[] = $err;
if ($err = validateEmail($email)) $errors[] = $err;
if ($err = validateMinLength($password, 8, 'Password')) $errors[] = $err;
```

### 8. File Upload Security ✅

**Secure file upload handling** to prevent malicious uploads.

#### Security Measures
1. **File Type Validation**: MIME type checking with `finfo`
2. **File Size Limits**: Maximum 5MB per file
3. **File Extension Whitelist**: Only jpg, jpeg, png allowed
4. **Unique File Names**: `uniqid()` prevents overwrites
5. **Image Validation**: `getimagesize()` verifies images
6. **Secure Storage**: Files stored outside web root

#### Implementation
```php
$errors = validateFileUpload(
    $_FILES['photo'],
    ['image/jpeg', 'image/jpg', 'image/png'],
    5242880  // 5MB
);
```

### 9. Security Headers ✅

**HTTP security headers** to protect against various attacks.

#### Implemented Headers
```php
X-Frame-Options: SAMEORIGIN              // Prevent clickjacking
X-XSS-Protection: 1; mode=block          // XSS protection
X-Content-Type-Options: nosniff          // Prevent MIME sniffing
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: [policy]        // CSP policy
```

#### Content Security Policy
```
default-src 'self';
script-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
font-src 'self' https://fonts.gstatic.com;
img-src 'self' data:;
```

### 10. Environment Configuration ✅

**Sensitive data protection** via environment variables.

#### Features
- Database credentials in `.env`
- `.env` excluded from version control
- `.env.example` for documentation
- Fallback values for missing variables
- Production/development modes

#### Environment Variables
```env
DB_HOST=localhost
DB_NAME=student_management_system
DB_USER=root
DB_PASS=secret_password
APP_ENV=production
APP_DEBUG=false
```

### 11. Error Handling ✅

**Secure error handling** that doesn't expose sensitive information.

#### Production Mode
- Generic error messages for users
- Detailed errors logged to file
- `display_errors = Off`
- Stack traces hidden

#### Development Mode
- Detailed error display
- Full stack traces
- Debugging information

#### Configuration
```php
if ($appEnv === 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
```

### 12. Access Control ✅

**Role-based access control (RBAC)** for authorization.

#### Roles
- **Admin**: Full access to all features
- **Teacher**: Limited access (grades, attendance)
- **Student**: View-only access

#### Functions
```php
requireLogin();      // Require authentication
requireAdmin();      // Require admin role
hasRole('admin');    // Check user role
```

## Security Best Practices

### For Administrators

1. **Change Default Credentials**
   ```sql
   UPDATE users SET password = '[new_hash]' WHERE username = 'admin';
   ```

2. **Keep Software Updated**
   ```bash
   composer update
   apt update && apt upgrade
   ```

3. **Regular Backups**
   ```bash
   # Database backup
   mysqldump -u root -p student_management_system > backup.sql
   
   # Files backup
   tar -czf uploads_backup.tar.gz uploads/
   ```

4. **Monitor Logs**
   ```bash
   tail -f logs/error.log
   tail -f /var/log/apache2/access.log
   ```

5. **Use HTTPS**
   ```bash
   certbot --apache -d yourdomain.com
   ```

### For Developers

1. **Never Store Sensitive Data in Code**
   - Use environment variables
   - Exclude `.env` from git

2. **Always Validate Input**
   ```php
   $errors = [];
   if ($err = validateRequired($input)) $errors[] = $err;
   if ($err = validateEmail($email)) $errors[] = $err;
   ```

3. **Always Sanitize Output**
   ```php
   echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
   ```

4. **Use Prepared Statements**
   ```php
   $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
   $stmt->bindParam(':id', $id, PDO::PARAM_INT);
   ```

5. **Add CSRF to All Forms**
   ```php
   <form method="POST">
       <?php echo csrfField(); ?>
       ...
   </form>
   ```

6. **Log Security Events**
   ```php
   error_log("Failed login attempt for user: $username");
   ```

## Known Limitations

1. **Rate Limiting**: Session-based only (resets on browser close)
2. **Two-Factor Authentication**: Not implemented (future enhancement)
3. **Email Verification**: Not implemented (future enhancement)
4. **API Authentication**: Not implemented (no API yet)

## Security Audit Checklist

- [x] CSRF protection on all forms
- [x] SQL injection prevention (prepared statements)
- [x] XSS protection (input sanitization, output escaping)
- [x] Secure session configuration
- [x] Password hashing (bcrypt)
- [x] Rate limiting (login attempts)
- [x] Input validation
- [x] File upload security
- [x] Security headers
- [x] Environment configuration
- [x] Error handling
- [x] Access control (RBAC)
- [ ] Two-factor authentication (future)
- [ ] Email verification (future)

## Reporting Security Issues

If you discover a security vulnerability, please email:
- **Email**: security@example.com
- **Response Time**: 48 hours
- **Disclosure**: Responsible disclosure policy

**Do not** create public GitHub issues for security vulnerabilities.

## Security Updates

Keep track of security updates:
- Subscribe to repository releases
- Monitor security advisories
- Update dependencies regularly

## Compliance

This system implements security controls aligned with:
- **OWASP Top 10**: Protection against common vulnerabilities
- **SANS Top 25**: CWE/SANS most dangerous software errors
- **GDPR**: Data protection and privacy (basic compliance)

## Additional Resources

- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [MySQL Security Guidelines](https://dev.mysql.com/doc/refman/8.0/en/security-guidelines.html)

## Last Updated

Security documentation last updated: December 2024

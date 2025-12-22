<?php
/**
 * Security Helper Functions
 * CSRF Protection, Input Validation, Sanitization
 */

// ============================================
// CSRF Protection
// ============================================

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token']) || 
        !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > 3600) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Check if token has expired (1 hour)
    if ((time() - $_SESSION['csrf_token_time']) > 3600) {
        return false;
    }
    
    // Compare tokens using hash_equals to prevent timing attacks
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token field HTML
 */
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Require CSRF token validation for POST requests
 */
function requireCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        
        if (!validateCSRFToken($token)) {
            http_response_code(403);
            die('CSRF token validation failed. Please refresh the page and try again.');
        }
    }
}

// ============================================
// Input Validation
// ============================================

/**
 * Validate required field
 */
function validateRequired($value, $fieldName = 'Field') {
    if (empty($value) && $value !== '0') {
        return "$fieldName is required";
    }
    return null;
}

/**
 * Validate email
 */
function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }
    return null;
}

/**
 * Validate minimum length
 */
function validateMinLength($value, $min, $fieldName = 'Field') {
    if (strlen($value) < $min) {
        return "$fieldName must be at least $min characters";
    }
    return null;
}

/**
 * Validate maximum length
 */
function validateMaxLength($value, $max, $fieldName = 'Field') {
    if (strlen($value) > $max) {
        return "$fieldName must not exceed $max characters";
    }
    return null;
}

/**
 * Validate numeric value
 */
function validateNumeric($value, $fieldName = 'Field') {
    if (!is_numeric($value)) {
        return "$fieldName must be a number";
    }
    return null;
}

/**
 * Validate integer value
 */
function validateInteger($value, $fieldName = 'Field') {
    if (!filter_var($value, FILTER_VALIDATE_INT)) {
        return "$fieldName must be an integer";
    }
    return null;
}

/**
 * Validate date format
 */
function validateDate($date, $format = 'Y-m-d', $fieldName = 'Date') {
    $d = DateTime::createFromFormat($format, $date);
    if (!$d || $d->format($format) !== $date) {
        return "$fieldName is not a valid date";
    }
    return null;
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'], $maxSize = 5242880) {
    $errors = [];
    
    if (!isset($file['error']) || is_array($file['error'])) {
        $errors[] = 'Invalid file upload';
        return $errors;
    }
    
    // Check for upload errors
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return $errors; // No file uploaded, might be optional
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $errors[] = 'File size exceeds maximum allowed size';
            break;
        default:
            $errors[] = 'Unknown file upload error';
            break;
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        $maxSizeMB = $maxSize / (1024 * 1024);
        $errors[] = "File size must not exceed {$maxSizeMB}MB";
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes);
    }
    
    // Check for valid image
    if (strpos($mimeType, 'image/') === 0) {
        if (!getimagesize($file['tmp_name'])) {
            $errors[] = 'File is not a valid image';
        }
    }
    
    return $errors;
}

// ============================================
// Input Sanitization
// ============================================

/**
 * Sanitize string input
 */
function sanitizeString($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize email
 */
function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Sanitize integer
 */
function sanitizeInt($input) {
    return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * Sanitize float
 */
function sanitizeFloat($input) {
    return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

/**
 * Sanitize URL
 */
function sanitizeUrl($url) {
    return filter_var(trim($url), FILTER_SANITIZE_URL);
}

/**
 * Sanitize filename
 */
function sanitizeFilename($filename) {
    // Remove any path components
    $filename = basename($filename);
    
    // Remove special characters, keep only alphanumeric, dots, hyphens, underscores
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    
    // Prevent double extensions
    $filename = preg_replace('/\.{2,}/', '.', $filename);
    
    return $filename;
}

/**
 * Sanitize HTML (allow safe tags)
 */
function sanitizeHtml($input, $allowedTags = '<p><br><strong><em><u>') {
    return strip_tags(trim($input), $allowedTags);
}

// ============================================
// Security Headers
// ============================================

/**
 * Set security headers
 */
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Prevent MIME sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy
    $csp = "default-src 'self'; ";
    $csp .= "script-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ";
    $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ";
    $csp .= "font-src 'self' https://fonts.gstatic.com; ";
    $csp .= "img-src 'self' data:; ";
    header("Content-Security-Policy: $csp");
    
    // Remove X-Powered-By header
    header_remove('X-Powered-By');
}

// ============================================
// Rate Limiting
// ============================================

/**
 * Check rate limit for login attempts
 */
function checkLoginRateLimit($identifier, $maxAttempts = 5, $lockoutTime = 900) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $key = 'login_attempts_' . md5($identifier);
    $timeKey = 'login_lockout_' . md5($identifier);
    
    // Check if currently locked out
    if (isset($_SESSION[$timeKey]) && time() < $_SESSION[$timeKey]) {
        $remainingTime = $_SESSION[$timeKey] - time();
        return [
            'allowed' => false,
            'remaining_time' => $remainingTime
        ];
    }
    
    // Initialize or increment attempts
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = 1;
    } else {
        $_SESSION[$key]++;
    }
    
    // Check if max attempts reached
    if ($_SESSION[$key] >= $maxAttempts) {
        $_SESSION[$timeKey] = time() + $lockoutTime;
        return [
            'allowed' => false,
            'remaining_time' => $lockoutTime
        ];
    }
    
    return [
        'allowed' => true,
        'attempts' => $_SESSION[$key]
    ];
}

/**
 * Reset login rate limit
 */
function resetLoginRateLimit($identifier) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $key = 'login_attempts_' . md5($identifier);
    $timeKey = 'login_lockout_' . md5($identifier);
    
    unset($_SESSION[$key]);
    unset($_SESSION[$timeKey]);
}

// ============================================
// Session Security
// ============================================

/**
 * Initialize secure session
 */
function initSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Lax');
        
        // Set session cookie lifetime (2 hours)
        ini_set('session.gc_maxlifetime', 7200);
        ini_set('session.cookie_lifetime', 7200);
        
        // Start session
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            // Regenerate session ID every 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

/**
 * Generate secure random password
 */
function generateSecurePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    $max = strlen($chars) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $max)];
    }
    
    return $password;
}

/**
 * Validate password strength
 */
function validatePasswordStrength($password, $minLength = 8) {
    $errors = [];
    
    if (strlen($password) < $minLength) {
        $errors[] = "Password must be at least $minLength characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return $errors;
}
?>

<?php
// Load environment variables (simple implementation without composer)
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load .env file
loadEnv(__DIR__ . '/../.env');

// Initialize secure session
require_once __DIR__ . '/security.php';
initSecureSession();
setSecurityHeaders();

// Error reporting
$appEnv = getenv('APP_ENV') ?: 'production';
$appDebug = filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN);

if ($appEnv === 'development' && $appDebug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

// Site settings
define('SITE_NAME', getenv('SITE_NAME') ?: 'Student Management System');
define('BASE_URL', getenv('APP_URL') ?: 'http://localhost/student-management-system/');

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('STUDENT_PHOTO_DIR', UPLOAD_DIR .  'students/');
define('TEACHER_PHOTO_DIR', UPLOAD_DIR . 'teachers/');

// Create upload directories if they don't exist
if (! file_exists(STUDENT_PHOTO_DIR)) {
    mkdir(STUDENT_PHOTO_DIR, 0755, true);
}
if (!file_exists(TEACHER_PHOTO_DIR)) {
    mkdir(TEACHER_PHOTO_DIR, 0755, true);
}

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/../logs/';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0755, true);
}

// Include database
require_once __DIR__ . '/database.php';

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!hasRole('admin')) {
        header('Location: ' . BASE_URL .  'dashboard.php');
        exit();
    }
}

function calculateGrade($marks, $totalMarks) {
    $percentage = ($marks / $totalMarks) * 100;
    
    if ($percentage >= 90) return 'A+';
    elseif ($percentage >= 80) return 'A';
    elseif ($percentage >= 70) return 'B+';
    elseif ($percentage >= 60) return 'B';
    elseif ($percentage >= 50) return 'C';
    elseif ($percentage >= 40) return 'D';
    else return 'F';
}
?>
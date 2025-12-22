<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Site settings
define('SITE_NAME', 'Student Management System');
define('BASE_URL', 'http://localhost/student-management-system/');

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('STUDENT_PHOTO_DIR', UPLOAD_DIR .  'students/');
define('TEACHER_PHOTO_DIR', UPLOAD_DIR . 'teachers/');

// Create upload directories if they don't exist
if (! file_exists(STUDENT_PHOTO_DIR)) {
    mkdir(STUDENT_PHOTO_DIR, 0777, true);
}
if (!file_exists(TEACHER_PHOTO_DIR)) {
    mkdir(TEACHER_PHOTO_DIR, 0777, true);
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
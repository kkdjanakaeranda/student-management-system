<?php
// config/config.php  — drop-in replacement
session_start();

define('SITE_NAME', 'Student Management System');
define('BASE_URL',  'http://localhost/student-management-system/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// ── Database class ────────────────────────────────────────────────────────────
class Database {
    private $host     = 'localhost';
    private $db_name  = 'student_management_system';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection(): PDO {
        if ($this->conn !== null) {
            return $this->conn;
        }
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
        return $this->conn;
    }
}

// ── Auth helpers ──────────────────────────────────────────────────────────────
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

function hasRole(string $role): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function currentRole(): string {
    return $_SESSION['role'] ?? 'guest';
}

// Returns the user's real display name, falling back to username.
function displayName(): string {
    return htmlspecialchars(
        $_SESSION['display_name'] ?? $_SESSION['username'] ?? 'User'
    );
}

// ── CSRF helpers ──────────────────────────────────────────────────────────────
/**
 * Generate a CSRF token and store it in the session.
 * Call inside every form-rendering page.
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Emit a hidden CSRF input field.  Call inside every <form>.
 *   <?php csrfField(); ?>
 */
function csrfField(): void {
    echo '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrfToken()) . '">';
}

/**
 * Verify the CSRF token from a POST request.
 * Dies with 403 if invalid.
 */
function verifyCsrf(): void {
    $posted = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';
    if (!hash_equals($stored, $posted)) {
        http_response_code(403);
        die('Invalid CSRF token. Please go back and try again.');
    }
    // Rotate the token after successful verification
    unset($_SESSION['csrf_token']);
}

// ── File upload helper ────────────────────────────────────────────────────────
/**
 * Validate and move an uploaded photo.
 * Returns the relative path string on success, or null on failure/no upload.
 *
 * @param string $field     $_FILES key
 * @param string $subdir    e.g. 'students' or 'teachers'
 */
function handlePhotoUpload(string $field, string $subdir): ?string {
    if (empty($_FILES[$field]['tmp_name'])) {
        return null;
    }
    $file = $_FILES[$field];

    // Read actual magic bytes — don't trust browser-supplied MIME
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowed, true)) {
        return null;
    }

    // 2 MB max
    if ($file['size'] > 2 * 1024 * 1024) {
        return null;
    }

    $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = uniqid($subdir . '_', true) . '.' . strtolower($ext);
    $destDir = UPLOAD_PATH . $subdir . '/';

    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $dest = $destDir . $newName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }

    return 'uploads/' . $subdir . '/' . $newName;
}

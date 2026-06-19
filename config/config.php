<?php
// config/config.php — drop-in replacement
session_start();

define('SITE_NAME', 'Student Management System');
define('BASE_URL',  'http://localhost/student-management-system/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('STUDENT_PHOTO_DIR', UPLOAD_PATH . 'students/');
define('TEACHER_PHOTO_DIR', UPLOAD_PATH . 'teachers/');

// ── Database ──────────────────────────────────────────────────────────────────
class Database {
    private $host     = 'localhost';
    private $db_name  = 'student_management_system';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection(): PDO {
        if ($this->conn !== null) return $this->conn;
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

// These match the original project's function names exactly
function requireAdmin(): void {
    requireLogin();
    if (!hasRole('admin')) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }
}

function requireTeacher(): void {
    requireLogin();
    if (!hasRole('teacher') && !hasRole('admin')) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }
}

function requireStudent(): void {
    requireLogin();
    if (!hasRole('student')) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }
}

// Returns the user's real display name, falling back to username
function displayName(): string {
    return htmlspecialchars(
        $_SESSION['display_name'] ?? $_SESSION['username'] ?? 'User'
    );
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void {
    header('Location: ' . $path);
    exit();
}

function requirePost(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        die('Method not allowed.');
    }
}

function currentTeacherRowId(PDO $db): int {
    if (!hasRole('teacher')) return 0;
    if (isset($_SESSION['teacher_row_id'])) return (int)$_SESSION['teacher_row_id'];

    $stmt = $db->prepare("SELECT id FROM teachers WHERE user_id = :uid LIMIT 1");
    $stmt->execute([':uid' => $_SESSION['user_id'] ?? 0]);
    $row = $stmt->fetch();
    $_SESSION['teacher_row_id'] = $row ? (int)$row['id'] : 0;
    return (int)$_SESSION['teacher_row_id'];
}

function currentStudentRowId(PDO $db): int {
    if (!hasRole('student')) return 0;
    if (isset($_SESSION['student_row_id'])) return (int)$_SESSION['student_row_id'];

    $stmt = $db->prepare("SELECT id FROM students WHERE user_id = :uid LIMIT 1");
    $stmt->execute([':uid' => $_SESSION['user_id'] ?? 0]);
    $row = $stmt->fetch();
    $_SESSION['student_row_id'] = $row ? (int)$row['id'] : 0;
    return (int)$_SESSION['student_row_id'];
}

function canAccessStudent(PDO $db, int $studentId): bool {
    if (hasRole('admin')) return true;
    if (hasRole('student')) return currentStudentRowId($db) === $studentId;
    if (!hasRole('teacher')) return false;

    $stmt = $db->prepare(
        "SELECT 1
         FROM enrollments e
         JOIN classes c ON c.id = e.class_id
         WHERE e.student_id = :student_id
           AND c.teacher_id = :teacher_id
         LIMIT 1"
    );
    $stmt->execute([
        ':student_id' => $studentId,
        ':teacher_id' => currentTeacherRowId($db),
    ]);
    return (bool)$stmt->fetch();
}

function logAction(PDO $db, string $action, string $entityType, ?int $entityId = null, string $details = ''): void {
    try {
        $stmt = $db->prepare(
            "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details)
             VALUES (:user_id, :action, :entity_type, :entity_id, :details)"
        );
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'] ?? null,
            ':action' => $action,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':details' => $details,
        ]);
    } catch (Exception $e) {
        // Audit logging should never break the user workflow.
    }
}

// ── CSRF helpers ──────────────────────────────────────────────────────────────
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): void {
    echo '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf(): void {
    $posted = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';
    if (!hash_equals($stored, $posted)) {
        http_response_code(403);
        die('Invalid CSRF token. Please go back and try again.');
    }
    unset($_SESSION['csrf_token']);
}

// ── File upload helper ────────────────────────────────────────────────────────
function handlePhotoUpload(string $field, string $subdir): ?string {
    if (empty($_FILES[$field]['tmp_name'])) return null;
    $file = $_FILES[$field];

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowed, true)) return null;
    if ($file['size'] > 2 * 1024 * 1024) return null;

    $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = uniqid($subdir . '_', true) . '.' . strtolower($ext);
    $destDir = UPLOAD_PATH . $subdir . '/';
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    $dest = $destDir . $newName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
    return 'uploads/' . $subdir . '/' . $newName;
}

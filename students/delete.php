<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

verifyCsrf();

$id = $_POST['id'] ?? 0;

// Get student info
$query = "SELECT * FROM students WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$student = $stmt->fetch();

if (!$student) {
    header('Location: index.php');
    exit();
}

try {
    // Delete photo if exists
    if ($student['photo'] && file_exists(STUDENT_PHOTO_DIR . $student['photo'])) {
        unlink(STUDENT_PHOTO_DIR . $student['photo']);
    }
    
    // Preserve academic history by deactivating instead of deleting.
    $query = "UPDATE students SET status = 'inactive' WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    logAction($db, 'deactivated', 'student', (int)$id, $student['student_id'] ?? '');
    
    header('Location: index.php');
    exit();
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>

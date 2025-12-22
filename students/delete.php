<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

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
    
    // Delete student record (user will be deleted by cascade)
    $query = "DELETE FROM students WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    header('Location: index.php');
    exit();
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
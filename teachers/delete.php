<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

$query = "SELECT * FROM teachers WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$teacher = $stmt->fetch();

if (!$teacher) {
    header('Location: index.php');
    exit();
}

try {
    if ($teacher['photo'] && file_exists(TEACHER_PHOTO_DIR . $teacher['photo'])) {
        unlink(TEACHER_PHOTO_DIR . $teacher['photo']);
    }
    
    $query = "DELETE FROM teachers WHERE id = : id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    header('Location: index.php');
    exit();
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
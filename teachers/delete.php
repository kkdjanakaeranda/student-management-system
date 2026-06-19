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
    
    $query = "UPDATE teachers SET status = 'inactive' WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    header('Location: index.php');
    exit();
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>

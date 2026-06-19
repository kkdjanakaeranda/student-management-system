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

try {
    $query = "UPDATE courses SET status = 'inactive' WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    logAction($db, 'deactivated', 'course', (int)$id);
    
    header('Location:  index.php');
    exit();
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>

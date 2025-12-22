<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

try {
    $query = "DELETE FROM classes WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    header('Location:  index.php');
    exit();
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
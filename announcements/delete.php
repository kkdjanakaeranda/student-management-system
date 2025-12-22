<?php
require_once '../config/config.php';
requireLogin();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'Invalid announcement ID';
    header('Location: index.php');
    exit();
}

$id = sanitizeInt($_GET['id']);

// Handle POST request for deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Delete the announcement
        $query = "DELETE FROM announcements WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Announcement deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete announcement';
        }
    } catch (PDOException $e) {
        error_log("Delete announcement error: " . $e->getMessage());
        $_SESSION['error'] = 'An error occurred while deleting the announcement';
    }
    
    header('Location: index.php');
    exit();
}

// Get announcement details for confirmation
$database = new Database();
$db = $database->getConnection();

$query = "SELECT a.*, u.username FROM announcements a 
          LEFT JOIN users u ON a.posted_by = u.id 
          WHERE a.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = 'Announcement not found';
    header('Location: index.php');
    exit();
}

$announcement = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Announcement - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1>🗑️ Delete Announcement</h1>
                    <p>Confirm announcement deletion</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-secondary">↩️ Back to Announcements</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>⚠️ Confirm Deletion</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action cannot be undone. Are you sure you want to delete this announcement?
                    </div>
                    
                    <div class="announcement-details" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h3 style="margin-top: 0;"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                        <p><strong>Priority:</strong> <span class="badge badge-<?php echo htmlspecialchars($announcement['priority']); ?>"><?php echo htmlspecialchars(ucfirst($announcement['priority'])); ?></span></p>
                        <p><strong>Target Audience:</strong> <?php echo htmlspecialchars(ucfirst($announcement['target_audience'])); ?></p>
                        <p><strong>Posted By:</strong> <?php echo htmlspecialchars($announcement['username']); ?></p>
                        <p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($announcement['created_at'])); ?></p>
                        <div style="margin-top: 15px;">
                            <p><strong>Content:</strong></p>
                            <div style="padding: 10px; background: white; border-radius: 4px;">
                                <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="" style="margin-top: 30px;">
                        <?php echo csrfField(); ?>
                        
                        <div style="display: flex; gap: 15px; justify-content: flex-end;">
                            <a href="index.php" class="btn btn-secondary">✖️ Cancel</a>
                            <button type="submit" class="btn btn-danger">🗑️ Delete Announcement</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>

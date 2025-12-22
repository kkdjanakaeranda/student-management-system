<?php
require_once '../config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

$query = "SELECT * FROM announcements WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$announcement = $stmt->fetch();

if (!$announcement) {
    header('Location: index.php');
    exit();
}

// Check permission
if (! hasRole('admin') && $_SESSION['user_id'] != $announcement['posted_by']) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $title = $_POST['title'];
    $content = $_POST['content'];
    $target_audience = $_POST['target_audience'];
    $priority = $_POST['priority'];
    
    try {
        $query = "UPDATE announcements SET title = :title, content = :content, 
                  target_audience = :target_audience, priority = :priority 
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':target_audience', $target_audience);
        $stmt->bindParam(':priority', $priority);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        header('Location: index.php');
        exit();
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Announcement - <?php echo SITE_NAME; ?></title>
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
                    <h1>✏️ Edit Announcement</h1>
                    <p>Update announcement details</p>
                </div>
                <a href="index.php" class="btn btn-secondary">← Back</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" class="form-grid">
                        <?php echo csrfField(); ?>
                        <div class="form-section">
                            <h3>📋 Announcement Details</h3>
                            
                            <div class="form-group">
                                <label for="title">Title <span>*</span></label>
                                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($announcement['title']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="content">Content <span>*</span></label>
                                <textarea id="content" name="content" rows="8" required><?php echo htmlspecialchars($announcement['content']); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="target_audience">Target Audience <span>*</span></label>
                                    <select id="target_audience" name="target_audience" required>
                                        <option value="all" <?php echo $announcement['target_audience'] == 'all' ? 'selected' : ''; ?>>All</option>
                                        <option value="students" <?php echo $announcement['target_audience'] == 'students' ? 'selected' : ''; ?>>Students Only</option>
                                        <option value="teachers" <?php echo $announcement['target_audience'] == 'teachers' ? 'selected' : ''; ?>>Teachers Only</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="priority">Priority <span>*</span></label>
                                    <select id="priority" name="priority" required>
                                        <option value="low" <?php echo $announcement['priority'] == 'low' ? 'selected' : ''; ?>>Low</option>
                                        <option value="medium" <?php echo $announcement['priority'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                        <option value="high" <?php echo $announcement['priority'] == 'high' ? 'selected' :  ''; ?>>High</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Update Announcement</button>
                            <a href="index.php" class="btn btn-secondary">❌ Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
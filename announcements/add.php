<?php
require_once '../config/config.php';
requireLogin();

if (!hasRole('admin') && !hasRole('teacher')) {
    header('Location: index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $target_audience = $_POST['target_audience'];
    $priority = $_POST['priority'];
    
    try {
        $query = "INSERT INTO announcements (title, content, posted_by, target_audience, priority) 
                  VALUES (:title, :content, :posted_by, :target_audience, :priority)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':posted_by', $_SESSION['user_id']);
        $stmt->bindParam(':target_audience', $target_audience);
        $stmt->bindParam(':priority', $priority);
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
    <title>Post Announcement - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo filemtime('../assets/css/style.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1>Post Announcement</h1>
                    <p>Create a new announcement</p>
                </div>
                <a href="index.php" class="btn btn-secondary">Back</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Announcement Information</h2>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    <form method="POST">
                        <?php csrfField(); ?>
                        <div class="form-section">
                            <div class="form-group">
                                <label for="title" >Title <span>*</span></label>
                                <input type="text" id="title" name="title" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="content">Content <span>*</span></label>
                                <textarea id="content" name="content" class="form-control" rows="8" required></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="target_audience">Target Audience <span>*</span></label>
                                    <select id="target_audience" name="target_audience" class="form-control" required>
                                        <option value="all">All</option>
                                        <option value="students">Students Only</option>
                                        <option value="teachers">Teachers Only</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="priority">Priority <span>*</span></label>
                                    <select id="priority" name="priority" class="form-control" required>
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Announcement</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js?v=<?php echo filemtime('../assets/js/main.js'); ?>"></script>
</body>
</html>

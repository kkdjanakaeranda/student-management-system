<?php
require_once '../config/config.php';
requireLogin();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'Invalid exam ID';
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
        // Check if there are any grades associated with this exam
        $query = "SELECT COUNT(*) as count FROM grades WHERE exam_id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            $_SESSION['error'] = 'Cannot delete exam. There are grades associated with this exam. Please delete the grades first.';
            header('Location: index.php');
            exit();
        }
        
        // Delete the exam
        $query = "DELETE FROM exams WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Exam deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete exam';
        }
    } catch (PDOException $e) {
        error_log("Delete exam error: " . $e->getMessage());
        $_SESSION['error'] = 'An error occurred while deleting the exam';
    }
    
    header('Location: index.php');
    exit();
}

// Get exam details for confirmation
$database = new Database();
$db = $database->getConnection();

$query = "SELECT e.*, s.name as subject_name, c.name as class_name 
          FROM exams e 
          LEFT JOIN subjects s ON e.subject_id = s.id 
          LEFT JOIN classes c ON e.class_id = c.id 
          WHERE e.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = 'Exam not found';
    header('Location: index.php');
    exit();
}

$exam = $stmt->fetch();

// Check if there are grades
$query = "SELECT COUNT(*) as count FROM grades WHERE exam_id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$grades_count = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Exam - <?php echo SITE_NAME; ?></title>
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
                    <h1>🗑️ Delete Exam</h1>
                    <p>Confirm exam deletion</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-secondary">↩️ Back to Exams</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>⚠️ Confirm Deletion</h2>
                </div>
                <div class="card-body">
                    <?php if ($grades_count > 0): ?>
                        <div class="alert alert-error">
                            <strong>Cannot Delete:</strong> This exam has <?php echo $grades_count; ?> associated grade(s). 
                            Please delete all grades for this exam before deleting the exam itself.
                        </div>
                        <div style="margin-top: 20px;">
                            <a href="index.php" class="btn btn-primary">↩️ Back to Exams</a>
                            <a href="../grades/index.php?exam_id=<?php echo $exam['id']; ?>" class="btn btn-secondary">View Grades</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> This action cannot be undone. Are you sure you want to delete this exam?
                        </div>
                        
                        <div class="exam-details" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                            <h3 style="margin-top: 0;"><?php echo htmlspecialchars($exam['name']); ?></h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                                <p><strong>Type:</strong> <span class="badge"><?php echo htmlspecialchars(ucfirst($exam['exam_type'])); ?></span></p>
                                <p><strong>Subject:</strong> <?php echo htmlspecialchars($exam['subject_name']); ?></p>
                                <p><strong>Class:</strong> <?php echo htmlspecialchars($exam['class_name']); ?></p>
                                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($exam['exam_date'])); ?></p>
                                <p><strong>Total Marks:</strong> <?php echo htmlspecialchars($exam['total_marks']); ?></p>
                                <p><strong>Passing Marks:</strong> <?php echo htmlspecialchars($exam['passing_marks']); ?></p>
                            </div>
                            <?php if (!empty($exam['description'])): ?>
                                <div style="margin-top: 15px;">
                                    <p><strong>Description:</strong></p>
                                    <div style="padding: 10px; background: white; border-radius: 4px;">
                                        <?php echo nl2br(htmlspecialchars($exam['description'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" action="" style="margin-top: 30px;">
                            <?php echo csrfField(); ?>
                            
                            <div style="display: flex; gap: 15px; justify-content: flex-end;">
                                <a href="index.php" class="btn btn-secondary">✖️ Cancel</a>
                                <button type="submit" class="btn btn-danger">🗑️ Delete Exam</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>

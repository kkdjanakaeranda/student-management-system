<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit();
}

$stmt = $db->prepare("SELECT * FROM courses WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Course - <?php echo SITE_NAME; ?></title>
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
                    <h1>Course Details</h1>
                    <p class="page-description">View course information</p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="edit.php?id=<?php echo $course['id']; ?>" class="btn btn-warning">Edit</a>
                    <a href="index.php" class="btn btn-secondary">Back to List</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Course Information</h2>
                    <span class="badge badge-<?php echo e($course['status']); ?>"><?php echo ucfirst(e($course['status'])); ?></span>
                </div>
                <div class="card-body">
                    <div class="details-grid">
                        <div class="detail-item">
                            <label>Course Code</label>
                            <p><?php echo e($course['course_code']); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Course Name</label>
                            <p><?php echo e($course['course_name']); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Credits</label>
                            <p><?php echo e((string)($course['credits'] ?? 'N/A')); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Duration</label>
                            <p><?php echo e($course['duration'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Description</label>
                            <p><?php echo e($course['description'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Created</label>
                            <p><?php echo !empty($course['created_at']) ? date('M j, Y', strtotime($course['created_at'])) : 'N/A'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>

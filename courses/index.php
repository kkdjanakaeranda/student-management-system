<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM courses ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - <?php echo SITE_NAME; ?></title>
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
                    <h1>📖 Courses</h1>
                    <p>Manage all course information</p>
                </div>
                <a href="add.php" class="btn btn-primary">
                    ➕ Add New Course
                </a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>📋 All Courses (<?php echo count($courses); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (count($courses) > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Credits</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($course['credits'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($course['duration'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $course['status']; ?>">
                                                    <?php echo ucfirst($course['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">
                                                    <a href="edit.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                                                    <a href="delete.php?id=<?php echo $course['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Are you sure? ')">🗑️ Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📖</div>
                            <h3>No Courses Found</h3>
                            <p>There are no courses in the system yet.</p>
                            <a href="add.php" class="btn btn-primary mt-3">Add First Course</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
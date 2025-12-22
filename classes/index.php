<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT c.*, co.course_name, t.first_name, t.last_name 
          FROM classes c 
          LEFT JOIN courses co ON c.course_id = co.id 
          LEFT JOIN teachers t ON c.teacher_id = t.id 
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$classes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes - <?php echo SITE_NAME; ?></title>
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
                    <h1>📚 Classes</h1>
                    <p>Manage all class information</p>
                </div>
                <a href="add.php" class="btn btn-primary">
                    ➕ Add New Class
                </a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>📋 All Classes (<?php echo count($classes); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (count($classes) > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Section</th>
                                        <th>Course</th>
                                        <th>Teacher</th>
                                        <th>Academic Year</th>
                                        <th>Room</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $class): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($class['class_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($class['section'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($class['course_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars(($class['first_name'] ?? '') . ' ' . ($class['last_name'] ?? 'N/A')); ?></td>
                                            <td><?php echo htmlspecialchars($class['academic_year'] ??  'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($class['room_number'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $class['status']; ?>">
                                                    <?php echo ucfirst($class['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">
                                                    <a href="edit.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                                                    <a href="delete.php?id=<?php echo $class['id']; ?>" 
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
                            <div class="empty-state-icon">📚</div>
                            <h3>No Classes Found</h3>
                            <p>There are no classes in the system yet.</p>
                            <a href="add.php" class="btn btn-primary mt-3">Add First Class</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
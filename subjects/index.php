<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT s.*, c.class_name, t.first_name, t.last_name 
          FROM subjects s 
          LEFT JOIN classes c ON s.class_id = c.id 
          LEFT JOIN teachers t ON s.teacher_id = t.id 
          ORDER BY s.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$subjects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects - <?php echo SITE_NAME; ?></title>
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
                    <h1>📝 Subjects</h1>
                    <p>Manage all subject information</p>
                </div>
                <a href="add.php" class="btn btn-primary">
                    ➕ Add New Subject
                </a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>📋 All Subjects (<?php echo count($subjects); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (count($subjects) > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Subject Code</th>
                                        <th>Subject Name</th>
                                        <th>Class</th>
                                        <th>Teacher</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($subject['class_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars(($subject['first_name'] ??  '') . ' ' . ($subject['last_name'] ?? 'N/A')); ?></td>
                                            <td>
                                                <div style="display: flex; gap:  5px;">
                                                    <a href="edit.php?id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                                                    <a href="delete.php?id=<?php echo $subject['id']; ?>" 
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
                            <div class="empty-state-icon">📝</div>
                            <h3>No Subjects Found</h3>
                            <p>There are no subjects in the system yet. </p>
                            <a href="add.php" class="btn btn-primary mt-3">Add First Subject</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
<?php
require_once '../config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT s.*, u.email FROM students s 
          LEFT JOIN users u ON s.user_id = u.id 
          ORDER BY s.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - <?php echo SITE_NAME; ?></title>
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
                    <h1>👨‍🎓 Students</h1>
                    <p>Manage all student records and information</p>
                </div>
                <?php if (hasRole('admin')): ?>
                    <a href="add.php" class="btn btn-primary">
                        ➕ Add New Student
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>📋 All Students (<?php echo count($students); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (count($students) > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Student ID</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Gender</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo $student['photo'] ? '../uploads/students/' . $student['photo'] : '../assets/images/default-avatar.png'; ?>" 
                                                     alt="Photo" class="table-avatar">
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($student['first_name'] .  ' ' . $student['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($student['gender'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $student['status']; ?>">
                                                    <?php echo ucfirst($student['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">
                                                    <a href="view.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-info">👁️ View</a>
                                                    <?php if (hasRole('admin')): ?>
                                                        <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                                                        <a href="delete.php? id=<?php echo $student['id']; ?>" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('Are you sure? ')">🗑️ Delete</a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">👨‍🎓</div>
                            <h3>No Students Found</h3>
                            <p>There are no students in the system yet. </p>
                            <?php if (hasRole('admin')): ?>
                                <a href="add.php" class="btn btn-primary mt-3">Add First Student</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT t.*, u.email FROM teachers t 
          LEFT JOIN users u ON t.user_id = u. id 
          ORDER BY t. created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$teachers = $stmt->fetchAll();
?>
<! DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers - <?php echo SITE_NAME; ?></title>
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
                    <h1>👨‍🏫 Teachers</h1>
                    <p>Manage all teacher records and information</p>
                </div>
                <a href="add.php" class="btn btn-primary">
                    ➕ Add New Teacher
                </a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>📋 All Teachers (<?php echo count($teachers); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (count($teachers) > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Teacher ID</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Specialization</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo $teacher['photo'] ? '../uploads/teachers/' . $teacher['photo'] : '../assets/images/default-avatar.png'; ?>" 
                                                     alt="Photo" class="table-avatar">
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($teacher['teacher_id']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($teacher['first_name'] .  ' ' . $teacher['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['email'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['specialization'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $teacher['status']; ?>">
                                                    <?php echo ucfirst($teacher['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">
                                                    <a href="view.php?id=<?php echo $teacher['id']; ?>" class="btn btn-sm btn-info">👁️ View</a>
                                                    <a href="edit.php?id=<?php echo $teacher['id']; ?>" class="btn btn-sm btn-warning">✏️ Edit</a>
                                                    <a href="delete.php?id=<?php echo $teacher['id']; ?>" 
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
                            <div class="empty-state-icon">👨‍🏫</div>
                            <h3>No Teachers Found</h3>
                            <p>There are no teachers in the system yet. </p>
                            <a href="add.php" class="btn btn-primary mt-3">Add First Teacher</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
<?php
require_once '../config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT e.*, c.class_name, s.subject_name 
          FROM exams e 
          JOIN classes c ON e.class_id = c.id 
          JOIN subjects s ON e.subject_id = s.id 
          ORDER BY e.exam_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$exams = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exams - <?php echo SITE_NAME; ?></title>
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
                    <h1>📋 Exams</h1>
                    <p>Manage all exam schedules</p>
                </div>
                <?php if (hasRole('admin') || hasRole('teacher')): ?>
                    <a href="add.php" class="btn btn-primary">
                        ➕ Add New Exam
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>📋 All Exams (<?php echo count($exams); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (count($exams) > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Exam Name</th>
                                        <th>Type</th>
                                        <th>Class</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th>Total Marks</th>
                                        <th>Duration</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($exams as $exam): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong></td>
                                            <td><span class="badge badge-low"><?php echo ucfirst($exam['exam_type']); ?></span></td>
                                            <td><?php echo htmlspecialchars($exam['class_name']); ?></td>
                                            <td><?php echo htmlspecialchars($exam['subject_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($exam['exam_date'])); ?></td>
                                            <td><?php echo $exam['total_marks']; ?></td>
                                            <td><?php echo htmlspecialchars($exam['duration'] ?? 'N/A'); ?></td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">
                                                    <?php if (hasRole('admin') || hasRole('teacher')): ?>
                                                        <a href="edit.php?id=<?php echo $exam['id']; ?>" class="btn btn-sm btn-warning">✏️</a>
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
                            <div class="empty-state-icon">📋</div>
                            <h3>No Exams Found</h3>
                            <p>There are no exams scheduled yet.</p>
                            <?php if (hasRole('admin') || hasRole('teacher')): ?>
                                <a href="add. php" class="btn btn-primary mt-3">Add First Exam</a>
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
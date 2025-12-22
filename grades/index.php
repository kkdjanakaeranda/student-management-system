<?php
require_once '../config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT g.*, s.student_id, s.first_name, s.last_name, 
          e.exam_name, e.total_marks, sub.subject_name 
          FROM grades g 
          JOIN students s ON g.student_id = s.id 
          JOIN exams e ON g.exam_id = e.id 
          JOIN subjects sub ON e.subject_id = sub.id 
          ORDER BY g.created_at DESC LIMIT 50";
$stmt = $db->prepare($query);
$stmt->execute();
$grades = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades - <?php echo SITE_NAME; ?></title>
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
                    <h1>🎯 Grades</h1>
                    <p>View and manage student grades</p>
                </div>
                <?php if (hasRole('admin') || hasRole('teacher')): ?>
                    <a href="add.php" class="btn btn-primary">
                        ➕ Add Grades
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>📋 Recent Grades (<?php echo count($grades); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (count($grades) > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Exam</th>
                                        <th>Subject</th>
                                        <th>Marks Obtained</th>
                                        <th>Total Marks</th>
                                        <th>Percentage</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $grade): ?>
                                        <?php
                                        $percentage = ($grade['marks_obtained'] / $grade['total_marks']) * 100;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($grade['exam_name']); ?></td>
                                            <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                                            <td><?php echo $grade['marks_obtained']; ?></td>
                                            <td><?php echo $grade['total_marks']; ?></td>
                                            <td><?php echo number_format($percentage, 2); ?>%</td>
                                            <td>
                                                <span class="badge badge-<?php echo $percentage >= 50 ? 'active' : 'inactive'; ?>">
                                                    <?php echo htmlspecialchars($grade['grade']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🎯</div>
                            <h3>No Grades Found</h3>
                            <p>There are no grades recorded yet.</p>
                            <?php if (hasRole('admin') || hasRole('teacher')): ?>
                                <a href="add.php" class="btn btn-primary mt-3">Add First Grade</a>
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
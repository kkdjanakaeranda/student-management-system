<?php
// students/my_grades.php — personal grades page for the student role
require_once '../config/config.php';
requireLogin();

if (!hasRole('student')) {
    header('Location: ../dashboard.php');
    exit();
}

$sid      = (int) ($_SESSION['student_id'] ?? 0);
$database = new Database();
$db       = $database->getConnection();

// All grades for this student, joined to subject + exam
$stmt = $db->prepare(
    "SELECT g.marks_obtained, g.grade, g.created_at,
            e.exam_name, e.exam_type, e.total_marks, e.exam_date,
            s.name AS subject_name,
            c.name AS class_name, c.section
     FROM   grades   g
     JOIN   exams    e ON e.id = g.exam_id
     JOIN   classes  c ON c.id = e.class_id
     LEFT JOIN subjects s ON s.id = e.subject_id
     WHERE  g.student_id = :sid
     ORDER  BY e.exam_date DESC"
);
$stmt->execute([':sid' => $sid]);
$grades = $stmt->fetchAll();

// Compute overall average
$avgStmt = $db->prepare(
    "SELECT ROUND(AVG(g.marks_obtained / e.total_marks * 100), 1) AS avg_pct
     FROM grades g JOIN exams e ON e.id = g.exam_id
     WHERE g.student_id = :sid AND e.total_marks > 0"
);
$avgStmt->execute([':sid' => $sid]);
$avgPct = $avgStmt->fetch()['avg_pct'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">

        <div class="page-header">
            <h1>📊 My Grades</h1>
            <?php if ($avgPct): ?>
                <div class="stat-card stat-primary" style="padding:12px 20px;">
                    <div class="stat-details">
                        <h3><?php echo $avgPct; ?>%</h3>
                        <p>Overall Average</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if (empty($grades)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <h3>No grades yet</h3>
                        <p>Your grades will appear here once they have been entered.</p>
                    </div>
                <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Score</th>
                            <th>%</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($grades as $g):
                        $pct = $g['total_marks'] > 0
                               ? round(($g['marks_obtained'] / $g['total_marks']) * 100, 1)
                               : 0;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($g['exam_name']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($g['exam_type'])); ?></td>
                            <td><?php echo htmlspecialchars($g['subject_name'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($g['class_name'] . ' ' . $g['section']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($g['exam_date'])); ?></td>
                            <td><?php echo (float)$g['marks_obtained']; ?> / <?php echo (int)$g['total_marks']; ?></td>
                            <td><?php echo $pct; ?>%</td>
                            <td>
                                <span class="badge badge-<?php echo $g['grade'] === 'F' ? 'inactive' : 'active'; ?>">
                                    <?php echo htmlspecialchars($g['grade']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>

<?php
require_once '../config/config.php';
requireLogin();

if (!hasRole('student')) {
    header('Location: ../dashboard.php'); exit();
}

$database = new Database();
$db = $database->getConnection();

// Get student row via user_id
$s = $db->prepare("SELECT id FROM students WHERE user_id=:uid LIMIT 1");
$s->execute([':uid' => $_SESSION['user_id']]);
$row = $s->fetch();
$sid = $row ? (int)$row['id'] : 0;

$classes = []; $subjectsByClass = []; $upcomingExams = [];

if ($sid) {
    // Enrolled classes — uses class_name, enrollment_date, status='enrolled'
    $classStmt = $db->prepare(
        "SELECT c.id AS class_id, c.class_name, c.section,
                co.course_name,
                t.first_name AS teacher_first, t.last_name AS teacher_last,
                e.enrollment_date
         FROM enrollments e
         JOIN classes c ON c.id=e.class_id
         LEFT JOIN courses co ON co.id=c.course_id
         LEFT JOIN teachers t ON t.id=c.teacher_id
         WHERE e.student_id=:sid AND e.status='enrolled' AND c.status='active'
         ORDER BY c.class_name");
    $classStmt->execute([':sid' => $sid]);
    $classes = $classStmt->fetchAll();

    // Subjects per class — uses subject_name
    if ($classes) {
        $classIds = array_column($classes, 'class_id');
        $inList   = implode(',', array_fill(0, count($classIds), '?'));
        $subStmt  = $db->prepare(
            "SELECT s.class_id, s.subject_name, s.subject_code,
                    t.first_name AS t_first, t.last_name AS t_last
             FROM subjects s
             LEFT JOIN teachers t ON t.id=s.teacher_id
             WHERE s.class_id IN ($inList)
             ORDER BY s.subject_name");
        $subStmt->execute($classIds);
        foreach ($subStmt->fetchAll() as $sub) {
            $subjectsByClass[$sub['class_id']][] = $sub;
        }
    }

    // Upcoming exams next 30 days
    $examStmt = $db->prepare(
        "SELECT ex.exam_name, ex.exam_type, ex.exam_date, ex.total_marks,
                c.class_name, c.section, s.subject_name
         FROM exams ex
         JOIN enrollments en ON en.class_id=ex.class_id
         JOIN classes c ON c.id=ex.class_id
         LEFT JOIN subjects s ON s.id=ex.subject_id
         WHERE en.student_id=:sid AND en.status='enrolled'
           AND ex.exam_date>=CURDATE()
           AND ex.exam_date<=DATE_ADD(CURDATE(),INTERVAL 30 DAY)
         ORDER BY ex.exam_date");
    $examStmt->execute([':sid' => $sid]);
    $upcomingExams = $examStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Timetable — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header"><h1>📅 My Timetable</h1></div>

        <?php if ($upcomingExams): ?>
        <div class="card" style="margin-bottom:24px;border-left:4px solid #f59e0b">
            <div class="card-header"><h2>⚠️ Upcoming Exams (next 30 days)</h2></div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Date</th><th>Exam</th><th>Type</th><th>Subject</th><th>Class</th><th>Total Marks</th></tr></thead>
                    <tbody>
                    <?php foreach ($upcomingExams as $ex): ?>
                        <tr>
                            <td><?php echo date('D, M d Y', strtotime($ex['exam_date'])); ?></td>
                            <td><?php echo htmlspecialchars($ex['exam_name']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($ex['exam_type'])); ?></td>
                            <td><?php echo htmlspecialchars($ex['subject_name'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($ex['class_name'].' '.$ex['section']); ?></td>
                            <td><?php echo (int)$ex['total_marks']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($classes)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📚</div>
                <h3>Not enrolled in any classes</h3>
                <p>Please contact your administrator to get enrolled in classes.</p>
            </div>
        <?php else: ?>
            <?php foreach ($classes as $cls): ?>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header">
                    <h2><?php echo htmlspecialchars($cls['class_name']); ?>
                        <span style="font-weight:400;font-size:14px">— <?php echo htmlspecialchars($cls['section']); ?></span>
                    </h2>
                    <div style="font-size:13px;color:var(--color-text-secondary)">
                        <?php if ($cls['course_name']): ?>
                            📖 <?php echo htmlspecialchars($cls['course_name']); ?> &nbsp;·&nbsp;
                        <?php endif; ?>
                        <?php if ($cls['teacher_first']): ?>
                            👨‍🏫 <?php echo htmlspecialchars($cls['teacher_first'].' '.$cls['teacher_last']); ?> &nbsp;·&nbsp;
                        <?php endif; ?>
                        Enrolled <?php echo date('M Y', strtotime($cls['enrollment_date'])); ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php $subs = $subjectsByClass[$cls['class_id']] ?? []; ?>
                    <?php if ($subs): ?>
                    <table class="table">
                        <thead><tr><th>Code</th><th>Subject</th><th>Teacher</th></tr></thead>
                        <tbody>
                        <?php foreach ($subs as $sub): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sub['subject_code']); ?></td>
                                <td><?php echo htmlspecialchars($sub['subject_name']); ?></td>
                                <td><?php echo $sub['t_first'] ? htmlspecialchars($sub['t_first'].' '.$sub['t_last']) : '—'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p style="color:var(--color-text-secondary)">No subjects assigned to this class yet.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>

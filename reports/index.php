<?php
require_once '../config/config.php';
requireTeacher();

$database = new Database();
$db = $database->getConnection();
$isAdmin = hasRole('admin');
$teacherId = currentTeacherRowId($db);

$params = [];
$classScope = '';
if (!$isAdmin) {
    $classScope = ' WHERE c.teacher_id = :teacher_id';
    $params[':teacher_id'] = $teacherId;
}

function reportCount(PDO $db, string $sql, array $params = []): int {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return (int)($stmt->fetch()['total'] ?? 0);
}

$totalStudents = reportCount(
    $db,
    "SELECT COUNT(DISTINCT s.id) AS total
     FROM students s
     JOIN enrollments e ON e.student_id = s.id
     JOIN classes c ON c.id = e.class_id
     $classScope",
    $params
);

$totalClasses = reportCount(
    $db,
    "SELECT COUNT(*) AS total FROM classes c $classScope",
    $params
);

$attendanceParams = $params + [':start_date' => date('Y-m-d', strtotime('-30 days'))];
$attendanceSql = "SELECT
        COUNT(*) AS total_records,
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_records
     FROM attendance a
     JOIN classes c ON c.id = a.class_id
     WHERE a.date >= :start_date";
if (!$isAdmin) {
    $attendanceSql .= " AND c.teacher_id = :teacher_id";
}
$attStmt = $db->prepare($attendanceSql);
$attStmt->execute($attendanceParams);
$attendance = $attStmt->fetch();
$attendancePct = ($attendance && (int)$attendance['total_records'] > 0)
    ? round(((int)$attendance['present_records'] / (int)$attendance['total_records']) * 100, 1)
    : 0;

$gradeSql = "SELECT ROUND(AVG(g.marks_obtained / NULLIF(e.total_marks, 0) * 100), 1) AS avg_grade
             FROM grades g
             JOIN exams e ON e.id = g.exam_id
             JOIN classes c ON c.id = e.class_id";
if (!$isAdmin) {
    $gradeSql .= " WHERE c.teacher_id = :teacher_id";
}
$gradeStmt = $db->prepare($gradeSql);
$gradeStmt->execute($params);
$avgGrade = $gradeStmt->fetch()['avg_grade'] ?? 0;

$classStmt = $db->prepare(
    "SELECT c.class_name, c.section,
            COUNT(DISTINCT e.student_id) AS student_count,
            ROUND(AVG(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100, 1) AS attendance_pct
     FROM classes c
     LEFT JOIN enrollments e ON e.class_id = c.id AND e.status = 'enrolled'
     LEFT JOIN attendance a ON a.class_id = c.id AND a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
     " . (!$isAdmin ? "WHERE c.teacher_id = :teacher_id" : "") . "
     GROUP BY c.id
     ORDER BY c.class_name"
);
$classStmt->execute($params);
$classReports = $classStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>Reports</h1>
                <p>Academic and attendance overview</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card stat-primary"><div class="stat-details"><h3><?php echo $totalStudents; ?></h3><p>Students</p></div></div>
            <div class="stat-card stat-success"><div class="stat-details"><h3><?php echo $totalClasses; ?></h3><p>Classes</p></div></div>
            <div class="stat-card stat-warning"><div class="stat-details"><h3><?php echo $attendancePct; ?>%</h3><p>30-day Attendance</p></div></div>
            <div class="stat-card stat-info"><div class="stat-details"><h3><?php echo $avgGrade ?: 0; ?>%</h3><p>Average Grade</p></div></div>
        </div>

        <div class="card">
            <div class="card-header"><h2>Class Summary</h2></div>
            <div class="card-body">
                <table class="data-table">
                    <thead><tr><th>Class</th><th>Section</th><th>Students</th><th>30-day Attendance</th></tr></thead>
                    <tbody>
                    <?php foreach ($classReports as $row): ?>
                        <tr>
                            <td><?php echo e($row['class_name']); ?></td>
                            <td><?php echo e($row['section'] ?? '-'); ?></td>
                            <td><?php echo (int)$row['student_count']; ?></td>
                            <td><?php echo $row['attendance_pct'] !== null ? e((string)$row['attendance_pct']) . '%' : 'No data'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>

<?php
require_once 'config/config.php';
requireLogin();

$database = new Database();
$db       = $database->getConnection();
$role     = currentRole();

// Get teacher/student row id from users.user_id link
$linkedId = 0;
if ($role === 'teacher') {
    $s = $db->prepare("SELECT id FROM teachers WHERE user_id = :uid LIMIT 1");
    $s->execute([':uid' => $_SESSION['user_id']]);
    $row = $s->fetch();
    $linkedId = $row ? (int)$row['id'] : 0;
    $_SESSION['teacher_row_id'] = $linkedId;
} elseif ($role === 'student') {
    $s = $db->prepare("SELECT id FROM students WHERE user_id = :uid LIMIT 1");
    $s->execute([':uid' => $_SESSION['user_id']]);
    $row = $s->fetch();
    $linkedId = $row ? (int)$row['id'] : 0;
    $_SESSION['student_row_id'] = $linkedId;
}

function countQ(PDO $db, string $sql, array $p = []): int {
    $s = $db->prepare($sql);
    $s->execute($p);
    return (int)($s->fetch()['total'] ?? 0);
}

$stats = [];

if ($role === 'admin') {
    $stats['students'] = countQ($db, "SELECT COUNT(*) AS total FROM students WHERE status='active'");
    $stats['teachers'] = countQ($db, "SELECT COUNT(*) AS total FROM teachers WHERE status='active'");
    $stats['classes']  = countQ($db, "SELECT COUNT(*) AS total FROM classes  WHERE status='active'");
    $stats['courses']  = countQ($db, "SELECT COUNT(*) AS total FROM courses  WHERE status='active'");

} elseif ($role === 'teacher') {
    $tid = $linkedId;
    $stats['my_classes']  = countQ($db,
        "SELECT COUNT(*) AS total FROM classes WHERE teacher_id=:tid AND status='active'",
        [':tid'=>$tid]);
    $stats['my_subjects'] = countQ($db,
        "SELECT COUNT(*) AS total FROM subjects WHERE teacher_id=:tid",
        [':tid'=>$tid]);
    $stats['my_students'] = countQ($db,
        "SELECT COUNT(DISTINCT e.student_id) AS total
         FROM enrollments e JOIN classes c ON c.id=e.class_id
         WHERE c.teacher_id=:tid AND e.status='enrolled'",
        [':tid'=>$tid]);
    $stats['upcoming_exams'] = countQ($db,
        "SELECT COUNT(*) AS total FROM exams ex
         JOIN subjects s ON s.id=ex.subject_id
         WHERE s.teacher_id=:tid
           AND ex.exam_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 7 DAY)",
        [':tid'=>$tid]);

} elseif ($role === 'student') {
    $sid = $linkedId;
    $stats['enrolled_classes'] = countQ($db,
        "SELECT COUNT(*) AS total FROM enrollments WHERE student_id=:sid AND status='enrolled'",
        [':sid'=>$sid]);
    $avgStmt = $db->prepare(
        "SELECT COALESCE(ROUND(AVG(g.marks_obtained/e.total_marks*100),1),0) AS avg_pct
         FROM grades g JOIN exams e ON e.id=g.exam_id WHERE g.student_id=:sid");
    $avgStmt->execute([':sid'=>$sid]);
    $stats['avg_grade'] = $avgStmt->fetch()['avg_pct'] ?? 0;

    $attStmt = $db->prepare(
        "SELECT ROUND(SUM(CASE WHEN status='present' THEN 1 ELSE 0 END)/NULLIF(COUNT(*),0)*100,1) AS att_pct
         FROM attendance WHERE student_id=:sid AND date>=DATE_SUB(CURDATE(),INTERVAL 30 DAY)");
    $attStmt->execute([':sid'=>$sid]);
    $stats['attendance_pct'] = $attStmt->fetch()['att_pct'] ?? 0;

    $stats['upcoming_exams'] = countQ($db,
        "SELECT COUNT(*) AS total FROM exams ex
         JOIN enrollments e ON e.class_id=ex.class_id
         WHERE e.student_id=:sid AND e.status='enrolled'
           AND ex.exam_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 7 DAY)",
        [':sid'=>$sid]);
}

// Announcements - column is target_audience
$audFilter = match($role) {
    'student' => "AND (a.target_audience='all' OR a.target_audience='students')",
    'teacher' => "AND (a.target_audience='all' OR a.target_audience='teachers')",
    default   => ''
};
$annStmt = $db->prepare(
    "SELECT a.*, u.display_name AS poster_name
     FROM announcements a
     LEFT JOIN users u ON u.id=a.posted_by
     WHERE 1=1 $audFilter
     ORDER BY a.created_at DESC LIMIT 5");
$annStmt->execute();
$announcements = $annStmt->fetchAll();

// Teacher: classes with no attendance today
$pendingAttendance = [];
if ($role === 'teacher' && $linkedId) {
    $paStmt = $db->prepare(
        "SELECT c.id, c.class_name, c.section
         FROM classes c WHERE c.teacher_id=:tid AND c.status='active'
           AND c.id NOT IN (SELECT DISTINCT class_id FROM attendance WHERE date=CURDATE())
         LIMIT 5");
    $paStmt->execute([':tid'=>$linkedId]);
    $pendingAttendance = $paStmt->fetchAll();
}

// Student: recent grades
$recentGrades = [];
if ($role === 'student' && $linkedId) {
    $rgStmt = $db->prepare(
        "SELECT g.marks_obtained, g.grade, e.exam_name, e.total_marks,
                s.subject_name, c.class_name, c.section
         FROM grades g
         JOIN exams e ON e.id=g.exam_id
         JOIN classes c ON c.id=e.class_id
         LEFT JOIN subjects s ON s.id=e.subject_id
         WHERE g.student_id=:sid
         ORDER BY g.created_at DESC LIMIT 5");
    $rgStmt->execute([':sid'=>$linkedId]);
    $recentGrades = $rgStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">

        <div class="page-header">
            <div>
                <h1>📊 Dashboard</h1>
                <p>Welcome back, <?php echo displayName(); ?>!</p>
            </div>
        </div>

        <?php if ($role === 'admin'): ?>
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">👨‍🎓</div>
                <div class="stat-details"><h3><?php echo $stats['students']; ?></h3><p>Active Students</p></div>
            </div>
            <div class="stat-card stat-success">
                <div class="stat-icon">👨‍🏫</div>
                <div class="stat-details"><h3><?php echo $stats['teachers']; ?></h3><p>Active Teachers</p></div>
            </div>
            <div class="stat-card stat-warning">
                <div class="stat-icon">📚</div>
                <div class="stat-details"><h3><?php echo $stats['classes']; ?></h3><p>Running Classes</p></div>
            </div>
            <div class="stat-card stat-info">
                <div class="stat-icon">📖</div>
                <div class="stat-details"><h3><?php echo $stats['courses']; ?></h3><p>Active Courses</p></div>
            </div>
        </div>
        <div class="card" style="margin-bottom:24px">
            <div class="card-header"><h2>🎯 Quick Actions</h2></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
                <a href="students/add.php"      class="btn btn-primary"  style="width:100%">➕ Add Student</a>
                <a href="teachers/add.php"      class="btn btn-success"  style="width:100%">➕ Add Teacher</a>
                <a href="classes/add.php"        class="btn btn-info"     style="width:100%">➕ Create Class</a>
                <a href="announcements/add.php"  class="btn btn-warning"  style="width:100%">📢 Post Announcement</a>
            </div>
        </div>

        <?php elseif ($role === 'teacher'): ?>
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">📚</div>
                <div class="stat-details"><h3><?php echo $stats['my_classes']; ?></h3><p>My Classes</p></div>
            </div>
            <div class="stat-card stat-success">
                <div class="stat-icon">📝</div>
                <div class="stat-details"><h3><?php echo $stats['my_subjects']; ?></h3><p>My Subjects</p></div>
            </div>
            <div class="stat-card stat-warning">
                <div class="stat-icon">👨‍🎓</div>
                <div class="stat-details"><h3><?php echo $stats['my_students']; ?></h3><p>My Students</p></div>
            </div>
            <div class="stat-card stat-info">
                <div class="stat-icon">📅</div>
                <div class="stat-details"><h3><?php echo $stats['upcoming_exams']; ?></h3><p>Exams This Week</p></div>
            </div>
        </div>
        <?php if ($pendingAttendance): ?>
        <div class="card" style="margin-bottom:24px">
            <div class="card-header"><h2>⚠️ Attendance Not Yet Marked Today</h2></div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Class</th><th>Section</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($pendingAttendance as $cls): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cls['class_name']); ?></td>
                            <td><?php echo htmlspecialchars($cls['section']); ?></td>
                            <td><a href="attendance/mark.php?class_id=<?php echo $cls['id']; ?>" class="btn btn-sm btn-primary">Mark Now</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap">
            <a href="attendance/mark.php"   class="btn btn-primary">✅ Mark Attendance</a>
            <a href="grades/add.php"         class="btn btn-success">📝 Enter Grades</a>
            <a href="announcements/add.php"  class="btn btn-warning">📢 Post Announcement</a>
        </div>

        <?php elseif ($role === 'student'): ?>
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">📚</div>
                <div class="stat-details"><h3><?php echo $stats['enrolled_classes']; ?></h3><p>Enrolled Classes</p></div>
            </div>
            <div class="stat-card stat-success">
                <div class="stat-icon">📊</div>
                <div class="stat-details"><h3><?php echo $stats['avg_grade']; ?>%</h3><p>Average Grade</p></div>
            </div>
            <div class="stat-card stat-warning">
                <div class="stat-icon">✅</div>
                <div class="stat-details"><h3><?php echo $stats['attendance_pct']; ?>%</h3><p>Attendance (30 days)</p></div>
            </div>
            <div class="stat-card stat-info">
                <div class="stat-icon">📅</div>
                <div class="stat-details"><h3><?php echo $stats['upcoming_exams']; ?></h3><p>Exams This Week</p></div>
            </div>
        </div>
        <?php if ($recentGrades): ?>
        <div class="card" style="margin-bottom:24px">
            <div class="card-header">
                <h2>📝 Recent Grades</h2>
                <a href="students/my_grades.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Exam</th><th>Subject</th><th>Score</th><th>Grade</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentGrades as $g): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($g['exam_name']); ?></td>
                            <td><?php echo htmlspecialchars($g['subject_name'] ?? '—'); ?></td>
                            <td><?php echo (int)$g['marks_obtained']; ?> / <?php echo (int)$g['total_marks']; ?></td>
                            <td><span class="badge badge-active"><?php echo htmlspecialchars($g['grade']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap">
            <a href="students/my_grades.php"     class="btn btn-primary">📊 My Grades</a>
            <a href="students/my_attendance.php" class="btn btn-success">✅ My Attendance</a>
            <a href="students/my_timetable.php"  class="btn btn-info">📅 My Timetable</a>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h2>📢 Announcements</h2></div>
            <div class="card-body">
                <?php if ($announcements): ?>
                <div class="announcements-list">
                    <?php foreach ($announcements as $ann): ?>
                    <div class="announcement-item">
                        <div class="announcement-header">
                            <h4><?php echo htmlspecialchars($ann['title']); ?></h4>
                            <span class="badge badge-<?php echo htmlspecialchars($ann['priority']); ?>">
                                <?php echo ucfirst(htmlspecialchars($ann['priority'])); ?>
                            </span>
                        </div>
                        <p><?php echo mb_substr(htmlspecialchars($ann['content']), 0, 200); ?>…</p>
                        <div class="announcement-footer">
                            <span>👤 <?php echo htmlspecialchars($ann['poster_name'] ?? 'System'); ?></span>
                            <span>📅 <?php echo date('M d, Y · h:i A', strtotime($ann['created_at'])); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>No Announcements</h3>
                    <p>Nothing to show right now.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>

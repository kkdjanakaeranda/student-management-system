<?php
// grades/add.php — subject-scoped, enrollment-enforced replacement
require_once '../config/config.php';
requireLogin();

if (!hasRole('admin') && !hasRole('teacher')) {
    header('Location: ../dashboard.php');
    exit();
}

$database = new Database();
$db       = $database->getConnection();
$tid      = (int) ($_SESSION['teacher_id'] ?? 0);
$isAdmin  = hasRole('admin');

// ── Load exams scoped to teacher's subjects ───────────────────────────────
$examStmt = $db->prepare(
    "SELECT ex.id, ex.exam_name, ex.exam_type, ex.total_marks, ex.exam_date,
            s.name AS subject_name, c.name AS class_name, c.section
     FROM   exams   ex
     JOIN   classes c ON c.id = ex.class_id
     LEFT JOIN subjects s ON s.id = ex.subject_id
     WHERE  (:is_admin = 1 OR c.teacher_id = :tid)
     ORDER  BY ex.exam_date DESC, ex.exam_name"
);
$examStmt->execute([':is_admin' => (int)$isAdmin, ':tid' => $tid]);
$exams = $examStmt->fetchAll();

$examId  = (int) ($_GET['exam_id'] ?? ($_POST['exam_id'] ?? 0));
$message = '';
$msgType = '';

// ── Handle POST — save grades ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $examId) {
    verifyCsrf();

    $records = $_POST['grades'] ?? [];   // [student_id => marks_obtained]

    if (empty($records)) {
        $message = 'No grade data submitted.';
        $msgType = 'error';
    } else {
        // Load exam for total_marks
        $exStmt = $db->prepare("SELECT * FROM exams WHERE id=:id LIMIT 1");
        $exStmt->execute([':id' => $examId]);
        $exam = $exStmt->fetch();

        if (!$exam) {
            $message = 'Exam not found.';
            $msgType = 'error';
        } else {
            $db->beginTransaction();
            try {
                // Upsert each grade
                $upsert = $db->prepare(
                    "INSERT INTO grades (student_id, exam_id, marks_obtained, grade, created_by, created_at)
                     VALUES (:sid, :eid, :marks, :grade, :uid, NOW())
                     ON DUPLICATE KEY UPDATE
                         marks_obtained = VALUES(marks_obtained),
                         grade          = VALUES(grade),
                         created_by     = VALUES(created_by)"
                );

                foreach ($records as $studentId => $marks) {
                    $studentId = (int) $studentId;
                    $marks     = min((float) $marks, (float) $exam['total_marks']);
                    $marks     = max(0, $marks);
                    $pct       = $exam['total_marks'] > 0
                                 ? ($marks / $exam['total_marks']) * 100
                                 : 0;
                    $grade     = gradeFromPct($pct);

                    $upsert->execute([
                        ':sid'   => $studentId,
                        ':eid'   => $examId,
                        ':marks' => $marks,
                        ':grade' => $grade,
                        ':uid'   => $_SESSION['user_id'],
                    ]);
                }
                $db->commit();
                $message = 'Grades saved successfully.';
                $msgType = 'success';
            } catch (Exception $e) {
                $db->rollBack();
                $message = 'Failed to save grades. Please try again.';
                $msgType = 'error';
            }
        }
    }
}

function gradeFromPct(float $pct): string {
    return match(true) {
        $pct >= 90 => 'A+',
        $pct >= 80 => 'A',
        $pct >= 70 => 'B+',
        $pct >= 60 => 'B',
        $pct >= 50 => 'C',
        $pct >= 40 => 'D',
        default    => 'F',
    };
}

// ── Load enrolled students for selected exam's class ──────────────────────
$students    = [];
$selectedExam = null;

if ($examId) {
    $exStmt = $db->prepare("SELECT * FROM exams WHERE id=:id LIMIT 1");
    $exStmt->execute([':id' => $examId]);
    $selectedExam = $exStmt->fetch();

    if ($selectedExam) {
        // Enforce: only students ENROLLED in this exam's class get grades
        $stuStmt = $db->prepare(
            "SELECT s.id, s.first_name, s.last_name, s.student_id AS reg_no,
                    COALESCE(g.marks_obtained, '') AS existing_marks,
                    COALESCE(g.grade, '')          AS existing_grade
             FROM   enrollments e
             JOIN   students    s ON s.id = e.student_id
             LEFT JOIN grades   g
                   ON  g.student_id = s.id
                   AND g.exam_id    = :eid
             WHERE  e.class_id = :cid
               AND  e.status   = 'active'
               AND  s.status   = 'active'
             ORDER  BY s.last_name, s.first_name"
        );
        $stuStmt->execute([
            ':eid' => $examId,
            ':cid' => $selectedExam['class_id'],
        ]);
        $students = $stuStmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Grades — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">

        <div class="page-header">
            <h1>📝 Enter Grades</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Exam picker -->
        <div class="card" style="margin-bottom:20px;">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="form-group" style="margin:0;">
                        <label>Select Exam</label>
                        <select name="exam_id" class="form-control" onchange="this.form.submit()">
                            <option value="">— Choose an exam —</option>
                            <?php foreach ($exams as $ex): ?>
                                <option value="<?php echo $ex['id']; ?>"
                                    <?php echo ($ex['id'] == $examId) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(
                                        $ex['exam_name']
                                        . ' | ' . $ex['class_name'] . ' ' . $ex['section']
                                        . ($ex['subject_name'] ? ' | ' . $ex['subject_name'] : '')
                                        . ' | ' . date('M d Y', strtotime($ex['exam_date']))
                                    ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($examId && $selectedExam): ?>
        <div class="card">
            <div class="card-header">
                <h2><?php echo htmlspecialchars($selectedExam['exam_name']); ?></h2>
                <span>Total marks: <?php echo (int)$selectedExam['total_marks']; ?></span>
            </div>
            <div class="card-body">

                <?php if (empty($students)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">👥</div>
                        <h3>No enrolled students</h3>
                        <p>No active students are enrolled in this class.</p>
                    </div>
                <?php else: ?>

                <form method="POST" action="">
                    <?php csrfField(); ?>
                    <input type="hidden" name="exam_id" value="<?php echo $examId; ?>">

                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Reg No.</th>
                                <th>Student</th>
                                <th style="width:160px;">Marks (/ <?php echo (int)$selectedExam['total_marks']; ?>)</th>
                                <th style="width:80px;">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($students as $i => $stu): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($stu['reg_no']); ?></td>
                                <td><?php echo htmlspecialchars($stu['first_name'] . ' ' . $stu['last_name']); ?></td>
                                <td>
                                    <input type="number"
                                           name="grades[<?php echo $stu['id']; ?>]"
                                           class="form-control grade-input"
                                           data-max="<?php echo (int)$selectedExam['total_marks']; ?>"
                                           value="<?php echo htmlspecialchars($stu['existing_marks']); ?>"
                                           min="0"
                                           max="<?php echo (int)$selectedExam['total_marks']; ?>"
                                           step="0.5"
                                           style="width:100px;">
                                </td>
                                <td>
                                    <span class="grade-preview badge badge-active">
                                        <?php echo $stu['existing_grade'] ?: '—'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div style="margin-top:20px;">
                        <button type="submit" class="btn btn-primary btn-lg">💾 Save Grades</button>
                    </div>
                </form>

                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </main>
</div>

<script src="../assets/js/main.js"></script>
<script>
// Live grade preview as marks are typed
const gradeScale = [
    [90,'A+'],[80,'A'],[70,'B+'],[60,'B'],[50,'C'],[40,'D'],[0,'F']
];
function pctToGrade(pct) {
    for (const [threshold, g] of gradeScale) {
        if (pct >= threshold) return g;
    }
    return 'F';
}
document.querySelectorAll('.grade-input').forEach(input => {
    const preview = input.closest('tr').querySelector('.grade-preview');
    input.addEventListener('input', () => {
        const max = parseFloat(input.dataset.max) || 100;
        const val = parseFloat(input.value);
        if (!isNaN(val) && max > 0) {
            preview.textContent = pctToGrade((val / max) * 100);
        } else {
            preview.textContent = '—';
        }
    });
});
</script>
</body>
</html>

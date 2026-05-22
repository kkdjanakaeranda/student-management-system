<?php
// attendance/mark.php — enrollment-enforced replacement
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

// ── Load classes scoped to this teacher (admin sees all) ──────────────────
$classStmt = $db->prepare(
    "SELECT c.id, c.name, c.section
     FROM   classes c
     WHERE  c.status = 'active'
       AND  (:is_admin = 1 OR c.teacher_id = :tid)
     ORDER  BY c.name"
);
$classStmt->execute([':is_admin' => (int)$isAdmin, ':tid' => $tid]);
$classes = $classStmt->fetchAll();

$classId      = (int) ($_GET['class_id'] ?? ($_POST['class_id'] ?? 0));
$selectedDate = $_POST['attendance_date'] ?? $_GET['date'] ?? date('Y-m-d');
$message      = '';
$messageType  = '';

// Verify teacher owns this class (skip for admin)
if ($classId && !$isAdmin) {
    $ownStmt = $db->prepare(
        "SELECT id FROM classes WHERE id=:id AND teacher_id=:tid LIMIT 1"
    );
    $ownStmt->execute([':id' => $classId, ':tid' => $tid]);
    if (!$ownStmt->fetch()) {
        $message     = 'You do not have permission to mark attendance for this class.';
        $messageType = 'error';
        $classId     = 0;
    }
}

// ── Handle POST — save attendance ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $classId && $message === '') {
    verifyCsrf();

    $date     = $_POST['attendance_date'] ?? date('Y-m-d');
    $records  = $_POST['attendance'] ?? [];   // [student_id => status]

    if (empty($records)) {
        $message     = 'No attendance data submitted.';
        $messageType = 'error';
    } else {
        // Delete existing records for this class+date then re-insert (idempotent)
        $db->beginTransaction();
        try {
            $delStmt = $db->prepare(
                "DELETE FROM attendance WHERE class_id=:cid AND date=:date"
            );
            $delStmt->execute([':cid' => $classId, ':date' => $date]);

            $insStmt = $db->prepare(
                "INSERT INTO attendance (student_id, class_id, date, status, created_by)
                 VALUES (:sid, :cid, :date, :status, :uid)"
            );

            foreach ($records as $studentId => $status) {
                $studentId = (int) $studentId;
                $status    = in_array($status, ['present','absent','late','excused'], true)
                             ? $status : 'absent';
                $insStmt->execute([
                    ':sid'    => $studentId,
                    ':cid'    => $classId,
                    ':date'   => $date,
                    ':status' => $status,
                    ':uid'    => $_SESSION['user_id'],
                ]);
            }
            $db->commit();
            $message     = 'Attendance saved successfully for ' . date('M d, Y', strtotime($date)) . '.';
            $messageType = 'success';
        } catch (Exception $e) {
            $db->rollBack();
            $message     = 'Failed to save attendance. Please try again.';
            $messageType = 'error';
        }
    }
}

// ── Load enrolled students for selected class ──────────────────────────────
$students = [];
if ($classId) {
    // ONLY students enrolled in this class — enrollment check enforced here
    $stuStmt = $db->prepare(
        "SELECT s.id, s.first_name, s.last_name, s.student_id AS reg_no,
                COALESCE(a.status, '') AS existing_status
         FROM   enrollments e
         JOIN   students    s ON s.id = e.student_id
         LEFT JOIN attendance a
               ON  a.student_id = s.id
               AND a.class_id   = :cid
               AND a.date       = :date
         WHERE  e.class_id = :cid2
           AND  e.status   = 'active'
           AND  s.status   = 'active'
         ORDER  BY s.last_name, s.first_name"
    );
    $stuStmt->execute([
        ':cid'   => $classId,
        ':date'  => $selectedDate,
        ':cid2'  => $classId,
    ]);
    $students = $stuStmt->fetchAll();
}

// ── Load selected class details ────────────────────────────────────────────
$selectedClass = null;
if ($classId) {
    $scStmt = $db->prepare("SELECT * FROM classes WHERE id=:id LIMIT 1");
    $scStmt->execute([':id' => $classId]);
    $selectedClass = $scStmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">

        <div class="page-header">
            <h1>✅ Mark Attendance</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Class + date picker -->
        <div class="card" style="margin-bottom:20px;">
            <div class="card-body">
                <form method="GET" action="" style="display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap;">
                    <div class="form-group" style="margin:0;flex:1;min-width:200px;">
                        <label>Class</label>
                        <select name="class_id" class="form-control" onchange="this.form.submit()">
                            <option value="">— Select a class —</option>
                            <?php foreach ($classes as $cls): ?>
                                <option value="<?php echo $cls['id']; ?>"
                                    <?php echo ($cls['id'] == $classId) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cls['name'] . ' (' . $cls['section'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control"
                               value="<?php echo htmlspecialchars($selectedDate); ?>"
                               max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Load</button>
                </form>
            </div>
        </div>

        <?php if ($classId && $selectedClass): ?>
        <div class="card">
            <div class="card-header">
                <h2>
                    <?php echo htmlspecialchars($selectedClass['name']); ?>
                    — <?php echo date('l, M d Y', strtotime($selectedDate)); ?>
                </h2>
                <span class="badge badge-active"><?php echo count($students); ?> enrolled students</span>
            </div>
            <div class="card-body">

                <?php if (empty($students)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">👥</div>
                        <h3>No enrolled students</h3>
                        <p>No active students are enrolled in this class. Enrol students first.</p>
                        <?php if ($isAdmin): ?>
                            <a href="../students/index.php" class="btn btn-primary">Manage Enrolments</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>

                <form method="POST" action="">
                    <?php csrfField(); ?>
                    <input type="hidden" name="class_id"        value="<?php echo $classId; ?>">
                    <input type="hidden" name="attendance_date" value="<?php echo htmlspecialchars($selectedDate); ?>">

                    <!-- Bulk mark buttons -->
                    <div style="margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;">
                        <button type="button" class="btn btn-sm btn-success" onclick="markAll('present')">✅ All Present</button>
                        <button type="button" class="btn btn-sm btn-danger"  onclick="markAll('absent')">❌ All Absent</button>
                    </div>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Reg No.</th>
                                <th>Student</th>
                                <th style="width:280px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($students as $i => $stu):
                            $existing = $stu['existing_status'] ?: 'present';
                            $id       = $stu['id'];
                        ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($stu['reg_no']); ?></td>
                                <td><?php echo htmlspecialchars($stu['first_name'] . ' ' . $stu['last_name']); ?></td>
                                <td>
                                    <div class="att-radio-group" style="display:flex;gap:8px;flex-wrap:wrap;">
                                        <?php foreach (['present','absent','late','excused'] as $s): ?>
                                        <label style="display:flex;align-items:center;gap:4px;cursor:pointer;">
                                            <input type="radio"
                                                   name="attendance[<?php echo $id; ?>]"
                                                   value="<?php echo $s; ?>"
                                                   <?php echo ($existing === $s) ? 'checked' : ''; ?>>
                                            <?php echo ucfirst($s); ?>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div style="margin-top:20px;">
                        <button type="submit" class="btn btn-primary btn-lg">💾 Save Attendance</button>
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
function markAll(status) {
    document.querySelectorAll('input[type="radio"][value="' + status + '"]')
        .forEach(r => r.checked = true);
}
</script>
</body>
</html>

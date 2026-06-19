<?php
require_once '../config/config.php';
if (!hasRole('admin') && !hasRole('teacher')) {
    header('Location: ../dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$isAdmin = hasRole('admin');
$teacherId = currentTeacherRowId($db);

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit();
}

$examStmt = $db->prepare(
    "SELECT e.*, c.teacher_id
     FROM exams e
     JOIN classes c ON c.id = e.class_id
     WHERE e.id = :id
     LIMIT 1"
);
$examStmt->execute([':id' => $id]);
$exam = $examStmt->fetch();

if (!$exam || (!$isAdmin && (int)$exam['teacher_id'] !== $teacherId)) {
    header('Location: index.php');
    exit();
}

$classQuery = "SELECT * FROM classes WHERE status = 'active'"
    . (!$isAdmin ? " AND teacher_id = :teacher_id" : "")
    . " ORDER BY class_name";
$classStmt = $db->prepare($classQuery);
$classStmt->execute(!$isAdmin ? [':teacher_id' => $teacherId] : []);
$classes = $classStmt->fetchAll();

$subjectQuery = "SELECT s.* FROM subjects s JOIN classes c ON c.id = s.class_id"
    . (!$isAdmin ? " WHERE c.teacher_id = :teacher_id" : "")
    . " ORDER BY s.subject_name";
$subjectStmt = $db->prepare($subjectQuery);
$subjectStmt->execute(!$isAdmin ? [':teacher_id' => $teacherId] : []);
$subjects = $subjectStmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $examName = trim($_POST['exam_name'] ?? '');
    $examType = $_POST['exam_type'] ?? '';
    $classId = (int)($_POST['class_id'] ?? 0);
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $examDate = $_POST['exam_date'] ?? '';
    $totalMarks = $_POST['total_marks'] ?? '';
    $duration = trim($_POST['duration'] ?? '');

    if ($examName === '' || $examType === '' || !$classId || !$subjectId || $examDate === '' || $totalMarks === '') {
        $error = 'Please fill in all required fields.';
    }

    if (!$error && !$isAdmin) {
        $allowed = $db->prepare("SELECT id FROM classes WHERE id = :class_id AND teacher_id = :teacher_id LIMIT 1");
        $allowed->execute([':class_id' => $classId, ':teacher_id' => $teacherId]);
        if (!$allowed->fetch()) {
            $error = 'You do not have permission to update exams for this class.';
        }
    }

    if (!$error) {
        $stmt = $db->prepare(
            "UPDATE exams
             SET exam_name = :exam_name,
                 exam_type = :exam_type,
                 class_id = :class_id,
                 subject_id = :subject_id,
                 exam_date = :exam_date,
                 total_marks = :total_marks,
                 duration = :duration
             WHERE id = :id"
        );
        $stmt->execute([
            ':exam_name' => $examName,
            ':exam_type' => $examType,
            ':class_id' => $classId,
            ':subject_id' => $subjectId,
            ':exam_date' => $examDate,
            ':total_marks' => $totalMarks,
            ':duration' => $duration,
            ':id' => $id,
        ]);

        header('Location: index.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exam - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo filemtime('../assets/css/style.css'); ?>">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1>Edit Exam</h1>
                    <p class="page-description">Update exam information</p>
                </div>
                <a href="index.php" class="btn btn-secondary">Back to List</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>Exam Information</h2>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    <form method="POST">
                        <?php csrfField(); ?>

                        <div class="form-group">
                            <label for="exam_name">Exam Name <span>*</span></label>
                            <input class="form-control" type="text" id="exam_name" name="exam_name" value="<?php echo e($exam['exam_name']); ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="exam_type">Exam Type <span>*</span></label>
                                <select class="form-control" id="exam_type" name="exam_type" required>
                                    <?php foreach (['midterm', 'final', 'quiz', 'assignment'] as $type): ?>
                                        <option value="<?php echo $type; ?>" <?php echo $exam['exam_type'] === $type ? 'selected' : ''; ?>><?php echo ucfirst($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="exam_date">Exam Date <span>*</span></label>
                                <input class="form-control" type="date" id="exam_date" name="exam_date" value="<?php echo e($exam['exam_date']); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="class_id">Class <span>*</span></label>
                                <select class="form-control" id="class_id" name="class_id" required>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>" <?php echo (int)$exam['class_id'] === (int)$class['id'] ? 'selected' : ''; ?>>
                                            <?php echo e($class['class_name'] . ' - ' . ($class['section'] ?? '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="subject_id">Subject <span>*</span></label>
                                <select class="form-control" id="subject_id" name="subject_id" required>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject['id']; ?>" <?php echo (int)$exam['subject_id'] === (int)$subject['id'] ? 'selected' : ''; ?>>
                                            <?php echo e($subject['subject_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="total_marks">Total Marks <span>*</span></label>
                                <input class="form-control" type="number" id="total_marks" name="total_marks" value="<?php echo e((string)$exam['total_marks']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="duration">Duration</label>
                                <input class="form-control" type="text" id="duration" name="duration" value="<?php echo e($exam['duration']); ?>" placeholder="e.g., 2 hours">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js?v=<?php echo filemtime('../assets/js/main.js'); ?>"></script>
</body>
</html>

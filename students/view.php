<?php
require_once '../config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

$query = "SELECT s.*, u.email, u.username FROM students s 
          LEFT JOIN users u ON s.user_id = u.id 
          WHERE s.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$student = $stmt->fetch();

if (!$student) {
    header('Location: index.php');
    exit();
}

if (!canAccessStudent($db, (int)$student['id'])) {
    header('Location: index.php');
    exit();
}

$message = '';
$messageType = '';
if (hasRole('admin') && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $classId = (int)($_POST['class_id'] ?? 0);
    if ($classId > 0) {
        try {
            $enroll = $db->prepare(
                "INSERT INTO enrollments (student_id, class_id, enrollment_date, status)
                 VALUES (:student_id, :class_id, CURDATE(), 'enrolled')"
            );
            $enroll->execute([':student_id' => $student['id'], ':class_id' => $classId]);
            $message = 'Student enrolled successfully.';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Could not enroll student: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$enrollmentsStmt = $db->prepare(
    "SELECT e.*, c.class_name, c.section, co.course_name
     FROM enrollments e
     JOIN classes c ON c.id = e.class_id
     LEFT JOIN courses co ON co.id = c.course_id
     WHERE e.student_id = :student_id
     ORDER BY e.enrollment_date DESC, e.id DESC"
);
$enrollmentsStmt->execute([':student_id' => $student['id']]);
$enrollments = $enrollmentsStmt->fetchAll();

$classes = [];
if (hasRole('admin')) {
    $classesStmt = $db->prepare("SELECT id, class_name, section FROM classes WHERE status = 'active' ORDER BY class_name");
    $classesStmt->execute();
    $classes = $classesStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - <?php echo SITE_NAME; ?></title>
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
                    <h1>👨‍🎓 Student Details</h1>
                    <p>Complete information about the student</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <?php if (hasRole('admin')): ?>
                        <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-warning">✏️ Edit</a>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-secondary">← Back</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>"><?php echo e($message); ?></div>
            <?php endif; ?>
            
            <div class="content-grid">
                <div class="card">
                    <div class="card-header">
                        <h2>📸 Photo</h2>
                    </div>
                    <div class="card-body" style="text-align: center;">
                        <img src="<?php echo $student['photo'] ? '../uploads/students/' . $student['photo'] : '../assets/images/default-avatar.svg'; ?>" 
                             alt="Student Photo" 
                             style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 5px solid var(--primary-color);">
                        <h3 style="margin-top: 20px;"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h3>
                        <p class="badge badge-<?php echo $student['status']; ?>" style="margin-top: 10px;">
                            <?php echo ucfirst($student['status']); ?>
                        </p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>ℹ️ Personal Information</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; gap: 15px;">
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Student ID:</strong>
                                <span><?php echo htmlspecialchars($student['student_id']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Date of Birth:</strong>
                                <span><?php echo date('F d, Y', strtotime($student['date_of_birth'])); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Gender:</strong>
                                <span><?php echo htmlspecialchars($student['gender']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding:  12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Phone:</strong>
                                <span><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></span>
                            </div>
                            <div style="display: flex; justify-content:  space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Email:</strong>
                                <span><?php echo htmlspecialchars($student['email']); ?></span>
                            </div>
                            <div style="padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Address:</strong>
                                <p style="margin-top: 8px;"><?php echo htmlspecialchars($student['address'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="content-grid">
                <div class="card">
                    <div class="card-header">
                        <h2>👨‍👩‍👧 Guardian Information</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; gap: 15px;">
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Guardian Name:</strong>
                                <span><?php echo htmlspecialchars($student['guardian_name'] ?? 'N/A'); ?></span>
                            </div>
                            <div style="display: flex; justify-content:  space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Guardian Phone:</strong>
                                <span><?php echo htmlspecialchars($student['guardian_phone'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>🎓 Academic Information</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; gap: 15px;">
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Admission Date:</strong>
                                <span><?php echo date('F d, Y', strtotime($student['admission_date'])); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Status:</strong>
                                <span class="badge badge-<?php echo $student['status']; ?>">
                                    <?php echo ucfirst($student['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 24px;">
                <div class="card-header"><h2>Enrollments</h2></div>
                <div class="card-body">
                    <?php if (hasRole('admin')): ?>
                        <form method="POST" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;margin-bottom:20px;">
                            <?php csrfField(); ?>
                            <div class="form-group" style="min-width:240px;margin:0;">
                                <label for="class_id">Class</label>
                                <select id="class_id" name="class_id" required>
                                    <option value="">Select class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>">
                                            <?php echo e($class['class_name'] . ' ' . ($class['section'] ?? '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Enroll</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($enrollments): ?>
                        <table class="data-table">
                            <thead><tr><th>Class</th><th>Course</th><th>Date</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($enrollments as $enrollment): ?>
                                    <tr>
                                        <td><?php echo e($enrollment['class_name'] . ' ' . ($enrollment['section'] ?? '')); ?></td>
                                        <td><?php echo e($enrollment['course_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo e($enrollment['enrollment_date']); ?></td>
                                        <td><?php echo e($enrollment['status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <h3>No Enrollments</h3>
                            <p>This student is not enrolled in any class yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>

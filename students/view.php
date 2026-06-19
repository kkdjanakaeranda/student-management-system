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
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo filemtime('../assets/css/style.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .person-view .profile-card .profile-summary {
            display: grid;
            grid-template-columns: 96px minmax(0, 1fr);
            align-items: center;
            gap: 1.25rem;
            padding: 1.25rem 1.5rem;
            min-height: auto;
        }

        .person-view .profile-card .profile-summary-photo {
            width: 96px;
            height: 96px;
            border-radius: 12px;
            object-fit: cover;
        }

        .person-view .profile-card .profile-summary-content h2 {
            margin: 0 0 0.25rem;
            font-size: 1.25rem;
        }

        .person-view .profile-card .profile-summary-meta {
            margin: 0 0 0.625rem;
        }

        .person-view .profile-card .details-grid {
            padding: 1.25rem 1.5rem 1.5rem;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        .person-view .enrollment-form {
            display: grid;
            grid-template-columns: minmax(240px, 1fr) auto;
            align-items: end;
            gap: 0.75rem;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .person-view .enrollment-form .form-group {
            margin: 0;
        }

        @media (max-width: 768px) {
            .person-view .profile-card .profile-summary,
            .person-view .enrollment-form {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content person-view">
            <div class="page-header">
                <div>
                    <h1>Student Details</h1>
                    <p class="page-description">View student information and class enrollments</p>
                </div>
                <div class="action-buttons">
                    <?php if (hasRole('admin')): ?>
                        <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-warning">Edit</a>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-secondary">Back to List</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>"><?php echo e($message); ?></div>
            <?php endif; ?>
            
            <div class="card profile-card">
                <div class="profile-summary">
                    <img src="<?php echo $student['photo'] ? '../uploads/students/' . $student['photo'] : '../assets/images/default-avatar.svg'; ?>" 
                         alt="Student Photo" 
                         class="profile-summary-photo">
                    <div class="profile-summary-content">
                        <h2><?php echo e($student['first_name'] . ' ' . $student['last_name']); ?></h2>
                        <p class="profile-summary-meta"><?php echo e($student['student_id']); ?><?php echo !empty($student['email']) ? ' - ' . e($student['email']) : ''; ?></p>
                        <span class="badge badge-<?php echo e($student['status']); ?>"><?php echo ucfirst(e($student['status'])); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="details-grid">
                        <div class="detail-item">
                            <label>Student ID</label>
                            <p><?php echo e($student['student_id']); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Date of Birth</label>
                            <p><?php echo !empty($student['date_of_birth']) ? date('F d, Y', strtotime($student['date_of_birth'])) : 'N/A'; ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Gender</label>
                            <p><?php echo e($student['gender'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Phone</label>
                            <p><?php echo e($student['phone'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Email</label>
                            <p><?php echo e($student['email'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Admission Date</label>
                            <p><?php echo !empty($student['admission_date']) ? date('F d, Y', strtotime($student['admission_date'])) : 'N/A'; ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Guardian Name</label>
                            <p><?php echo e($student['guardian_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Guardian Phone</label>
                            <p><?php echo e($student['guardian_phone'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item detail-item-full">
                            <label>Address</label>
                            <p><?php echo e($student['address'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card enrollment-card">
                <div class="card-header"><h2>Enrollments</h2></div>
                <div class="card-body">
                    <?php if (hasRole('admin')): ?>
                        <form method="POST" class="attendance-toolbar enrollment-form">
                            <?php csrfField(); ?>
                            <div class="form-group" style="min-width:240px;margin:0;">
                                <label for="class_id">Class</label>
                                <select id="class_id" name="class_id" class="form-control" required>
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
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead><tr><th>Class</th><th>Course</th><th>Date</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php foreach ($enrollments as $enrollment): ?>
                                        <tr>
                                            <td><?php echo e($enrollment['class_name'] . ' ' . ($enrollment['section'] ?? '')); ?></td>
                                            <td><?php echo e($enrollment['course_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo e($enrollment['enrollment_date']); ?></td>
                                            <td><span class="badge badge-<?php echo e($enrollment['status']); ?>"><?php echo e($enrollment['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
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
    
    <script src="../assets/js/main.js?v=<?php echo filemtime('../assets/js/main.js'); ?>"></script>
</body>
</html>

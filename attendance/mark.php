<?php
require_once '../config/config.php';
if (!hasRole('admin') && !hasRole('teacher')) {
    header('Location: ../dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get classes
$query = "SELECT * FROM classes WHERE status = 'active' ORDER BY class_name";
$stmt = $db->prepare($query);
$stmt->execute();
$classes = $stmt->fetchAll();

$students = [];
$selected_class = null;
$selected_date = date('Y-m-d');

if (isset($_GET['class_id']) && isset($_GET['date'])) {
    $selected_class = $_GET['class_id'];
    $selected_date = $_GET['date'];
    
    // Get students in this class
    $query = "SELECT s.* FROM students s 
              JOIN enrollments e ON s.id = e.student_id 
              WHERE e.class_id = :class_id AND s.status = 'active'
              ORDER BY s.first_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':class_id', $selected_class);
    $stmt->execute();
    $students = $stmt->fetchAll();
    
    // Get existing attendance
    $query = "SELECT * FROM attendance WHERE class_id = :class_id AND date = :date";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':class_id', $selected_class);
    $stmt->bindParam(':date', $selected_date);
    $stmt->execute();
    $existing_attendance = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $attendance_data = $_POST['attendance'] ?? [];
    
    try {
        $db->beginTransaction();
        
        // Delete existing attendance for this class and date
        $query = "DELETE FROM attendance WHERE class_id = :class_id AND date = : date";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        
        // Insert new attendance records
        foreach ($attendance_data as $student_id => $data) {
            $status = $data['status'];
            $remarks = $data['remarks'] ?? '';
            
            $query = "INSERT INTO attendance (student_id, class_id, date, status, remarks) 
                      VALUES (:student_id, :class_id, :date, :status, :remarks)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':class_id', $class_id);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':remarks', $remarks);
            $stmt->execute();
        }
        
        $db->commit();
        header('Location: index.php');
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - <?php echo SITE_NAME; ?></title>
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
                    <h1>✅ Mark Attendance</h1>
                    <p>Select class and date to mark attendance</p>
                </div>
                <a href="index.php" class="btn btn-secondary">← Back</a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>📋 Select Class and Date</h2>
                </div>
                <div class="card-body">
                    <form method="GET" class="form-row" style="margin-bottom: 30px;">
                        <div class="form-group">
                            <label for="class_id">Class <span>*</span></label>
                            <select id="class_id" name="class_id" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo $selected_class == $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['class_name'] . ' - ' . ($class['section'] ?? '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date">Date <span>*</span></label>
                            <input type="date" id="date" name="date" value="<?php echo $selected_date; ?>" required>
                        </div>
                        
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary">🔍 Load Students</button>
                        </div>
                    </form>
                    
                    <?php if (! empty($students)): ?>
                        <form method="POST">
                        <?php echo csrfField(); ?>
                            <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
                            <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
                            
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <?php
                                            $existing = $existing_attendance[$student['id']] ?? null;
                                            $current_status = $existing ? $existing['status'] : 'present';
                                            $current_remarks = $existing ? $existing['remarks'] : '';
                                            ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($student['first_name'] .  ' ' . $student['last_name']); ?></td>
                                                <td>
                                                    <select name="attendance[<?php echo $student['id']; ?>][status]" required style="padding: 8px; border: 2px solid var(--border-color); border-radius: 8px;">
                                                        <option value="present" <?php echo $current_status == 'present' ? 'selected' : ''; ?>>Present</option>
                                                        <option value="absent" <?php echo $current_status == 'absent' ? 'selected' : ''; ?>>Absent</option>
                                                        <option value="late" <?php echo $current_status == 'late' ?  'selected' : ''; ?>>Late</option>
                                                        <option value="excused" <?php echo $current_status == 'excused' ? 'selected' : ''; ?>>Excused</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" name="attendance[<?php echo $student['id']; ?>][remarks]" 
                                                           value="<?php echo htmlspecialchars($current_remarks); ?>"
                                                           placeholder="Optional remarks"
                                                           style="padding: 8px; border: 2px solid var(--border-color); border-radius: 8px; width: 100%;">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div style="margin-top: 30px; display: flex; gap: 15px;">
                                <button type="submit" class="btn btn-primary">💾 Save Attendance</button>
                                <a href="index.php" class="btn btn-secondary">❌ Cancel</a>
                            </div>
                        </form>
                    <?php elseif ($selected_class): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">👨‍🎓</div>
                            <h3>No Students Found</h3>
                            <p>There are no students enrolled in this class.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
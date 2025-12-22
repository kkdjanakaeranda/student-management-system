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

// Get subjects
$query = "SELECT * FROM subjects ORDER BY subject_name";
$stmt = $db->prepare($query);
$stmt->execute();
$subjects = $stmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $exam_name = $_POST['exam_name'];
    $exam_type = $_POST['exam_type'];
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $exam_date = $_POST['exam_date'];
    $total_marks = $_POST['total_marks'];
    $duration = $_POST['duration'];
    
    try {
        $query = "INSERT INTO exams (exam_name, exam_type, class_id, subject_id, exam_date, total_marks, duration) 
                  VALUES (:exam_name, :exam_type, :class_id, :subject_id, :exam_date, :total_marks, :duration)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':exam_name', $exam_name);
        $stmt->bindParam(':exam_type', $exam_type);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->bindParam(':exam_date', $exam_date);
        $stmt->bindParam(':total_marks', $total_marks);
        $stmt->bindParam(':duration', $duration);
        $stmt->execute();
        
        header('Location: index.php');
        exit();
    } catch (Exception $e) {
        $error = 'Error: ' .  $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Exam - <?php echo SITE_NAME; ?></title>
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
                    <h1>➕ Add New Exam</h1>
                    <p>Schedule a new exam</p>
                </div>
                <a href="index.php" class="btn btn-secondary">← Back</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" class="form-grid">
                        <?php echo csrfField(); ?>
                        <div class="form-section">
                            <h3>📋 Exam Information</h3>
                            
                            <div class="form-group">
                                <label for="exam_name">Exam Name <span>*</span></label>
                                <input type="text" id="exam_name" name="exam_name" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="exam_type">Exam Type <span>*</span></label>
                                    <select id="exam_type" name="exam_type" required>
                                        <option value="">Select Type</option>
                                        <option value="midterm">Midterm</option>
                                        <option value="final">Final</option>
                                        <option value="quiz">Quiz</option>
                                        <option value="assignment">Assignment</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="exam_date">Exam Date <span>*</span></label>
                                    <input type="date" id="exam_date" name="exam_date" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="class_id">Class <span>*</span></label>
                                    <select id="class_id" name="class_id" required>
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>">
                                                <?php echo htmlspecialchars($class['class_name'] . ' - ' . ($class['section'] ?? '')); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="subject_id">Subject <span>*</span></label>
                                    <select id="subject_id" name="subject_id" required>
                                        <option value="">Select Subject</option>
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?php echo $subject['id']; ?>">
                                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="total_marks">Total Marks <span>*</span></label>
                                    <input type="number" id="total_marks" name="total_marks" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="duration">Duration</label>
                                    <input type="text" id="duration" name="duration" placeholder="e. g., 2 hours">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Add Exam</button>
                            <a href="index.php" class="btn btn-secondary">❌ Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
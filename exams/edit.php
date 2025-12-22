<?php
require_once '../config/config.php';
requireLogin();

$success = '';
$error = '';

// Get exam ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Exam ID is required';
    header('Location: index.php');
    exit();
}

$exam_id = sanitizeInt($_GET['id']);

// Get exam details
$database = new Database();
$db = $database->getConnection();

$query = "SELECT e.*, s.name as subject_name, c.name as class_name 
          FROM exams e 
          LEFT JOIN subjects s ON e.subject_id = s.id 
          LEFT JOIN classes c ON e.class_id = c.id 
          WHERE e.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $exam_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = 'Exam not found';
    header('Location: index.php');
    exit();
}

$exam = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    // Validate and sanitize inputs
    $name = sanitizeString($_POST['name'] ?? '');
    $exam_type = sanitizeString($_POST['exam_type'] ?? '');
    $subject_id = sanitizeInt($_POST['subject_id'] ?? '');
    $class_id = sanitizeInt($_POST['class_id'] ?? '');
    $exam_date = sanitizeString($_POST['exam_date'] ?? '');
    $start_time = sanitizeString($_POST['start_time'] ?? '');
    $end_time = sanitizeString($_POST['end_time'] ?? '');
    $total_marks = sanitizeInt($_POST['total_marks'] ?? '');
    $passing_marks = sanitizeInt($_POST['passing_marks'] ?? '');
    $description = sanitizeString($_POST['description'] ?? '');
    
    // Validation
    $errors = [];
    
    if ($err = validateRequired($name, 'Exam name')) $errors[] = $err;
    if ($err = validateRequired($exam_type, 'Exam type')) $errors[] = $err;
    if ($err = validateRequired($subject_id, 'Subject')) $errors[] = $err;
    if ($err = validateRequired($class_id, 'Class')) $errors[] = $err;
    if ($err = validateRequired($exam_date, 'Exam date')) $errors[] = $err;
    if ($err = validateDate($exam_date, 'Y-m-d', 'Exam date')) $errors[] = $err;
    if ($err = validateRequired($total_marks, 'Total marks')) $errors[] = $err;
    if ($err = validateRequired($passing_marks, 'Passing marks')) $errors[] = $err;
    
    if (empty($errors)) {
        try {
            $query = "UPDATE exams SET 
                      name = :name, 
                      exam_type = :exam_type, 
                      subject_id = :subject_id, 
                      class_id = :class_id, 
                      exam_date = :exam_date, 
                      start_time = :start_time, 
                      end_time = :end_time, 
                      total_marks = :total_marks, 
                      passing_marks = :passing_marks, 
                      description = :description, 
                      updated_at = NOW() 
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':exam_type', $exam_type);
            $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
            $stmt->bindParam(':exam_date', $exam_date);
            $stmt->bindParam(':start_time', $start_time);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->bindParam(':total_marks', $total_marks, PDO::PARAM_INT);
            $stmt->bindParam(':passing_marks', $passing_marks, PDO::PARAM_INT);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':id', $exam_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Exam updated successfully!';
                header('Location: index.php');
                exit();
            } else {
                $error = 'Failed to update exam';
            }
        } catch (PDOException $e) {
            error_log("Update exam error: " . $e->getMessage());
            $error = 'An error occurred while updating the exam';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

// Get subjects list
$query = "SELECT id, name FROM subjects WHERE status = 'active' ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$subjects = $stmt->fetchAll();

// Get classes list
$query = "SELECT id, name FROM classes WHERE status = 'active' ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$classes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exam - <?php echo SITE_NAME; ?></title>
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
                    <h1>✏️ Edit Exam</h1>
                    <p>Update exam details</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-secondary">↩️ Back to Exams</a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Exam Information</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php echo csrfField(); ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Exam Name *</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($exam['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="exam_type">Exam Type *</label>
                                <select id="exam_type" name="exam_type" required>
                                    <option value="">Select Type</option>
                                    <option value="midterm" <?php echo $exam['exam_type'] === 'midterm' ? 'selected' : ''; ?>>Midterm</option>
                                    <option value="final" <?php echo $exam['exam_type'] === 'final' ? 'selected' : ''; ?>>Final</option>
                                    <option value="quiz" <?php echo $exam['exam_type'] === 'quiz' ? 'selected' : ''; ?>>Quiz</option>
                                    <option value="assignment" <?php echo $exam['exam_type'] === 'assignment' ? 'selected' : ''; ?>>Assignment</option>
                                    <option value="practical" <?php echo $exam['exam_type'] === 'practical' ? 'selected' : ''; ?>>Practical</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="subject_id">Subject *</label>
                                <select id="subject_id" name="subject_id" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject['id']; ?>" <?php echo $exam['subject_id'] == $subject['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="class_id">Class *</label>
                                <select id="class_id" name="class_id" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>" <?php echo $exam['class_id'] == $class['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="exam_date">Exam Date *</label>
                                <input type="date" id="exam_date" name="exam_date" value="<?php echo htmlspecialchars($exam['exam_date']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="start_time">Start Time</label>
                                <input type="time" id="start_time" name="start_time" value="<?php echo htmlspecialchars($exam['start_time']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="end_time">End Time</label>
                                <input type="time" id="end_time" name="end_time" value="<?php echo htmlspecialchars($exam['end_time']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="total_marks">Total Marks *</label>
                                <input type="number" id="total_marks" name="total_marks" min="1" value="<?php echo htmlspecialchars($exam['total_marks']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="passing_marks">Passing Marks *</label>
                                <input type="number" id="passing_marks" name="passing_marks" min="1" value="<?php echo htmlspecialchars($exam['passing_marks']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($exam['description']); ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">💾 Update Exam</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>

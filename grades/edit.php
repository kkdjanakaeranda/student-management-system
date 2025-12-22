<?php
require_once '../config/config.php';
requireLogin();

$success = '';
$error = '';

// Get grade ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Grade ID is required';
    header('Location: index.php');
    exit();
}

$grade_id = sanitizeInt($_GET['id']);

// Get grade details
$database = new Database();
$db = $database->getConnection();

$query = "SELECT g.*, s.first_name, s.last_name, s.student_id as student_code, 
          e.name as exam_name, e.total_marks as exam_total_marks, sub.name as subject_name 
          FROM grades g 
          JOIN students s ON g.student_id = s.id 
          JOIN exams e ON g.exam_id = e.id 
          JOIN subjects sub ON e.subject_id = sub.id 
          WHERE g.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $grade_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = 'Grade not found';
    header('Location: index.php');
    exit();
}

$grade = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    // Validate and sanitize inputs
    $marks_obtained = sanitizeFloat($_POST['marks_obtained'] ?? '');
    $remarks = sanitizeString($_POST['remarks'] ?? '');
    
    // Validation
    $errors = [];
    
    if ($err = validateRequired($marks_obtained, 'Marks obtained')) $errors[] = $err;
    if ($err = validateNumeric($marks_obtained, 'Marks obtained')) $errors[] = $err;
    
    // Check if marks don't exceed total marks
    if ($marks_obtained > $grade['exam_total_marks']) {
        $errors[] = "Marks obtained cannot exceed total marks ({$grade['exam_total_marks']})";
    }
    
    if ($marks_obtained < 0) {
        $errors[] = "Marks obtained cannot be negative";
    }
    
    if (empty($errors)) {
        // Calculate grade
        $calculated_grade = calculateGrade($marks_obtained, $grade['exam_total_marks']);
        
        try {
            $query = "UPDATE grades SET 
                      marks_obtained = :marks_obtained, 
                      grade = :grade, 
                      remarks = :remarks, 
                      updated_at = NOW() 
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':marks_obtained', $marks_obtained);
            $stmt->bindParam(':grade', $calculated_grade);
            $stmt->bindParam(':remarks', $remarks);
            $stmt->bindParam(':id', $grade_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Grade updated successfully!';
                header('Location: index.php');
                exit();
            } else {
                $error = 'Failed to update grade';
            }
        } catch (PDOException $e) {
            error_log("Update grade error: " . $e->getMessage());
            $error = 'An error occurred while updating the grade';
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Grade - <?php echo SITE_NAME; ?></title>
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
                    <h1>✏️ Edit Grade</h1>
                    <p>Update student grade</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-secondary">↩️ Back to Grades</a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Grade Information</h2>
                </div>
                <div class="card-body">
                    <div class="info-section" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                            <div>
                                <strong>Student:</strong><br>
                                <?php echo htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']); ?><br>
                                <small>(<?php echo htmlspecialchars($grade['student_code']); ?>)</small>
                            </div>
                            <div>
                                <strong>Exam:</strong><br>
                                <?php echo htmlspecialchars($grade['exam_name']); ?>
                            </div>
                            <div>
                                <strong>Subject:</strong><br>
                                <?php echo htmlspecialchars($grade['subject_name']); ?>
                            </div>
                            <div>
                                <strong>Total Marks:</strong><br>
                                <?php echo htmlspecialchars($grade['exam_total_marks']); ?>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="">
                        <?php echo csrfField(); ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="marks_obtained">Marks Obtained * (Max: <?php echo htmlspecialchars($grade['exam_total_marks']); ?>)</label>
                                <input type="number" 
                                       id="marks_obtained" 
                                       name="marks_obtained" 
                                       min="0" 
                                       max="<?php echo htmlspecialchars($grade['exam_total_marks']); ?>" 
                                       step="0.01" 
                                       value="<?php echo htmlspecialchars($grade['marks_obtained']); ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label>Current Grade</label>
                                <input type="text" value="<?php echo htmlspecialchars($grade['grade']); ?>" readonly style="background-color: #e9ecef;">
                                <small style="color: #6c757d;">Grade will be automatically calculated</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="remarks">Remarks</label>
                            <textarea id="remarks" name="remarks" rows="3"><?php echo htmlspecialchars($grade['remarks']); ?></textarea>
                        </div>
                        
                        <div class="info-box" style="background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 20px 0;">
                            <strong>📊 Grade Scale:</strong>
                            <ul style="margin: 10px 0 0 20px;">
                                <li>A+ : 90% and above</li>
                                <li>A : 80% - 89%</li>
                                <li>B+ : 70% - 79%</li>
                                <li>B : 60% - 69%</li>
                                <li>C : 50% - 59%</li>
                                <li>D : 40% - 49%</li>
                                <li>F : Below 40%</li>
                            </ul>
                        </div>
                        
                        <div class="form-actions">
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">💾 Update Grade</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // Real-time grade calculation preview
        document.getElementById('marks_obtained').addEventListener('input', function() {
            const marks = parseFloat(this.value) || 0;
            const total = <?php echo $grade['exam_total_marks']; ?>;
            const percentage = (marks / total) * 100;
            
            let grade = 'F';
            if (percentage >= 90) grade = 'A+';
            else if (percentage >= 80) grade = 'A';
            else if (percentage >= 70) grade = 'B+';
            else if (percentage >= 60) grade = 'B';
            else if (percentage >= 50) grade = 'C';
            else if (percentage >= 40) grade = 'D';
            
            document.querySelector('input[readonly]').value = grade + ' (' + percentage.toFixed(2) + '%)';
        });
    </script>
</body>
</html>

<?php
require_once '../config/config.php';
if (!hasRole('admin') && !hasRole('teacher')) {
    header('Location:  ../dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Get exams
$query = "SELECT e.*, c.class_name, s.subject_name 
          FROM exams e 
          JOIN classes c ON e.class_id = c. id 
          JOIN subjects s ON e.subject_id = s. id 
          ORDER BY e. exam_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$exams = $stmt->fetchAll();

$students = [];
$selected_exam = null;
$exam_details = null;
$existing_grades = [];

if (isset($_GET['exam_id'])) {
    $selected_exam = $_GET['exam_id'];
    
    // Get exam details
    $query = "SELECT e.*, c.class_name, s.subject_name 
              FROM exams e 
              JOIN classes c ON e.class_id = c.id 
              JOIN subjects s ON e.subject_id = s.id 
              WHERE e.id = :exam_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':exam_id', $selected_exam);
    $stmt->execute();
    $exam_details = $stmt->fetch();
    
    if ($exam_details) {
        // Get students in this exam's class
        $query = "SELECT s. * FROM students s 
                  JOIN enrollments e ON s.id = e.student_id 
                  WHERE e.class_id = :class_id AND s. status = 'active'
                  ORDER BY s.first_name, s.last_name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_id', $exam_details['class_id']);
        $stmt->execute();
        $students = $stmt->fetchAll();
        
        // Get existing grades
        $query = "SELECT student_id, marks_obtained, grade, remarks 
                  FROM grades 
                  WHERE exam_id = :exam_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':exam_id', $selected_exam);
        $stmt->execute();
        $grades_result = $stmt->fetchAll();
        
        // Convert to associative array with student_id as key
        foreach ($grades_result as $grade) {
            $existing_grades[$grade['student_id']] = $grade;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_id = $_POST['exam_id'];
    $grades_data = $_POST['grades'] ?? [];
    $remarks_data = $_POST['remarks'] ??  [];
    
    // Get total marks for the exam
    $query = "SELECT total_marks FROM exams WHERE id = :exam_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':exam_id', $exam_id);
    $stmt->execute();
    $exam = $stmt->fetch();
    $total_marks = $exam['total_marks'];
    
    try {
        $db->beginTransaction();
        
        foreach ($grades_data as $student_id => $marks_obtained) {
            $marks_obtained = trim($marks_obtained);
            if ($marks_obtained === '') continue;
            
            // Validate marks
            if (!is_numeric($marks_obtained) || $marks_obtained < 0 || $marks_obtained > $total_marks) {
                throw new Exception("Invalid marks for student ID: $student_id");
            }
            
            $grade = calculateGrade($marks_obtained, $total_marks);
            $remarks = trim($remarks_data[$student_id] ?? '');
            
            // Check if grade already exists
            $query = "SELECT id FROM grades WHERE student_id = :student_id AND exam_id = :exam_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':exam_id', $exam_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Update existing grade
                $query = "UPDATE grades 
                          SET marks_obtained = :marks_obtained, 
                              grade = :grade, 
                              remarks = :remarks,
                              updated_at = NOW()
                          WHERE student_id = :student_id AND exam_id = :exam_id";
            } else {
                // Insert new grade
                $query = "INSERT INTO grades (student_id, exam_id, marks_obtained, grade, remarks, created_at) 
                          VALUES (:student_id, :exam_id, :marks_obtained, :grade, :remarks, NOW())";
            }
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':exam_id', $exam_id);
            $stmt->bindParam(':marks_obtained', $marks_obtained);
            $stmt->bindParam(':grade', $grade);
            $stmt->bindParam(':remarks', $remarks);
            $stmt->execute();
        }
        
        $db->commit();
        $success = 'Grades saved successfully!  Redirecting...';
        header('refresh: 2;url=index.php');
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Error: ' . $e->getMessage();
    }
}

// // Grade calculation function
// function calculateGrade($marks, $total) {
//     $percentage = ($marks / $total) * 100;
    
//     if ($percentage >= 90) return 'A+';
//     if ($percentage >= 80) return 'A';
//     if ($percentage >= 70) return 'B+';
//     if ($percentage >= 60) return 'B';
//     if ($percentage >= 50) return 'C+';
//     if ($percentage >= 40) return 'C';
//     if ($percentage >= 30) return 'D';
//     return 'F';
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Grades - <?php echo SITE_NAME; ?></title>
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
                    <h1>📊 Add/Edit Grades</h1>
                    <p class="page-description">Enter student grades for selected exam</p>
                </div>
                <a href="index.php" class="btn btn-secondary">← Back to Grades</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span style="font-size: 1.25rem;">❌</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <span style="font-size: 1.25rem;">✅</span>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>📋 Select Exam</h2>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    <form method="GET">
                        <div class="form-group">
                            <label for="exam_id">Exam <span style="color: #EF4444;">*</span></label>
                            <select id="exam_id" name="exam_id" class="form-control" required onchange="this.form.submit()">
                                <option value="">-- Select Exam --</option>
                                <?php foreach ($exams as $exam): ?>
                                    <option value="<?php echo $exam['id']; ?>" <?php echo $selected_exam == $exam['id'] ? 'selected' :  ''; ?>>
                                        <?php echo htmlspecialchars($exam['exam_name'] .  ' | ' . $exam['class_name'] . ' | ' . $exam['subject_name'] . ' | ' . date('M j, Y', strtotime($exam['exam_date']))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small style="display: block; font-size: 0.75rem; color: #6B7280; margin-top: 0.375rem;">Select an exam to load students</small>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if (! empty($students) && $exam_details): ?>
            <div class="card">
                <div class="card-header">
                    <h2>📝 Enter Grades</h2>
                    <span class="badge badge-active"><?php echo count($students); ?> Students</span>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    <!-- Exam Info Box -->
                    <div style="margin-bottom: 2rem; padding: 1.5rem; background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.1)); border-radius: 12px; border-left: 4px solid #6366F1;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                            <div>
                                <strong style="color: #4B5563; font-size: 0.875rem;">Exam Name:</strong>
                                <p style="color: #1F2937; font-weight: 600; margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($exam_details['exam_name']); ?></p>
                            </div>
                            <div>
                                <strong style="color: #4B5563; font-size: 0.875rem;">Class:</strong>
                                <p style="color: #1F2937; font-weight: 600; margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($exam_details['class_name']); ?></p>
                            </div>
                            <div>
                                <strong style="color: #4B5563; font-size: 0.875rem;">Subject:</strong>
                                <p style="color: #1F2937; font-weight: 600; margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($exam_details['subject_name']); ?></p>
                            </div>
                            <div>
                                <strong style="color: #4B5563; font-size: 0.875rem;">Total Marks:</strong>
                                <p style="color: #1F2937; font-weight:  600; margin: 0.25rem 0 0 0;"><?php echo $exam_details['total_marks']; ?></p>
                            </div>
                            <div>
                                <strong style="color: #4B5563; font-size: 0.875rem;">Date:</strong>
                                <p style="color: #1F2937; font-weight: 600; margin: 0.25rem 0 0 0;"><?php echo date('M j, Y', strtotime($exam_details['exam_date'])); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" id="gradesForm">
                        <input type="hidden" name="exam_id" value="<?php echo $selected_exam; ?>">
                        
                        <div class="table-responsive">
                            <table class="data-table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th style="width: 150px;">Marks Obtained <span style="color: #EF4444;">*</span></th>
                                        <th style="width: 100px;">Grade</th>
                                        <th style="width:  200px;">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $counter = 1; ?>
                                    <?php foreach ($students as $student): ?>
                                    <?php
                                        $existing = $existing_grades[$student['id']] ?? null;
                                        $marks = $existing['marks_obtained'] ?? '';
                                        $grade = $existing['grade'] ?? '';
                                        $remarks = $existing['remarks'] ?? '';
                                    ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($student['first_name'] .  ' ' . $student['last_name']); ?></td>
                                        <td>
                                            <input type="number" 
                                                   name="grades[<?php echo $student['id']; ?>]" 
                                                   class="form-control marks-input" 
                                                   min="0" 
                                                   max="<?php echo $exam_details['total_marks']; ?>" 
                                                   step="0.5"
                                                   value="<?php echo htmlspecialchars($marks); ?>"
                                                   data-student-id="<?php echo $student['id']; ?>"
                                                   data-total="<?php echo $exam_details['total_marks']; ?>"
                                                   placeholder="0">
                                        </td>
                                        <td>
                                            <span class="grade-badge" id="grade-<?php echo $student['id']; ?>">
                                                <?php echo htmlspecialchars($grade); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   name="remarks[<?php echo $student['id']; ?>]" 
                                                   class="form-control" 
                                                   value="<?php echo htmlspecialchars($remarks); ?>"
                                                   placeholder="Optional">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <span>💾</span>
                                <span>Save Grades</span>
                            </button>
                            <button type="button" class="btn btn-warning" onclick="fillAllMarks()">
                                <span>📝</span>
                                <span>Fill All with Same Marks</span>
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <span>❌</span>
                                <span>Cancel</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            <?php elseif (isset($_GET['exam_id'])): ?>
            <div class="card">
                <div class="card-body" style="padding: 3rem; text-align: center;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                    <h3 style="color: #6B7280;">No students found for this exam</h3>
                    <p style="color: #9CA3AF;">Please make sure students are enrolled in the class. </p>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <style>
        .grade-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            min-width: 60px;
            text-align:  center;
        }
        
        .marks-input {
            text-align: center;
            font-weight: 600;
            font-size: 1rem;
        }
    </style>
    
    <script>
        // Auto-calculate grade when marks are entered
        document. querySelectorAll('.marks-input').forEach(input => {
            input.addEventListener('input', function() {
                const studentId = this. dataset.studentId;
                const total = parseFloat(this.dataset. total);
                const marks = parseFloat(this.value);
                
                if (marks >= 0 && marks <= total) {
                    const grade = calculateGrade(marks, total);
                    const badge = document.getElementById('grade-' + studentId);
                    badge.textContent = grade;
                    badge.className = 'grade-badge grade-' + grade. replace('+', 'plus').toLowerCase();
                } else if (this.value !== '') {
                    alert(`⚠️ Marks must be between 0 and ${total}`);
                    this.value = '';
                }
            });
        });
        
        function calculateGrade(marks, total) {
            const percentage = (marks / total) * 100;
            
            if (percentage >= 90) return 'A+';
            if (percentage >= 80) return 'A';
            if (percentage >= 70) return 'B+';
            if (percentage >= 60) return 'B';
            if (percentage >= 50) return 'C+';
            if (percentage >= 40) return 'C';
            if (percentage >= 30) return 'D';
            return 'F';
        }
        
        function fillAllMarks() {
            const marks = prompt('Enter marks to fill for all students:');
            if (marks !== null && marks !== '') {
                const total = document.querySelector('. marks-input').dataset.total;
                if (parseFloat(marks) >= 0 && parseFloat(marks) <= parseFloat(total)) {
                    document.querySelectorAll('.marks-input').forEach(input => {
                        input.value = marks;
                        input.dispatchEvent(new Event('input'));
                    });
                } else {
                    alert(`⚠️ Marks must be between 0 and ${total}`);
                }
            }
        }
        
        // Form validation
        document.getElementById('gradesForm').addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll('. marks-input');
            let hasValue = false;
            
            inputs.forEach(input => {
                if (input.value.trim() !== '') {
                    hasValue = true;
                }
            });
            
            if (!hasValue) {
                e.preventDefault();
                alert('⚠️ Please enter marks for at least one student');
                return false;
            }
            
            if (! confirm('Are you sure you want to save these grades?')) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
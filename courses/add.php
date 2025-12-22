<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $course_code = trim($_POST['course_code'] ?? '');
    $course_name = trim($_POST['course_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $credits = $_POST['credits'] ?? null;
    $duration = trim($_POST['duration'] ??  '');
    $status = $_POST['status'] ??  'active';
    
    // Validation
    if (empty($course_code) || empty($course_name)) {
        $error = 'Course Code and Course Name are required!';
    } else {
        try {
            // Check if course code already exists
            $checkQuery = "SELECT id FROM courses WHERE course_code = :course_code LIMIT 1";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':course_code', $course_code);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $error = 'Course code already exists!';
            } else {
                $query = "INSERT INTO courses (course_code, course_name, description, credits, duration, status, created_at) 
                          VALUES (:course_code, :course_name, :description, :credits, :duration, :status, NOW())";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':course_code', $course_code);
                $stmt->bindParam(':course_name', $course_name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':credits', $credits);
                $stmt->bindParam(':duration', $duration);
                $stmt->bindParam(':status', $status);
                
                if ($stmt->execute()) {
                    $success = 'Course added successfully!  Redirecting... ';
                    header('refresh:2;url=index.php');
                } else {
                    $error = 'Failed to add course.  Please try again.';
                }
            }
        } catch (Exception $e) {
            $error = 'Error: ' .  $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2? family=Inter: wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1>➕ Add New Course</h1>
                    <p class="page-description">Fill in the course information below</p>
                </div>
                <a href="index.php" class="btn btn-secondary">← Back to List</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span style="font-size: 1.25rem;">❌</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <span style="font-size:  1.25rem;">✅</span>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>📋 Course Information</h2>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <!-- Course Code and Name -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="course_code">Course Code <span style="color: #EF4444;">*</span></label>
                                <input type="text" 
                                       id="course_code" 
                                       name="course_code" 
                                       class="form-control" 
                                       placeholder="e. g., CS101, MATH201" 
                                       value="<?php echo htmlspecialchars($_POST['course_code'] ?? ''); ?>"
                                       maxlength="20"
                                       required>
                                <small class="form-helper">Unique identifier for the course</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="course_name">Course Name <span style="color: #EF4444;">*</span></label>
                                <input type="text" 
                                       id="course_name" 
                                       name="course_name" 
                                       class="form-control" 
                                       placeholder="e.g., Introduction to Computer Science"
                                       value="<?php echo htmlspecialchars($_POST['course_name'] ?? ''); ?>"
                                       maxlength="200"
                                       required>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" 
                                      name="description" 
                                      class="form-control" 
                                      rows="4"
                                      placeholder="Enter detailed course description, objectives, and learning outcomes..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <div id="char-counter" style="text-align: right; font-size: 0.875rem; color: #6B7280; margin-top: 0.5rem;">0 characters</div>
                        </div>
                        
                        <!-- Credits, Duration, and Status -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="credits">Credits</label>
                                <input type="number" 
                                       id="credits" 
                                       name="credits" 
                                       class="form-control" 
                                       min="0" 
                                       max="20" 
                                       step="0.5"
                                       placeholder="e.g., 3"
                                       value="<?php echo htmlspecialchars($_POST['credits'] ?? ''); ?>">
                                <small class="form-helper">Academic credit hours</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="duration">Duration</label>
                                <input type="text" 
                                       id="duration" 
                                       name="duration" 
                                       class="form-control" 
                                       placeholder="e.g., 6 months, 1 semester, 15 weeks"
                                       value="<?php echo htmlspecialchars($_POST['duration'] ?? ''); ?>">
                                <small class="form-helper">Course length/timeframe</small>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="status">Status <span style="color: #EF4444;">*</span></label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="active" <?php echo (($_POST['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (($_POST['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                                <small class="form-helper">Active courses are available for enrollment</small>
                            </div>
                            
                            <div class="form-group">
                                <!-- Empty for grid alignment -->
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <span>💾</span>
                                <span>Save Course</span>
                            </button>
                            <button type="reset" class="btn btn-warning">
                                <span>🔄</span>
                                <span>Reset Form</span>
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <span>❌</span>
                                <span>Cancel</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const courseCode = document. getElementById('course_code').value.trim();
            const courseName = document.getElementById('course_name').value.trim();
            
            if (!courseCode || !courseName) {
                e.preventDefault();
                alert('⚠️ Please fill in all required fields (Course Code and Course Name)');
                return false;
            }
            
            // Confirm submission
            if (! confirm('Are you sure you want to add this course?')) {
                e.preventDefault();
                return false;
            }
        });
        
        // Character counter for description
        const description = document.getElementById('description');
        const charCounter = document.getElementById('char-counter');
        
        function updateCounter() {
            const length = description.value.length;
            charCounter.textContent = length + ' character' + (length !== 1 ? 's' :  '');
            
            // Color coding
            if (length > 500) {
                charCounter.style.color = '#EF4444'; // Red
            } else if (length > 300) {
                charCounter.style.color = '#F59E0B'; // Orange
            } else {
                charCounter. style.color = '#6B7280'; // Gray
            }
        }
        
        description.addEventListener('input', updateCounter);
        updateCounter();
        
        // Auto-uppercase and format course code
        document.getElementById('course_code').addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
        });
        
        // Prevent spaces in course code
        document.getElementById('course_code').addEventListener('keydown', function(e) {
            if (e.key === ' ') {
                e.preventDefault();
            }
        });
        
        // Credits validation
        document.getElementById('credits').addEventListener('change', function() {
            if (this.value && (this.value < 0 || this.value > 20)) {
                alert('⚠️ Credits must be between 0 and 20');
                this.value = '';
            }
        });
    </script>
</body>
</html>
<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

$query = "SELECT * FROM courses WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$course = $stmt->fetch();

if (!$course) {
    header('Location:  index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_code = trim($_POST['course_code'] ??  '');
    $course_name = trim($_POST['course_name'] ?? '');
    $description = trim($_POST['description'] ??  '');
    $credits = $_POST['credits'] ?? null;
    $duration = trim($_POST['duration'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($course_code) || empty($course_name)) {
        $error = 'Course Code and Course Name are required! ';
    } else {
        try {
            // Check if course code exists for other courses
            $checkQuery = "SELECT id FROM courses WHERE course_code = :course_code AND id != :id LIMIT 1";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':course_code', $course_code);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $error = 'Course code already exists for another course!';
            } else {
                $query = "UPDATE courses SET 
                          course_code = :course_code, 
                          course_name = :course_name, 
                          description = :description, 
                          credits = :credits, 
                          duration = :duration, 
                          status = :status,
                          updated_at = NOW()
                          WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':course_code', $course_code);
                $stmt->bindParam(':course_name', $course_name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':credits', $credits);
                $stmt->bindParam(':duration', $duration);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $success = 'Course updated successfully!  Redirecting...';
                    // Refresh course data
                    $stmt = $db->prepare("SELECT * FROM courses WHERE id = :id");
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                    $course = $stmt->fetch();
                    
                    header('refresh:2;url=index.php');
                } else {
                    $error = 'Failed to update course. Please try again. ';
                }
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter: wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1>✏️ Edit Course</h1>
                    <p class="page-description">Update course information</p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="view.php? id=<?php echo $course['id']; ?>" class="btn btn-info">👁️ View Details</a>
                    <a href="index.php" class="btn btn-secondary">← Back to List</a>
                </div>
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
                    <h2>📋 Course Information</h2>
                    <span class="badge badge-<?php echo $course['status']; ?>">
                        <?php echo ucfirst($course['status']); ?>
                    </span>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    <form method="POST">
                        <!-- Course Code and Name -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="course_code">Course Code <span style="color: #EF4444;">*</span></label>
                                <input type="text" 
                                       id="course_code" 
                                       name="course_code" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($course['course_code']); ?>" 
                                       maxlength="20"
                                       required>
                                <small style="display: block; font-size:  0.75rem; color: #6B7280; margin-top: 0.375rem;">Unique identifier for the course</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="course_name">Course Name <span style="color: #EF4444;">*</span></label>
                                <input type="text" 
                                       id="course_name" 
                                       name="course_name" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($course['course_name']); ?>" 
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
                                      placeholder="Enter course description..."><?php echo htmlspecialchars($course['description'] ?? ''); ?></textarea>
                            <div id="char-counter" style="text-align: right; font-size: 0.875rem; color: #6B7280; margin-top: 0.5rem;">0 characters</div>
                        </div>
                        
                        <!-- Credits and Duration -->
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
                                       value="<?php echo htmlspecialchars($course['credits'] ?? ''); ?>">
                                <small style="display: block; font-size: 0.75rem; color: #6B7280; margin-top: 0.375rem;">Academic credit hours</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="duration">Duration</label>
                                <input type="text" 
                                       id="duration" 
                                       name="duration" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($course['duration'] ?? ''); ?>">
                                <small style="display: block; font-size: 0.75rem; color: #6B7280; margin-top: 0.375rem;">Course length/timeframe</small>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="status">Status <span style="color: #EF4444;">*</span></label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="active" <?php echo $course['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $course['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                                <small style="display: block; font-size: 0.75rem; color: #6B7280; margin-top: 0.375rem;">Active courses are available for enrollment</small>
                            </div>
                            
                            <div class="form-group">
                                <!-- Empty for grid alignment -->
                            </div>
                        </div>
                        
                        <!-- Metadata (Read-only info) -->
                        <?php if (! empty($course['created_at'])): ?>
                        <div class="alert" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.1)); border-left: 4px solid #6366F1; margin-bottom: 1.5rem;">
                            <div style="display: flex; gap: 2rem; flex-wrap: wrap; font-size: 0.875rem;">
                                <div>
                                    <strong style="color: #4B5563;">Created:</strong>
                                    <span style="color: #1F2937;"><?php echo date('M j, Y g:i A', strtotime($course['created_at'])); ?></span>
                                </div>
                                <?php if (! empty($course['updated_at']) && $course['updated_at'] != $course['created_at']): ?>
                                <div>
                                    <strong style="color: #4B5563;">Last Updated:</strong>
                                    <span style="color: #1F2937;"><?php echo date('M j, Y g:i A', strtotime($course['updated_at'])); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <span>💾</span>
                                <span>Update Course</span>
                            </button>
                            <a href="view.php?id=<?php echo $course['id']; ?>" class="btn btn-info">
                                <span>👁️</span>
                                <span>View Details</span>
                            </a>
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
            const courseCode = document.getElementById('course_code').value. trim();
            const courseName = document.getElementById('course_name').value.trim();
            
            if (!courseCode || !courseName) {
                e.preventDefault();
                alert('⚠️ Please fill in all required fields');
                return false;
            }
            
            // Confirm update
            if (! confirm('Are you sure you want to update this course?')) {
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
            
            if (length > 500) {
                charCounter.style.color = '#EF4444';
            } else if (length > 300) {
                charCounter.style.color = '#F59E0B';
            } else {
                charCounter.style.color = '#6B7280';
            }
        }
        
        description.addEventListener('input', updateCounter);
        updateCounter();
        
        // Auto-uppercase course code
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
                this.value = '<?php echo $course['credits'] ?? ''; ?>';
            }
        });
    </script>
</body>
</html>
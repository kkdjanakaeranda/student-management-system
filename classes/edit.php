<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

// Get class
$query = "SELECT * FROM classes WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$class = $stmt->fetch();

if (!$class) {
    header('Location: index.php');
    exit();
}

// Get courses
$query = "SELECT * FROM courses WHERE status = 'active' ORDER BY course_name";
$stmt = $db->prepare($query);
$stmt->execute();
$courses = $stmt->fetchAll();

// Get teachers
$query = "SELECT * FROM teachers WHERE status = 'active' ORDER BY first_name";
$stmt = $db->prepare($query);
$stmt->execute();
$teachers = $stmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = $_POST['class_name'];
    $section = $_POST['section'];
    $course_id = $_POST['course_id'] ?: null;
    $teacher_id = $_POST['teacher_id'] ?: null;
    $academic_year = $_POST['academic_year'];
    $room_number = $_POST['room_number'];
    $status = $_POST['status'];
    
    try {
        $query = "UPDATE classes SET class_name = :class_name, section = :section, course_id = :course_id, 
                  teacher_id = :teacher_id, academic_year = :academic_year, room_number = :room_number, 
                  status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_name', $class_name);
        $stmt->bindParam(':section', $section);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->bindParam(':academic_year', $academic_year);
        $stmt->bindParam(':room_number', $room_number);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        header('Location: index.php');
        exit();
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Class - <?php echo SITE_NAME; ?></title>
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
                    <h1>✏️ Edit Class</h1>
                    <p>Update class information</p>
                </div>
                <a href="index.php" class="btn btn-secondary">← Back to List</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" class="form-grid">
                        <div class="form-section">
                            <h3>📋 Class Information</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="class_name">Class Name <span>*</span></label>
                                    <input type="text" id="class_name" name="class_name" value="<?php echo htmlspecialchars($class['class_name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="section">Section</label>
                                    <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($class['section']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="course_id">Course</label>
                                    <select id="course_id" name="course_id">
                                        <option value="">Select Course</option>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?php echo $course['id']; ?>" <?php echo $class['course_id'] == $course['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($course['course_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="teacher_id">Class Teacher</label>
                                    <select id="teacher_id" name="teacher_id">
                                        <option value="">Select Teacher</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?php echo $teacher['id']; ?>" <?php echo $class['teacher_id'] == $teacher['id'] ? 'selected' :  ''; ?>>
                                                <?php echo htmlspecialchars($teacher['first_name'] .  ' ' . $teacher['last_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="academic_year">Academic Year</label>
                                    <input type="text" id="academic_year" name="academic_year" value="<?php echo htmlspecialchars($class['academic_year']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="room_number">Room Number</label>
                                    <input type="text" id="room_number" name="room_number" value="<?php echo htmlspecialchars($class['room_number']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="active" <?php echo $class['status'] == 'active' ? 'selected' :  ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $class['status'] == 'inactive' ? 'selected' :  ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Update Class</button>
                            <a href="index.php" class="btn btn-secondary">❌ Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main. js"></script>
</body>
</html>
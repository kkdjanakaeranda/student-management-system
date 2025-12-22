<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

$query = "SELECT * FROM subjects WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$subject = $stmt->fetch();

if (!$subject) {
    header('Location:  index.php');
    exit();
}

// Get classes
$query = "SELECT * FROM classes WHERE status = 'active' ORDER BY class_name";
$stmt = $db->prepare($query);
$stmt->execute();
$classes = $stmt->fetchAll();

// Get teachers
$query = "SELECT * FROM teachers WHERE status = 'active' ORDER BY first_name";
$stmt = $db->prepare($query);
$stmt->execute();
$teachers = $stmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $description = $_POST['description'];
    $class_id = $_POST['class_id'] ?: null;
    $teacher_id = $_POST['teacher_id'] ?: null;
    
    try {
        $query = "UPDATE subjects SET subject_code = :subject_code, subject_name = :subject_name, 
                  description = :description, class_id = :class_id, teacher_id = :teacher_id 
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':subject_code', $subject_code);
        $stmt->bindParam(':subject_name', $subject_name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':teacher_id', $teacher_id);
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
    <title>Edit Subject - <?php echo SITE_NAME; ?></title>
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
                    <h1>✏️ Edit Subject</h1>
                    <p>Update subject information</p>
                </div>
                <a href="index.php" class="btn btn-secondary">← Back to List</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" class="form-grid">
                        <?php echo csrfField(); ?>
                        <div class="form-section">
                            <h3>📋 Subject Information</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="subject_code">Subject Code <span>*</span></label>
                                    <input type="text" id="subject_code" name="subject_code" value="<?php echo htmlspecialchars($subject['subject_code']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="subject_name">Subject Name <span>*</span></label>
                                    <input type="text" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars($subject['subject_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($subject['description']); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="class_id">Class</label>
                                    <select id="class_id" name="class_id">
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>" <?php echo $subject['class_id'] == $class['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name'] . ' - ' . ($class['section'] ?? '')); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="teacher_id">Teacher</label>
                                    <select id="teacher_id" name="teacher_id">
                                        <option value="">Select Teacher</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?php echo $teacher['id']; ?>" <?php echo $subject['teacher_id'] == $teacher['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Update Subject</button>
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
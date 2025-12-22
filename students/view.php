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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - <?php echo SITE_NAME; ?></title>
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
                    <h1>👨‍🎓 Student Details</h1>
                    <p>Complete information about the student</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <?php if (hasRole('admin')): ?>
                        <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-warning">✏️ Edit</a>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-secondary">← Back</a>
                </div>
            </div>
            
            <div class="content-grid">
                <div class="card">
                    <div class="card-header">
                        <h2>📸 Photo</h2>
                    </div>
                    <div class="card-body" style="text-align: center;">
                        <img src="<?php echo $student['photo'] ? '../uploads/students/' . $student['photo'] : '../assets/images/default-avatar.png'; ?>" 
                             alt="Student Photo" 
                             style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 5px solid var(--primary-color);">
                        <h3 style="margin-top: 20px;"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h3>
                        <p class="badge badge-<?php echo $student['status']; ?>" style="margin-top: 10px;">
                            <?php echo ucfirst($student['status']); ?>
                        </p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>ℹ️ Personal Information</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; gap: 15px;">
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Student ID:</strong>
                                <span><?php echo htmlspecialchars($student['student_id']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Date of Birth:</strong>
                                <span><?php echo date('F d, Y', strtotime($student['date_of_birth'])); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Gender:</strong>
                                <span><?php echo htmlspecialchars($student['gender']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding:  12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Phone:</strong>
                                <span><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></span>
                            </div>
                            <div style="display: flex; justify-content:  space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Email:</strong>
                                <span><?php echo htmlspecialchars($student['email']); ?></span>
                            </div>
                            <div style="padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Address:</strong>
                                <p style="margin-top: 8px;"><?php echo htmlspecialchars($student['address'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="content-grid">
                <div class="card">
                    <div class="card-header">
                        <h2>👨‍👩‍👧 Guardian Information</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; gap: 15px;">
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Guardian Name:</strong>
                                <span><?php echo htmlspecialchars($student['guardian_name'] ?? 'N/A'); ?></span>
                            </div>
                            <div style="display: flex; justify-content:  space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Guardian Phone:</strong>
                                <span><?php echo htmlspecialchars($student['guardian_phone'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>🎓 Academic Information</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; gap: 15px;">
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Admission Date:</strong>
                                <span><?php echo date('F d, Y', strtotime($student['admission_date'])); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Status:</strong>
                                <span class="badge badge-<?php echo $student['status']; ?>">
                                    <?php echo ucfirst($student['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
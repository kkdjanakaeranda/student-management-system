<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

$query = "SELECT s.*, u.email FROM students s 
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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $guardian_name = $_POST['guardian_name'];
    $guardian_phone = $_POST['guardian_phone'];
    $status = $_POST['status'];
    $email = $_POST['email'];
    
    $photo_name = $student['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            if ($student['photo'] && file_exists(STUDENT_PHOTO_DIR . $student['photo'])) {
                unlink(STUDENT_PHOTO_DIR . $student['photo']);
            }
            $photo_name = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], STUDENT_PHOTO_DIR . $photo_name);
        }
    }
    
    try {
        $db->beginTransaction();
        
        $query = "UPDATE students SET first_name = :first_name, last_name = :last_name, 
                  date_of_birth = :date_of_birth, gender = : gender, phone = :phone, 
                  address = :address, guardian_name = :guardian_name, guardian_phone = :guardian_phone, 
                  photo = :photo, status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':date_of_birth', $date_of_birth);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':guardian_name', $guardian_name);
        $stmt->bindParam(':guardian_phone', $guardian_phone);
        $stmt->bindParam(':photo', $photo_name);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $query = "UPDATE users SET email = :email WHERE id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_id', $student['user_id']);
        $stmt->execute();
        
        $db->commit();
        
        header('Location: view.php?id=' . $id);
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Error:  ' . $e->getMessage();
    }
}
?>
<! DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - <?php echo SITE_NAME; ?></title>
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
                    <h1>✏️ Edit Student</h1>
                    <p>Update student information</p>
                </div>
                <a href="index.php" class="btn btn-secondary">← Back to List</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="form-grid">
                        <div class="form-section">
                            <h3>📋 Personal Information</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name <span>*</span></label>
                                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="last_name">Last Name <span>*</span></label>
                                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth <span>*</span></label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $student['date_of_birth']; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="gender">Gender <span>*</span></label>
                                    <select id="gender" name="gender" required>
                                        <option value="Male" <?php echo $student['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $student['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo $student['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email <span>*</span></label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($student['address']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="active" <?php echo $student['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $student['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="graduated" <?php echo $student['status'] == 'graduated' ? 'selected' : ''; ?>>Graduated</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="photo">Photo</label>
                                <?php if ($student['photo']): ?>
                                    <img src="../uploads/students/<?php echo $student['photo']; ?>" style="width: 100px; height: 100px; border-radius: 50%; margin-bottom: 10px;">
                                <?php endif; ?>
                                <input type="file" id="photo" name="photo" accept="image/*">
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>👨‍👩‍👧 Guardian Information</h3>
                            
                            <div class="form-group">
                                <label for="guardian_name">Guardian Name</label>
                                <input type="text" id="guardian_name" name="guardian_name" value="<?php echo htmlspecialchars($student['guardian_name']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="guardian_phone">Guardian Phone</label>
                                <input type="tel" id="guardian_phone" name="guardian_phone" value="<?php echo htmlspecialchars($student['guardian_phone']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Update Student</button>
                            <a href="view. php?id=<?php echo $id; ?>" class="btn btn-secondary">❌ Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html
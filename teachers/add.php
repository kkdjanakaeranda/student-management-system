<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $teacher_id = $_POST['teacher_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $qualification = $_POST['qualification'];
    $specialization = $_POST['specialization'];
    $joining_date = $_POST['joining_date'];
    $password = $_POST['password'];
    
    $photo_name = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $photo_name = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], TEACHER_PHOTO_DIR . $photo_name);
        }
    }
    
    try {
        $db->beginTransaction();
        
        $query = "INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, 'teacher')";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $teacher_id);
        $stmt->bindParam(':password', password_hash($password, PASSWORD_DEFAULT));
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user_id = $db->lastInsertId();
        
        $query = "INSERT INTO teachers (user_id, teacher_id, first_name, last_name, date_of_birth, gender, 
                  phone, email, address, qualification, specialization, joining_date, photo) 
                  VALUES (: user_id, :teacher_id, :first_name, :last_name, :date_of_birth, :gender, 
                  :phone, :email, : address, :qualification, :specialization, :joining_date, :photo)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->bindParam(': first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':date_of_birth', $date_of_birth);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':qualification', $qualification);
        $stmt->bindParam(': specialization', $specialization);
        $stmt->bindParam(':joining_date', $joining_date);
        $stmt->bindParam(': photo', $photo_name);
        $stmt->execute();
        
        $db->commit();
        
        header('Location: index.php');
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Error: ' .  $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Teacher - <?php echo SITE_NAME; ?></title>
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
                    <h1>➕ Add New Teacher</h1>
                    <p>Fill in the teacher information below</p>
                </div>
                <a href="index.php" class="btn btn-secondary">← Back to List</a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="form-grid">
                        <?php echo csrfField(); ?>
                        <div class="form-section">
                            <h3>📋 Personal Information</h3>
                            
                            <div class="form-group">
                                <label for="teacher_id" class="form-group">Teacher ID <span>*</span></label>
                                <input type="text" id="teacher_id" name="teacher_id" class="form-control" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name <span>*</span></label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="last_name">Last Name <span>*</span></label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth <span>*</span></label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="gender">Gender <span>*</span></label>
                                    <select id="gender" name="gender" class="form-control" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" >
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email <span>*</span></label>
                                    <input type="email" id="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" rows="3" class="form-control" ></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="photo">Photo</label>
                                <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>🎓 Professional Information</h3>
                            
                            <div class="form-group">
                                <label for="qualification">Qualification</label>
                                <input type="text" id="qualification" name="qualification" class="form-control" >
                            </div>
                            
                            <div class="form-group">
                                <label for="specialization">Specialization</label>
                                <input type="text" id="specialization" name="specialization" class="form-control" >
                            </div>
                            
                            <div class="form-group">
                                <label for="joining_date">Joining Date <span>*</span></label>
                                <input type="date" id="joining_date" name="joining_date" class="form-control"  required>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>🔐 Login Credentials</h3>
                            
                            <div class="form-group">
                                <label for="password">Password <span>*</span></label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Add Teacher</button>
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
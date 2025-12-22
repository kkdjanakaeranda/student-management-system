<?php
require_once '../config/config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

$query = "SELECT t.*, u.email, u.username FROM teachers t 
          LEFT JOIN users u ON t.user_id = u.id 
          WHERE t.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$teacher = $stmt->fetch();

if (!$teacher) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Teacher - <?php echo SITE_NAME; ?></title>
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
                    <h1>👨‍🏫 Teacher Details</h1>
                    <p>Complete information about the teacher</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="edit.php?id=<?php echo $teacher['id']; ?>" class="btn btn-warning">✏️ Edit</a>
                    <a href="index.php" class="btn btn-secondary">← Back</a>
                </div>
            </div>
            
            <div class="content-grid">
                <div class="card">
                    <div class="card-header">
                        <h2>📸 Photo</h2>
                    </div>
                    <div class="card-body" style="text-align: center;">
                        <img src="<?php echo $teacher['photo'] ? '../uploads/teachers/' . $teacher['photo'] : '../assets/images/default-avatar.png'; ?>" 
                             alt="Teacher Photo" 
                             style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 5px solid var(--secondary-color);">
                        <h3 style="margin-top: 20px;"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></h3>
                        <p class="badge badge-<?php echo $teacher['status']; ?>" style="margin-top: 10px;">
                            <?php echo ucfirst($teacher['status']); ?>
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
                                <strong>Teacher ID:</strong>
                                <span><?php echo htmlspecialchars($teacher['teacher_id']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Date of Birth:</strong>
                                <span><?php echo date('F d, Y', strtotime($teacher['date_of_birth'])); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Gender:</strong>
                                <span><?php echo htmlspecialchars($teacher['gender']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding:  12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Phone:</strong>
                                <span><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></span>
                            </div>
                            <div style="display:  flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Email:</strong>
                                <span><?php echo htmlspecialchars($teacher['email']); ?></span>
                            </div>
                            <div style="padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <strong>Address:</strong>
                                <p style="margin-top: 8px;"><?php echo htmlspecialchars($teacher['address'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>🎓 Professional Information</h2>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                        <div style="padding: 12px; background: var(--light-color); border-radius: 8px;">
                            <strong>Qualification:</strong>
                            <p style="margin-top: 8px;"><?php echo htmlspecialchars($teacher['qualification'] ??  'N/A'); ?></p>
                        </div>
                        <div style="padding: 12px; background: var(--light-color); border-radius: 8px;">
                            <strong>Specialization: </strong>
                            <p style="margin-top: 8px;"><?php echo htmlspecialchars($teacher['specialization'] ??  'N/A'); ?></p>
                        </div>
                        <div style="padding: 12px; background: var(--light-color); border-radius: 8px;">
                            <strong>Joining Date: </strong>
                            <p style="margin-top: 8px;"><?php echo date('F d, Y', strtotime($teacher['joining_date'])); ?></p>
                        </div>
                        <div style="padding: 12px; background: var(--light-color); border-radius: 8px;">
                            <strong>Status:</strong>
                            <p style="margin-top: 8px;">
                                <span class="badge badge-<?php echo $teacher['status']; ?>">
                                    <?php echo ucfirst($teacher['status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main. js"></script>
</body>
</html>
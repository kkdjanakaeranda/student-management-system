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
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo filemtime('../assets/css/style.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .person-view .profile-card .profile-summary {
            display: grid;
            grid-template-columns: 96px minmax(0, 1fr);
            align-items: center;
            gap: 1.25rem;
            padding: 1.25rem 1.5rem;
            min-height: auto;
        }

        .person-view .profile-card .profile-summary-photo {
            width: 96px;
            height: 96px;
            border-radius: 12px;
            object-fit: cover;
        }

        .person-view .profile-card .profile-summary-content h2 {
            margin: 0 0 0.25rem;
            font-size: 1.25rem;
        }

        .person-view .profile-card .profile-summary-meta {
            margin: 0 0 0.625rem;
        }

        .person-view .profile-card .details-grid {
            padding: 1.25rem 1.5rem 1.5rem;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        @media (max-width: 768px) {
            .person-view .profile-card .profile-summary {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content person-view">
            <div class="page-header">
                <div>
                    <h1>Teacher Details</h1>
                    <p class="page-description">View teacher information and professional details</p>
                </div>
                <div class="action-buttons">
                    <a href="edit.php?id=<?php echo $teacher['id']; ?>" class="btn btn-warning">Edit</a>
                    <a href="index.php" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
            
            <div class="card profile-card">
                <div class="profile-summary">
                    <img src="<?php echo $teacher['photo'] ? '../uploads/teachers/' . $teacher['photo'] : '../assets/images/default-avatar.svg'; ?>" 
                         alt="Teacher Photo" 
                         class="profile-summary-photo">
                    <div class="profile-summary-content">
                        <h2><?php echo e($teacher['first_name'] . ' ' . $teacher['last_name']); ?></h2>
                        <p class="profile-summary-meta"><?php echo e($teacher['teacher_id']); ?><?php echo !empty($teacher['email']) ? ' - ' . e($teacher['email']) : ''; ?></p>
                        <span class="badge badge-<?php echo e($teacher['status']); ?>"><?php echo ucfirst(e($teacher['status'])); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="details-grid">
                        <div class="detail-item">
                            <label>Teacher ID</label>
                            <p><?php echo e($teacher['teacher_id']); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Date of Birth</label>
                            <p><?php echo !empty($teacher['date_of_birth']) ? date('F d, Y', strtotime($teacher['date_of_birth'])) : 'N/A'; ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Gender</label>
                            <p><?php echo e($teacher['gender'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Phone</label>
                            <p><?php echo e($teacher['phone'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Email</label>
                            <p><?php echo e($teacher['email'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Joining Date</label>
                            <p><?php echo !empty($teacher['joining_date']) ? date('F d, Y', strtotime($teacher['joining_date'])) : 'N/A'; ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Qualification</label>
                            <p><?php echo e($teacher['qualification'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Specialization</label>
                            <p><?php echo e($teacher['specialization'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item detail-item-full">
                            <label>Address</label>
                            <p><?php echo e($teacher['address'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js?v=<?php echo filemtime('../assets/js/main.js'); ?>"></script>
</body>
</html>

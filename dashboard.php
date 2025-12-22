<?php
require_once 'config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

$query = "SELECT COUNT(*) as total FROM students WHERE status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['students'] = $stmt->fetch()['total'];

$query = "SELECT COUNT(*) as total FROM teachers WHERE status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['teachers'] = $stmt->fetch()['total'];

$query = "SELECT COUNT(*) as total FROM classes WHERE status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['classes'] = $stmt->fetch()['total'];

$query = "SELECT COUNT(*) as total FROM courses WHERE status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['courses'] = $stmt->fetch()['total'];

$query = "SELECT a.*, u.username FROM announcements a 
          LEFT JOIN users u ON a.posted_by = u.id 
          ORDER BY a.created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$announcements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1>📊 Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! Here's what's happening today. </p>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['students']; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">👨‍🏫</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['teachers']; ?></h3>
                        <p>Total Teachers</p>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">📚</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['classes']; ?></h3>
                        <p>Total Classes</p>
                    </div>
                </div>
                
                <div class="stat-card stat-info">
                    <div class="stat-icon">📖</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['courses']; ?></h3>
                        <p>Total Courses</p>
                    </div>
                </div>
            </div>
            
            <div class="content-grid">
                <div class="card" style="grid-column: 1 / -1;">
                    <div class="card-header">
                        <h2>📢 Recent Announcements</h2>
                    </div>
                    <div class="card-body">
                        <?php if (count($announcements) > 0): ?>
                            <div class="announcements-list">
                                <?php foreach ($announcements as $announcement): ?>
                                    <div class="announcement-item">
                                        <div class="announcement-header">
                                            <h4><?php echo htmlspecialchars($announcement['title']); ?></h4>
                                            <span class="badge badge-<?php echo $announcement['priority']; ?>">
                                                <?php echo ucfirst($announcement['priority']); ?>
                                            </span>
                                        </div>
                                        <p><?php echo htmlspecialchars(substr($announcement['content'], 0, 200)); ?>...</p>
                                        <div class="announcement-footer">
                                            <span>👤 By:  <?php echo htmlspecialchars($announcement['username']); ?></span>
                                            <span>📅 <?php echo date('M d, Y - h:i A', strtotime($announcement['created_at'])); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else:  ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">📭</div>
                                <h3>No Announcements Yet</h3>
                                <p>There are no announcements to display at the moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="content-grid">
                <div class="card">
                    <div class="card-header">
                        <h2>📊 Quick Stats</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <span style="font-weight: 600;">Active Students</span>
                                <span class="badge badge-active"><?php echo $stats['students']; ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <span style="font-weight: 600;">Active Teachers</span>
                                <span class="badge badge-active"><?php echo $stats['teachers']; ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 12px; background: var(--light-color); border-radius: 8px;">
                                <span style="font-weight: 600;">Running Classes</span>
                                <span class="badge badge-active"><?php echo $stats['classes']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>🎯 Quick Actions</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <?php if (hasRole('admin')): ?>
                                <a href="students/add.php" class="btn btn-primary" style="width: 100%;">
                                    ➕ Add New Student
                                </a>
                                <a href="teachers/add.php" class="btn btn-success" style="width: 100%;">
                                    ➕ Add New Teacher
                                </a>
                                <a href="classes/add.php" class="btn btn-info" style="width: 100%;">
                                    ➕ Create New Class
                                </a>
                            <?php endif; ?>
                            <a href="announcements/add.php" class="btn btn-warning" style="width: 100%;">
                                📢 Post Announcement
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
<?php
require_once '../config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

// Get today's date
$today = date('Y-m-d');

$query = "SELECT a.*, s.student_id, s.first_name, s.last_name, c.class_name 
          FROM attendance a 
          JOIN students s ON a.student_id = s.id 
          JOIN classes c ON a.class_id = c.id 
          WHERE a.date = :today
          ORDER BY c.class_name, s.first_name";
$stmt = $db->prepare($query);
$stmt->bindParam(':today', $today);
$stmt->execute();
$attendances = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - <?php echo SITE_NAME; ?></title>
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
                    <h1>✅ Attendance</h1>
                    <p>Manage student attendance records</p>
                </div>
                <?php if (hasRole('admin') || hasRole('teacher')): ?>
                    <a href="mark.php" class="btn btn-primary">
                        ➕ Mark Attendance
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>📋 Today's Attendance - <?php echo date('F d, Y'); ?> (<?php echo count($attendances); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (count($attendances) > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendances as $attendance): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($attendance['student_id']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($attendance['first_name'] . ' ' . $attendance['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($attendance['class_name']); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                switch($attendance['status']) {
                                                    case 'present':  $statusClass = 'badge-active'; break;
                                                    case 'absent': $statusClass = 'badge-inactive'; break;
                                                    case 'late': $statusClass = 'badge-medium'; break;
                                                    case 'excused': $statusClass = 'badge-low'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($attendance['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($attendance['remarks'] ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else:  ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">✅</div>
                            <h3>No Attendance Records</h3>
                            <p>No attendance has been marked for today.</p>
                            <?php if (hasRole('admin') || hasRole('teacher')): ?>
                                <a href="mark.php" class="btn btn-primary mt-3">Mark Attendance Now</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
<?php
// students/my_attendance.php — personal attendance view
require_once '../config/config.php';
requireLogin();

if (!hasRole('student')) {
    header('Location: ../dashboard.php');
    exit();
}

$sid      = (int) ($_SESSION['student_id'] ?? 0);
$database = new Database();
$db       = $database->getConnection();

// Summary per class
$summaryStmt = $db->prepare(
    "SELECT c.name AS class_name, c.section,
            COUNT(*)                                                   AS total_days,
            SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END)       AS present,
            SUM(CASE WHEN a.status='absent'  THEN 1 ELSE 0 END)       AS absent,
            SUM(CASE WHEN a.status='late'    THEN 1 ELSE 0 END)       AS late,
            SUM(CASE WHEN a.status='excused' THEN 1 ELSE 0 END)       AS excused,
            ROUND(
                SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END)
                / NULLIF(COUNT(*),0) * 100
            , 1) AS pct
     FROM   attendance a
     JOIN   classes    c ON c.id = a.class_id
     WHERE  a.student_id = :sid
     GROUP  BY c.id, c.name, c.section
     ORDER  BY c.name"
);
$summaryStmt->execute([':sid' => $sid]);
$summary = $summaryStmt->fetchAll();

// Recent 30 detailed records
$detailStmt = $db->prepare(
    "SELECT a.date, a.status,
            c.name AS class_name, c.section
     FROM   attendance a
     JOIN   classes    c ON c.id = a.class_id
     WHERE  a.student_id = :sid
     ORDER  BY a.date DESC
     LIMIT  60"
);
$detailStmt->execute([':sid' => $sid]);
$detail = $detailStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container">
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">

        <div class="page-header">
            <h1>✅ My Attendance</h1>
        </div>

        <!-- Summary by class -->
        <?php if ($summary): ?>
        <div class="stats-grid" style="margin-bottom:24px;">
            <?php foreach ($summary as $row):
                $color = $row['pct'] >= 75 ? 'stat-success'
                       : ($row['pct'] >= 50 ? 'stat-warning' : 'stat-danger');
            ?>
            <div class="stat-card <?php echo $color; ?>">
                <div class="stat-details">
                    <h3><?php echo $row['pct']; ?>%</h3>
                    <p><?php echo htmlspecialchars($row['class_name'] . ' ' . $row['section']); ?></p>
                    <small>
                        P:<?php echo $row['present']; ?>
                        A:<?php echo $row['absent']; ?>
                        L:<?php echo $row['late']; ?>
                        E:<?php echo $row['excused']; ?>
                    </small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Detailed records -->
        <div class="card">
            <div class="card-header"><h2>Recent Records</h2></div>
            <div class="card-body">
                <?php if (empty($detail)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <h3>No attendance records</h3>
                        <p>Your attendance records will appear here once they have been marked.</p>
                    </div>
                <?php else: ?>
                <table class="table">
                    <thead>
                        <tr><th>Date</th><th>Class</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($detail as $r): ?>
                        <tr>
                            <td><?php echo date('D, M d Y', strtotime($r['date'])); ?></td>
                            <td><?php echo htmlspecialchars($r['class_name'] . ' ' . $r['section']); ?></td>
                            <td>
                                <?php
                                $badge = match($r['status']) {
                                    'present' => 'badge-active',
                                    'absent'  => 'badge-inactive',
                                    'late'    => 'badge-warning',
                                    'excused' => 'badge-info',
                                    default   => ''
                                };
                                ?>
                                <span class="badge <?php echo $badge; ?>">
                                    <?php echo ucfirst(htmlspecialchars($r['status'])); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>

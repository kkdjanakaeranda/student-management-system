<?php
require_once '../config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']) && hasRole('admin')) {
    verifyCsrf();

    try {
        $stmt = $db->prepare("DELETE FROM announcements WHERE id = :id");
        $stmt->execute([':id' => (int)$_POST['delete_id']]);
        $success = 'Announcement deleted successfully.';
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

$allowedFilters = ['all', 'students', 'teachers'];
$filter = $_GET['filter'] ?? 'all';
if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}
$search = trim($_GET['search'] ?? '');

$query = "SELECT a.*, u.username
          FROM announcements a
          LEFT JOIN users u ON a.posted_by = u.id";
$where = [];
$params = [];

if ($filter !== 'all') {
    $where[] = "(a.target_audience = :filter OR a.target_audience = 'all')";
    $params[':filter'] = $filter;
}

if ($search !== '') {
    $where[] = "(a.title LIKE :search OR a.content LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($where) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY a.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$announcements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - <?php echo SITE_NAME; ?></title>
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
                    <h1>Announcements</h1>
                    <p class="page-description">Manage notices for students and teachers</p>
                </div>
                <?php if (hasRole('admin') || hasRole('teacher')): ?>
                    <a href="add.php" class="btn btn-primary">Add Announcement</a>
                <?php endif; ?>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo e($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>Filter Announcements</h2>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    <form method="GET" style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;">
                        <div class="form-group" style="flex:1;min-width:240px;margin-bottom:0;">
                            <label for="search">Search</label>
                            <input type="text" id="search" name="search" class="form-control"
                                   placeholder="Search title or content"
                                   value="<?php echo e($search); ?>">
                        </div>

                        <div class="form-group" style="min-width:220px;margin-bottom:0;">
                            <label for="filter">Audience</label>
                            <select id="filter" name="filter" class="form-control">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All announcements</option>
                                <option value="students" <?php echo $filter === 'students' ? 'selected' : ''; ?>>Visible to students</option>
                                <option value="teachers" <?php echo $filter === 'teachers' ? 'selected' : ''; ?>>Visible to teachers</option>
                            </select>
                        </div>

                        <div style="display:flex;gap:0.5rem;">
                            <button type="submit" class="btn btn-primary">Apply</button>
                            <a href="index.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>All Announcements (<?php echo count($announcements); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if ($announcements): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Audience</th>
                                        <th>Priority</th>
                                        <th>Posted By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($announcements as $announcement): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo e($announcement['title']); ?></strong>
                                                <div style="color:var(--gray-medium);font-size:0.8125rem;margin-top:4px;">
                                                    <?php echo e(mb_substr($announcement['content'], 0, 90)); ?><?php echo mb_strlen($announcement['content']) > 90 ? '...' : ''; ?>
                                                </div>
                                            </td>
                                            <td><?php echo ucfirst(e($announcement['target_audience'])); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo e($announcement['priority']); ?>">
                                                    <?php echo ucfirst(e($announcement['priority'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo e($announcement['username'] ?? 'System'); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($announcement['created_at'])); ?></td>
                                            <td>
                                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                                    <?php if (hasRole('admin') || $_SESSION['user_id'] == $announcement['posted_by']): ?>
                                                        <a href="edit.php?id=<?php echo $announcement['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                    <?php endif; ?>

                                                    <?php if (hasRole('admin')): ?>
                                                        <form method="POST" action="index.php" style="display:inline" onsubmit="return confirm('Delete this announcement?');">
                                                            <?php csrfField(); ?>
                                                            <input type="hidden" name="delete_id" value="<?php echo $announcement['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <h3>No Announcements Found</h3>
                            <p>There are no announcements to show right now.</p>
                            <?php if (hasRole('admin') || hasRole('teacher')): ?>
                                <a href="add.php" class="btn btn-primary mt-3">Add Announcement</a>
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

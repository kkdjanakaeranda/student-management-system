<?php
require_once '../config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Handle delete
if (isset($_GET['delete']) && hasRole('admin')) {
    $delete_id = $_GET['delete'];
    
    try {
        $query = "DELETE FROM announcements WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $delete_id);
        
        if ($stmt->execute()) {
            $success = 'Announcement deleted successfully!';
        } else {
            $error = 'Failed to delete announcement. ';
        }
    } catch (Exception $e) {
        $error = 'Error:  ' . $e->getMessage();
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query based on filter
$query = "SELECT a.*, u.username, u.role 
          FROM announcements a 
          LEFT JOIN users u ON a. posted_by = u.id";

$where = [];
$params = [];

if ($filter !== 'all') {
    $where[] = "a.target_audience = :filter";
    $params[':filter'] = $filter;
}

if (! empty($search)) {
    $where[] = "(a.title LIKE :search OR a.content LIKE :search)";
    $params[':search'] = '%' . $search .  '%';
}

if (! empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY a.created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$announcements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2? family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1>📢 Announcements</h1>
                    <p class="page-description">View all announcements and important notices</p>
                </div>
                <?php if (hasRole('admin')): ?>
                <a href="add.php" class="btn btn-primary">
                    <span>➕</span>
                    <span>Post Announcement</span>
                </a>
                <?php endif; ?>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <span style="font-size: 1.25rem;">✅</span>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span style="font-size:  1.25rem;">❌</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Filter and Search Bar -->
            <div class="card">
                <div class="card-body" style="padding: 1.5rem;">
                    <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
                        <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
                            <label for="search" style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">🔍 Search</label>
                            <input type="text" 
                                   id="search" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Search by title or content..."
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <div class="form-group" style="min-width: 200px; margin-bottom: 0;">
                            <label for="filter" style="font-size:  0.875rem; font-weight: 600; margin-bottom: 0.5rem;">🎯 Filter by Audience</label>
                            <select id="filter" name="filter" class="form-control">
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Audiences</option>
                                <option value="students" <?php echo $filter === 'students' ? 'selected' : ''; ?>>Students Only</option>
                                <option value="teachers" <?php echo $filter === 'teachers' ? 'selected' : ''; ?>>Teachers Only</option>
                                <option value="admin" <?php echo $filter === 'admin' ? 'selected' : ''; ?>>Admin Only</option>
                            </select>
                        </div>
                        
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="submit" class="btn btn-primary">Apply</button>
                            <a href="index.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>📋 All Announcements</h2>
                    <span class="badge badge-active"><?php echo count($announcements); ?> Total</span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <?php if (count($announcements) > 0): ?>
                        <div class="announcements-list">
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="announcement-card">
                                    <div class="announcement-card-header">
                                        <div>
                                            <h3 class="announcement-title">
                                                <?php echo htmlspecialchars($announcement['title']); ?>
                                            </h3>
                                            <div class="announcement-meta">
                                                <span class="meta-item">
                                                    <span style="opacity: 0.7;">👤</span>
                                                    <?php echo htmlspecialchars($announcement['username']); ?>
                                                </span>
                                                <span class="meta-item">
                                                    <span style="opacity: 0.7;">🎯</span>
                                                    <?php echo ucfirst($announcement['target_audience']); ?>
                                                </span>
                                                <span class="meta-item">
                                                    <span style="opacity: 0.7;">📅</span>
                                                    <?php echo date('M j, Y - g:i A', strtotime($announcement['created_at'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <?php
                                                $priorityColors = [
                                                    'low' => 'secondary',
                                                    'medium' => 'warning',
                                                    'high' => 'danger',
                                                    'urgent' => 'danger'
                                                ];
                                                $priorityColor = $priorityColors[$announcement['priority']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?php echo $priorityColor; ?>">
                                                <?php 
                                                    if ($announcement['priority'] === 'urgent') echo '🚨 ';
                                                    if ($announcement['priority'] === 'high') echo '⚠️ ';
                                                    echo ucfirst($announcement['priority']); 
                                                ?>
                                            </span>
                                            
                                            <?php if (hasRole('admin') || $_SESSION['user_id'] == $announcement['posted_by']): ?>
                                            <div class="btn-group">
                                                <a href="edit. php?id=<?php echo $announcement['id']; ?>" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="Edit">
                                                    ✏️
                                                </a>
                                                <?php if (hasRole('admin')): ?>
                                                <a href="? delete=<?php echo $announcement['id']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   title="Delete"
                                                   onclick="return confirm('⚠️ Are you sure you want to delete this announcement?\n\nTitle: <?php echo htmlspecialchars($announcement['title']); ?>\n\nThis action cannot be undone!');">
                                                    🗑️
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="announcement-content">
                                        <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else:  ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📢</div>
                            <h3>No Announcements Found</h3>
                            <p>
                                <?php if (! empty($search) || $filter !== 'all'): ?>
                                    No announcements match your search criteria. 
                                <?php else: ?>
                                    There are no announcements yet.
                                <?php endif; ?>
                            </p>
                            <?php if (hasRole('admin')): ?>
                                <a href="add.php" class="btn btn-primary" style="margin-top: 1rem;">
                                    <span>➕</span>
                                    <span>Post First Announcement</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <style>
        .announcements-list {
            display:  flex;
            flex-direction: column;
            gap: 0;
        }
        
        .announcement-card {
            padding: 2rem;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s;
        }
        
        . announcement-card:last-child {
            border-bottom: none;
        }
        
        .announcement-card:hover {
            background:  linear-gradient(135deg, rgba(99, 102, 241, 0.02), rgba(139, 92, 246, 0.03));
        }
        
        .announcement-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
            gap: 1rem;
        }
        
        .announcement-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0 0 0.75rem 0;
        }
        
        .announcement-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1. 5rem;
            font-size: 0.875rem;
            color: var(--gray-medium);
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        .announcement-content {
            color: var(--dark-color);
            line-height: 1.8;
            font-size: 0.9375rem;
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            min-width: auto;
        }
        
        @media (max-width: 768px) {
            .announcement-card {
                padding: 1.5rem;
            }
            
            .announcement-card-header {
                flex-direction: column;
            }
            
            .announcement-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
    
    <script>
        // Auto-hide success message
        <?php if ($success): ?>
        setTimeout(() => {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                alert.style.transition = 'opacity 0.3s';
                alert.style. opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
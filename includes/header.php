<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$in_subfolder = (strpos($_SERVER['PHP_SELF'], '/students/') !== false || 
                 strpos($_SERVER['PHP_SELF'], '/teachers/') !== false || 
                 strpos($_SERVER['PHP_SELF'], '/classes/') !== false  || 
                 strpos($_SERVER['PHP_SELF'], '/courses/') !== false ||
                 strpos($_SERVER['PHP_SELF'], '/subjects/') !== false || 
                 strpos($_SERVER['PHP_SELF'], '/attendance/') !== false  || 
                 strpos($_SERVER['PHP_SELF'], '/exams/') !== false ||
                 strpos($_SERVER['PHP_SELF'], '/grades/') !== false ||
                 strpos($_SERVER['PHP_SELF'], '/announcements/') !== false );
$prefix = $in_subfolder ? '../' : '';

// Get user role badge color
$roleColors = [
    'admin' => 'role-admin',
    'teacher' => 'role-teacher',
    'student' => 'role-student'
];
$roleClass = $roleColors[$_SESSION['role'] ?? 'student'] ?? 'role-student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Enhanced Header Styles */
        .header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(249, 250, 251, 0.95));
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow:  0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 2px solid transparent;
            border-image: linear-gradient(90deg, #6366F1, #8B5CF6, #EC4899) 1;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #6366F1, #8B5CF6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        .header h1 {
            background: linear-gradient(135deg, #6366F1, #8B5CF6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.5rem;
            font-weight:  700;
            margin: 0;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        /* Current Time Display */
        .current-time {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
            border-radius: 8px;
            font-size: 0.875rem;
            color: var(--dark-color);
            font-weight: 500;
        }

        .current-time . clock-icon {
            font-size:  1rem;
        }

        /* User Info Enhanced */
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.05));
            border-radius: 10px;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #6366F1, #8B5CF6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.875rem;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.875rem;
        }

        .user-role {
            display: inline-block;
            padding: 0.125rem 0.625rem;
            border-radius: 6px;
            font-size:  0.6875rem;
            font-weight:  600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .role-admin {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.2));
            color: #DC2626;
        }

        .role-teacher {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.2));
            color: #059669;
        }

        . role-student {
            background:  linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.2));
            color: #2563EB;
        }

        /* Logout Button Enhanced */
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.15));
            color: #DC2626;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 8px;
            border: 1px solid rgba(239, 68, 68, 0.2);
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #EF4444, #DC2626);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* Notifications Badge */
        .notification-icon {
            position: relative;
            padding: 0.5rem;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .notification-icon:hover {
            background:  linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.2));
            transform: scale(1.1);
        }

        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 18px;
            height: 18px;
            background: linear-gradient(135deg, #EF4444, #DC2626);
            color: white;
            border-radius: 50%;
            display:  flex;
            align-items:  center;
            justify-content:  center;
            font-size:  0.625rem;
            font-weight:  700;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 0.75rem 1rem;
            }

            .header h1 {
                font-size: 1.125rem;
            }

            . current-time {
                display: none;
            }

            .user-details {
                display: none;
            }

            .notification-icon {
                display: none;
            }
        }

        @media (max-width:  480px) {
            .header-logo {
                width: 32px;
                height: 32px;
                font-size: 16px;
            }

            .header h1 {
                font-size: 1rem;
            }

            .logout-btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <div class="header-logo">🎓</div>
            <h1>Student Management System</h1>
        </div>

        <div class="header-right">

            <!-- User Info -->
            <div class="user-info">
                <div class="user-avatar">
                    <?php 
                    $username = $_SESSION['username'] ?? 'User';
                    echo strtoupper(substr($username, 0, 1)); 
                    ?>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                    <span class="user-role <?php echo $roleClass; ?>">
                        <?php echo htmlspecialchars($_SESSION['role'] ?? 'Student'); ?>
                    </span>
                </div>
            </div>

            <!-- Logout Button -->
            <a href="<?php echo $prefix; ?>logout.php" class="logout-btn">
                <span>🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </header>

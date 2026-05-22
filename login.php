<?php
// login.php — drop-in replacement
require_once 'config/config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check on login (protects against login-CSRF)
    verifyCsrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $database = new Database();
        $db       = $database->getConnection();

        // Fetch user AND linked student/teacher ids + display_name
        $stmt = $db->prepare(
            "SELECT id, username, password, email, role,
                    display_name, student_id, teacher_id
             FROM   users
             WHERE  username = :username
             LIMIT  1"
        );
        $stmt->execute([':username' => $username]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();

            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);    // prevent session fixation

                $_SESSION['user_id']      = $user['id'];
                $_SESSION['username']     = $user['username'];
                $_SESSION['display_name'] = $user['display_name'] ?: $user['username'];
                $_SESSION['email']        = $user['email'];
                $_SESSION['role']         = $user['role'];
                $_SESSION['student_id']   = $user['student_id'];   // null for non-students
                $_SESSION['teacher_id']   = $user['teacher_id'];   // null for non-teachers

                header('Location: dashboard.php');
                exit();
            }
        }
        // Generic message — don't reveal which field was wrong
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-page">
<div class="login-container">
    <div class="login-box">
        <div class="login-header">
            <div class="logo">🎓</div>
            <h1><?php echo SITE_NAME; ?></h1>
            <p>Welcome back! Please login to continue.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="login-form">
            <?php csrfField(); ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       placeholder="Enter your username"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                🔐 Login to Dashboard
            </button>
        </form>
    </div>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>

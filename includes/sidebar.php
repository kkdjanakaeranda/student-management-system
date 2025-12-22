<aside class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="<?php echo BASE_URL; ?>dashboard.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <?php if (hasRole('admin') || hasRole('teacher')): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>students/index.php" class="nav-link">
                        <span class="nav-icon">👨‍🎓</span>
                        <span class="nav-text">Students</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (hasRole('admin')): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>teachers/index.php" class="nav-link">
                        <span class="nav-icon">👨‍🏫</span>
                        <span class="nav-text">Teachers</span>
                    </a>
                </li>
                
                <li>
                    <a href="<?php echo BASE_URL; ?>classes/index.php" class="nav-link">
                        <span class="nav-icon">📚</span>
                        <span class="nav-text">Classes</span>
                    </a>
                </li>
                
                <li>
                    <a href="<?php echo BASE_URL; ?>courses/index.php" class="nav-link">
                        <span class="nav-icon">📖</span>
                        <span class="nav-text">Courses</span>
                    </a>
                </li>
                
                <li>
                    <a href="<?php echo BASE_URL; ?>subjects/index.php" class="nav-link">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text">Subjects</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (hasRole('admin') || hasRole('teacher')): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>attendance/index.php" class="nav-link">
                        <span class="nav-icon">✅</span>
                        <span class="nav-text">Attendance</span>
                    </a>
                </li>
                
                <li>
                    <a href="<?php echo BASE_URL; ?>exams/index.php" class="nav-link">
                        <span class="nav-icon">📋</span>
                        <span class="nav-text">Exams</span>
                    </a>
                </li>
                
                <li>
                    <a href="<?php echo BASE_URL; ?>grades/index.php" class="nav-link">
                        <span class="nav-icon">🎯</span>
                        <span class="nav-text">Grades</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <li>
                <a href="<?php echo BASE_URL; ?>announcements/index.php" class="nav-link">
                    <span class="nav-icon">📢</span>
                    <span class="nav-text">Announcements</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
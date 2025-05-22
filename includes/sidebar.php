<?php
// Session is already started in the main file
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;
// Helper to get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h3>Mentor Management</h3>
    </div>
    <ul class="sidebar-menu">
        <?php if ($user_role === 'mentor'): ?>
            <li><a href="mentor_dashboard.php" class="<?= $current_page == 'mentor_dashboard.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="mentee_list.php" class="<?= $current_page == 'mentee_list.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> My Mentees</a></li>
            <li><a href="activities.php" class="<?= $current_page == 'activities.php' ? 'active' : '' ?>"><i class="fas fa-tasks"></i> Activities</a></li>
            <li><a href="feedback.php" class="<?= $current_page == 'feedback.php' ? 'active' : '' ?>"><i class="fas fa-comment"></i> Feedback</a></li>
            <li><a href="certifications.php" class="<?= $current_page == 'certifications.php' ? 'active' : '' ?>"><i class="fas fa-certificate"></i> Certifications</a></li>
            <li><a href="profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>"><i class="fas fa-user"></i> Profile</a></li>
        <?php else: ?>
            <li><a href="mentee_dashboard.php" class="<?= $current_page == 'mentee_dashboard.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="marks.php" class="<?= $current_page == 'marks.php' ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Subject Marks</a></li>
            <li><a href="activities.php" class="<?= $current_page == 'activities.php' ? 'active' : '' ?>"><i class="fas fa-tasks"></i> Activities</a></li>
            <li><a href="certifications.php" class="<?= $current_page == 'certifications.php' ? 'active' : '' ?>"><i class="fas fa-certificate"></i> Certifications</a></li>
            <li><a href="profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>"><i class="fas fa-user"></i> Profile</a></li>
        <?php endif; ?>
        <li><a href="logout.php" class="<?= $current_page == 'logout.php' ? 'active' : '' ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 250px;
    background: #1e293b;
    padding: 20px;
    color: #fff;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    z-index: 1000;
}

.sidebar-header {
    padding: 20px 0;
    border-bottom: 1px solid #334155;
    margin-bottom: 20px;
}

.sidebar-header h3 {
    color: #6C63FF;
    margin: 0;
    font-size: 1.5em;
    background: none;
    padding: 0;
    border-radius: 0;
    display: block;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 20px 0 0 0;
}

.sidebar-menu li {
    margin: 5px 0;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #e2e8f0;
    text-decoration: none;
    transition: all 0.3s ease;
    border-radius: 6px;
}

.sidebar-menu a.active,
.sidebar-menu a:hover {
    background: #6C63FF;
    color: #fff;
}

.sidebar-menu a.active:hover {
    background: #5A52D5;
    color: #fff;
}

.sidebar-menu a:hover {
    background: #5A52D5;
    color: #fff;
}

.sidebar-menu a:hover i,
.sidebar-menu a.active i {
    color: #fff;
}

.sidebar-menu i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
    color: #6C63FF;
    transition: color 0.3s ease;
}

/* Adjust main content to account for sidebar */
.main-content {
    margin-left: 250px;
    padding: 20px;
}
</style> 
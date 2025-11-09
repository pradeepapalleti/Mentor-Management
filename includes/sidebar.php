<?php
// Session is already started in the main file
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;
// Helper to get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Hamburger Menu Button -->
<button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle Menu">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>Mentor<br>Management</h2>
        <p><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></p>
    </div>
    <nav>
        <?php if ($user_role === 'mentor'): ?>
            <a href="mentor_dashboard.php" class="<?= $current_page == 'mentor_dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> <span>Dashboard</span>
            </a>
            <a href="mentee_list.php" class="<?= $current_page == 'mentee_list.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> <span>My Mentees</span>
            </a>
            <a href="activities.php" class="<?= $current_page == 'activities.php' ? 'active' : '' ?>">
                <i class="fas fa-tasks"></i> <span>Activities</span>
            </a>
            <a href="feedback.php" class="<?= $current_page == 'feedback.php' ? 'active' : '' ?>">
                <i class="fas fa-comment"></i> <span>Feedback</span>
            </a>
            <a href="certifications.php" class="<?= $current_page == 'certifications.php' ? 'active' : '' ?>">
                <i class="fas fa-certificate"></i> <span>Certifications</span>
            </a>
            <a href="profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>">
                <i class="fas fa-user"></i> <span>Profile</span>
            </a>
        <?php else: ?>
            <a href="mentee_dashboard.php" class="<?= $current_page == 'mentee_dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> <span>Dashboard</span>
            </a>
            <a href="marks.php" class="<?= $current_page == 'marks.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i> <span>Subject Marks</span>
            </a>
            <a href="activities.php" class="<?= $current_page == 'activities.php' ? 'active' : '' ?>">
                <i class="fas fa-tasks"></i> <span>Activities</span>
            </a>
            <a href="certifications.php" class="<?= $current_page == 'certifications.php' ? 'active' : '' ?>">
                <i class="fas fa-certificate"></i> <span>Certifications</span>
            </a>
            <a href="profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>">
                <i class="fas fa-user"></i> <span>Profile</span>
            </a>
        <?php endif; ?>
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </nav>
</div>

<script>
// Hamburger menu toggle
const hamburgerBtn = document.getElementById('hamburgerBtn');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

hamburgerBtn.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    sidebarOverlay.classList.toggle('active');
    document.body.classList.toggle('sidebar-open');
});

sidebarOverlay.addEventListener('click', () => {
    sidebar.classList.remove('active');
    sidebarOverlay.classList.remove('active');
    document.body.classList.remove('sidebar-open');
});

// Close sidebar when clicking nav link on mobile
const navLinks = sidebar.querySelectorAll('nav a');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    });
});
</script> 
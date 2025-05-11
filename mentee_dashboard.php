<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mentee') {
    header("Location: login.php");
    exit;
}
$mentee_id = $_SESSION['user_id'];

// Get mentee's name
$mentee_query = $conn->query("SELECT name FROM users WHERE id = $mentee_id");
$mentee = $mentee_query->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mentee Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="container">
    <div class="dashboard-header">
        <h2>Welcome <?php echo htmlspecialchars($mentee['name']); ?></h2>
        <button onclick="window.location.href='logout.php'" class="logout">Logout</button>
    </div>

    <div class="dashboard-grid">
        <?php
        // Get mentor details
        $sql = "SELECT u.name, u.email FROM users u
                JOIN mentor_mentee mm ON u.id = mm.mentor_id
                WHERE mm.mentee_id = $mentee_id";
        $mentor = $conn->query($sql)->fetch_assoc();
        
        if ($mentor) {
            echo "<div class='mentor-info'>";
            echo "<h3>Your Mentor</h3>";
            echo "<p><strong>Name:</strong> " . htmlspecialchars($mentor['name']) . "</p>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($mentor['email']) . "</p>";
            echo "</div>";
        } else {
            echo "<div class='msg'>You haven't been assigned a mentor yet.</div>";
        }
        ?>

        <div class="activities-section">
            <h3>Your Activities</h3>
            <div class="activity-grid">
                <?php
                $acts = $conn->query("SELECT * FROM activities WHERE mentee_id=$mentee_id ORDER BY date DESC");
                if ($acts->num_rows == 0) {
                    echo "<p>No activities recorded yet.</p>";
                } else {
                    while ($act = $acts->fetch_assoc()) {
                        $progress_class = '';
                        if ($act['progress'] == 'Completed') {
                            $progress_class = 'style="color: var(--success-color);"';
                        } elseif ($act['progress'] == 'In Progress') {
                            $progress_class = 'style="color: var(--primary-color);"';
                        }
                        echo "<div class='activity'>";
                        echo "<div class='activity-content'>";
                        echo "<div class='activity-header'>";
                        echo "<strong>{$act['activity']}</strong>";
                        echo "<span {$progress_class}>{$act['progress']}</span>";
                        echo "</div>";
                        echo "<div class='activity-date'>" . date('F j, Y', strtotime($act['date'])) . "</div>";
                        echo "</div>";
                        echo "</div>";
                    }
                }
                ?>
            </div>

            <h3>Add New Activity</h3>
            <form method="POST" action="add_activity.php">
                <div class="form-group">
                    <input type="text" name="activity" placeholder="What are you working on?" required>
                </div>
                <div class="form-group">
                    <select name="progress" required>
                        <option value="">Select Progress</option>
                        <option value="Not Started">Not Started</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <button type="submit">Add Activity</button>
            </form>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div class="nav-menu">
        <a href="#activities">Activities</a>
        <a href="#add-activity">Add Activity</a>
        <button onclick="window.location.href='logout.php'" class="logout">Logout</button>
    </div>
</div>
</body>
</html>
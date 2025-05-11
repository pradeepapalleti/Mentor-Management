<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mentor') {
    header("Location: login.php");
    exit;
}
$mentor_id = $_SESSION['user_id'];

// Get mentor's name
$mentor_query = $conn->query("SELECT name FROM users WHERE id = $mentor_id");
$mentor = $mentor_query->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mentor Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Welcome <?php echo htmlspecialchars($mentor['name']); ?></h2>
    <a href="logout.php" class="logout">Logout</a>

    <h3>Your Mentees and Their Progress</h3>
    <?php
    // Get all mentees assigned to this mentor
    $sql = "SELECT u.id, u.name, u.email 
            FROM users u
            JOIN mentor_mentee mm ON u.id = mm.mentee_id
            WHERE mm.mentor_id = $mentor_id
            ORDER BY u.name";
    $mentees = $conn->query($sql);

    if ($mentees->num_rows == 0) {
        echo "<p>No mentees have been assigned to you yet.</p>";
    } else {
        while ($mentee = $mentees->fetch_assoc()) {
            echo "<div class='mentee'>";
            echo "<strong>{$mentee['name']}</strong> ({$mentee['email']})";
            echo "<ul>";
            
            // Get activities for this mentee
            $mid = $mentee['id'];
            $acts = $conn->query("SELECT * FROM activities WHERE mentee_id=$mid ORDER BY date DESC");
            
            if ($acts->num_rows == 0) {
                echo "<li>No activities recorded yet.</li>";
            } else {
                while ($act = $acts->fetch_assoc()) {
                    $progress_class = '';
                    if ($act['progress'] == 'Completed') {
                        $progress_class = 'style="color: var(--success-color);"';
                    } elseif ($act['progress'] == 'In Progress') {
                        $progress_class = 'style="color: var(--primary-color);"';
                    }
                    echo "<li>{$act['activity']} - <span {$progress_class}>{$act['progress']}</span> ({$act['date']})</li>";
                }
            }
            echo "</ul>";
            echo "</div>";
        }
    }
    ?>
</div>
</body>
</html>
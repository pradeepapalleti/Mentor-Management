<?php
session_start();
include 'db.php';

// Debug session
error_log("Session data: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mentor') {
    error_log("Session check failed. User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . 
              ", Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));
    header("Location: login.php");
    exit();
}

$mentor_id = $_SESSION['user_id'];

// Get mentor's name and their mentor if they are a mentee
$mentor_query = $conn->query("SELECT m.name, m.is_mentor_mentee, m2.name as mentor_name 
                             FROM mentors m 
                             LEFT JOIN mentors m2 ON m.mentor_id = m2.id 
                             WHERE m.id = $mentor_id");
$mentor = $mentor_query->fetch_assoc();

// Debug mentor data
error_log("Mentor data: " . print_r($mentor, true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #0f172a;
            color: #ffffff;
        }
        .container {
            background: #1e293b;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .section {
            background: #334155;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            color: #ffffff;
            margin-bottom: 20px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #475569;
            padding-bottom: 10px;
        }
        .section-header h3 {
            color: #ffffff;
            font-size: 1.4em;
        }
        .dashboard-header {
            background: #1e293b;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            border: 1px solid #475569;
        }
        .dashboard-header h2 {
            margin: 0;
            font-size: 1.8em;
            color: #38bdf8;
        }
        .add-button {
            background: #38bdf8;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
        }
        .add-button:hover {
            background: #0284c7;
            transform: translateY(-2px);
        }
        .mentee-card {
            border: 1px solid #475569;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            background: #334155;
            transition: all 0.3s ease;
        }
        .mentee-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            border-color: #38bdf8;
        }
        .mentee-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #475569;
        }
        .mentee-header h4 {
            color: #38bdf8;
            margin: 0;
            font-size: 1.3em;
        }
        .mentee-actions {
            display: flex;
            gap: 10px;
        }
        .action-button {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            color: white;
            transition: all 0.3s ease;
            border: none;
        }
        .action-button:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }
        .add-result { 
            background: #818cf8;
        }
        .add-activity { 
            background: #f472b6;
        }
        .add-certification { 
            background: #a78bfa;
        }
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .data-item {
            background: #475569;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .data-item h5 {
            color: #38bdf8;
            margin: 0 0 10px 0;
            font-size: 1.1em;
            border-bottom: 1px solid #64748b;
            padding-bottom: 8px;
        }
        .data-item p {
            color: #e2e8f0;
            margin: 8px 0;
            font-size: 0.95em;
            line-height: 1.4;
        }
        .mentor-info {
            background: #334155;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #475569;
        }
        .mentor-info h3 {
            color: #38bdf8;
            margin-top: 0;
            font-size: 1.4em;
        }
        .mentor-info p {
            color: #e2e8f0;
            margin: 8px 0;
            font-size: 1em;
        }
        .logout {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            background: #475569;
            transition: all 0.3s ease;
            border: 1px solid #64748b;
        }
        .logout:hover {
            background: #64748b;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="dashboard-header">
        <h2>Welcome <?php echo htmlspecialchars($mentor['name']); ?></h2>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <?php if ($mentor['is_mentor_mentee']): ?>
        <div class="section">
            <h3>Your Mentor</h3>
            <?php if ($mentor['mentor_name']): ?>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($mentor['mentor_name']); ?></p>
            <?php else: ?>
                <p>You haven't selected a mentor yet.</p>
                <a href="select_mentor.php" class="button">Select Mentor</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="section">
        <div class="section-header">
            <h3>Your Mentees</h3>
            <a href="add_mentee.php" class="add-button">Add New Mentee</a>
        </div>
        
        <?php
        // Get all mentees assigned to this mentor
        $sql = "SELECT m.id as mentee_id, m.name, m.email 
                FROM mentees m
                JOIN mentor_mentee_relationship mmr ON m.id = mmr.mentee_id
                WHERE mmr.mentor_id = $mentor_id
                ORDER BY m.name";
        $mentees = $conn->query($sql);

        if ($mentees->num_rows == 0) {
            echo "<p>No mentees have been assigned to you yet.</p>";
        } else {
            while ($mentee = $mentees->fetch_assoc()) {
                echo "<div class='mentee-card'>";
                echo "<div class='mentee-header'>";
                echo "<h4>" . htmlspecialchars($mentee['name']) . "</h4>";
                echo "<div class='mentee-actions'>";
                echo "<a href='add_result.php?mentee_id={$mentee['mentee_id']}' class='action-button add-result'>Add Result</a>";
                echo "<a href='add_activity.php?mentee_id={$mentee['mentee_id']}' class='action-button add-activity'>Add Activity</a>";
                echo "<a href='add_certification.php?mentee_id={$mentee['mentee_id']}' class='action-button add-certification'>Add Certification</a>";
                echo "</div>";
                echo "</div>";
                
                // Get semester results
                $results = $conn->query("SELECT * FROM semester_results WHERE mentee_id={$mentee['mentee_id']} ORDER BY academic_year DESC, semester DESC LIMIT 3");
                if ($results->num_rows > 0) {
                    echo "<div class='data-grid'>";
                    echo "<div class='data-item'>";
                    echo "<h5>Recent Academic Results</h5>";
                    while ($result = $results->fetch_assoc()) {
                        echo "<p>Semester {$result['semester']} ({$result['academic_year']}): GPA {$result['gpa']}</p>";
                    }
                    echo "</div>";
                }
                
                // Get recent activities
                $activities = $conn->query("SELECT * FROM activities WHERE mentee_id={$mentee['mentee_id']} ORDER BY date DESC LIMIT 3");
                if ($activities->num_rows > 0) {
                    echo "<div class='data-item'>";
                    echo "<h5>Recent Activities</h5>";
                    while ($act = $activities->fetch_assoc()) {
                        echo "<p>{$act['activity_type']} - " . date('M j, Y', strtotime($act['date'])) . "</p>";
                    }
                    echo "</div>";
                }
                
                // Get recent certifications
                $certs = $conn->query("SELECT * FROM certifications WHERE mentee_id={$mentee['mentee_id']} ORDER BY issue_date DESC LIMIT 3");
                if ($certs->num_rows > 0) {
                    echo "<div class='data-item'>";
                    echo "<h5>Recent Certifications</h5>";
                    while ($cert = $certs->fetch_assoc()) {
                        echo "<p>{$cert['certification_name']} - " . date('M j, Y', strtotime($cert['issue_date'])) . "</p>";
                    }
                    echo "</div>";
                }
                
                echo "</div>"; // Close data-grid
                echo "</div>"; // Close mentee-card
            }
        }
        ?>
    </div>
</div>
</body>
</html>
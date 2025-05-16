<?php
session_start();
include 'db.php';

// Debug session
error_log("Session data: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mentee') {
    error_log("Session check failed. User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . 
              ", Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));
    header("Location: login.php");
    exit();
}

$mentee_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_certification'])) {
    $certification_name = $_POST['certification_name'];
    $issuing_organization = $_POST['issuing_organization'];
    $issue_date = $_POST['issue_date'];
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    
    $sql = "INSERT INTO certifications (mentee_id, certification_name, issuing_organization, issue_date, expiry_date) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $mentee_id, $certification_name, $issuing_organization, $issue_date, $expiry_date);
    $stmt->execute();
}

// Get mentee's name
$mentee_query = $conn->query("SELECT name FROM mentees WHERE id = $mentee_id");
$mentee = $mentee_query->fetch_assoc();

// Debug mentee data
error_log("Mentee data: " . print_r($mentee, true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentee Dashboard</title>
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
        .form {
            background: #334155;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            border: 1px solid #475569;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #e2e8f0;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #475569;
            border-radius: 4px;
            background: #475569;
            color: #ffffff;
            transition: all 0.3s ease;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #38bdf8;
            outline: none;
            box-shadow: 0 0 0 2px rgba(56,189,248,0.2);
            background: #334155;
        }
        .button {
            background: #38bdf8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .button:hover {
            background: #0284c7;
            transform: translateY(-2px);
        }
        .error {
            color: #f87171;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(248,113,113,0.1);
            border-radius: 4px;
            border: 1px solid rgba(248,113,113,0.2);
        }
        .semester-result, .certification, .activity {
            background: #475569;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 10px;
            border: 1px solid #64748b;
        }
        .semester-result h4, .certification-header strong {
            color: #38bdf8;
            margin: 0 0 8px 0;
        }
        .activity-content {
            color: #e2e8f0;
        }
        .activity-header strong {
            color: #38bdf8;
        }
        .activity-date {
            color: #94a3b8;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .add-certification-form {
            background: #334155;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #475569;
        }
        .add-certification-form h4 {
            color: #38bdf8;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        .certification-actions {
            margin: 20px 0;
            text-align: right;
        }
        .add-button {
            display: inline-block;
            background: #38bdf8;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 1em;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid #0284c7;
        }
        .add-button:hover {
            background: #0284c7;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="dashboard-header">
        <h2>Welcome <?php echo htmlspecialchars($mentee['name']); ?></h2>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="dashboard-grid">
        <?php
        // Get mentor details
        $sql = "SELECT m.name, m.email 
                FROM mentors m
                JOIN mentor_mentee_relationship mmr ON m.id = mmr.mentor_id
                WHERE mmr.mentee_id = $mentee_id";
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

        <div class="academic-section">
            <h3>Academic Progress</h3>
            <?php
            $results = $conn->query("SELECT * FROM semester_results WHERE mentee_id=$mentee_id ORDER BY academic_year DESC, semester DESC");
            if ($results->num_rows == 0) {
                echo "<p>No academic records found.</p>";
            } else {
                while ($result = $results->fetch_assoc()) {
                    echo "<div class='semester-result'>";
                    echo "<h4>Semester {$result['semester']} ({$result['academic_year']})</h4>";
                    echo "<p>GPA: {$result['gpa']}</p>";
                    echo "</div>";
                }
            }
            ?>
        </div>

        <div class="certifications-section">
            <h3>Your Certifications</h3>
            
            <div class="certification-actions">
                <a href="add_certification_mentee.php" class="add-button" target="_blank">Add New Certification</a>
            </div>

            <?php
            $certs = $conn->query("SELECT * FROM certifications WHERE mentee_id=$mentee_id ORDER BY issue_date DESC");
            if ($certs->num_rows == 0) {
                echo "<p>No certifications uploaded yet.</p>";
            } else {
                while ($cert = $certs->fetch_assoc()) {
                    echo "<div class='certification'>";
                    echo "<div class='certification-header'>";
                    echo "<strong>{$cert['certification_name']}</strong>";
                    echo "</div>";
                    echo "<p>Issuing Organization: {$cert['issuing_organization']}</p>";
                    echo "<p>Issue Date: " . date('F j, Y', strtotime($cert['issue_date'])) . "</p>";
                    if ($cert['expiry_date']) {
                        echo "<p>Expiry Date: " . date('F j, Y', strtotime($cert['expiry_date'])) . "</p>";
                    }
                    echo "</div>";
                }
            }
            ?>
        </div>

        <div class="activities-section">
            <h3>Recent Activities</h3>
            <div class="activity-grid">
                <?php
                $acts = $conn->query("SELECT * FROM activities WHERE mentee_id=$mentee_id ORDER BY date DESC");
                if ($acts->num_rows == 0) {
                    echo "<p>No activities recorded yet.</p>";
                } else {
                    while ($act = $acts->fetch_assoc()) {
                        echo "<div class='activity'>";
                        echo "<div class='activity-content'>";
                        echo "<div class='activity-header'>";
                        echo "<strong>{$act['activity_type']}</strong>";
                        echo "</div>";
                        echo "<p>{$act['description']}</p>";
                        echo "<div class='activity-date'>" . date('F j, Y', strtotime($act['date'])) . "</div>";
                        echo "</div>";
                        echo "</div>";
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
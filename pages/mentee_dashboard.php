<?php
session_start();
include '../config/db.php';

// Debug session
error_log("Session data: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mentee') {
    error_log("Session check failed. User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . 
              ", Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));
    header("Location: ../login.php");
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

// Get mentee's name and details
$mentee_query = "SELECT m.*, u.name, u.email, u.mobile_number, u.parent_mobile_number 
                FROM mentees m 
                JOIN users u ON m.user_id = u.id 
                WHERE m.id = ?";
$stmt = $conn->prepare($mentee_query);
$stmt->bind_param("i", $mentee_id);
$stmt->execute();
$mentee = $stmt->get_result()->fetch_assoc();

// Debug mentee data
error_log("Mentee data: " . print_r($mentee, true));

// Get mentor details
$mentor_query = "SELECT u.name, u.email, u.mobile_number 
                FROM mentors m 
                JOIN users u ON m.user_id = u.id 
                JOIN mentor_mentee_relationship mmr ON m.id = mmr.mentor_id 
                WHERE mmr.mentee_id = ?";
$stmt = $conn->prepare($mentor_query);
$stmt->bind_param("i", $mentee_id);
$stmt->execute();
$mentor = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentee Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #0f172a;
            color: #ffffff;
            text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
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
            color: #38bdf8;
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
        .semester-result {
            background: #334155;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .semester-result h4 {
            color: #38bdf8;
            margin-bottom: 15px;
        }
        .semester-result p {
            color: #e2e8f0;
            margin: 8px 0;
        }
        .activity {
            background: #334155;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .activity-header strong {
            color: #38bdf8;
            font-size: 1.2em;
        }
        .activity-content p {
            color: #e2e8f0;
            margin: 8px 0;
        }
        .activity-date {
            color: #94a3b8;
            font-size: 0.9em;
        }
        .mentor-info {
            background: #334155;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .mentor-info h3 {
            color: #38bdf8;
            margin-bottom: 15px;
        }
        .mentor-info p {
            color: #e2e8f0;
            margin: 8px 0;
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
        @media print {
            * {
                color-adjust: exact;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <script src="../assets/js/theme-toggle.js"></script>

    <div class="main-content">
        <div class="container">
            <div class="dashboard-header">
                <h2>Welcome, <?php echo htmlspecialchars($mentee['name']); ?>!</h2>
            </div>

            <?php
            if ($mentor) {
                echo "<div class='section'>";
                echo "<div class='section-header'><h3>Your Mentor</h3></div>";
                echo "<p><strong>Name:</strong> " . htmlspecialchars($mentor['name']) . "</p>";
                echo "<p><strong>Email:</strong> " . htmlspecialchars($mentor['email']) . "</p>";
                echo "</div>";
            } else {
                echo "<div class='section'>";
                echo "<p style='color: #38bdf8;'>You haven't been assigned a mentor yet.</p>";
                echo "</div>";
            }
            ?>

            <div class="section">
                <div class="section-header">
                    <h3>Academic Progress</h3>
                </div>
                <?php
                $results = $conn->query("SELECT * FROM detailed_marks WHERE mentee_id=$mentee_id ORDER BY academic_year DESC, semester DESC");
                if ($results->num_rows == 0) {
                    echo "<p>No academic records found.</p>";
                } else {
                    while ($result = $results->fetch_assoc()) {
                        $overall = round(($result['first_ia_marks'] + $result['second_ia_marks'] + $result['final_exam_marks']) / 3, 2);
                        
                        echo "<div class='semester-result'>";
                        echo "<h4>Semester {$result['semester']} ({$result['academic_year']})</h4>";
                        echo "<p>First IA: {$result['first_ia_marks']}</p>";
                        echo "<p>Second IA: {$result['second_ia_marks']}</p>";
                        echo "<p>Final Exam: {$result['final_exam_marks']}</p>";
                        echo "<p>Overall: {$overall}</p>";
                        echo "<p>Project CGPA: {$result['project_cgpa']}</p>";
                        echo "</div>";
                    }
                }
                ?>
            </div>

            <div class="section">
                <div class="section-header">
                    <h3>Recent Activities</h3>
                </div>
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

            <div class="section">
                <div class="section-header">
                    <h3>My Certifications</h3>
                </div>
                <?php
                $certs = $conn->query("SELECT c.* FROM certifications c 
                                     JOIN mentees m ON c.user_id = m.user_id 
                                     WHERE m.id = $mentee_id 
                                     ORDER BY c.date DESC");
                if ($certs->num_rows == 0) {
                    echo "<p>No certifications uploaded yet.</p>";
                } else {
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-dark table-bordered'>";
                    echo "<thead>";
                    echo "<tr>";
                    echo "<th>TITLE</th>";
                    echo "<th>ISSUER</th>";
                    echo "<th>DATE</th>";
                    echo "<th>DESCRIPTION</th>";
                    echo "<th>ACTIONS</th>";
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";
                    while ($cert = $certs->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$cert['title']}</td>";
                        echo "<td>{$cert['issuer']}</td>";
                        echo "<td>" . date('M d, Y', strtotime($cert['date'])) . "</td>";
                        echo "<td>" . ($cert['description'] ? $cert['description'] : '-') . "</td>";
                        echo "<td>";
                        if ($cert['file_path']) {
                            echo "<a href='../{$cert['file_path']}' class='btn btn-sm btn-primary' target='_blank'>";
                            echo "<i class='fas fa-download'></i> View";
                            echo "</a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody>";
                    echo "</table>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>

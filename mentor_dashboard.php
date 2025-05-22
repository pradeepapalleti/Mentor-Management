<?php
session_start();
require_once 'db.php';

// Debug session
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in and is a mentor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor') {
    error_log("Session check failed. User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . 
              ", Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));
    header('Location: login.php');
    exit();
}

$mentor_id = $_SESSION['user_id'];

// Get mentor's name
$mentor_query = "SELECT u.name FROM mentors m 
                JOIN users u ON m.user_id = u.id 
                WHERE m.id = ?";
$stmt = $conn->prepare($mentor_query);
$stmt->bind_param("i", $mentor_id);
$stmt->execute();
$mentor = $stmt->get_result()->fetch_assoc();

// Debug mentor data
error_log("Mentor data: " . print_r($mentor, true));

// Get list of mentees
$mentees_query = "SELECT m.*, u.name, u.email, u.mobile_number, u.parent_mobile_number,
                 (SELECT COUNT(*) FROM feedback WHERE mentee_id = m.id) as feedback_count,
                 (SELECT COUNT(*) FROM subject_marks WHERE mentee_id = m.id) as marks_count
                 FROM mentees m
                 JOIN users u ON m.user_id = u.id
                 JOIN mentor_mentee_relationship mmr ON m.id = mmr.mentee_id
                 WHERE mmr.mentor_id = ?
                 ORDER BY u.name";
$stmt = $conn->prepare($mentees_query);
$stmt->bind_param("i", $mentor_id);
$stmt->execute();
$mentees = $stmt->get_result();

// Get recent activities
$activities_query = "SELECT a.*, u.name as mentee_name 
                    FROM activities a
                    JOIN mentees m ON a.mentee_id = m.id
                    JOIN users u ON m.user_id = u.id
                    JOIN mentor_mentee_relationship mmr ON m.id = mmr.mentee_id
                    WHERE mmr.mentor_id = ?
                    ORDER BY a.date DESC
                    LIMIT 5";
$stmt = $conn->prepare($activities_query);
$stmt->bind_param("i", $mentor_id);
$stmt->execute();
$activities = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
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
            margin: 0;
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
        .activity {
            background: #334155;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .activity-header h5 {
            color: #38bdf8;
            margin: 0;
        }
        .activity-date {
            color: #94a3b8;
            font-size: 0.9em;
        }
        .table {
            color: #e2e8f0;
        }
        .table thead th {
            color: #38bdf8;
            border-bottom: 2px solid #475569;
        }
        .table td {
            border-bottom: 1px solid #475569;
        }
        .btn-primary {
            background: #38bdf8;
            border: none;
        }
        .btn-primary:hover {
            background: #0284c7;
        }
        .btn-success {
            background: #10b981;
            border: none;
        }
        .btn-success:hover {
            background: #059669;
        }
        .btn-info {
            background: #0ea5e9;
            border: none;
        }
        .btn-info:hover {
            background: #0284c7;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="dashboard-header">
                <h2>Welcome, <?php echo htmlspecialchars($mentor['name']); ?>!</h2>
                <p class="text-light mb-0">Here's an overview of your mentorship activities</p>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="section">
                        <h4 class="text-light">Total Mentees</h4>
                        <p class="text-light h3 mb-0"><?php echo $mentees->num_rows; ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="section">
                        <h4 class="text-light">Total Feedback</h4>
                        <p class="text-light h3 mb-0"><?php 
                            $total_feedback = 0;
                            while ($mentee = $mentees->fetch_assoc()) {
                                $total_feedback += $mentee['feedback_count'];
                            }
                            echo $total_feedback;
                        ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="section">
                        <h4 class="text-light">Total Marks</h4>
                        <p class="text-light h3 mb-0"><?php 
                            $mentees->data_seek(0);
                            $total_marks = 0;
                            while ($mentee = $mentees->fetch_assoc()) {
                                $total_marks += $mentee['marks_count'];
                            }
                            echo $total_marks;
                        ?></p>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="section">
                        <div class="section-header">
                            <h3>My Mentees</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $mentees->data_seek(0);
                                    while ($mentee = $mentees->fetch_assoc()): 
                                    ?>
                                    <tr>
                                        <td class="text-light"><?php echo htmlspecialchars($mentee['name']); ?></td>
                                        <td class="text-light"><?php echo htmlspecialchars($mentee['email']); ?></td>
                                        <td class="text-light"><?php echo htmlspecialchars($mentee['mobile_number']); ?></td>
                                        <td>
                                            <a href="mentee_details.php?id=<?php echo $mentee['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-user"></i> Details
                                            </a>
                                            <a href="marks.php?mentee_id=<?php echo $mentee['id']; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-chart-bar"></i> Marks
                                            </a>
                                            <a href="feedback.php?mentee_id=<?php echo $mentee['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-comment"></i> Feedback
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="section">
                        <div class="section-header">
                            <h3>Recent Activities</h3>
                        </div>
                        <?php while ($activity = $activities->fetch_assoc()): ?>
                        <div class="activity">
                            <div class="activity-header">
                                <h5><?php echo htmlspecialchars($activity['activity_type']); ?></h5>
                                <span class="activity-date">
                                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($activity['date'])); ?>
                                </span>
                            </div>
                            <p class="text-light">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($activity['mentee_name']); ?>
                            </p>
                            <p class="text-light"><?php echo htmlspecialchars($activity['description']); ?></p>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
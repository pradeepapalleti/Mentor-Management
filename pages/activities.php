<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // For mentees, use their own ID; for mentors, use the selected mentee_id
    if ($user_role === 'mentee') {
        $mentee_id = $user_id;
    } else {
        $mentee_id = $_POST['mentee_id'];
    }
    
    $activity_type = $_POST['activity_type'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    
    // Add logging to verify the data being inserted
    error_log("Adding activity: mentee_id=$mentee_id, type=$activity_type, role=$user_role");
    
    $sql = "INSERT INTO activities (mentee_id, activity_type, description, date) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $mentee_id, $activity_type, $description, $date);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Activity added successfully!";
        header('Location: activities.php');
        exit();
    } else {
        error_log("Error adding activity: " . $conn->error);
        $error = "Error adding activity: " . $conn->error;
    }
}

// Get activities based on role
if ($user_role === 'mentor') {
    $activities_query = "SELECT a.*, u.name as mentee_name 
                        FROM activities a 
                        JOIN mentees m ON a.mentee_id = m.id 
                        JOIN users u ON m.user_id = u.id 
                        JOIN mentor_mentee_relationship mmr ON m.id = mmr.mentee_id 
                        WHERE mmr.mentor_id = ? 
                        ORDER BY a.date DESC";
    error_log("Mentor viewing activities for mentor_id: $user_id");
} else {
    // For mentees, show only their own activities
    $activities_query = "SELECT a.*, u.name as mentee_name 
                        FROM activities a 
                        JOIN mentees m ON a.mentee_id = m.id 
                        JOIN users u ON m.user_id = u.id 
                        WHERE m.id = ? 
                        ORDER BY a.date DESC";
    error_log("Mentee viewing their own activities for mentee_id: $user_id");
}
$stmt = $conn->prepare($activities_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$activities = $stmt->get_result();
error_log("Activities found: " . $activities->num_rows);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #0f172a;
            color: #ffffff;
        }
        .activities-container {
            background: #1e293b;
            border-radius: 10px;
            padding: 30px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-control {
            background: #334155;
            border: 1px solid #475569;
            color: #ffffff;
        }
        .form-control:focus {
            background: #334155;
            border-color: #38bdf8;
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
        }
        .btn-primary {
            background: #38bdf8;
            border: none;
        }
        .btn-primary:hover {
            background: #0284c7;
        }
        .activity-card {
            background: #334155;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .table {
            color: #ffffff;
        }
        .table-dark {
            background-color: #1e293b;
        }
        .table-dark th {
            background-color: #0f172a;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="activities-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Activities</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addActivityModal">
                        <i class="fas fa-plus"></i> Add New Activity
                    </button>
                </div>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Add Activity Modal -->
                <div class="modal fade" id="addActivityModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content bg-dark text-white">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Activity</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <?php if ($user_role === 'mentor'): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Mentee</label>
                                        <select name="mentee_id" class="form-control" required>
                                            <option value="">Select Mentee</option>
                                            <?php
                                            $mentees_query = "SELECT m.id, u.name 
                                                            FROM mentees m 
                                                            JOIN users u ON m.user_id = u.id 
                                                            JOIN mentor_mentee_relationship mmr ON m.id = mmr.mentee_id 
                                                            WHERE mmr.mentor_id = ?";
                                            $stmt = $conn->prepare($mentees_query);
                                            $stmt->bind_param("i", $user_id);
                                            $stmt->execute();
                                            $mentees = $stmt->get_result();
                                            while ($m = $mentees->fetch_assoc()) {
                                                echo "<option value='{$m['id']}'>" . htmlspecialchars($m['name']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Activity Type</label>
                                        <select name="activity_type" class="form-control" required>
                                            <option value="">Select Activity Type</option>
                                            <option value="Class">Class</option>
                                            <option value="Workshop">Workshop</option>
                                            <option value="Project">Project</option>
                                            <option value="Assignment">Assignment</option>
                                            <option value="Exam">Exam</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" name="date" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Add Activity</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <?php if ($user_role === 'mentor'): ?>
                                <th>Mentee</th>
                                <?php endif; ?>
                                <th>Activity Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($activity = $activities->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($activity['date'])); ?></td>
                                    <?php if ($user_role === 'mentor'): ?>
                                    <td><?php echo htmlspecialchars($activity['mentee_name']); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($activity['activity_type']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($activity['description'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mentor') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['mentee_id'])) {
    header("Location: mentor_dashboard.php");
    exit();
}

$mentee_id = $_GET['mentee_id'];
$mentor_id = $_SESSION['user_id'];

// Verify that the mentee belongs to this mentor
$check_sql = "SELECT m.name FROM mentees m 
              JOIN mentor_mentee_relationship mmr ON m.id = mmr.mentee_id 
              WHERE m.id = ? AND mmr.mentor_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $mentee_id, $mentor_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: mentor_dashboard.php");
    exit();
}

$mentee = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $activity_type = $_POST['activity_type'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    
    $sql = "INSERT INTO activities (mentee_id, activity_type, description, date) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $mentee_id, $activity_type, $description, $date);
    
    if ($stmt->execute()) {
        header("Location: mentor_dashboard.php");
        exit();
    } else {
        $error = "Error adding activity: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Activity</title>
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
            max-width: 600px;
            margin: 40px auto;
        }
        .form {
            background: #334155;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            border: 1px solid #475569;
        }
        .form h2 {
            color: #38bdf8;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.8em;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #e2e8f0;
            font-weight: 500;
            font-size: 1.1em;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #475569;
            border-radius: 6px;
            background: #475569;
            color: #ffffff;
            font-size: 1em;
            transition: all 0.3s ease;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #38bdf8;
            outline: none;
            box-shadow: 0 0 0 2px rgba(56,189,248,0.2);
            background: #334155;
        }
        .button {
            background: #38bdf8;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
        }
        .button:hover {
            background: #0284c7;
            transform: translateY(-2px);
        }
        .error {
            color: #f87171;
            margin-bottom: 20px;
            padding: 12px;
            background: rgba(248,113,113,0.1);
            border-radius: 6px;
            border: 1px solid rgba(248,113,113,0.2);
            font-size: 1em;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #e2e8f0;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            background: #475569;
            transition: all 0.3s ease;
            border: 1px solid #64748b;
        }
        .back-link:hover {
            background: #64748b;
            transform: translateY(-2px);
            color: #ffffff;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="mentor_dashboard.php" class="back-link">← Back to Dashboard</a>
    
    <div class="form">
        <h2>Add Activity</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Activity Type:</label>
                <select name="activity_type" required>
                    <option value="">Select Activity Type</option>
                    <option value="Workshop">Workshop</option>
                    <option value="Seminar">Seminar</option>
                    <option value="Conference">Conference</option>
                    <option value="Project">Project</option>
                    <option value="Competition">Competition</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description:</label>
                <textarea name="description" required placeholder="Enter activity description"></textarea>
            </div>
            <div class="form-group">
                <label>Date:</label>
                <input type="date" name="date" required>
            </div>
            <button type="submit" class="button">Add Activity</button>
        </form>
    </div>
</div>
</body>
</html>
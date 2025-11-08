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

// Get mentee details if mentee_id is provided
$mentee = null;
if (isset($_GET['mentee_id'])) {
    $mentee_query = "SELECT m.*, u.name, u.email 
                    FROM mentees m 
                    JOIN users u ON m.user_id = u.id 
                    WHERE m.id = ?";
    $stmt = $conn->prepare($mentee_query);
    $stmt->bind_param("i", $_GET['mentee_id']);
    $stmt->execute();
    $mentee = $stmt->get_result()->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mentee_id = $_POST['mentee_id'];
    $feedback_text = $_POST['feedback_text'];
    $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
    $return_url = isset($_POST['return_url']) ? $_POST['return_url'] : 'feedback.php';
    
    // Get mentor's profile id (mentors.id) from user_id
    $mentor_query = "SELECT id FROM mentors WHERE user_id = ?";
    $stmt = $conn->prepare($mentor_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $mentor_result = $stmt->get_result()->fetch_assoc();
    $mentor_id = $mentor_result['id'];
    
    $sql = "INSERT INTO feedback (mentee_id, mentor_id, feedback_text, date) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $mentee_id, $mentor_id, $feedback_text, $date);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Feedback submitted successfully!";
        header('Location: ' . $return_url);
        exit();
    } else {
        $_SESSION['error_message'] = "Error submitting feedback: " . $conn->error;
        header('Location: ' . $return_url);
        exit();
    }
}

// Get feedback history
$feedback_query = "SELECT f.*, u.name as mentee_name 
                  FROM feedback f 
                  JOIN mentees m ON f.mentee_id = m.id 
                  JOIN users u ON m.user_id = u.id 
                  WHERE f.mentor_id = ? 
                  ORDER BY f.date DESC";
$stmt = $conn->prepare($feedback_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$feedback_history = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #0f172a;
            color: #ffffff;
            text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        .feedback-container {
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
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #0284c7;
        }
        .feedback-card {
            background: #334155;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }
        @media print {
            * {
                color-adjust: exact;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        .text-align {
            text-align: match-parent;
            text-align: -webkit-match-parent;
            text-align: inherit;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="feedback-container">
                <h2 class="mb-4">Feedback</h2>
                
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

                <?php if ($user_role === 'mentor'): ?>
                    <form method="POST" class="mb-4">
                        <div class="mb-3">
                            <label for="mentee_id" class="form-label">Select Mentee</label>
                            <select id="mentee_id" name="mentee_id" class="form-control" required 
                                    title="Select a mentee" aria-label="Select a mentee">
                                <option value="">Choose a mentee...</option>
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
                        
                        <div class="mb-3">
                            <label for="feedback_text" class="form-label">Feedback</label>
                            <textarea id="feedback_text" name="feedback_text" class="form-control" rows="4" required 
                                      placeholder="Enter your feedback here" title="Feedback text"
                                      aria-label="Enter your feedback"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" title="Submit feedback" aria-label="Submit feedback">
                            Submit Feedback
                        </button>
                    </form>
                <?php endif; ?>

                <h3 class="mb-3">Feedback History</h3>
                <?php while ($feedback = $feedback_history->fetch_assoc()): ?>
                    <div class="feedback-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="mb-0"><?php echo htmlspecialchars($feedback['mentee_name']); ?></h5>
                            <small class="text-muted"><?php echo date('M d, Y', strtotime($feedback['date'])); ?></small>
                        </div>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($feedback['feedback_text'])); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mentor') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mentor_id = $_SESSION['user_id'];
    $selected_mentor = $_POST['selected_mentor'];
    
    $sql = "UPDATE mentors SET mentor_id = ? WHERE mentor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $selected_mentor, $mentor_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Mentor selected successfully!";
    } else {
        $_SESSION['error'] = "Error selecting mentor.";
    }
    header("Location: mentor_dashboard.php");
    exit;
}

// Get available mentors
$sql = "SELECT mentor_id, name FROM mentors WHERE is_mentor_mentee = FALSE AND mentor_id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Your Mentor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Select Your Mentor</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="select_mentor.php">
        <div class="form-group">
            <select name="selected_mentor" required>
                <option value="">Select a Mentor</option>
                <?php while ($mentor = $result->fetch_assoc()): ?>
                    <option value="<?php echo $mentor['mentor_id']; ?>">
                        <?php echo htmlspecialchars($mentor['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit">Select Mentor</button>
    </form>
    
    <a href="mentor_dashboard.php" class="back-link">Back to Dashboard</a>
</div>
</body>
</html> 
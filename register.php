<?php
session_start();
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'mentor'; // Only mentors can register
    $is_mentor_mentee = isset($_POST['is_mentor_mentee']) ? 1 : 0;
    // New required fields (schema requires mobile_number)
    $mobile_number = isset($_POST['mobile_number']) ? $_POST['mobile_number'] : '';
    $parent_mobile_number = isset($_POST['parent_mobile_number']) && $_POST['parent_mobile_number'] !== '' ? $_POST['parent_mobile_number'] : null;
    
    // Check if email already exists in users table
    $check_sql = "SELECT email FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already exists!";
    } else {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // First insert into users table (include mobile numbers to match schema)
            $user_sql = "INSERT INTO users (name, email, password, role, mobile_number, parent_mobile_number) VALUES (?, ?, ?, ?, ?, ?)";
            $user_stmt = $conn->prepare($user_sql);
            if (!$user_stmt) {
                throw new Exception('Prepare failed for users insert: ' . $conn->error);
            }
            $user_stmt->bind_param("ssssss", $name, $email, $password, $role, $mobile_number, $parent_mobile_number);
            if (!$user_stmt->execute()) {
                throw new Exception('Execute failed for users insert: ' . $user_stmt->error);
            }

            // Use the inserted users.id for mentors.user_id
            $user_id = (int)$conn->insert_id;

            // Insert into mentors table
            $mentor_sql = "INSERT INTO mentors (user_id, is_mentor_mentee) VALUES (?, ?)";
            $mentor_stmt = $conn->prepare($mentor_sql);
            if (!$mentor_stmt) {
                throw new Exception('Prepare failed for mentors insert: ' . $conn->error);
            }
            $mentor_stmt->bind_param("ii", $user_id, $is_mentor_mentee);
            if (!$mentor_stmt->execute()) {
                throw new Exception('Execute failed for mentors insert: ' . $mentor_stmt->error);
            }

            $conn->commit();
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: login.php");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Error in registration: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register as Mentor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Register as Mentor</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="register.php" class="register-form">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="mobile_number">Mobile Number</label>
            <input type="text" id="mobile_number" name="mobile_number" required placeholder="Enter your mobile number">
        </div>
        
        <div class="form-group">
            <label for="parent_mobile_number">Parent Mobile Number (Optional)</label>
            <input type="text" id="parent_mobile_number" name="parent_mobile_number" placeholder="Enter parent mobile number (optional)">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_mentor_mentee" id="is_mentor_mentee">
                I am also a mentee and would like to have a mentor
            </label>
        </div>
        
        <button type="submit" class="button">Register as Mentor</button>
    </form>
    
    <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>
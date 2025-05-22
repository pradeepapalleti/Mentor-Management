<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'mentor'; // Only mentors can register
    $is_mentor_mentee = isset($_POST['is_mentor_mentee']) ? 1 : 0;
    
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
            // First insert into users table
            $user_sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("ssss", $name, $email, $password, $role);
            $user_stmt->execute();
            
            // Insert into mentors table
            $mentor_sql = "INSERT INTO mentors (user_id, is_mentor_mentee) VALUES (?, ?)";
            $mentor_stmt = $conn->prepare($mentor_sql);
            $mentor_stmt->bind_param("ii", $conn->insert_id, $is_mentor_mentee);
            $mentor_stmt->execute();
            
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
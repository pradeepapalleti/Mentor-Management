<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: text/html; charset=utf-8');

// Debug session data
error_log("Session data at start: " . print_r($_SESSION, true));
error_log("Session ID: " . session_id());

include 'db.php';

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    error_log("User already logged in. Redirecting to dashboard.");
    if ($_SESSION['role'] === 'mentor') {
        header("Location: mentor_dashboard.php");
    } else {
        header("Location: mentee_dashboard.php");
    }
    exit();
}

// Debug POST data
error_log("POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    error_log("Login attempt for email: " . $email);
    
    // First check in users table
    $user_sql = "SELECT * FROM users WHERE email = ?";
    $user_stmt = $conn->prepare($user_sql);
    if (!$user_stmt) {
        error_log("User prepare failed: " . $conn->error);
        die("Database error. Please try again later.");
    }
    
    $user_stmt->bind_param("s", $email);
    if (!$user_stmt->execute()) {
        error_log("User execute failed: " . $user_stmt->error);
        die("Database error. Please try again later.");
    }
    
    $user_result = $user_stmt->get_result();
    
    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        error_log("Found user: " . print_r($user, true));
        
        if (password_verify($password, $user['password'])) {
            error_log("Password verified successfully");
            
            // Get additional details based on role
            if ($user['role'] == 'mentor') {
                $details_sql = "SELECT * FROM mentors WHERE email = ?";
                $details_stmt = $conn->prepare($details_sql);
                if (!$details_stmt) {
                    error_log("Mentor details prepare failed: " . $conn->error);
                    die("Database error. Please try again later.");
                }
                
                $details_stmt->bind_param("s", $email);
                if (!$details_stmt->execute()) {
                    error_log("Mentor details execute failed: " . $details_stmt->error);
                    die("Database error. Please try again later.");
                }
                
                $details = $details_stmt->get_result()->fetch_assoc();
                
                if ($details) {
                    $_SESSION['user_id'] = $details['id'];
                    $_SESSION['name'] = $details['name'];
                    $_SESSION['email'] = $details['email'];
                    $_SESSION['role'] = 'mentor';
                    
                    error_log("Mentor login successful for: " . $email);
                    error_log("Session data before redirect: " . print_r($_SESSION, true));
                    
                    // Ensure session is written before redirect
                    session_write_close();
                    header("Location: mentor_dashboard.php");
                    exit();
                } else {
                    error_log("Mentor details not found for: " . $email);
                    $_SESSION['error'] = "Mentor details not found!";
                }
            } else {
                $details_sql = "SELECT * FROM mentees WHERE email = ?";
                $details_stmt = $conn->prepare($details_sql);
                if (!$details_stmt) {
                    error_log("Mentee details prepare failed: " . $conn->error);
                    die("Database error. Please try again later.");
                }
                
                $details_stmt->bind_param("s", $email);
                if (!$details_stmt->execute()) {
                    error_log("Mentee details execute failed: " . $details_stmt->error);
                    die("Database error. Please try again later.");
                }
                
                $details = $details_stmt->get_result()->fetch_assoc();
                
                if ($details) {
                    $_SESSION['user_id'] = $details['id'];
                    $_SESSION['name'] = $details['name'];
                    $_SESSION['email'] = $details['email'];
                    $_SESSION['role'] = 'mentee';
                    
                    error_log("Mentee login successful for: " . $email);
                    error_log("Session data before redirect: " . print_r($_SESSION, true));
                    
                    // Ensure session is written before redirect
                    session_write_close();
                    header("Location: mentee_dashboard.php");
                    exit();
                } else {
                    error_log("Mentee details not found for: " . $email);
                    $_SESSION['error'] = "Mentee details not found!";
                }
            }
        } else {
            error_log("Invalid password for user: " . $email);
            $_SESSION['error'] = "Invalid password!";
        }
    } else {
        error_log("Email not found: " . $email);
        $_SESSION['error'] = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Login</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="msg success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="login-form" id="loginForm">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required autocomplete="email">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        
        <button type="submit" class="button" id="loginButton">Login</button>
    </form>
    
    <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
</div>

<script>
// Add form submission debugging
document.getElementById('loginForm').addEventListener('submit', function(e) {
    console.log('Form submitted');
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    console.log('Email:', email);
    console.log('Password length:', password.length);
    
    // Prevent form submission if fields are empty
    if (!email || !password) {
        e.preventDefault();
        alert('Please fill in all fields');
        return false;
    }
    
    // Disable the button to prevent double submission
    document.getElementById('loginButton').disabled = true;
    
    // Show loading state
    document.getElementById('loginButton').textContent = 'Logging in...';
    
    // Submit the form
    return true;
});

// Enable the button if the form is reset
document.getElementById('loginForm').addEventListener('reset', function() {
    document.getElementById('loginButton').disabled = false;
    document.getElementById('loginButton').textContent = 'Login';
});
</script>
</body>
</html>
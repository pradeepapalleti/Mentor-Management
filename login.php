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
    
    // Check in users table
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
                $details_sql = "SELECT m.*, u.name, u.email 
                              FROM mentors m 
                              JOIN users u ON m.user_id = u.id 
                              WHERE m.user_id = ?";
                $details_stmt = $conn->prepare($details_sql);
                if (!$details_stmt) {
                    error_log("Mentor details prepare failed: " . $conn->error);
                    die("Database error. Please try again later.");
                }
                
                $details_stmt->bind_param("i", $user['id']);
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
                $details_sql = "SELECT m.*, u.name, u.email 
                              FROM mentees m 
                              JOIN users u ON m.user_id = u.id 
                              WHERE m.user_id = ?";
                $details_stmt = $conn->prepare($details_sql);
                if (!$details_stmt) {
                    error_log("Mentee details prepare failed: " . $conn->error);
                    die("Database error. Please try again later.");
                }
                
                $details_stmt->bind_param("i", $user['id']);
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
        error_log("No user found with email: " . $email);
        $_SESSION['error'] = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mentor Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            color: #1e293b;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: #64748b;
            margin: 0;
        }
        .form-control {
            padding: 0.75rem;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .form-control:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 0.2rem rgba(56,189,248,0.25);
        }
        .btn-primary {
            background: #38bdf8;
            border: none;
            padding: 0.75rem;
            border-radius: 6px;
            width: 100%;
            font-weight: 500;
            margin-top: 1rem;
        }
        .btn-primary:hover {
            background: #0ea5e9;
        }
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
        }
        .register-link a {
            color: #38bdf8;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .alert {
            margin-bottom: 1rem;
            border-radius: 6px;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Please login to your account</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
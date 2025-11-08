<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: text/html; charset=utf-8');

// Debug session data
error_log("Session data at start: " . print_r($_SESSION, true));
error_log("Session ID: " . session_id());

include 'config/db.php';

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    error_log("User already logged in. Redirecting to dashboard.");
    if ($_SESSION['role'] === 'mentor') {
        header("Location: pages/mentor_dashboard.php");
    } else {
        header("Location: pages/mentee_dashboard.php");
    }
    exit();
}

// If accessed directly without POST, redirect to login form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pages/login_form.php");
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
                    // Most pages expect session user_id to be the mentor/mentee profile id (mentors.id / mentees.id).
                    // Keep that behavior for backward compatibility and also store canonical users.id as `users_id`.
                    $_SESSION['user_id'] = $details['id']; // mentor record id (used across the app)
                    $_SESSION['users_id'] = $user['id'];   // canonical users.id
                    $_SESSION['profile_id'] = $details['id']; // mentor record id (alias)
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = 'mentor';
                    
                    error_log("Mentor login successful for: " . $email);
                    error_log("Session data before redirect: " . print_r($_SESSION, true));
                    
                    // Ensure session is written before redirect
                    session_write_close();
                    header("Location: pages/mentor_dashboard.php");
                    exit();
                } else {
                    error_log("Mentor details not found for: " . $email);
                    $_SESSION['error'] = "Mentor details not found!";
                    header("Location: pages/login_form.php");
                    exit();
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
                    // Keep legacy behavior: session user_id is mentee.id
                    $_SESSION['user_id'] = $details['id']; // mentee record id
                    $_SESSION['users_id'] = $user['id'];   // canonical users.id
                    $_SESSION['profile_id'] = $details['id']; // mentee record id (alias)
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = 'mentee';
                    
                    error_log("Mentee login successful for: " . $email);
                    error_log("Session data before redirect: " . print_r($_SESSION, true));
                    
                    // Ensure session is written before redirect
                    session_write_close();
                    header("Location: pages/mentee_dashboard.php");
                    exit();
                } else {
                    error_log("Mentee details not found for: " . $email);
                    $_SESSION['error'] = "Mentee details not found!";
                    header("Location: pages/login_form.php");
                    exit();
                }
            }
        } else {
            error_log("Invalid password for user: " . $email);
            $_SESSION['error'] = "Invalid email or password!";
            header("Location: pages/login_form.php");
            exit();
        }
    } else {
        error_log("No user found with email: " . $email);
        $_SESSION['error'] = "Invalid email or password!";
        header("Location: pages/login_form.php");
        exit();
    }
}

// If we reach here without POST, redirect to login form
header("Location: pages/login_form.php");
exit();
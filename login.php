<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] == 'mentor') {
                header("Location: mentor_dashboard.php");
            } else {
                header("Location: mentee_dashboard.php");
            }
            exit;
        } else {
            $msg = "Invalid credentials";
        }
    } else {
        $msg = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<h2>Login</h2>
<?php 
if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    echo "<p style='color: green; background: #d4edda; padding: 10px; border-radius: 4px; text-align: center;'>Registration successful! Please login with your credentials.</p>";
}
if (isset($msg)) echo "<p class='msg'>$msg</p>"; 
?>
<form method="POST" action="login.php">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Login</button>
</form>
<p>Don't have an account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>
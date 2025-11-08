<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mentor Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error-message {
            background-color: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #fcc;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome to Mentorship System</h2>
        <h3>Login</h3>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php 
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">
            Don't have an account? <a href="register.html" style="color: #007bff; text-decoration: none;">Register here</a>
        </p>
    </div>
</body>
</html>

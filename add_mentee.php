<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mentor') {
    header("Location: login.php");
    exit();
}

$mentor_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert into mentees table
        $sql = "INSERT INTO mentees (name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $password);
        $stmt->execute();
        $mentee_id = $conn->insert_id;
        
        // Insert into users table
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'mentee')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $password);
        $stmt->execute();
        
        // Create mentor-mentee relationship
        $sql = "INSERT INTO mentor_mentee_relationship (mentor_id, mentee_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $mentor_id, $mentee_id);
        $stmt->execute();
        
        $conn->commit();
        header("Location: mentor_dashboard.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error adding mentee: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Mentee</title>
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
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #475569;
            border-radius: 6px;
            background: #475569;
            color: #ffffff;
            font-size: 1em;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
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
        <h2>Add New Mentee</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required placeholder="Enter mentee's full name">
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required placeholder="Enter mentee's email address">
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required placeholder="Enter a secure password">
            </div>
            <button type="submit" class="button">Add Mentee</button>
        </form>
    </div>
</div>
</body>
</html> 
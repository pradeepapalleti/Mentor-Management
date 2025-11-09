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
            header("Location: pages/login_form.php");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Error in registration: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Mentor Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6C63FF;
            --primary-hover: #5A52D5;
            --background-dark: #1A1A1A;
            --container-bg: #2D2D2D;
            --text-light: #FFFFFF;
            --text-muted: #B3B3B3;
            --input-bg: #3D3D3D;
            --border-color: #404040;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--background-dark);
            color: var(--text-light);
            line-height: 1.6;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(108, 99, 255, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(76, 175, 80, 0.1) 0%, transparent 20%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: var(--container-bg);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 500px;
            border: 1px solid var(--border-color);
        }

        .register-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .register-header h1 {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .register-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
            color: #ef4444;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background: var(--input-bg);
            color: var(--text-light);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            background: var(--primary-color);
            color: var(--text-light);
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(108, 99, 255, 0.3);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: normal;
        }

        .text-center {
            text-align: center;
        }

        a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="register-container">
    <div class="register-header">
        <h1>Register as Mentor</h1>
        <p>Join our mentorship community</p>
    </div>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="register.php">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>
        
        <div class="form-group">
            <label for="mobile_number">Mobile Number</label>
            <input type="tel" id="mobile_number" name="mobile_number" class="form-control" placeholder="Enter mobile number" required>
        </div>
        
        <div class="form-group">
            <label for="parent_mobile_number">Parent Mobile Number (Optional)</label>
            <input type="tel" id="parent_mobile_number" name="parent_mobile_number" class="form-control" placeholder="Parent mobile (optional)">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Create a password" required>
        </div>
        
        <div class="checkbox-group">
            <input type="checkbox" name="is_mentor_mentee" id="is_mentor_mentee">
            <label for="is_mentor_mentee">I am also a mentee and would like to have a mentor</label>
        </div>
        
        <button type="submit" class="btn-primary">
            <i class="fas fa-user-plus"></i> Register as Mentor
        </button>
    </form>
    
    <p style="margin-top: 20px; color: var(--text-muted);" class="text-center">
        Already have an account? <a href="pages/login_form.php">Login here</a>
    </p>
</div>
</body>
</html>
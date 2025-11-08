<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is a mentor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor') {
    header('Location: login.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate all required fields are present
    if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['mobile_number']) || 
        !isset($_POST['parent_mobile_number']) || !isset($_POST['usn']) || 
        !isset($_POST['semester']) || !isset($_POST['department'])) {
        $error = "All fields are required!";
    } else {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $mobile_number = trim($_POST['mobile_number']);
        $parent_mobile_number = trim($_POST['parent_mobile_number']);
        $usn = trim($_POST['usn']);
        $semester = intval($_POST['semester']);
        $department = trim($_POST['department']);
        $password = password_hash($usn, PASSWORD_DEFAULT); // Use USN as initial password
        $mentor_id = $_SESSION['user_id'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // First insert into users table
            $user_sql = "INSERT INTO users (name, email, password, role, mobile_number, parent_mobile_number) 
                        VALUES (?, ?, ?, 'mentee', ?, ?)";
            $user_stmt = $conn->prepare($user_sql);
            if (!$user_stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $user_stmt->bind_param("sssss", $name, $email, $password, $mobile_number, $parent_mobile_number);
            if (!$user_stmt->execute()) {
                // Check if duplicate email error
                if (strpos($user_stmt->error, 'Duplicate entry') !== false && strpos($user_stmt->error, 'email') !== false) {
                    throw new Exception("This email address is already registered. Please use a different email.");
                }
                throw new Exception("Execute failed: " . $user_stmt->error);
            }
            $user_id = $conn->insert_id;

            // Then insert into mentees table with new fields
            $mentee_sql = "INSERT INTO mentees (user_id, usn, semester, department) VALUES (?, ?, ?, ?)";
            $mentee_stmt = $conn->prepare($mentee_sql);
            if (!$mentee_stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $mentee_stmt->bind_param("isis", $user_id, $usn, $semester, $department);
            if (!$mentee_stmt->execute()) {
                // Check if duplicate USN error
                if (strpos($mentee_stmt->error, 'Duplicate entry') !== false && strpos($mentee_stmt->error, 'usn') !== false) {
                    throw new Exception("This USN is already registered. Please check the USN.");
                }
                throw new Exception("Execute failed: " . $mentee_stmt->error);
            }
            $mentee_id = $conn->insert_id;

            // Create mentor-mentee relationship
            $relationship_sql = "INSERT INTO mentor_mentee_relationship (mentor_id, mentee_id) VALUES (?, ?)";
            $relationship_stmt = $conn->prepare($relationship_sql);
            if (!$relationship_stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $relationship_stmt->bind_param("ii", $mentor_id, $mentee_id);
            if (!$relationship_stmt->execute()) {
                throw new Exception("Execute failed: " . $relationship_stmt->error);
            }

            // Commit transaction
            $conn->commit();

            // Redirect to mentee list with success message
            $_SESSION['success_message'] = "Mentee added successfully! Initial password is their USN.";
            header('Location: mentee_list.php');
            exit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = "Error adding mentee: " . $e->getMessage();
        }
    }
}

// If we reach here (not POST or has error), redirect to mentee_list
// The form should be filled in the modal on mentee_list.php
if (isset($error)) {
    $_SESSION['error'] = $error;
}
header('Location: mentee_list.php');
exit();

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
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #475569;
            border-radius: 6px;
            background: #475569;
            color: #ffffff;
            font-size: 1em;
            transition: all 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus {
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
                <label>USN:</label>
                <input type="text" name="usn" required placeholder="Enter mentee's USN" pattern="[1-9][A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{3}" title="Please enter a valid USN (e.g., 1MS20CS001)">
            </div>
            <div class="form-group">
                <label>Semester:</label>
                <select name="semester" required>
                    <option value="">Select Semester</option>
                    <?php for($i = 1; $i <= 8; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Department:</label>
                <select name="department" required>
                    <option value="">Select Department</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Information Science">Information Science</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Electrical">Electrical</option>
                    <option value="Mechanical">Mechanical</option>
                    <option value="Civil">Civil</option>
                </select>
            </div>
            <div class="form-group">
                <label>Mobile Number:</label>
                <input type="text" name="mobile_number" required placeholder="Enter mentee's mobile number">
            </div>
            <div class="form-group">
                <label>Parent Mobile Number:</label>
                <input type="text" name="parent_mobile_number" required placeholder="Enter mentee's parent mobile number">
            </div>
            <button type="submit" class="button">Add Mentee</button>
        </form>
    </div>
</div>
</body>
</html> 
<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Get user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// If user is a mentee, get additional mentee details
$mentee_details = null;
if ($user_role === 'mentee') {
    $mentee_query = "SELECT m.*, u.name, u.email, u.mobile_number, u.parent_mobile_number 
                    FROM mentees m 
                    JOIN users u ON m.user_id = u.id 
                    WHERE m.user_id = ?";
    $stmt = $conn->prepare($mentee_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $mentee_details = $stmt->get_result()->fetch_assoc();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile_number = $_POST['mobile_number'];
    $parent_mobile_number = $_POST['parent_mobile_number'] ?? null;
    
    $update_sql = "UPDATE users SET name = ?, email = ?, mobile_number = ?, parent_mobile_number = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $name, $email, $mobile_number, $parent_mobile_number, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header('Location: profile.php');
        exit();
    } else {
        $error = "Error updating profile: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0f172a;
            color: #ffffff;
        }
        .profile-container {
            background: #1e293b;
            border-radius: 10px;
            padding: 30px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .profile-header {
            background: #334155;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #475569;
        }
        .profile-header h2 {
            color: #38bdf8;
            margin: 0;
        }
        .form-control {
            background: #334155;
            border: 1px solid #475569;
            color: #ffffff;
        }
        .form-control:focus {
            background: #334155;
            border-color: #38bdf8;
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
        }
        .btn-primary {
            background: #38bdf8;
            border: none;
        }
        .btn-primary:hover {
            background: #0284c7;
        }
        .profile-info {
            background: #334155;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .profile-info h3 {
            color: #38bdf8;
            margin-bottom: 15px;
        }
        .profile-info p {
            color: #e2e8f0;
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="section">
                <div class="section-header">
                    <h3>Profile</h3>
                </div>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success_message']; 
                        unset($_SESSION['success_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <?php if ($user_role === 'mentee' && $mentee_details): ?>
                            <div class="card bg-dark text-white mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Academic Information</h5>
                                    <p><strong>USN:</strong> <?php echo htmlspecialchars($mentee_details['usn']); ?></p>
                                    <p><strong>Semester:</strong> <?php echo htmlspecialchars($mentee_details['semester']); ?></p>
                                    <p><strong>Department:</strong> <?php echo htmlspecialchars($mentee_details['department']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h5 class="card-title">Personal Information</h5>
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Mobile Number</label>
                                        <input type="tel" name="mobile_number" class="form-control" value="<?php echo htmlspecialchars($user['mobile_number']); ?>" required>
                                    </div>
                                    <?php if ($user_role === 'mentee'): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Parent Mobile Number</label>
                                        <input type="text" name="parent_mobile_number" class="form-control" value="<?php echo htmlspecialchars($user['parent_mobile_number'] ?? ''); ?>">
                                    </div>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
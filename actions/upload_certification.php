<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mentee') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $issuer = $_POST['issuer'];
    $issue_date = $_POST['issue_date'];
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    
    // Handle file upload
    $target_dir = "../uploads/certificates/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["certificate_file"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    $file_path = "uploads/certificates/" . $new_filename;
    
    if (move_uploaded_file($_FILES["certificate_file"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO certifications (user_id, title, issuer, date, description, file_path) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $user_id, $title, $issuer, $issue_date, $description, $file_path);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Certification uploaded successfully!";
        } else {
            $_SESSION['error'] = "Error uploading certification.";
        }
    } else {
        $_SESSION['error'] = "Error uploading file.";
    }
    header("Location: ../pages/mentee_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Certification</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
    <div class="container">
        <h2>Upload Certification</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Certification Title</label>
                <input type="text" name="title" placeholder="e.g., AWS Certified Solutions Architect" required>
            </div>
            <div class="form-group">
                <label>Issuing Organization</label>
                <input type="text" name="issuer" placeholder="e.g., Amazon Web Services" required>
            </div>
            <div class="form-group">
                <label>Issue Date</label>
                <input type="date" name="issue_date" required>
            </div>
            <div class="form-group">
                <label>Description (Optional)</label>
                <textarea name="description" placeholder="Brief description about the certification"></textarea>
            </div>
            <div class="form-group">
                <label>Upload Certificate (PDF or Image)</label>
                <input type="file" name="certificate_file" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
            <button type="submit" class="button">Upload Certification</button>
            <a href="../pages/mentee_dashboard.php" class="button" style="background: #6c757d; margin-left: 10px;">Back to Dashboard</a>
        </form>
    </div>
</div>
</body>
</html> 

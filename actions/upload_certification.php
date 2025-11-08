<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mentee') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mentee_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $issuer = $_POST['issuer'];
    $issue_date = $_POST['issue_date'];
    $expiry_date = $_POST['expiry_date'];
    
    // Handle file upload
    $target_dir = "certificates/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["certificate_file"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($_FILES["certificate_file"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO certifications (mentee_id, title, issuer, issue_date, expiry_date, certificate_file) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $mentee_id, $title, $issuer, $issue_date, $expiry_date, $new_filename);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Certification uploaded successfully!";
        } else {
            $_SESSION['error'] = "Error uploading certification.";
        }
    } else {
        $_SESSION['error'] = "Error uploading file.";
    }
    header("Location: mentee_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Certification</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Upload Certification</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="upload_certification.php" enctype="multipart/form-data">
        <div class="form-group">
            <input type="text" name="title" placeholder="Certification Title" required>
        </div>
        <div class="form-group">
            <input type="text" name="issuer" placeholder="Issuing Organization" required>
        </div>
        <div class="form-group">
            <input type="date" name="issue_date" required>
            <label>Issue Date</label>
        </div>
        <div class="form-group">
            <input type="date" name="expiry_date">
            <label>Expiry Date (if applicable)</label>
        </div>
        <div class="form-group">
            <input type="file" name="certificate_file" accept=".pdf,.jpg,.jpeg,.png" required>
            <label>Upload Certificate (PDF or Image)</label>
        </div>
        <button type="submit">Upload Certification</button>
    </form>
    
    <a href="mentee_dashboard.php" class="back-link">Back to Dashboard</a>
</div>
</body>
</html> 
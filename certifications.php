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

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $issuer = $_POST['issuer'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    
    // Handle file upload
    $target_dir = "uploads/certificates/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($_FILES["certificate_file"]["name"]);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
    // Check if image file is a actual image or fake image
    if(isset($_FILES["certificate_file"])) {
        $check = getimagesize($_FILES["certificate_file"]["tmp_name"]);
        if($check === false) {
            $error = "File is not an image.";
            $uploadOk = 0;
        }
    }
    
    // Check file size (5MB max)
    if ($_FILES["certificate_file"]["size"] > 5000000) {
        $error = "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "pdf") {
        $error = "Sorry, only JPG, JPEG, PNG & PDF files are allowed.";
        $uploadOk = 0;
    }
    
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["certificate_file"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO certifications (user_id, title, issuer, date, description, file_path) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssss", $user_id, $title, $issuer, $date, $description, $file_name);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Certificate uploaded successfully!";
                header('Location: certifications.php');
                exit();
            } else {
                $error = "Error saving certificate details: " . $conn->error;
                unlink($target_file); // Delete uploaded file if database insert fails
            }
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    }
}

// Get certifications
$certifications_query = "SELECT c.*, u.name 
                        FROM certifications c 
                        JOIN users u ON c.user_id = u.id 
                        WHERE c.user_id = ? 
                        ORDER BY c.date DESC";
$stmt = $conn->prepare($certifications_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$certifications = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #0f172a;
            color: #ffffff;
        }
        .cert-container {
            background: #1e293b;
            border-radius: 10px;
            padding: 30px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        .cert-card {
            background: #334155;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .cert-image {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="cert-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Certifications</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCertificationModal">
                        <i class="fas fa-plus"></i> Add New Certificate
                    </button>
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

                <!-- Add Certification Modal -->
                <div class="modal fade" id="addCertificationModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content bg-dark text-white">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Certificate</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Certificate Title</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Issuing Organization</label>
                                        <input type="text" name="issuer" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Date Received</label>
                                        <input type="date" name="date" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Certificate File (JPG, PNG, or PDF)</label>
                                        <input type="file" name="certificate_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Upload Certificate</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Issuer</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($cert = $certifications->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cert['title']); ?></td>
                                    <td><?php echo htmlspecialchars($cert['issuer']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($cert['date'])); ?></td>
                                    <td><?php echo htmlspecialchars($cert['description']); ?></td>
                                    <td>
                                        <a href="uploads/certificates/<?php echo htmlspecialchars($cert['file_path']); ?>" 
                                           class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fas fa-download"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
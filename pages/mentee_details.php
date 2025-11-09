<?php
session_start();
require_once '../config/db.php';  // Changed from includes/db_connect.php to db.php

// Check if user is logged in and is a mentor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor') {
    header('Location: ../login.php');
    exit();
}

$mentee_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$mentee_id) {
    header('Location: mentor_dashboard.php');
    exit();
}

// Get mentee details
$mentee_query = "SELECT m.*, u.name, u.email, u.mobile_number, u.parent_mobile_number 
                FROM mentees m 
                JOIN users u ON m.user_id = u.id 
                WHERE m.id = ?";
$stmt = $conn->prepare($mentee_query);
$stmt->bind_param("i", $mentee_id);
$stmt->execute();
$mentee = $stmt->get_result()->fetch_assoc();

// Get certifications for the mentee
$cert_query = "SELECT c.* FROM certifications c 
               JOIN mentees m ON c.user_id = m.user_id 
               WHERE m.id = ? 
               ORDER BY c.date DESC";
$stmt = $conn->prepare($cert_query);
$stmt->bind_param("i", $mentee_id);
$stmt->execute();
$certifications = $stmt->get_result();

// Get activities for the mentee
$activity_query = "SELECT * FROM activities WHERE mentee_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($activity_query);
$stmt->bind_param("i", $mentee_id);
$stmt->execute();
$activities = $stmt->get_result();

// Get feedback for the mentee
$feedback_query = "SELECT f.*, m.user_id as mentor_user_id, u.name as mentor_name 
                   FROM feedback f 
                   JOIN mentors m ON f.mentor_id = m.id 
                   JOIN users u ON m.user_id = u.id 
                   WHERE f.mentee_id = ? 
                   ORDER BY f.date DESC";
$stmt = $conn->prepare($feedback_query);
$stmt->bind_param("i", $mentee_id);
$stmt->execute();
$feedbacks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentee Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #0f172a;
            color: #ffffff;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .container {
            background: #1e293b;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .section {
            background: #334155;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            color: #ffffff;
            margin-bottom: 20px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #475569;
            padding-bottom: 10px;
        }
        .section-header h3 {
            color: #38bdf8;
            font-size: 1.4em;
        }
        .btn-primary {
            background: #38bdf8;
            border: none;
        }
        .btn-primary:hover {
            background: #0284c7;
        }
        .table {
            color: #ffffff;
        }
        .table-dark {
            background-color: #334155;
        }
        .table-dark th {
            background-color: #1e40af;
            color: white;
        }
        .table-dark td {
            color: #e2e8f0;
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
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <script src="../assets/js/theme-toggle.js"></script>

    <div class="main-content">
        <div class="container">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="section">
                <div class="section-header">
                    <h3>Mentee Details</h3>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($mentee['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($mentee['email']); ?></p>
                        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($mentee['mobile_number']); ?></p>
                        <p><strong>Parent Mobile:</strong> <?php echo htmlspecialchars($mentee['parent_mobile_number']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Certifications Section -->
            <div class="section">
                <div class="section-header">
                    <h3><i class="fas fa-certificate"></i> Certifications</h3>
                </div>

                <?php if ($certifications->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($cert = $certifications->fetch_assoc()): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card" style="background: #475569; border: none; border-left: 4px solid #ec4899;">
                                    <div class="card-body">
                                        <h5 class="card-title" style="color: #38bdf8;">
                                            <?php echo htmlspecialchars($cert['title']); ?>
                                        </h5>
                                        <p class="card-text" style="color: #e2e8f0;">
                                            <strong>Issuer:</strong> <?php echo htmlspecialchars($cert['issuer']); ?><br>
                                            <strong>Date:</strong> <?php echo date('M d, Y', strtotime($cert['date'])); ?><br>
                                            <?php if ($cert['description']): ?>
                                                <strong>Description:</strong> <?php echo htmlspecialchars($cert['description']); ?><br>
                                            <?php endif; ?>
                                        </p>
                                        <?php if ($cert['file_path']): ?>
                                            <a href="../<?php echo htmlspecialchars($cert['file_path']); ?>" 
                                               class="btn btn-sm btn-primary" target="_blank">
                                                <i class="fas fa-download"></i> View Certificate
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" style="background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.3); color: #38bdf8;">
                        <i class="fas fa-info-circle"></i> No certifications uploaded yet.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Activities Section -->
            <div class="section">
                <div class="section-header">
                    <h3><i class="fas fa-tasks"></i> Activities</h3>
                </div>

                <?php if ($activities->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-bordered">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($activity = $activities->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <span class="badge" style="background: #8b5cf6; padding: 6px 12px;">
                                                <?php echo htmlspecialchars($activity['activity_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo nl2br(htmlspecialchars($activity['description'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($activity['date'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" style="background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.3); color: #38bdf8;">
                        <i class="fas fa-info-circle"></i> No activities recorded yet.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Feedback Section -->
            <div class="section">
                <div class="section-header">
                    <h3>Feedback History</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFeedbackModal">
                        <i class="fas fa-plus"></i> Add Feedback
                    </button>
                </div>

                <?php if ($feedbacks->num_rows > 0): ?>
                    <?php while ($feedback = $feedbacks->fetch_assoc()): ?>
                        <div class="feedback-card mb-3" style="background: #475569; padding: 15px; border-radius: 8px; border-left: 4px solid #38bdf8;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong style="color: #38bdf8;">
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($feedback['mentor_name']); ?>
                                    </strong>
                                    <span style="color: #94a3b8; margin-left: 15px;">
                                        <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($feedback['date'])); ?>
                                    </span>
                                </div>
                            </div>
                            <p style="color: #e2e8f0; margin: 0;">
                                <?php echo nl2br(htmlspecialchars($feedback['feedback_text'])); ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info" style="background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.3); color: #38bdf8;">
                        <i class="fas fa-info-circle"></i> No feedback has been given yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Feedback Modal -->
    <div class="modal fade" id="addFeedbackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background: #1e293b; color: #ffffff;">
                <div class="modal-header" style="border-bottom: 1px solid #475569;">
                    <h5 class="modal-title">Add Feedback</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="feedback.php">
                    <div class="modal-body">
                        <input type="hidden" name="mentee_id" value="<?php echo $mentee_id; ?>">
                        <input type="hidden" name="return_url" value="mentee_details.php?id=<?php echo $mentee_id; ?>">
                        <div class="mb-3">
                            <label class="form-label" style="color: #e2e8f0;">Feedback</label>
                            <textarea name="feedback_text" class="form-control" rows="5" required 
                                      style="background: #334155; border: 1px solid #475569; color: #ffffff;"
                                      placeholder="Enter your feedback here..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="color: #e2e8f0;">Date</label>
                            <input type="date" name="date" class="form-control" required 
                                   value="<?php echo date('Y-m-d'); ?>"
                                   style="background: #334155; border: 1px solid #475569; color: #ffffff;">
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #475569;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Feedback</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 

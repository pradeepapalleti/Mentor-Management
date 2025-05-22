<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is a mentor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor') {
    header('Location: login.php');
    exit();
}

$mentor_id = $_SESSION['user_id'];

// Get list of mentees with their feedback and marks counts
$sql = "SELECT m.*, u.name, u.email, u.mobile_number, u.parent_mobile_number,
        (SELECT COUNT(*) FROM feedback WHERE mentee_id = m.id) as feedback_count,
        (SELECT COUNT(*) FROM subject_marks WHERE mentee_id = m.id) as marks_count
        FROM mentees m
        JOIN users u ON m.user_id = u.id
        JOIN mentor_mentee_relationship mmr ON m.id = mmr.mentee_id
        WHERE mmr.mentor_id = ?
        ORDER BY u.name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mentor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Mentees - Mentor Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #0f172a;
            color: #ffffff;
            text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
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
            margin: 0;
        }
        .dashboard-header {
            background: #1e293b;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            border: 1px solid #475569;
        }
        .dashboard-header h2 {
            margin: 0;
            font-size: 1.8em;
            color: #38bdf8;
        }
        .mentee-card {
            background: #334155;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            border: 1px solid #475569;
        }
        .mentee-card:hover {
            transform: translateY(-5px);
        }
        .mentee-card h5 {
            color: #38bdf8;
            margin-bottom: 15px;
        }
        .mentee-card p {
            color: #e2e8f0;
            margin-bottom: 15px;
        }
        .stats-badge {
            background: #475569;
            color: #e2e8f0;
            font-size: 0.9rem;
            padding: 0.5rem 0.8rem;
            border-radius: 6px;
        }
        .btn-primary {
            background: #38bdf8;
            border: none;
        }
        .btn-primary:hover {
            background: #0284c7;
        }
        .btn-success {
            background: #10b981;
            border: none;
        }
        .btn-success:hover {
            background: #059669;
        }
        .btn-info {
            background: #0ea5e9;
            border: none;
        }
        .btn-info:hover {
            background: #0284c7;
        }
        .modal-content {
            background: #1e293b;
            color: #ffffff;
        }
        .modal-header {
            border-bottom: 1px solid #475569;
        }
        .modal-footer {
            border-top: 1px solid #475569;
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
            box-shadow: 0 0 0 0.2rem rgba(56,189,248,0.25);
        }
        .form-label {
            color: #e2e8f0;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="dashboard-header">
                <h2>My Mentees</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMenteeModal">
                    <i class="fas fa-plus"></i> Add New Mentee
                </button>
            </div>

            <div class="row">
                <?php while ($mentee = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="mentee-card">
                        <h5><?php echo htmlspecialchars($mentee['name']); ?></h5>
                        <p>
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($mentee['email']); ?><br>
                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($mentee['mobile_number']); ?><br>
                            <i class="fas fa-user-friends"></i> Parent: <?php echo htmlspecialchars($mentee['parent_mobile_number']); ?>
                        </p>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="stats-badge">
                                <i class="fas fa-comments"></i> <?php echo $mentee['feedback_count']; ?> Feedback
                            </span>
                            <span class="stats-badge">
                                <i class="fas fa-chart-line"></i> <?php echo $mentee['marks_count']; ?> Marks
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="mentee_details.php?id=<?php echo $mentee['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-user"></i> Details
                            </a>
                            <a href="marks.php?mentee_id=<?php echo $mentee['id']; ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-chart-bar"></i> Marks
                            </a>
                            <a href="feedback.php?mentee_id=<?php echo $mentee['id']; ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-comment"></i> Feedback
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Add Mentee Modal -->
    <div class="modal fade" id="addMenteeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Mentee</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="add_mentee.php" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="mobile_number" class="form-label">Mobile Number</label>
                            <input type="tel" class="form-control" id="mobile_number" name="mobile_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="parent_mobile_number" class="form-label">Parent's Mobile Number</label>
                            <input type="tel" class="form-control" id="parent_mobile_number" name="parent_mobile_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Mentee</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
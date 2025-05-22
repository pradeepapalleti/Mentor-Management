<?php
session_start();
require_once 'db.php';  // Changed from includes/db_connect.php to db.php

// Check if user is logged in and is a mentor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor') {
    header('Location: login.php');
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

// Get marks for the mentee
$marks_query = "SELECT * FROM detailed_marks WHERE mentee_id = ? ORDER BY academic_year DESC, semester DESC";
$stmt = $conn->prepare($marks_query);
$stmt->bind_param("i", $mentee_id);
$stmt->execute();
$marks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentee Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
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

            <div class="section">
                <div class="section-header">
                    <h3>Academic Performance</h3>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMarksModal">
                        <i class="fas fa-plus"></i> Add New Semester Marks
                    </button>
                </div>

                <?php while ($semester_marks = $marks->fetch_assoc()): ?>
                <div class="table-responsive mb-4">
                    <h4 class="mb-3" style="color: #38bdf8;">
                        Semester <?php echo htmlspecialchars($semester_marks['semester']); ?> 
                        (<?php echo htmlspecialchars($semester_marks['academic_year']); ?>)
                    </h4>
                    <table class="table table-dark table-bordered">
                        <thead>
                            <tr>
                                <th>First IA</th>
                                <th>Second IA</th>
                                <th>Final Exam</th>
                                <th>Project CGPA</th>
                                <th>Overall</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo number_format($semester_marks['first_ia_marks'], 2); ?></td>
                                <td><?php echo number_format($semester_marks['second_ia_marks'], 2); ?></td>
                                <td><?php echo number_format($semester_marks['final_exam_marks'], 2); ?></td>
                                <td><?php echo number_format($semester_marks['project_cgpa'], 2); ?></td>
                                <td>
                                    <?php
                                    $overall = ($semester_marks['first_ia_marks'] + $semester_marks['second_ia_marks'] + $semester_marks['final_exam_marks']) / 3;
                                    echo number_format($overall, 2);
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editMarks(<?php echo $semester_marks['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Add Marks Modal -->
    <div class="modal fade" id="addMarksModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Semester Marks</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="marks.php">
                    <div class="modal-body">
                        <input type="hidden" name="mentee_id" value="<?php echo $mentee_id; ?>">
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <input type="text" name="semester" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Academic Year</label>
                            <input type="text" name="academic_year" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">First IA Marks</label>
                            <input type="number" step="0.01" name="first_ia_marks" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Second IA Marks</label>
                            <input type="number" step="0.01" name="second_ia_marks" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Final Exam Marks</label>
                            <input type="number" step="0.01" name="final_exam_marks" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Project CGPA</label>
                            <input type="number" step="0.01" name="project_cgpa" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Marks</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editMarks(marksId) {
            // Implement edit functionality
            window.location.href = `marks.php?mentee_id=<?php echo $mentee_id; ?>&edit=${marksId}`;
        }
    </script>
</body>
</html> 
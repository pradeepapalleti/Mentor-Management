<?php
session_start();
require_once '../config/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Get mentee ID (either from URL or current user)
$mentee_id = isset($_GET['mentee_id']) ? $_GET['mentee_id'] : $user_id;

// Handle form submission for adding/updating marks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_role === 'mentor') {
    $subject_id = isset($_POST['subject_id']) ? intval($_POST['subject_id']) : 0;
    $first_ia = isset($_POST['first_ia_marks']) ? floatval($_POST['first_ia_marks']) : 0;
    $second_ia = isset($_POST['second_ia_marks']) ? floatval($_POST['second_ia_marks']) : 0;
    $final_exam = isset($_POST['final_exam_marks']) ? floatval($_POST['final_exam_marks']) : 0;
    $project_marks = isset($_POST['project_marks']) ? floatval($_POST['project_marks']) : 0;

    if ($subject_id > 0) {
        // Check if marks already exist for this subject
        $check_query = "SELECT id FROM subject_marks WHERE subject_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing marks
            $query = "UPDATE subject_marks SET 
                      first_ia_marks = ?, 
                      second_ia_marks = ?, 
                      final_exam_marks = ?, 
                      project_marks = ? 
                      WHERE subject_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ddddi", $first_ia, $second_ia, $final_exam, $project_marks, $subject_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Marks updated successfully!";
            } else {
                $_SESSION['error_message'] = "Error updating marks: " . $stmt->error;
            }
        } else {
            // Insert new marks
            $query = "INSERT INTO subject_marks (subject_id, first_ia_marks, second_ia_marks, final_exam_marks, project_marks) 
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("idddd", $subject_id, $first_ia, $second_ia, $final_exam, $project_marks);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Marks added successfully!";
            } else {
                $_SESSION['error_message'] = "Error adding marks: " . $stmt->error;
            }
        }
    }
    
    header("Location: marks.php?mentee_id=" . $mentee_id);
    exit();
}

// First get all semesters
$query = "SELECT id, semester_number, academic_year 
          FROM semesters 
          WHERE mentee_id = ? 
          ORDER BY academic_year DESC, semester_number DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $mentee_id);
$stmt->execute();
$semesters = $stmt->get_result();

// Get mentee details
$mentee_query = "SELECT m.*, u.name, u.email 
                FROM mentees m 
                JOIN users u ON m.user_id = u.id 
                WHERE m.id = ?";
$stmt = $conn->prepare($mentee_query);
$stmt->bind_param("i", $mentee_id);
$stmt->execute();
$mentee = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marks & Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <script src="../assets/js/theme-toggle.js"></script>

    <div class="main-content">
        <div class="container">
            <div class="dashboard-header">
                <h1>Subject Marks</h1>
                <p class="text-light">View your academic performance</p>
            </div>

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

            <?php if ($user_role === 'mentor'): ?>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="section">
                        <div class="section-header">
                            <h2>Semester Result</h2>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="../actions/add_result.php?mentee_id=<?php echo htmlspecialchars($mentee_id); ?>" class="btn btn-primary">Add Semester Result</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="section">
                <div class="section-header">
                    <h2>Marks History</h2>
                </div>
                <?php if ($semesters->num_rows > 0): ?>
                    <?php while ($semester = $semesters->fetch_assoc()): ?>
                        <?php
                        // Get subjects for this semester
                        $subjects_query = "SELECT s.id as subject_id, s.subject_code, s.subject_name, s.credits 
                                         FROM subjects s WHERE s.semester_id = ?";
                        $stmt = $conn->prepare($subjects_query);
                        $stmt->bind_param("i", $semester['id']);
                        $stmt->execute();
                        $subjects = $stmt->get_result();
                        ?>
                        <div class="table-responsive mb-4">
                            <h4 class="mb-3" style="color: #38bdf8;">
                                Semester <?php echo htmlspecialchars($semester['semester_number']); ?> 
                                (<?php echo htmlspecialchars($semester['academic_year']); ?>)
                            </h4>
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th>Subject Code</th>
                                        <th>Subject Name</th>
                                        <th>Credits</th>
                                        <th>First IA</th>
                                        <th>Second IA</th>
                                        <th>Final Exam</th>
                                        <th>Project</th>
                                        <th>Overall</th>
                                        <?php if ($user_role === 'mentor'): ?>
                                        <th>Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($subject = $subjects->fetch_assoc()): ?>
                                        <?php
                                        // Get marks for this subject
                                        $marks_query = "SELECT * FROM subject_marks WHERE subject_id = ?";
                                        $stmt = $conn->prepare($marks_query);
                                        $stmt->bind_param("i", $subject['subject_id']);
                                        $stmt->execute();
                                        $marks = $stmt->get_result()->fetch_assoc();
                                        
                                        $first_ia = $marks ? $marks['first_ia_marks'] : 0;
                                        $second_ia = $marks ? $marks['second_ia_marks'] : 0;
                                        $final_exam = $marks ? $marks['final_exam_marks'] : 0;
                                        $project = $marks ? $marks['project_marks'] : 0;
                                        $overall = ($first_ia + $second_ia + $final_exam) / 3;
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                            <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                            <td><?php echo htmlspecialchars($subject['credits']); ?></td>
                                            <td><?php echo number_format($first_ia, 2); ?></td>
                                            <td><?php echo number_format($second_ia, 2); ?></td>
                                            <td><?php echo number_format($final_exam, 2); ?></td>
                                            <td><?php echo number_format($project, 2); ?></td>
                                            <td><?php echo number_format($overall, 2); ?></td>
                                            <?php if ($user_role === 'mentor'): ?>
                                            <td>
                                                <button class="btn btn-sm btn-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editMarksModal"
                                                        data-subject-id="<?php echo $subject['subject_id']; ?>"
                                                        data-subject-name="<?php echo htmlspecialchars($subject['subject_name']); ?>"
                                                        data-first-ia="<?php echo $first_ia; ?>"
                                                        data-second-ia="<?php echo $second_ia; ?>"
                                                        data-final-exam="<?php echo $final_exam; ?>"
                                                        data-project="<?php echo $project; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        No marks have been recorded yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
    .btn-primary {
        background-color: #38bdf8;
        border-color: #38bdf8;
    }

    .btn-primary:hover {
        background-color: #0284c7;
        border-color: #0284c7;
    }

    .btn-info {
        background-color: #38bdf8;
        border-color: #38bdf8;
    }

    .btn-info:hover {
        background-color: #0284c7;
        border-color: #0284c7;
    }

    .btn-danger {
        background-color: #ef4444;
        border-color: #ef4444;
    }

    .btn-danger:hover {
        background-color: #dc2626;
        border-color: #dc2626;
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

    <!-- Edit Marks Modal -->
    <?php if ($user_role === 'mentor'): ?>
    <div class="modal fade" id="editMarksModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Marks - <span id="subjectName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="subject_id" id="subject_id">
                        <div class="mb-3">
                            <label class="form-label">First IA Marks</label>
                            <input type="number" step="0.01" name="first_ia_marks" id="first_ia_marks" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Second IA Marks</label>
                            <input type="number" step="0.01" name="second_ia_marks" id="second_ia_marks" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Final Exam Marks</label>
                            <input type="number" step="0.01" name="final_exam_marks" id="final_exam_marks" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Project Marks</label>
                            <input type="number" step="0.01" name="project_marks" id="project_marks" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Handle edit marks modal
    document.addEventListener('DOMContentLoaded', function() {
        const editMarksModal = document.getElementById('editMarksModal');
        if (editMarksModal) {
            editMarksModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const subjectId = button.getAttribute('data-subject-id');
                const subjectName = button.getAttribute('data-subject-name');
                const firstIa = button.getAttribute('data-first-ia');
                const secondIa = button.getAttribute('data-second-ia');
                const finalExam = button.getAttribute('data-final-exam');
                const project = button.getAttribute('data-project');

                editMarksModal.querySelector('#subject_id').value = subjectId;
                editMarksModal.querySelector('#subjectName').textContent = subjectName;
                editMarksModal.querySelector('#first_ia_marks').value = firstIa;
                editMarksModal.querySelector('#second_ia_marks').value = secondIa;
                editMarksModal.querySelector('#final_exam_marks').value = finalExam;
                editMarksModal.querySelector('#project_marks').value = project;
            });
        }
    });
    </script>
</body>
</html> 

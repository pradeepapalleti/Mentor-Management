<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$mentee_id = isset($_GET['mentee_id']) ? $_GET['mentee_id'] : $user_id;

// Handle form submission for adding/updating marks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_role === 'mentor') {
    $semester_id = isset($_POST['semester_id']) ? intval($_POST['semester_id']) : 0;
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
    } else {
        $_SESSION['error_message'] = "Invalid subject ID!";
    }
    
    header("Location: subject_marks.php?mentee_id=" . $mentee_id);
    exit();
}

// Get mentee details
$mentee_query = "SELECT m.*, u.name, u.email 
                FROM mentees m 
                JOIN users u ON m.user_id = u.id 
                WHERE m.id = ?";
$stmt = $conn->prepare($mentee_query);
$stmt->bind_param("i", $mentee_id);
$stmt->execute();
$mentee = $stmt->get_result()->fetch_assoc();

// Get all semesters for the mentee
$semesters_query = "SELECT * FROM semesters WHERE mentee_id = ? ORDER BY academic_year DESC, semester_number DESC";
$stmt = $conn->prepare($semesters_query);
$stmt->bind_param("i", $mentee_id);
$stmt->execute();
$semesters = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Marks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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
            color: #1e40af;
            font-size: 1.4em;
        }
        .table {
            background: #f3f4f6;
            border-radius: 8px;
            overflow: hidden;
        }
        .table th {
            background: #1e40af;
            color: white;
            padding: 12px;
        }
        .table td {
            color: #000000;
            padding: 12px;
        }
        .table tr:nth-child(even) {
            background: #e5e7eb;
        }
        .table tr:nth-child(odd) {
            background: #f3f4f6;
        }
        .btn-primary {
            background: #38bdf8;
            border: none;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #0284c7;
        }
        @media print {
            * {
                color-adjust: exact;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Subject Marks - <?php echo htmlspecialchars($mentee['name']); ?></h2>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php while ($semester = $semesters->fetch_assoc()): ?>
                <div class="semester-card">
                    <div class="semester-header">
                        <h4>Semester <?php echo $semester['semester_number']; ?> (<?php echo $semester['academic_year']; ?>)</h4>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered subject-table">
                            <thead>
                                <tr>
                                    <th>Subject Code</th>
                                    <th>Subject Name</th>
                                    <th>Credits</th>
                                    <th>First IA</th>
                                    <th>Second IA</th>
                                    <th>Final Exam</th>
                                    <th>Project</th>
                                    <th>Total</th>
                                    <?php if ($user_role === 'mentor'): ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Get subjects for this semester
                                $subjects_query = "SELECT s.id as subject_id, s.subject_code, s.subject_name, s.credits,
                                                 sm.id as marks_id, sm.first_ia_marks, sm.second_ia_marks, 
                                                 sm.final_exam_marks, sm.project_marks
                                                 FROM subjects s 
                                                 LEFT JOIN subject_marks sm ON s.id = sm.subject_id 
                                                 WHERE s.semester_id = ?";
                                $stmt = $conn->prepare($subjects_query);
                                $stmt->bind_param("i", $semester['id']);
                                $stmt->execute();
                                $subjects = $stmt->get_result();

                                $total_credits = 0;
                                $weighted_sum = 0;

                                while ($subject = $subjects->fetch_assoc()):
                                    // Handle NULL values with defaults
                                    $first_ia = $subject['first_ia_marks'] ?? 0;
                                    $second_ia = $subject['second_ia_marks'] ?? 0;
                                    $final_exam = $subject['final_exam_marks'] ?? 0;
                                    $project = $subject['project_marks'] ?? 0;
                                    
                                    $total = ($first_ia + $second_ia + $final_exam) / 3;
                                    $total_credits += $subject['credits'];
                                    $weighted_sum += ($total * $subject['credits']);
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                        <td><?php echo $subject['credits']; ?></td>
                                        <td><?php echo number_format($first_ia, 2); ?></td>
                                        <td><?php echo number_format($second_ia, 2); ?></td>
                                        <td><?php echo number_format($final_exam, 2); ?></td>
                                        <td><?php echo number_format($project, 2); ?></td>
                                        <td><?php echo number_format($total, 2); ?></td>
                                        <?php if ($user_role === 'mentor'): ?>
                                            <td>
                                                <button class="btn btn-sm btn-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editMarksModal"
                                                        data-semester-id="<?php echo $semester['id']; ?>"
                                                        data-subject-id="<?php echo $subject['subject_id']; ?>"
                                                        data-first-ia="<?php echo $first_ia; ?>"
                                                        data-second-ia="<?php echo $second_ia; ?>"
                                                        data-final-exam="<?php echo $final_exam; ?>"
                                                        data-project="<?php echo $project; ?>">
                                                    Edit
                                                </button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="cgpa-section">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Semester CGPA: <?php echo number_format($weighted_sum / $total_credits, 2); ?></h5>
                            </div>
                            <div class="col-md-6 text-end">
                                <h5>Total Credits: <?php echo $total_credits; ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Edit Marks Modal -->
    <?php if ($user_role === 'mentor'): ?>
    <div class="modal fade" id="editMarksModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Marks</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="mentee_id" value="<?php echo $mentee_id; ?>">
                        <input type="hidden" name="semester_id" id="semester_id">
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
                    const semesterId = button.getAttribute('data-semester-id');
                    const subjectId = button.getAttribute('data-subject-id');
                    const firstIa = button.getAttribute('data-first-ia');
                    const secondIa = button.getAttribute('data-second-ia');
                    const finalExam = button.getAttribute('data-final-exam');
                    const project = button.getAttribute('data-project');

                    editMarksModal.querySelector('#semester_id').value = semesterId;
                    editMarksModal.querySelector('#subject_id').value = subjectId;
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
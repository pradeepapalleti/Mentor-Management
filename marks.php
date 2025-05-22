<?php
session_start();
require_once 'db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Get mentee ID (either from URL or current user)
$mentee_id = isset($_GET['mentee_id']) ? $_GET['mentee_id'] : $user_id;

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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="dashboard-header">
                <h1>Subject Marks</h1>
                <p class="text-light">View your academic performance</p>
            </div>

            <?php if ($user_role === 'mentor'): ?>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="section">
                        <div class="section-header">
                            <h2>Semester Result</h2>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="add_result.php?mentee_id=<?php echo htmlspecialchars($mentee_id); ?>" class="btn btn-primary">Add Semester Result</a>
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
                        $subjects_query = "SELECT s.* FROM subjects s WHERE s.semester_id = ?";
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
                                        $stmt->bind_param("i", $subject['id']);
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
                                                <button class="btn btn-sm btn-info" onclick="editMarks(<?php echo $subject['id']; ?>)">
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
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editMarks(subjectId) {
        // Add your edit functionality here
        alert('Edit functionality will be implemented here');
    }
    </script>
</body>
</html> 
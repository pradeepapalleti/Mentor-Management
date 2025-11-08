<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is a mentor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mentor') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mentee_id = $_POST['mentee_id'];
    $semester = $_POST['semester'];
    $academic_year = $_POST['academic_year'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert semester record
        $sql = "INSERT INTO semesters (semester_number, academic_year, mentee_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $semester, $academic_year, $mentee_id);
        $stmt->execute();
        $semester_id = $conn->insert_id;
        
        // Insert subjects and their marks
        $subjects = $_POST['subjects'];
        foreach ($subjects as $subject) {
            $sql = "INSERT INTO subjects (semester_id, subject_name, subject_code, credits) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issi", $semester_id, $subject['name'], $subject['code'], $subject['credits']);
            $stmt->execute();
            $subject_id = $conn->insert_id;
            
            $sql = "INSERT INTO subject_marks (subject_id, first_ia_marks, second_ia_marks, final_exam_marks, project_marks) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("idddd", $subject_id, $subject['first_ia'], $subject['second_ia'], $subject['final_exam'], $subject['project']);
            $stmt->execute();
        }
        
        $conn->commit();
        $_SESSION['success_message'] = "Marks added successfully!";
        header('Location: marks.php?mentee_id=' . $mentee_id);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error adding marks: " . $e->getMessage();
    }
}

// Get mentee_id from URL if not POST
$mentee_id = isset($_GET['mentee_id']) ? $_GET['mentee_id'] : null;

// If no mentee_id provided, redirect to dashboard
if (!$mentee_id) {
    header('Location: mentor_dashboard.php');
    exit();
}

// Verify that the mentee exists and is assigned to this mentor
$sql = "SELECT m.id FROM mentees m 
        JOIN mentor_mentee_relationship mmr ON m.id = mmr.mentee_id 
        JOIN mentors mt ON mmr.mentor_id = mt.id
        WHERE m.id = ? AND mt.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $mentee_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: mentor_dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Semester Result</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #0f172a;
            color: #ffffff;
        }
        .container {
            background: #1e293b;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            margin: 40px auto;
        }
        .form {
            background: #334155;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            border: 1px solid #475569;
        }
        .form h2 {
            color: #38bdf8;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.8em;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #e2e8f0;
            font-weight: 500;
            font-size: 1.1em;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #475569;
            border-radius: 6px;
            background: #475569;
            color: #ffffff;
            font-size: 1em;
            transition: all 0.3s ease;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #38bdf8;
            outline: none;
            box-shadow: 0 0 0 2px rgba(56,189,248,0.2);
            background: #334155;
        }
        .button {
            background: #38bdf8;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
        }
        .button:hover {
            background: #0284c7;
            transform: translateY(-2px);
        }
        .subject-container {
            background: #475569;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #64748b;
        }
        .subject-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .subject-title {
            color: #38bdf8;
            font-size: 1.2em;
            font-weight: 500;
        }
        .remove-subject {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .remove-subject:hover {
            background: #dc2626;
        }
        .add-subject {
            background: #10b981;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 20px;
            width: 100%;
        }
        .add-subject:hover {
            background: #059669;
        }
        .error {
            color: #f87171;
            margin-bottom: 20px;
            padding: 12px;
            background: rgba(248,113,113,0.1);
            border-radius: 6px;
            border: 1px solid rgba(248,113,113,0.2);
            font-size: 1em;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #e2e8f0;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            background: #475569;
            transition: all 0.3s ease;
            border: 1px solid #64748b;
        }
        .back-link:hover {
            background: #64748b;
            transform: translateY(-2px);
            color: #ffffff;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="marks.php?mentee_id=<?php echo htmlspecialchars($mentee_id); ?>" class="back-link">← Back to Marks</a>
    
    <div class="form">
        <h2>Add Semester Result</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" id="marksForm">
            <input type="hidden" name="mentee_id" value="<?php echo htmlspecialchars($mentee_id); ?>">
            
            <div class="form-group">
                <label>Semester:</label>
                <select name="semester" required>
                    <option value="">Select Semester</option>
                    <option value="1">Semester 1</option>
                    <option value="2">Semester 2</option>
                    <option value="3">Semester 3</option>
                    <option value="4">Semester 4</option>
                    <option value="5">Semester 5</option>
                    <option value="6">Semester 6</option>
                    <option value="7">Semester 7</option>
                    <option value="8">Semester 8</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Academic Year:</label>
                <input type="text" name="academic_year" required placeholder="e.g., 2023-2024">
            </div>
            
            <div id="subjectsContainer">
                <!-- Initial 5 subjects -->
                <?php for($i = 0; $i < 5; $i++): ?>
                <div class="subject-container">
                    <div class="subject-header">
                        <?php if($i > 0): ?>
                        <button type="button" class="remove-subject" onclick="removeSubject(this)">Remove</button>
                        <?php endif; ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Subject Name:</label>
                                <input type="text" name="subjects[<?php echo $i; ?>][name]" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Subject Code:</label>
                                <input type="text" name="subjects[<?php echo $i; ?>][code]" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Credits:</label>
                                <input type="number" name="subjects[<?php echo $i; ?>][credits]" min="1" max="4" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>First IA:</label>
                                <input type="number" name="subjects[<?php echo $i; ?>][first_ia]" step="0.01" min="0" max="100" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Second IA:</label>
                                <input type="number" name="subjects[<?php echo $i; ?>][second_ia]" step="0.01" min="0" max="100" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Final Exam:</label>
                                <input type="number" name="subjects[<?php echo $i; ?>][final_exam]" step="0.01" min="0" max="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Project:</label>
                                <input type="number" name="subjects[<?php echo $i; ?>][project]" step="0.01" min="0" max="100" required>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            
            <button type="button" class="add-subject" onclick="addSubject()">Add Another Subject</button>
            <button type="submit" class="button">Save All Marks</button>
        </form>
    </div>
</div>

<script>
let subjectCount = 5;

function addSubject() {
    const container = document.getElementById('subjectsContainer');
    const newSubject = document.createElement('div');
    newSubject.className = 'subject-container';
    newSubject.innerHTML = `
        <div class="subject-header">
            <button type="button" class="remove-subject" onclick="removeSubject(this)">Remove</button>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Subject Name:</label>
                    <input type="text" name="subjects[${subjectCount}][name]" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Subject Code:</label>
                    <input type="text" name="subjects[${subjectCount}][code]" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Credits:</label>
                    <input type="number" name="subjects[${subjectCount}][credits]" min="1" max="4" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>First IA:</label>
                    <input type="number" name="subjects[${subjectCount}][first_ia]" step="0.01" min="0" max="100" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Second IA:</label>
                    <input type="number" name="subjects[${subjectCount}][second_ia]" step="0.01" min="0" max="100" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Final Exam:</label>
                    <input type="number" name="subjects[${subjectCount}][final_exam]" step="0.01" min="0" max="100" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Project:</label>
                    <input type="number" name="subjects[${subjectCount}][project]" step="0.01" min="0" max="100" required>
                </div>
            </div>
        </div>
    `;
    container.appendChild(newSubject);
    subjectCount++;
}

function removeSubject(button) {
    button.closest('.subject-container').remove();
}
</script>
</body>
</html> 
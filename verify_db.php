<?php
require_once 'db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Verifying Database Structure</h2>";

// Check if tables exist and their structure
$tables = [
    'semesters' => "CREATE TABLE IF NOT EXISTS semesters (
        id INT PRIMARY KEY AUTO_INCREMENT,
        semester_number INT NOT NULL,
        academic_year VARCHAR(20) NOT NULL,
        mentee_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (mentee_id) REFERENCES mentees(id),
        UNIQUE KEY unique_semester (semester_number, academic_year, mentee_id)
    )",
    
    'subjects' => "CREATE TABLE IF NOT EXISTS subjects (
        id INT PRIMARY KEY AUTO_INCREMENT,
        semester_id INT NOT NULL,
        subject_name VARCHAR(100) NOT NULL,
        subject_code VARCHAR(20) NOT NULL,
        credits INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
    )",
    
    'subject_marks' => "CREATE TABLE IF NOT EXISTS subject_marks (
        id INT PRIMARY KEY AUTO_INCREMENT,
        subject_id INT NOT NULL,
        first_ia_marks DECIMAL(5,2) NOT NULL,
        second_ia_marks DECIMAL(5,2) NOT NULL,
        final_exam_marks DECIMAL(5,2) NOT NULL,
        project_marks DECIMAL(5,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    )"
];

// Create or recreate tables
foreach ($tables as $table => $sql) {
    // Drop table if exists
    $conn->query("DROP TABLE IF EXISTS $table");
    echo "Dropped table $table<br>";
    
    // Create table
    if ($conn->query($sql)) {
        echo "Created table $table<br>";
    } else {
        echo "Error creating table $table: " . $conn->error . "<br>";
    }
}

// Insert sample data
// First semester
$conn->query("INSERT INTO semesters (semester_number, academic_year, mentee_id) VALUES (5, '2023-2024', 1)");
$semester_id = $conn->insert_id;
echo "Inserted semester 5<br>";

// Insert subjects for first semester
$subjects = [
    ['Database Management Systems', 'CS301', 4],
    ['Operating Systems', 'CS302', 4],
    ['Computer Networks', 'CS303', 4]
];

foreach ($subjects as $subject) {
    $stmt = $conn->prepare("INSERT INTO subjects (semester_id, subject_name, subject_code, credits) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $semester_id, $subject[0], $subject[1], $subject[2]);
    $stmt->execute();
    $subject_id = $conn->insert_id;
    
    // Insert marks for this subject
    $stmt = $conn->prepare("INSERT INTO subject_marks (subject_id, first_ia_marks, second_ia_marks, final_exam_marks, project_marks) VALUES (?, ?, ?, ?, ?)");
    $first_ia = rand(75, 95);
    $second_ia = rand(75, 95);
    $final_exam = rand(75, 95);
    $project = rand(75, 95);
    $stmt->bind_param("idddd", $subject_id, $first_ia, $second_ia, $final_exam, $project);
    $stmt->execute();
    echo "Inserted subject {$subject[1]} with marks<br>";
}

// Verify the data
echo "<h3>Verifying Data:</h3>";
$query = "SELECT s.id, s.semester_number as semester, s.academic_year,
          GROUP_CONCAT(
              CONCAT(
                  sb.subject_code, ':', 
                  sb.subject_name, ':', 
                  sb.credits, ':', 
                  sm.first_ia_marks, ':', 
                  sm.second_ia_marks, ':', 
                  sm.final_exam_marks, ':',
                  sm.project_marks
              ) SEPARATOR '|'
          ) as subject_marks
          FROM semesters s
          LEFT JOIN subjects sb ON s.id = sb.semester_id
          LEFT JOIN subject_marks sm ON sb.id = sm.subject_id
          WHERE s.mentee_id = 1
          GROUP BY s.id, s.semester_number, s.academic_year
          ORDER BY s.academic_year DESC, s.semester_number DESC";

$result = $conn->query($query);
if ($result) {
    echo "Query executed successfully!<br>";
    if ($result->num_rows > 0) {
        echo "<h4>Results:</h4>";
        while ($row = $result->fetch_assoc()) {
            echo "Semester: " . $row['semester'] . "<br>";
            echo "Academic Year: " . $row['academic_year'] . "<br>";
            echo "Subject Marks: " . $row['subject_marks'] . "<br><br>";
        }
    } else {
        echo "No results found<br>";
    }
} else {
    echo "Error executing query: " . $conn->error . "<br>";
}

echo "<h2>Database verification complete!</h2>";
?> 
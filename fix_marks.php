<?php
require_once 'db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Fixing Database Structure</h2>";

// First, drop all related tables in correct order
$tables = ['subject_marks', 'subjects', 'semesters'];
foreach ($tables as $table) {
    $conn->query("DROP TABLE IF EXISTS $table");
    echo "Dropped table $table<br>";
}

// Create semesters table
$sql = "CREATE TABLE semesters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    semester_number INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    mentee_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentee_id) REFERENCES mentees(id),
    UNIQUE KEY unique_semester (semester_number, academic_year, mentee_id)
)";
$conn->query($sql);
echo "Created semesters table<br>";

// Create subjects table
$sql = "CREATE TABLE subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    semester_id INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    credits INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
)";
$conn->query($sql);
echo "Created subjects table<br>";

// Create subject_marks table with EXACT column names
$sql = "CREATE TABLE subject_marks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT NOT NULL,
    first_ia_marks DECIMAL(5,2) NOT NULL,
    second_ia_marks DECIMAL(5,2) NOT NULL,
    final_exam_marks DECIMAL(5,2) NOT NULL,
    project_marks DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
)";
$conn->query($sql);
echo "Created subject_marks table<br>";

// Insert sample data
// First semester
$conn->query("INSERT INTO semesters (semester_number, academic_year, mentee_id) VALUES (5, '2023-2024', 1)");
$semester_id = $conn->insert_id;
echo "Inserted semester 5<br>";

// Insert subjects
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

// Show table structure
echo "<h3>Table Structure:</h3>";
$tables = ['semesters', 'subjects', 'subject_marks'];
foreach ($tables as $table) {
    echo "<h4>$table table structure:</h4>";
    $result = $conn->query("SHOW COLUMNS FROM $table");
    if ($result) {
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Error showing columns: " . $conn->error . "<br>";
    }
}

// Test the exact query from marks.php
echo "<h3>Testing marks.php query:</h3>";
$query = "SELECT * FROM subject_marks WHERE subject_id = 1";
$result = $conn->query($query);
if ($result) {
    echo "Query executed successfully!<br>";
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        $fields = $result->fetch_fields();
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<th>" . $field->name . "</th>";
        }
        echo "</tr>";
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No results found<br>";
    }
} else {
    echo "Error executing query: " . $conn->error . "<br>";
}

echo "<h2>Database fix complete! Now try accessing marks.php again.</h2>";
?> 
<?php
require_once 'db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Checking subject_marks Table Columns</h2>";

// Show the actual table structure
$result = $conn->query("SHOW COLUMNS FROM subject_marks");
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
    echo "Error: " . $conn->error;
}

// Try to recreate the table with the exact column names
echo "<h2>Recreating Table with Correct Column Names</h2>";

// Drop the table
$conn->query("DROP TABLE IF EXISTS subject_marks");

// Create the table with exact column names
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

if ($conn->query($sql)) {
    echo "Table recreated successfully!<br>";
    
    // Insert a test record
    $stmt = $conn->prepare("INSERT INTO subject_marks (subject_id, first_ia_marks, second_ia_marks, final_exam_marks, project_marks) VALUES (?, ?, ?, ?, ?)");
    $subject_id = 1; // Make sure this subject_id exists
    $first_ia = 85.5;
    $second_ia = 88.0;
    $final_exam = 90.0;
    $project = 92.0;
    $stmt->bind_param("idddd", $subject_id, $first_ia, $second_ia, $final_exam, $project);
    
    if ($stmt->execute()) {
        echo "Test record inserted successfully!<br>";
    } else {
        echo "Error inserting test record: " . $stmt->error . "<br>";
    }
} else {
    echo "Error recreating table: " . $conn->error . "<br>";
}

// Verify the table structure again
echo "<h2>Verifying New Table Structure</h2>";
$result = $conn->query("SHOW COLUMNS FROM subject_marks");
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
    echo "Error: " . $conn->error;
}
?> 
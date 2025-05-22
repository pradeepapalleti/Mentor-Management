<?php
require_once 'db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Check</h2>";

// Check if tables exist
$tables = ['semesters', 'subjects', 'subject_marks'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "Table $table exists<br>";
        
        // Show structure
        echo "<h3>Structure of $table:</h3>";
        $columns = $conn->query("SHOW COLUMNS FROM $table");
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($col = $columns->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "<td>" . $col['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show sample data
        echo "<h3>Sample data from $table:</h3>";
        $data = $conn->query("SELECT * FROM $table LIMIT 5");
        if ($data->num_rows > 0) {
            echo "<table border='1'>";
            $fields = $data->fetch_fields();
            echo "<tr>";
            foreach ($fields as $field) {
                echo "<th>" . $field->name . "</th>";
            }
            echo "</tr>";
            $data->data_seek(0);
            while ($row = $data->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No data found in $table<br>";
        }
    } else {
        echo "Table $table does NOT exist!<br>";
    }
    echo "<hr>";
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
?> 
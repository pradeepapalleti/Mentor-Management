<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli('localhost', 'root', '', 'mentor_management');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Adding Password Column to Mentees Table:</h2>";

// Check if password column exists
$result = $conn->query("SHOW COLUMNS FROM mentees LIKE 'password'");
if ($result->num_rows == 0) {
    // Add password column
    $sql = "ALTER TABLE mentees ADD COLUMN password VARCHAR(255) NOT NULL AFTER email";
    if ($conn->query($sql)) {
        echo "Password column added successfully to mentees table<br>";
        
        // Update existing mentees with a default password (they should change it later)
        $default_password = password_hash('changeme123', PASSWORD_DEFAULT);
        $update_sql = "UPDATE mentees SET password = ? WHERE password = '' OR password IS NULL";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("s", $default_password);
        
        if ($stmt->execute()) {
            echo "Default passwords set for existing mentees<br>";
            echo "Note: Existing mentees should change their password on first login<br>";
        } else {
            echo "Error setting default passwords: " . $stmt->error . "<br>";
        }
    } else {
        echo "Error adding password column: " . $conn->error . "<br>";
    }
} else {
    echo "Password column already exists in mentees table<br>";
}

// Verify the table structure
echo "<h3>Current Mentees Table Structure:</h3>";
$result = $conn->query("DESCRIBE mentees");
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
    echo "Error getting table structure: " . $conn->error . "<br>";
}

$conn->close();
?> 
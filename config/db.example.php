<?php
/**
 * Database Configuration Template
 * 
 * Copy this file to 'db.php' and update with your actual credentials
 * DO NOT commit db.php with real credentials to version control
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$host = 'localhost';           // Database host (usually localhost)
$username = 'root';             // Your MySQL username
$password = '';                 // Your MySQL password (leave empty for WAMP default)
$database = 'mentor_management'; // Database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Please check your database configuration.");
}

// Set charset to ensure proper encoding
$conn->set_charset("utf8mb4");

// Log successful connection (optional, comment out in production)
error_log("Database connection successful");
?>

<?php
// Turn on error reporting for debugging (disable in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Set database credentials
$host     = 'localhost';
$username = 'root';
$password = ''; // Change to your actual DB password
$database = 'l9_gym_db';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die('Database Connection Failed: ' . $conn->connect_error);
}

// Set charset
$conn->set_charset('utf8mb4');
?>

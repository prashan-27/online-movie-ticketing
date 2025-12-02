<?php
// Database credentials
$host = "localhost"; // Change this if using a remote server
$username = "root"; // Update if using a different user
$password = ""; // Update if your MySQL has a password
$database = "movie"; // Your database name

// Create a database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set character set to utf8
$conn->set_charset("utf8");
?>

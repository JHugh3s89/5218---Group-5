<?php
$servername = "localhost"; 
$username = "root"; 
$password = "root"; 
$dbname = "SHOPPINGWEB"; 

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8 for security (to prevent encoding issues)
$conn->set_charset("utf8mb4");

// Optional: Display a success message for debugging (remove in production)
// echo "Connected successfully";
?>

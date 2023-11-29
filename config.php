<?php 
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vijyaydata";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
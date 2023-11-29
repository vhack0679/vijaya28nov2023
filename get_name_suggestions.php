<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "patientmanagementsystem";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for a connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['query'])) {
    $name = $_POST['query'];

    // SQL query to fetch name suggestions based on user input
    $sql = "SELECT DISTINCT NAME FROM patients WHERE NAME LIKE '%$name%' LIMIT 5";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<p>' . $row['NAME'] . '</p>';
        }
    } else {
        echo '<p>No suggestions found</p>';
    }
}
?>

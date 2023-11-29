<?php
// Connect to your MySQL database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vijyaydata";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch data from the database
$sql = "SELECT name FROM patients WHERE name LIKE '%".$_GET['query']."%'";
$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    // Fetch data and store in array
    while($row = $result->fetch_assoc()) {
        $data[] = $row["name"];
    }
}

// Close the connection
$conn->close();

// Return the data as JSON
echo json_encode($data);
?>

<?php
require_once 'config.php';
// Start the session
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page or any other desired page if not logged in
    header("Location: login.html");
    exit();
}



// SQL query to retrieve the medication list from your database
$sql = "SELECT DISTINCT MEDICINE FROM med_master";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch the results into an array
    $medications = array();
    while ($row = $result->fetch_assoc()) {
        $medications[] = $row['MEDICINE'];
    }

    // Close the database connection
    $conn->close();

    // Return the medication list as JSON
    echo json_encode($medications);
} else {
    // No medications found
    $conn->close();
    echo json_encode(array());
}
?>
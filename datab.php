<?php

// Local MySQL server credentials
$localHost = 'localhost';
$localUsername = 'root';
$localPassword = '';
$localDatabase = 'patientmanagementsystem';  // Removed extra quote

// Remote MySQL server credentials
$remoteHost = 'sql202.infinityfree.com';
$remoteUsername = 'epiz_34063411';
$remotePassword = 'couRXORlv9FuAf';
$remoteDatabase = 'epiz_34063411_address';

// Create connection to local MySQL server
$localConnection = new mysqli($localHost, $localUsername, $localPassword, $localDatabase);

// Check local connection
if ($localConnection->connect_error) {
    die("Local Connection failed: " . $localConnection->connect_error);
}

// Create connection to remote MySQL server
$remoteConnection = new mysqli($remoteHost, $remoteUsername, $remotePassword, $remoteDatabase);

// Check remote connection
if ($remoteConnection->connect_error) {
    die("Remote Connection failed: " . $remoteConnection->connect_error);
}

// Fetch data from local database
$query = "SELECT * FROM your_table";  // Replace 'your_table' with your actual table name
$result = $localConnection->query($query);

// Check if query was successful
if (!$result) {
    die("Query failed: " . $localConnection->error);
}

// Iterate through the results and insert/update into the remote database
while ($row = $result->fetch_assoc()) {
    $insertQuery = "INSERT INTO diagnoses (patient_id, name, symptom, medication) VALUES ('" . $row['patient_id'] . "', '" . $row['name'] . "',  '" . $row['symptom'] . "', 

'" . $row['medication'] . "')"
                 . " ON DUPLICATE KEY UPDATE patient_id='" . $row['patient_id'] . "', name='" . $row['name'] . "', symptom='" . $row['symptom'] . "', medication='" . 

$row['medication'] . "' ";  // Removed extra space before 'medication'
    $insertResult = $remoteConnection->query($insertQuery);

    // Check if insert/update was successful
    if (!$insertResult) {
        die("Insert/Update failed: " . $remoteConnection->error);
    }
}

// Close connections
$localConnection->close();
$remoteConnection->close();

echo "Sync completed successfully.";

?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Patient Record Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patient Photos by ID</title>
</head>
<body  style="background-color:#5B5EA6; color:white;">
    <h1 class="text-center">View Patient Photos by ID</h1>
    <div class="row">
  <div class="mx-auto col-10 col-md-8 col-lg-6">
 
    <form method="post">
        <label for="patientID">Enter Patient ID:</label>
        <div class="d-inline-flex p-2">
        <input type="text" name="patientID" class="form-control" id="patientID" required></div>
        <a href="admin_dashboard.php"> <button type="button" class="btn    text-white" style="background-color:#FF6666;"><i class="fa fa-arrow-left" aria-hidden="true"></i> back</button> 
</a>
        <input type="submit" name="submit" class="btn btn-info   text-white" value="Submit">
    </form>
</div></div>
    <?php
    if (isset($_POST['submit'])) {
        if (isset($_POST['patientID'])) {
            $patientID = $_POST['patientID'];

            require_once 'config.php';

            // SQL query to retrieve patient photos based on patient ID
            $sql = "SELECT photo_data FROM patient_photos WHERE patient_id = '$patientID'";

            // Execute the SQL query
            $result = $conn->query($sql);
       
            if ($result && $result->num_rows > 0) {
                // Display the images directly on the page using the data URI scheme
                echo '<h4 class="text-center">Patient ID: ' . $patientID . '</h4>';
                while ($rowData = $result->fetch_assoc()) {
                    $base64data = $rowData['photo_data'];

                    // Remove the data URI scheme part (e.g., data:image/jpeg;base64,)
                    $base64data = preg_replace('#^data:image/\w+;base64,#i', '', $base64data);

                    echo '<img src="data:image/jpeg;base64,' . $base64data . '" alt="Patient Photo">';
                }
            } else {
                echo '<p>No photos found for the given patient ID.</p>';
            }
          
            // Close the database connection
            $conn->close();
        }
    }
    ?>

</body>
</html>

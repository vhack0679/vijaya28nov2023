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




// Initialize variables to store search query and results
$searchQuery = "";
$patientDetails = null;
$diagnosisData = array();

// Check if the search form is submitted
if (isset($_POST['search'])) {
    // Get the search query from the form
    $searchQuery = $_POST['searchQuery'];

    // SQL query to retrieve patient details and their diagnosis data using LEFT JOIN
$sql = "SELECT patients.*, diagnoses.diagnosis_datetime, diagnoses.symptom, diagnoses.medication
        FROM patients
        LEFT JOIN diagnoses ON patients.patient_id = diagnoses.patient_id
        WHERE patients.patient_id = '$searchQuery' OR patients.name = '$searchQuery'";




    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if there are any matching results
    if ($result->num_rows > 0) {
        // Separate patient details and diagnosis data
        while ($row = $result->fetch_assoc()) {
            // Store patient details only once
            if ($patientDetails === null) {
                $patientDetails = array(
                    'patient_id' => $row['patient_id'],
                    'name' => $row['name'],
                    'gender' => $row['gender'],
                    'address' => $row['address'],
                    'age' => $row['age'],
                    'phone_number' => $row['phone_number'],
                    'occupation' => $row['occupation'],
                    'diet' => $row['diet'],
                    'date_of_joining' => $row['date_of_joining'],
                    'reference' => $row['reference'],
                    'thermals' => $row['thermals'],
                    'email' => $row['email'],
                    'next_visit_date' => $row['next_visit_date'],
                    // Add other patient details here as needed
                );
            }

            // Check if diagnosis data exists and add it to the array
            if (!empty($row['diagnosis_datetime'])) {
                $diagnosisData[] = array(
                    'diagnosis_datetime' => $row['diagnosis_datetime'],
                    'symptom' => $row['symptom'],
                    'medication' => $row['medication'],

                );
            }
        }
    }
}

// Handle button clicks for editing patient details and adding diagnosis data
$isEditMode = true; // Initialize $isEditMode as false by default

if (isset($_POST['edit'])) {
    $isEditMode = true;
} elseif (isset($_POST['save'])) {
    // Save the updated patient details to the database
    $updatedPatientID = $_POST['updatedPatientID'];
    $updatedName = $_POST['updatedName'];
    $updatedGender = $_POST['updatedGender'];
    $updatedAddress = $_POST['updatedAddress'];
    $updatedAge = $_POST['updatedAge'];
    $updatedPhonenumber = $_POST['updatedPhonenumber'];
    $updatedOccupation = $_POST['updatedOccupation'];
    $updatedDite = $_POST['updatedDite'];
    $updatedReference = $_POST['updatedReference'];
    $updatedThermals = $_POST['updatedThermals'];
    $updatedEmail = $_POST['updatedEmail'];
    $updatedNextvisitdate = $_POST['updatedNextvisitdate'];
    //------------------------------------------------------ Add other fields here as needed

    //------------------------------------------------ SQL query to update the patient details in the database
    $updateSql = "UPDATE patients SET name = '$updatedName', gender = '$updatedGender', address ='$updatedAddress', age='$updatedAge', phone_number='$updatedPhonenumber', occupation='$updatedOccupation', diet='$updatedDite', reference='$updatedReference', thermals='$updatedThermals', email='$updatedEmail', next_visit_date='$updatedNextvisitdate' WHERE patient_id = '$updatedPatientID' ";

    //------------------------------------------------------Execute the SQL query to update the patient details
    if ($conn->query($updateSql) === TRUE) {
        echo "Patient details updated successfully!";
        $isEditMode = false; // Exit edit mode after saving
    } else {
        echo "Error updating patient details: " . $conn->error;
    }
}

//----------------------------------------------------------------- Code to add diagnosis data

//------------------------------------------
if (isset($_POST['saveData'])) {
    // Assuming $conn is a valid database connection object
    // Include necessary validation and sanitization for user inputs

    $patientID = mysqli_real_escape_string($conn, $_POST['patientID']);
    $diagnosisDateTimes = isset($_POST['diagnosis_datetime']) ? $_POST['diagnosis_datetime'] : array();
    $symptoms = isset($_POST['symptoms']) ? $_POST['symptoms'] : array();
    $medications = isset($_POST['medications']) ? $_POST['medications'] : array();

    // Check if the photo is captured
    $capturedPhotoData = isset($_POST['capturedPhoto']) ? json_decode($_POST['capturedPhoto'], true) : array();
    $diagnosisSuccessMessageDisplayed = false;
    $photoSuccessMessageDisplayed = false;

    // Iterate over the data arrays and insert each row into the database
    for ($i = 0; $i < count($diagnosisDateTimes); $i++) {
        $diagnosisDatetime = mysqli_real_escape_string($conn, $diagnosisDateTimes[$i]);
        $symptom = isset($symptoms[$i]) ? mysqli_real_escape_string($conn, $symptoms[$i]) : '';
        $medication = isset($medications[$i]) ? mysqli_real_escape_string($conn, $medications[$i]) : '';

        // Use prepared statement to insert diagnosis data
        $insertDiagnosisSql = "INSERT INTO diagnoses (patient_id, diagnosis_datetime, symptom, medication) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertDiagnosisSql);
        $stmt->bind_param("ssss", $patientID, $diagnosisDatetime, $symptom, $medication);

        if ($stmt->execute()) {
            $diagnosisSuccessMessageDisplayed = true;
        } else {
            echo "Error adding diagnosis data: " . $stmt->error;
            // You might want to add some error handling here.
        }

        $stmt->close();
    }

    // Check if there are captured photos
    if (!empty($capturedPhotoData) && !$photoSuccessMessageDisplayed) {
        // Iterate over the captured photos and insert each row into the database
        foreach ($capturedPhotoData as $photoData) {
            // Use prepared statement to insert photo data
            $insertPhotoSql = "INSERT INTO patient_photos (patient_id, photo_data) VALUES (?, ?)";
            $stmt = $conn->prepare($insertPhotoSql);
            $stmt->bind_param("ss", $patientID, $photoData);

            if ($stmt->execute()) {
                $photoSuccessMessageDisplayed = true;
            } else {
                echo "Error adding photo: " . $stmt->error;
                // You might want to add some error handling here.
            }

            $stmt->close();
        }
    }
    // Display the appropriate modal
    if ($diagnosisSuccessMessageDisplayed) {
        echo '<div id="successModal" class="modal" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" style="color: green;"><i class="fas fa-check-circle"></i> Success</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="document.getElementById(\'successModal\').style.display = \'none\'"></button>
                    </div>
                    <div class="modal-body">
                        <p style="color: green;">Diagnosis data added successfully!</p>
                        <button type="button" class="btn btn-secondary text-white ml-4" onclick="openPopup()">Print</button>
                    </div>
                </div>
            </div>
        </div>';
    } elseif ($photoSuccessMessageDisplayed) {
        echo '<div id="successModal" class="modal" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" style="color: green;"><i class="fas fa-check-circle"></i> Success</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="document.getElementById(\'successModal\').style.display = \'none\'"></button>
                    </div>
                    <div class="modal-body">
                        <p style="color: green;">Photo added successfully!</p>
                        <button type="button" class="btn btn-secondary text-white ml-4" onclick="openPopup()">Print</button>
                    </div>
                </div>
            </div>
        </div>';

    }
    
}

// Initialize variables
$phone = "";
$results = array(); // Array to store PNO and names

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['phone'])) {
    // Get the phone number from the form
    $phone = $_POST['phone'];

    // Check if the phone number is not empty
    if (!empty($phone)) {
        // SQL query to search for distinct PNO and NAME pairs with the given phone number
        $sql = "SELECT DISTINCT patient_id, name FROM patients WHERE phone_number = '$phone'";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // PNO and names found, retrieve and store them
            while ($row = $result->fetch_assoc()) {
                $results[] = array(
                    'patient_id' => $row['patient_id'],
                    'name' => $row['name']
                );
            }
        } else {
            // No matching records found
            $results[] = array(
                'patient_id' => "No records found for the given phone number.",
                'name' => "No records found for the given phone number."
            );
        }
    }
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Patient Record Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <!-- Include Bootstrap CSS for styling -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Include jQuery library -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body style="background-color:#95bbf0; color:black;">

<style>
        
        .popup {
        display: none;
        position: fixed;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        border: 1px solid #000;
        background: #181818;
        padding: 20px;
        z-index: 9999;
        width:400px;
    }
    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9998;
    }
    #newLocation {
            display: flex;
            justify-content: center;
           
        }
     
    .details-container {
        text-align: center;
    }

    #suggestionList {
        list-style-type: none;
        padding: 0;
        background-color: white;
        color: black;
        width: 500px;
        margin-left: 110px;
        margin-top: 0px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        max-height: 150px; /* Set a specific height for the suggestion list */
        overflow-y: auto; /* Add a vertical scrollbar when content exceeds the height */
    }

    #suggestionList li {
        padding: 5px 10px;
        cursor: pointer; /* Change the cursor to a pointer when hovering over list items */
    }

    
    #suggestionList1 {
        list-style-type: none;
        padding: 0;
        background-color: white;
        color: black;
        width: 90px;
        margin-left: 90px;
        margin-top: 0px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        max-height: 150px; /* Set a specific height for the suggestion list */
        overflow-y: auto; /* Add a vertical scrollbar when content exceeds the height */
    }

    #suggestionList1 li {
        padding: 5px 10px;
        cursor: pointer; /* Change the cursor to a pointer when hovering over list items */
    }
    hr.new5 {
  border: 2px solid red;
  border-radius: 5px;
 
}
</style>

    <h2 class="text-center" style="color:#CFFAE3;border:white;border-width:2px; border-style:solid; "><i class="fa fa-search"></i> OLD PATIENT</h2>
    <!----------------------------------------- search ------------------------------------------------->
    <a href="admin_dashboard.php"> <button type="button" class="btn  mb-2 ml-2  text-white" style="background-color:#FF6666;"><i class="fa fa-arrow-left" aria-hidden="true"></i></button> 
</a>
    <br/><div class="d-flex  flex-wrap bd-highlight mb-3">
    <div class="p-2 bd-highlight" >
   
        <form class="form-inline justify-content-center" method="post" action="viewpatients.php">
            <div class="d-inline-flex p-2">
                PATIENT ID:  <input type="text" style="width:90px;" id="patientNameInput1" name="searchQuery" autocomplete="off" placeholder=" Id">
            </div>
            <button type="submit" class="btn btn-primary " name="search"><i class="fa fa-search"></i></button>
        </form> 
        <ul id="suggestionList1"></ul>
    </div>
    <div class="p-2 bd-highlight" style="width:890px;">
        <form   method="post" action="viewpatients.php">
            PATIENT NAME:  
            <input type="text" style="width: 57%;" name="searchQuery"  oninput="convertToUpper(this)" placeholder="Enter Patient Name" id="patientNameInput" >
            <button type="submit" class="btn btn-primary " name="search"><i class="fa fa-search"></i></button>
          
        </form>
        <ul id="suggestionList"></ul>

        <script>
    function convertToUpper(input) {
        input.value = input.value.toUpperCase();
    }
</script>
    </div>
<div class="p-2 bd-highlight" >
<form method="post" action="viewpatients.php">
            
                
                <input type="text" id="phone" name="phone" placeholder="Phone No" style="width:100px;" autocomplete="off"  >
         
            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
        </form>
    </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function(){
        $('#patientNameInput').on('input', function() {
            var query = $(this).val();

            // Check if the input length is at least 5 characters
            if (query.length >= 4) {
                // Fetch data from PHP file
                $.ajax({
                    url: 'fetch_data.php',
                    data: {query: query},
                    type: 'GET',            
                    success: function(response) {
                        var suggestions = JSON.parse(response);
                        var suggestionList = $('#suggestionList');
                        suggestionList.empty(); // Clear the previous suggestions

                        suggestions.forEach(function(suggestion) {
                            var li = $('<li>' + suggestion + '</li>').click(function() {
                                $('#patientNameInput').val($(this).text()); // Set the input value to the clicked suggestion
                                suggestionList.empty(); // Clear the suggestion list
                            });
                            suggestionList.append(li);
                        });
                    }
                });
            } else {
                $('#suggestionList').empty(); // Clear the suggestion list if the input length is less than 5
            }
        });

        $('#patientNameInput1').on('input', function() {
            var query = $(this).val();

            // Fetch data from PHP file for patient ID
            $.ajax({
                url: 'id.php',
                data: {query: query},
                type: 'GET',
                success: function(response) {
                    var suggestions = JSON.parse(response);
                    var suggestionList = $('#suggestionList1');
                    suggestionList.empty(); // Clear the previous suggestions

                    suggestions.forEach(function(suggestion) {
                        var li = $('<li>' + suggestion + '</li>').click(function() {
                            $('#patientNameInput1').val($(this).text()); // Set the input value to the clicked suggestion
                            suggestionList.empty(); // Clear the suggestion list
                        });
                        suggestionList.append(li);
                    });
                }
            });
        });
    });
</script>

</div><hr class="new5">
<?php if (!empty($results)) : ?>
            <div class="mt-3">
                <table class="table table-bordered" style="color:white;">
                    <thead>
                        <tr>
                            <th>Reg.No</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result) : ?>
                            <tr>
                                <td><?php echo $result['patient_id']; ?></td>
                                <td><?php echo $result['name']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <!------------------------------------------------------------------------------------------------------->
    
    <!-- Display the patient details in input tags -->
    <?php if (!empty($patientDetails)) { ?>
        <form method="post" action="viewpatients.php">
        <?php
   if (isset($_POST['search'])) {
    if (isset($_POST['searchQuery'])) {
        $searchQuery = $_POST['searchQuery'];

        // SQL query to retrieve patient photos based on patient ID or name
       $sqlPhotos = "SELECT patient_photos.photo_data FROM patient_photos 
            INNER JOIN patients ON patient_photos.patient_id = patients.patient_id 
            WHERE patients.name = '$searchQuery'";
 //---------REMOVE THE  LIKE----------- 

        // Execute the SQL query for photos
        $resultPhotos = $conn->query($sqlPhotos);

        if ($resultPhotos && $resultPhotos->num_rows > 0) {
            // Display the images directly on the page using the data URI scheme
            echo '<div id="photoGallery">';
            $displayed = false; // Flag to track whether an image has been displayed
            while ($rowData = $resultPhotos->fetch_assoc()) {
                if (!$displayed) {
                    $base64data = $rowData['photo_data'];
                    $base64data = preg_replace('#^data:image/\w+;base64,#i', '', $base64data);
                    echo '<img src="data:image/jpeg;base64,' . $base64data . '" alt="Patient Photo" id="pre" class="rounded-3 shadow-4 float-end w-25">';
                    $displayed = true; // Set the flag to true after displaying the first image
                }
            }
            echo '</div>';
        } else {
            // If no images found based on patient name, then search by patient ID
            $sqlPhotos = "SELECT photo_data FROM patient_photos WHERE patient_id = '$searchQuery'";
            // Execute the SQL query for photos
            $resultPhotos = $conn->query($sqlPhotos);

            if ($resultPhotos && $resultPhotos->num_rows > 0) {
                // Display the images directly on the page using the data URI scheme
                echo '<div id="photoGallery">';
                $displayed = false; // Reset the flag
                while ($rowData = $resultPhotos->fetch_assoc()) {
                    if (!$displayed) {
                        $base64data = $rowData['photo_data'];
                        $base64data = preg_replace('#^data:image/\w+;base64,#i', '', $base64data);
                        echo '<img src="data:image/jpeg;base64,' . $base64data . '" alt="Patient Photo" id="pre"  class="rounded-3 shadow-4 float-end w-25">';
                        $displayed = true; // Set the flag to true after displaying the first image
                    }
                }
                echo '</div>';
            } else {
                echo '<p>No photos found for the given patient.</p>';
            }
        }
    }
}

?>    

        <div class="p-2 bd-highlight"> Patient ID: <input type="text"  style="width:90px;"  name="updatedPatientID" value="<?php echo $patientDetails['patient_id']; ?>" readonly></div>
        <div class="d-flex  flex-wrap bd-highlight mb-3">

        <div class="p-2 bd-highlight"  style="width: 57%;" >Name: <input type="text"name="updatedName"  style="width: 89.5%;"  value="<?php echo $patientDetails['name']; ?>" <?php echo $isEditMode ? '' : 'readonly'; ?>></div>
        <div class="p-2 bd-highlight"> age <input type="text" style="width:50px;" name="updatedAge" value="<?php echo $patientDetails['age']; ?>" <?php echo $isEditMode ? '' : 'readonly'; ?>>
        
    </div>

        <div class="p-2 bd-highlight">  Gender:<input type="text" style="width:60px;" name="updatedGender" value="<?php echo $patientDetails['gender']; ?>" <?php echo $isEditMode ? '' : 'readonly'; ?>></div>
               
            </div>
            <div class="d-flex  flex-wrap bd-highlight mb-3">
            <div class="p-2 bd-highlight">  Address:<input type="text"  name="updatedAddress" value="<?php echo $patientDetails['address']; ?>" <?php echo $isEditMode ? '' : 'readonly'; ?>> </div>
                <div class="p-2 bd-highlight">phone number:<input type="text" id="phoneNumberInput" style="width:120px;"   name="updatedPhonenumber" value="<?php echo $patientDetails['phone_number']; ?>" <?php echo $isEditMode ? '' : 'readonly'; ?>></div>
                <div class="p-2 bd-highlight">  occupation:<input type="text"  name="updatedOccupation" value="<?php echo $patientDetails['occupation']; ?>" <?php echo $isEditMode ? '' : 'readonly'; ?>></div>
                

</div>

            <div class="d-flex  flex-wrap bd-highlight mb-3">
            <div class="p-2 bd-highlight">  diet:<input type="text" style="width:110px;" name="updatedDite" value="<?php echo $patientDetails['diet']; ?>" <?php echo $isEditMode ? '' : 'readonly'; ?>></div>
                <div class="p-2 bd-highlight">  thermals:<input type="text" style="width:70px;"  name="updatedThermals" value="<?php echo $patientDetails['thermals']; ?>" <?php echo $isEditMode ? '' : 'readonly'; ?>></div>
               <!-- <div class="p-2 bd-highlight"> date of joining: <input type="date" style="width:114px;" value="<?php echo $patientDetails['date_of_joining']; ?>" readonly></div>
                --><div class="p-2 bd-highlight">ref:<input type="text"  name="updatedReference" value="<?php echo $patientDetails['reference']; ?>" <?php echo $isEditMode ? '' : 'readonly'; ?>></div>
                <div class="p-2 bd-highlight">  email:<input type="text" style="width:200px;"  name="updatedEmail" value="<?php echo $patientDetails['email']; ?>" <?php echo $isEditMode ? '' : 'readonly'; ?>> </div>
                
</div>      
                
               
            <div class="d-flex  flex-wrap bd-highlight mb-3">
               
               <div class="p-2 bd-highlight">  next visit date:<input type="date" id="nextVisitDate" name="updatedNextvisitdate" value="<?php echo $patientDetails['next_visit_date']; ?>" <?php echo $isEditMode ? '' : 'readonly'; ?>> </div>
             <div class="p-2 bd-highlight"> <input type="number"  style="width:50px;" id="visitInterval" oninput="updateNextVisitDate()" /></div>
               <?php if ($isEditMode) { ?>
                <button type="submit" class="btn btn-danger " style="margin-left:150px;" name="save">Save</button>
            <?php } else { ?>
                <button type="submit" class="btn btn-primary" name="edit">Edit</button>
            <?php } ?>
			</div>
            
                <img src="" />

            <script>
  function updateNextVisitDate() {
    const visitInterval = document.getElementById('visitInterval').value;
    const dateField = document.getElementById('nextVisitDate');
    const currentDate = new Date();
    currentDate.setDate(currentDate.getDate() + parseInt(visitInterval));

    const year = currentDate.getFullYear();
    const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
    const day = currentDate.getDate().toString().padStart(2, '0');
    dateField.value = `${year}-${month}-${day}`;
  }
</script>







<hr class="new5">
        
        </form>
        
    <?php } else if (isset($_POST['search'])) { ?>
        <!-- Display a message when no matching patient is found -->
        <p>No matching patient found.</p>
    <?php } ?>
    <?php if (!empty($diagnosisData)) { ?>
       
        <div style="max-height: 450px; overflow-y: auto;"  id="diagnosisDataContainer">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Symptoms</th>
                    <th>Medications</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($diagnosisData as $diagnosis) { ?>
                    <tr>
                        <td style="width:140px;"><input style="width:140px;" type="text" value="<?php echo date('d-M-y g:i A', strtotime($diagnosis['diagnosis_datetime'])); ?>" readonly></td>
                        <td><input type="text"   value="<?php echo $diagnosis['symptom']; ?>" readonly  style="width:100%;"></td>
                        <td><input type="text" value="<?php echo $diagnosis['medication']; ?>" readonly  style="width:100%;"></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        
        
                </div>
                <script>
    // Adjust this selector to match your container's ID
    var container = document.getElementById('diagnosisDataContainer');
    container.scrollTop = container.scrollHeight; // Set scroll to the bottom initially

    var formContainer = document.getElementById('formContainer');
    formContainer.scrollTop = formContainer.scrollHeight; // Set scroll to the bottom initially
</script>
                <?php } else if (isset($_POST['search'])) { ?>
        <div class="text-center">no treatment file found for this patient</div>
    
    <?php } else if (isset($_POST['search'])) { ?>
        <!-- Display a message when no matching patient is found -->
        <p class="text-center">No matching patient found.</p>
    <?php } ?>


     <!-- Form to add diagnosis data -->
     <?php if (!empty($patientDetails)) { ?>
    <form method="post" action="viewpatients.php">
    
        
<div style="max-height: 300px; overflow-y: auto;" id="formContainer">
    <table id="diagnosis-table" class="table table-bordered">
        <thead>
            <tr>
                <th style="width:4%">Date</th> 
                <th style="width: 50%;">Symptoms</th>
                <th style="width: 50%;">Medications</th>
            </tr>
        </thead>
        <tbody>
            <!-- Default row for initial display -->
            
        </tbody>
    </table>
</div>
<script>
var formContainer = document.getElementById('formContainer');
    formContainer.scrollTop = formContainer.scrollHeight; // Set scroll to the bottom initially
</script>
<div class="d-flex justify-content-center">
    <button type="button" class="btn btn-primary" onclick="addDiagnosisRow()">+ </button>
<button type="button" class="btn btn-danger ml-4" onclick="deleteLastRow()">- </button>
</div>

<script>
 
            // Function to delete the last row from the Diagnosis table
            function deleteLastRow() {
        var table = document.getElementById('diagnosis-table');
        var rowCount = table.rows.length;
        if (rowCount > 1) {
            table.deleteRow(rowCount - 1);
        }
    }
    let rowCount = 1; // Start with 1 to match the second row

    function addDiagnosisRow() {
    const tableBody = document.getElementById('diagnosis-table').getElementsByTagName('tbody')[0];
    const newRow = tableBody.insertRow();

    // Create new cells for the new row
    const dateCell = newRow.insertCell();
    const symptomsCell = newRow.insertCell();
    const medicationsCell = newRow.insertCell();

    // Set the HTML content for the new cells
    const datetimeInputId = `datetime-input-${rowCount}`;
    const symptomsInputId = `symptoms-input-${rowCount}`;
    const medicationsInputId = `medications-input-${rowCount}`;

    dateCell.innerHTML = `<input style="width:70px;" type="datetime-local" name="diagnosis_datetime[]" id="${datetimeInputId}" required>`;
    symptomsCell.innerHTML = `<input style="width:100%;" oninput="convertToUpper(this)"  autocomplete="off"  list="${symptomsInputId}" type="text" name="symptoms[]">`;
    medicationsCell.innerHTML = `<input style="width:100%;" oninput="convertToUpper(this)" autocomplete="off" list="${medicationsInputId}" type="text" name="medications[]">`;

    // Create datalist for symptoms
    const symptomsDatalist = document.createElement('datalist');
    symptomsDatalist.id = symptomsInputId;
    symptomsCell.appendChild(symptomsDatalist);

    // Create datalist for medications
    const medicationsDatalist = document.createElement('datalist');
    medicationsDatalist.id = medicationsInputId;
    medicationsCell.appendChild(medicationsDatalist);

    // Increment the rowCount for the next row
    rowCount++;

    // Update the date and time for the newly added row
    updateDateTimeField(datetimeInputId);

    // Fetch medication list from the server and populate datalist
    fetchMedicationList(medicationsDatalist);
    fetchSysList(symptomsDatalist);
    
}

function fetchSysList(datalist) {
    // Use AJAX to fetch medication list from the server
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const medications = JSON.parse(xhr.responseText);

            // Populate datalist with medications
            medications.forEach(function (medication) {
                const option = document.createElement('option');
                option.value = medication;
                datalist.appendChild(option);
            });
        }
    };

    // Adjust the URL to the endpoint that returns the medication list
    xhr.open('GET', 'fetch_symptoms_list.php', true);
    xhr.send();
}

    

function fetchMedicationList(datalist) {
    // Use AJAX to fetch medication list from the server
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const medications = JSON.parse(xhr.responseText);

            // Populate datalist with medications
            medications.forEach(function (medication) {
                const option = document.createElement('option');
                option.value = medication;
                datalist.appendChild(option);
            });
        }
    };

    // Adjust the URL to the endpoint that returns the medication list
    xhr.open('GET', 'fetch_medication_list.php', true);
    xhr.send();
}

function updateDateTimeField(datetimeInputId) {
    const currentDateTimeField = document.getElementById(datetimeInputId);
    currentDateTimeField.value = getCurrentDateTime();
}
// to disable enter key
document.addEventListener("keydown", function (event) {
  if (event.key === "Enter") {
    event.preventDefault();
  }
});
// Update the date and time every second for each datetime input field
setInterval(() => {
    for (let i = 1; i < rowCount; i++) {
        const datetimeInputId = `datetime-input-${i}`;
        updateDateTimeField(datetimeInputId);
    }
}, 1000);



    </script>
     <script>
       function convertToUpper(input) {
        input.value = input.value.toUpperCase();
    } </script>
    <!-- ********************** -->
    <div class="col-md-3">
    <input type="hidden" name="patientID" value="<?php echo $patientDetails['patient_id']; ?>">
    <button type="button" class="btn btn-danger text-white ml-4" onclick="openPopup()">Print</button>
    <button type="submit" class="btn btn-success text-white " style="margin-left:90px;" name="saveData">Save</button>
   
</div>
<div class="row justify-content-end">
    <div class="col-md-6">
        <label for="startCamera">Capture Photo</label>
        <button type="button" class="btn btn-primary" id="startCamera">Start Camera</button>
        <button type="button" class="btn btn-danger" id="capturePhoto" disabled>Capture Photo</button>
    </div>
</div>
<hr>
<div class="row justify-content-end mt-3">
    <div class="col-md-6">
        
        <video id="videoPreview" width="500px" height="500px"></video>
        <canvas id="canvasPreview" class="" width="100%" height="auto"></canvas>
        <input type="hidden" id="capturedPhoto" name="capturedPhoto">
        <div id="photosContainer"></div>
    </div>
</div>
<script>
// JavaScript code to handle live photo capturing

let capturedPhotos = [];

// Function to start the camera and show the video preview
function startCamera() {
    const constraints = {
        video: true,
    };

    // Access the user's webcam and display the video on the page
    navigator.mediaDevices.getUserMedia(constraints)
        .then((stream) => {
            const video = document.getElementById('videoPreview');
            video.srcObject = stream;
            video.onloadedmetadata = () => {
                video.play();
            };

            // Enable the "Capture Photo" button once the camera is ready
            document.getElementById('capturePhoto').disabled = false;
        })
        .catch((error) => {
            console.error('Error accessing the webcam:', error);
        });
}

// Function to capture a photo from the video stream
function capturePhoto() {
    const video = document.getElementById('videoPreview');
    const canvas = document.getElementById('canvasPreview');
    const context = canvas.getContext('2d');

    // Set the canvas dimensions to match the video stream
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    // Draw the current frame of the video on the canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Get the image data from the canvas and convert it to a data URL
    const imageData = canvas.toDataURL('image/jpeg');

    // Add the captured photo to the array
    capturedPhotos.push(imageData);

    // Display the captured photos
    displayPhotos();

    // Clear the canvas for the next capture
    context.clearRect(0, 0, canvas.width, canvas.height);
}
function deletePhoto(index) {
    capturedPhotos.splice(index, 1); // Remove the photo at the specified index
    displayPhotos(); // Refresh the displayed photos
}
// Function to display the captured photos
function displayPhotos() {
    const photosContainer = document.getElementById('photosContainer');
    photosContainer.innerHTML = '';

    capturedPhotos.forEach((photoData, index) => {
        const img = document.createElement('img');
        img.src = photoData;
        img.alt = `Captured Photo ${index + 1}`;
        img.style.width = '500px';
        
        img.style.marginRight = '5px';

        // Create a delete button for each photo
        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'btn btn-danger btn-sm';
        deleteButton.innerText = 'Delete';
        deleteButton.onclick = () => deletePhoto(index);

        const photoContainer = document.createElement('div');
        photoContainer.appendChild(img);
        photoContainer.appendChild(deleteButton);

        photosContainer.appendChild(photoContainer);
    });

    // Update the hidden input field with the current captured photos
    document.getElementById('capturedPhoto').value = JSON.stringify(capturedPhotos);
}


// Add event listeners to the "Start Camera," "Capture Photo," and "Save Photo" buttons
document.getElementById('startCamera').addEventListener('click', startCamera);
document.getElementById('capturePhoto').addEventListener('click', capturePhoto);
// Automatically save the photos when the form is submitted
document.getElementById('yourFormId').addEventListener('submit', function(event) {
    // Update the hidden input field with the current captured photos before submitting the form
    document.getElementById('capturedPhoto').value = JSON.stringify(capturedPhotos);
});
</script>

<button type="submit" class="btn btn-success text-white  ml-4" name="saveData"><i class="fa fa-save"></i> Save Data</button>
   
</body>
</html>
<div class="overlay" onclick="closePopup()" id="overlay"></div>
<div class="popup" id="popup">
    <div class="details-container ">
        <button type="button" class="close-button btn btn-danger float-right" onclick="closePopup()">X</button>
        <form id="popupForm">
       
            
            <div id="newLocation">
   
   </div>
           
            <p class="text-light" style="text-align: left;">Reg.No: <span id="popupID"></span></p> 
            <p class="text-light">Name: <input type="text" style="width:86%;" name="name" id="popupName" value="" /></p>
            <button type="button"  style="float: left;" class="btn btn-secondary text-white" onclick="printPopupContentphoto()">Print With Photo</button>
            <button type="button"  style="float: left;" class="btn btn-secondary text-white ml-3" onclick="printPopupContent()">Print Details</button>
        </form>
    </div>
</div>

<script>
    function moveImage() {
        // Get the src attribute of the image
        var imageSrc = document.getElementById('pre').src;

        // Create a new image tag and set the src attribute to the image source
        var img = document.createElement('img');
        img.src = imageSrc;
        img.alt = "Moved Image";
        img.className = "rounded-3 shadow-4 float-start w-25";

        // Append the new image to the new location
        document.getElementById('newLocation').appendChild(img);
    }

    // Call the moveImage function when the page loads
    window.onload = moveImage;
</script>
<script>
    function openPopup() {
        const popup = document.getElementById('popup');
        const overlay = document.getElementById('overlay');
        popup.style.display = "block";
        overlay.style.display = "block";
        const patientID = document.querySelector('input[name="updatedPatientID"]').value;
        const patientName = document.querySelector('input[name="updatedName"]').value;
        //const popupPhoto = document.getElementById('pre').src;
        // <img src="${popupPhoto}" alt="Patient Photo" />

        document.getElementById('popupID').innerText = patientID;
        document.getElementById('popupName').value = patientName;
        document.getElementById('popupPhoto').src = photoData;
    }

    function closePopup() {
        const popup = document.getElementById('popup');
        const overlay = document.getElementById('overlay');
        popup.style.display = "none";
        overlay.style.display = "none";
    }
    function printPopupContentphoto() {
    const popupID = document.getElementById('popupID').innerText;
    const popupName = document.getElementById('popupName').value;
    //const popupPhoto = document.getElementById('pre').src;

    const myWindow = window.open('', '_blank');
    myWindow.document.write(`
        <html>
            <head>
                <title>Popup Content</title>
                <style>
                .details-container {
                        position: absolute;
                        left: 32px;
                        top: 19px;
                        font-family: Arial, sans-serif;
                       
                    }
                    
                    .dp-circle {
                        width: 60px;
                        height: 60px;
                        border-radius: 50%;
                        overflow: hidden;
                        position: absolute;
                        left: 410px;
                        top: 14px;
                    }
                    .dp-circle img {
                        width: 100%;
                        height: 100%;
                    }
                </style>
            </head>
            <body>
                <div class="details-container">
                    <p><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Reg.No:&nbsp;&nbsp;&nbsp; ${popupID}</b></p>
                    <p><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Name:&nbsp;&nbsp;&nbsp;&nbsp;${popupName}</b></p>
                    <div class="dp-circle">
                        <img src="${popupPhoto}" alt="Patient Photo" />
                       </div> 
                    </div>
            </body>
        </html>
    `);
    myWindow.document.close();
    myWindow.print();
    setTimeout(function() {
        myWindow.close();
    }, 100);
}

function printPopupContent() {
    const popupID = document.getElementById('popupID').innerText;
    const popupName = document.getElementById('popupName').value;
    //const popupPhoto = document.getElementById('pre').src;

    const myWindow = window.open('', '_blank');
    myWindow.document.write(`
        <html>
            <head>
                <title>Popup Content</title>
                <style>
               
                .details-container {
                        position: absolute;
                        left: 32px;
                        top: 19px;
                        font-family: Arial, sans-serif;
                       
                    }
                
               
                </style>
            </head>
            <body>
                <div class="details-container">
                  
                <p><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Reg.No:&nbsp;&nbsp;&nbsp; ${popupID}</b></p>
                    <p><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Name:&nbsp;&nbsp;&nbsp;&nbsp;${popupName}</b></p>
                    
                </div>
            </body>
        </html>
    `);
    myWindow.document.close();
    myWindow.print();
    setTimeout(function() {
        myWindow.close();
    }, 100);
}
</script>



                <br/> <br/> <br/> <br/><br/><br/>
 
                <!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
#myDIV {
  width: 100%;
  padding: 50px 0;
  text-align: center;

 
  display:none;
}
</style>
</head>
<body>

<button onclick="myFunction()" class="btn btn-primary float-end">View More Photos....</button>

    <?php } ?>
  
    

    
<div id="myDIV">

<?php
  if (isset($_POST['search'])) {
    if (isset($_POST['searchQuery'])) {
        $searchQuery = $_POST['searchQuery'];

        // SQL query to retrieve patient photos based on patient ID or name
        $sqlPhotos = "SELECT patient_photos.photo_data FROM patient_photos 
            INNER JOIN patients ON patient_photos.patient_id = patients.patient_id 
            WHERE patients.name LIKE '%$searchQuery%'";

        // Execute the SQL query for photos
        $resultPhotos = $conn->query($sqlPhotos);

        $photoCount = 0; // Initialize the counter for the number of photos

        if ($resultPhotos && $resultPhotos->num_rows > 0) {
            // Display the images directly on the page using the data URI scheme
            echo '<div id="photoGallery">';
            while ($rowData = $resultPhotos->fetch_assoc()) {
                $base64data = $rowData['photo_data'];
                $base64data = preg_replace('#^data:image/\w+;base64,#i', '', $base64data);
                echo '<img src="data:image/jpeg;base64,' . $base64data . '" alt="Patient Photo" >';
                $photoCount++; // Increment the photo count for each photo displayed
            }
            echo '</div>';
        } else {
            // If no images found based on patient name, then search by patient ID
            $sqlPhotos = "SELECT photo_data FROM patient_photos WHERE patient_id = '$searchQuery'";
            // Execute the SQL query for photos
            $resultPhotos = $conn->query($sqlPhotos);

            if ($resultPhotos && $resultPhotos->num_rows > 0) {
                // Display the images directly on the page using the data URI scheme
                echo '<div id="photoGallery">';
                while ($rowData = $resultPhotos->fetch_assoc()) {
                    $base64data = $rowData['photo_data'];
                    $base64data = preg_replace('#^data:image/\w+;base64,#i', '', $base64data);
                    echo '<img src="data:image/jpeg;base64,' . $base64data . '" alt="Patient Photo" >';
                    $photoCount++; // Increment the photo count for each photo displayed
                }
                echo '</div>';
            } else {
                echo '<p>No photos found for the given patient.</p>';
            }
        }

        // Display the count of photos retrieved
        echo '<p>Total photos found: ' . $photoCount . '</p>';
    }
}

    ?>  
</div>


<script>
function myFunction() {
  var x = document.getElementById("myDIV");
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}

</script>

</body>
</html>


                
   


    <!-- Add the edit and save functionality -->
<script>
    //-------------------------------datetime------------------------------------------------
    function getCurrentDateTime() {
    const currentDate = new Date();
    const year = currentDate.getFullYear();
    const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
    const day = currentDate.getDate().toString().padStart(2, '0');
    const hours = currentDate.getHours().toString().padStart(2, '0');
    const minutes = currentDate.getMinutes().toString().padStart(2, '0');
   

    const currentDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    return currentDateTime;
}

// Update the time every second
setInterval(() => {
    const currentDateTime = getCurrentDateTime();
    document.getElementById("datetime-input").value = currentDateTime;
}, 1000); // 1000 milliseconds = 1 second


 
//-----------------------------------------------------------------------------------------
</script>
</script>
<?php

?>
<?php
// Database connection
$host = 'localhost';
$db   = 'vijyaydata';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if the search form is submitted
if (isset($_POST['search'])) {
    // Get the search queries from the form
    $name = $_POST['searchQuery'];

    // SQL query to retrieve photo paths based on exact match for PID or PNAME
    $sql = "SELECT PICNAME FROM photospath WHERE PID = :pid OR TRIM(PNAME) = :pname;";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pid', $name, PDO::PARAM_INT); // Assuming PID is an integer
    $stmt->bindParam(':pname', $name, PDO::PARAM_STR);
   
    $stmt->execute();

    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        echo '<h3>OLD PHOTOS:</h3>';
        foreach ($results as $result) {
            $photoPath = $result['PICNAME'];
            echo '<img src="' . $photoPath . '" alt="Photo">';
            echo '<br>';
        }
    } else {
        echo 'No old photos found for the given patient.';
    }
}


?>
</body>
</html>


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

// Initialize variables to store form data
$patient_id = "";
$name = "";
$gender = "";
$address = "";
$age = "";
$phone_number = "";
$occupation = "";
$diet = "";
$date_of_joining = "";
$reference = "";
$thermals = "";
$email = "";
$next_visit_date = "";
$nextPatientID = 1; // Default next patient ID is 1

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the form
    $name = $_POST["name"];
    $gender = $_POST["gender"];
    $address = $_POST["address"];
    $age = $_POST["age"];
    $phone_number = $_POST["phone_number"];
    $occupation = $_POST["occupation"];
    $diet = $_POST["diet"];
    $date_of_joining = $_POST["date_of_joining"];
    $reference = $_POST["reference"];
    $thermals = $_POST["thermals"];
    $email = $_POST["email"];
    $next_visit_date = $_POST["next_visit_date"];
  
    $photoData = $_POST['photo_data'];

    // SQL query to insert data into the patients table using prepared statements
    $stmt = $conn->prepare("INSERT INTO patients (name, gender, address, age, phone_number, occupation, diet, date_of_joining, reference, thermals, email, next_visit_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssissssssss", $name, $gender, $address, $age, $phone_number, $occupation, $diet, $date_of_joining, $reference, $thermals, $email, $next_visit_date);

    if ($stmt->execute()) {
        $patient_id = $stmt->insert_id; // Get the auto-incremented patient ID after insertion
        
        // Retrieve diagnosis data from the form
        $diagnosis_dates = $_POST["diagnosis_datetime"];
        $symptoms = $_POST["symptoms"];
        $medications = $_POST["medications"];

        // SQL query to insert diagnosis data into the diagnoses table using prepared statements
        $stmtDiagnosis = $conn->prepare("INSERT INTO diagnoses (patient_id, name, diagnosis_datetime, symptom, medication) VALUES (?, ?, ?, ?, ?)");
        $stmtDiagnosis->bind_param("issss", $patient_id, $name, $diagnosis_date, $symptom, $medication);

        for ($i = 0; $i < count($diagnosis_dates); $i++) {
            $diagnosis_date = $diagnosis_dates[$i];
            $symptom = $symptoms[$i];
            $medication = $medications[$i];
            
            if (!$stmtDiagnosis->execute()) {
                echo "Error: " . $stmtDiagnosis->error;
            }
        }

        // SQL query to insert the photo data into the database using prepared statements
        $stmtPhoto = $conn->prepare("INSERT INTO patient_photos (patient_id, name, photo_data ,capture_datetime) VALUES (?, ?, ?, ?)");
        $stmtPhoto->bind_param("isss", $patient_id, $name, $photoData, $diagnosis_date);
        if ($stmtPhoto->execute()) {
            echo "Photo captured and saved successfully!";
        } else {
            echo "Error saving photo: " . $stmtPhoto->error;
        }

        // Close the statements
        $stmt->close();
        $stmtDiagnosis->close();
        $stmtPhoto->close();
        
        // Redirect back to the form page with a success parameter in the URL
        header("Location: newpatient.php?success=true");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Retrieve the maximum patient ID from the patients table
$sqlMaxID = "SELECT MAX(patient_id) AS max_id FROM patients";
$resultMaxID = $conn->query($sqlMaxID);
if ($resultMaxID->num_rows > 0) {
    $rowMaxID = $resultMaxID->fetch_assoc();
    $nextPatientID = $rowMaxID["max_id"] + 1;
}
?>

<!-- *********************** ui starts from here************************************************ -->

<html lang="en">
  <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Patient record management</title>
    
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-resizable-columns/dist/jquery.resizableColumns.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

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
    .dp-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
        position: relative;
        margin: 0 auto;
    }
    .dp-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .details-container {
        text-align: center;
    }
</style>
     
</head>
<body style="background-color:#95bbf0; color:black;  ">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-resizable-columns/dist/jquery.resizableColumns.min.js"></script>

    <h2 class="text-center" style="color:#CFFAE3;border:white;border-width:2px; border-style:solid; "><i class="fa fa-user-plus f-center"></i>NEW PATIENT</h2><br/>
     <!-- Display the next patient ID to the user -->
    
    <form id="patient-form" class="mb-5" method="post" action="newpatient.php" enctype="multipart/form-data"  >
    <div class="float-right">
  <h2 class="text-center">Patient Photo </h2>
  <video id="video" width="340" height="280" autoplay></video>
  <br>
  <div class="text-center">
    
    <button onclick="capturePhoto()"  type="button" class="btn btn-primary" >Capture Photo</button>
</div>
    <br>
    <canvas id="canvas" width="640" height="480" style="display:none;"></canvas>
    <div class="text-center">
        
        <img id="preview" src="" alt="" style="width: 350; ">
    </div>
    <input type="hidden" name="patient_id" value="<?php echo $nextPatientID; ?>">
        <input type="hidden" id="photo_data" name="photo_data">
  </div> 
    <p> Patient ID: <font size="5px" style="color:black; "><b><?php echo $nextPatientID; ?></b></font></p>    
  

    <!-- Add patient data input fields here -->

    
    <div class="d-flex  flex-wrap bd-highlight mb-3">
  <div class="p-2 bd-highlight"  style="width: 57%;" >Name: <input type="text" name="name" style="width: 89.5%;"      oninput="convertToUpper(this)" required></div>
  <div id="name-suggestions"></div>
 
  <script>
    function convertToUpper(input) {
        input.value = input.value.toUpperCase();
    }
</script>
<div class="p-2  bd-highlight"> Age: <input type="number" style="width:50px;"  name="age" required>
  Gender: <select name="gender" id="gender" required>
  <option value="SELECT">SELECT</option>
  <option value="Male">Male</option>
  <option value="female">Female</option>
 
</select></div>



</div>
<div class="d-flex  flex-wrap bd-highlight mb-3">
<div class="p-2 bd-highlight">  Address: <input type="text" name="address" required></div>
  <div class="p-2 bd-highlight"> Phone Number: <input type="text" name="phone_number" ></div>
  <div class="p-2 bd-highlight">   Occupation: <input type="text" name="occupation" ></div>
</div>
<div class="d-flex  flex-wrap bd-highlight mb-3">
<div class="p-2 bd-highlight">  Diet: 
     
<select name="diet" id="diet" type="text" >
<option value="SELECT">SELECT</option>  
<option value="THIRSTY">THIRSTY</option>
  <option value="THIRST LESS">THIRST LESS</option>
 
</select>
</div>

<div class="p-2 bd-highlight">  Thermals: 
    
<select name="thermals" id="thermals" type="text" >
<option value="SELECT">SELECT</option>
  <option value="CHILLY">CHILLY</option>
  <option value="HOT">HOT</option>
  <option value="AMBI">AMBI</option>
</select>
</div>
  <div class="p-2 bd-highlight">  Date of Joining: <input type="date"  style="width:115px;" name="date_of_joining"  value="<?php echo date('Y-m-d'); ?>"></div>
  <div class="p-2 bd-highlight">   Reference: <input type="text" name="reference" ></div>
</div>
<div class="d-flex  flex-wrap bd-highlight mb-3">

    <div class="p-2 bd-highlight">   Email: <input type="email" name="email" ></div>
  <div class="p-2 bd-highlight">Next Visit in Days: <input type="number"  style="width:50px;" id="visitInterval" oninput="updateNextVisitDate()" /></div>
<div class="p-2 bd-highlight">Next Visit Date: <input type="date" name="next_visit_date" id="nextVisitDate"  /></div>
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
</div>


<div style="max-height: 300px; overflow-y: auto;">
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
            <tr>
                <td><input style="width:70px;" type="datetime-local" name="diagnosis_datetime[]" id="datetime-input" value="" required></td>
                <td><input type="text" name="symptoms[]" required style="width:100%;"></td>
                <td><input type="text" name="medications[]" required style="width:100%;"></td>
            </tr>
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function () {
        $('#diagnosis-table').resizableColumns();
    });
</script>

<script>

function getCurrentDateTime() {
        var currentDate = new Date();
        var year = currentDate.getFullYear();
        var month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
        var day = currentDate.getDate().toString().padStart(2, '0');
        var hours = currentDate.getHours().toString().padStart(2, '0');
        var minutes = currentDate.getMinutes().toString().padStart(2, '0');
        return day + '/' + month + '/' + year + 'T' + hours + ':' + minutes;
    }

        // Get the current date and time
        const currentDateTime = getCurrentDateTime();

        // Set the value of the input field
        document.getElementById("datetime-input").value = currentDateTime;
   
     // Function to update the current date and time every second

     function updateDateTimeField() {
        var currentDateTimeField = document.getElementById('datetime-input');
        currentDateTimeField.value = getCurrentDateTime();
    }

    // Update the date and time every second
    setInterval(updateDateTimeField, 1000);

    // Function to get the current date and time
    document.addEventListener('DOMContentLoaded', function() {
        const dateField = document.querySelector('input[name="date_of_joining"]');
        const currentDate = new Date();
        const year = currentDate.getFullYear();
        const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
        const day = currentDate.getDate().toString().padStart(2, '0');
        dateField.value = day + '/' + month + '/' + year;
    });


</script>
    <!-- Preview of the captured photo -->
    <br/>
    <div class="d-flex justify-content-center">
    <button type="button" class="btn btn-primary" onclick="addDiagnosisRow()">+ </button>
<button type="button" class="btn btn-danger ml-4" onclick="deleteLastRow()">- </button>
</div>

 <br/>
 <br/>
<button type="button" class="btn btn-secondary text-white ml-4" onclick="openPopup()">Print</button>


<button type="submit" class="btn btn-success text-white  ml-4" style="align=center;"><i class="fa fa-save"></i>  SAVE</button>

  
      <a href="admin_dashboard.php"> <button type="button" class="btn ml-4   text-white" style="background-color:#FF6666;"><i class="fa fa-arrow-left" aria-hidden="true"></i></button> 
</a>

   
</div>
<script>
    function openPopup() {
        const popup = document.getElementById('popup');
        const overlay = document.getElementById('overlay');
        popup.style.display = "block";
        overlay.style.display = "block";

        const patientID = document.querySelector('input[name="patient_id"]').value;
        const patientName = document.querySelector('input[name="name"]').value;
        const photoData = document.querySelector('#preview').src;

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
    const popupPhoto = document.getElementById('popupPhoto').src;

    const myWindow = window.open('', '_blank');
    myWindow.document.write(`
        <html>
            <head>
                <title>Popup Content</title>
                <style>
                .details-container {
                        position: absolute;
                        left: 36px;
                        top: 16px;
                        font-family: Arial, sans-serif;
                       
                    }
                    .dp-circle {
                        width: 50px;
                        height: 50px;
                        border-radius: 50%;
                        overflow: hidden;
                        position: absolute;
                        left: 300px;
                        top: 16px;
                    }
                    .dp-circle img {
                        width: 100%;
                        height: 100%;
                    }
                </style>
            </head>
            <body>
                <div class="details-container">
                    <div class="dp-circle">
                        <img src="${popupPhoto}" alt="Patient Photo">
                    </div>
                    <p>Reg.No: ${popupID}</p>
                    <p>Name: ${popupName}</p>
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
    const popupPhoto = document.getElementById('popupPhoto').src;

    const myWindow = window.open('', '_blank');
    myWindow.document.write(`
        <html>
            <head>
                <title>Popup Content</title>
                <style>
                .details-container {
                        position: absolute;
                        left: 36px;
                        top: 16px;
                        font-family: Arial, sans-serif;
                       
                    }
               
                </style>
            </head>
            <body>
                <div class="details-container">
                  
                    <p>Reg.No: ${popupID}</p>
                    <p>Name: ${popupName}</p>
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

    </form>
    <div class="overlay" onclick="closePopup()" id="overlay"></div>
<div class="popup" id="popup">
    <div class="details-container ">
        <button type="button" class="close-button btn btn-danger float-right" onclick="closePopup()">X</button>
        <form id="popupForm">
            <div class="dp-circle">
                <img src="" alt="Patient Photo" id="popupPhoto">
            </div>
            <p class="text-light" style="text-align: left;">Reg.No: <span id="popupID"></span></p> 
            <p class="text-light">Name: <input type="text" style="width:86%;" name="name" id="popupName" value="" /></p>
            <button type="button"  style="float: left;" class="btn btn-secondary text-white" onclick="printPopupContentphoto()">Print With Photo</button>
            <button type="button"  style="float: left;" class="btn btn-secondary text-white ml-3" onclick="printPopupContent()">Print Details</button>
        </form>
    </div>
</div>

<div id="successModal" class="modal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: green;"><i class="fas fa-check-circle"></i> Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="color: green;">Patient data inserted successfully!</p>
                <button type="button" class="btn btn-secondary text-white ml-4" onclick="openPopup()">Print</button>

            </div>
        </div>
    </div>
</div>
    <script>


        // JavaScript to set the current date in the Date of Joining field
        // This code will be executed when the page is loaded
        document.addEventListener('DOMContentLoaded', function() {
            const dateField = document.querySelector('input[name="date_of_joining"]');
            dateField.value = new Date().toISOString().split('T')[0];
        });
    </script>
     <script>
        
   
    //--------------------------------------------------------------------
       
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
    
    dateCell.innerHTML = `<input  style="width:70px;" type="datetime-local" name="diagnosis_datetime[]" id="${datetimeInputId}" required>`;
    symptomsCell.innerHTML = '<input style="width:100%;"  type="text" name="symptoms[]" >';
    medicationsCell.innerHTML = '<input style="width:100%;"  type="text" name="medications[]" >';

    // Increment the rowCount for the next row
    rowCount++;

    // Update the date and time for the newly added row
    updateDateTimeField(datetimeInputId);
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

function getCurrentDateTime() {
    const currentDate = new Date();
    const year = currentDate.getFullYear();
    const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
    const day = currentDate.getDate().toString().padStart(2, '0');
    const hours = currentDate.getHours().toString().padStart(2, '0');
    const minutes = currentDate.getMinutes().toString().padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}`;
}


    </script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#patient-form').submit(function (e) {
            e.preventDefault(); // Prevent the default form submission

            // Serialize the form data
            var formData = $(this).serialize();

            // Send the form data to the server using AJAX
            $.ajax({
                type: 'POST',
                url: 'newpatient.php',
                data: formData,
                success: function (response) {
                    // Handle the response here
                    if (response.includes('Photo captured and saved successfully!')) {
                        var successModal = document.getElementById('successModal');
                        successModal.style.display = 'block';

                        // Close the modal when the user clicks anywhere outside of it
                        successModal.addEventListener('click', function (event) {
                            if (event.target === successModal) {
                                successModal.style.display = 'none';
                            }
                        });
                    } else {
                        console.log(response); // Log the error response
                    }
                },
                error: function (error) {
                    console.error("Error occurred: ", error);
                }
            });
        });
    });
</script>

    <!-- JavaScript to handle live photo capture -->
    <script type="text/javascript">
        let videoStream;
        let video;
        let canvas;
        let context;

        function startCamera() {
            // Access the device camera
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    videoStream = stream;
                    video.srcObject = stream;
                })
                .catch(function (error) {
                    console.error("Error accessing the camera: " + error);
                });
        }

        function stopCamera() {
            if (videoStream) {
                videoStream.getTracks().forEach(function (track) {
                    track.stop();
                });
            }
        }
           
        function capturePhoto() {
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = canvas.toDataURL('image/jpeg'); // Change to image/jpeg for JPEG format

            // Display the captured photo as preview
            const preview = document.getElementById('preview');
            preview.src = imageData;
            preview.style.display = 'block';

            document.getElementById('photo_data').value = imageData;
        }

        window.addEventListener('load', function () {
            video = document.getElementById('video');
            canvas = document.getElementById('canvas');
            context = canvas.getContext('2d');

            startCamera();
        });

        window.addEventListener('unload', stopCamera);
    </script>
  
</body>
</html>
<!-- *********************** ui ends  here************************************************ -->


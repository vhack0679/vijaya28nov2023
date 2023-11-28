<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "patientmanagementsystem";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Get the patient ID and photo data from the form
    $patientID = $_POST['patient_id'];
    $photoData = $_POST['photo_data'];

    // SQL query to insert the photo data into the database
    $sql = "INSERT INTO patient_photos (patient_id, photo_data) VALUES ('$patientID', '$photoData')";

    // Execute the SQL query
    if ($conn->query($sql) === TRUE) {
        echo "Photo captured and saved successfully!";
    } else {
        echo "Error saving photo: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Live Photo Capture</title>
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
</head>
<body>
    <h2> Photo Capture</h2>
    <video id="video" width="640" height="480" autoplay></video>
    <br>
    <button onclick="capturePhoto()">Capture Photo</button>
    <br>
    <canvas id="canvas" width="640" height="480" style="display:none;"></canvas>

    <!-- Preview of the captured photo -->
    <div>
        <h3>Preview</h3>
        <img id="preview" src="" alt="Captured Photo" style="max-width: 100%; height: auto;">
    </div>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="patient_id" value="19"> <!-- Replace "1" with the actual patient ID -->
        <input type="hidden" id="photo_data" name="photo_data">
        <input type="submit" name="submit" value="Save Photo">
    </form>
</body>
</html>

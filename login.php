<?php
// Replace these credentials with your actual database information
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "patientmanagementsystem";

// Start the session (for maintaining user login state)
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the username and password from the login form
    $entered_username = $_POST["username"];
    $entered_password = $_POST["password"];

    try {
        // Connect to the database
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare the SQL statement to retrieve the user's information
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $entered_username);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            // Verify the entered password with the hashed password in the database
            if (password_verify($entered_password, $user['password'])) {
                // Authentication successful, set session variables
                $_SESSION["user_id"] = $user['user_id'];
                $_SESSION["username"] = $user['username'];
                $_SESSION["password"]= $user['password'];
                $_SESSION["role"] = $user['role'];

                // Redirect to the appropriate user's dashboard or home page based on role
                if ($user['role'] == 'admin') {
                    header("Location: admin_dashboard.php");
                } elseif ($user['role'] == 'doctor') {
                    header("Location: doctor_dashboard.php");
                } elseif ($user['role'] == 'staff') {
                    header("Location: staff_dashboard.php");
                }
                exit();
            } else {
                // Incorrect password
                echo "Invalid password. Please try again.";
            }
        } else {
            // User not found
            echo "User not found. Please check your username.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;
}

?>

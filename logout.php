<?php
// Start the session
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // If logged in, destroy the session to log out the user
    session_destroy();
    // Redirect to the login page or any other desired page after logout
    header("Location: login.html");
    exit();
} else {
    // If not logged in, redirect to the login page
    header("Location: login.html");
    exit();
}
?>

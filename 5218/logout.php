<?php
session_start(); // Start the session to destroy it

// Destroy the session
session_unset(); // Remove all session variables
session_destroy(); // Destroy the session

// Redirect to the main page (index.php) after logging out
header("Location: homePage.php");
exit();
?>

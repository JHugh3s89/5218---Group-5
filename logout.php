<?php
session_start();

// Secure session settings 
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,    // Ensure cookie is sent only over HTTPS
    'httponly' => true,  // Make the cookie inaccessible via JavaScript
    'samesite' => 'Strict'  // Prevent cookie from being sent in cross-site requests
]);

// Destroy the session
if (isset($_SESSION)) {
    session_unset(); // Remove all session variables
    session_destroy(); // Destroy the session

    // Clear session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }

    // Regenerate session ID to prevent session fixation (optional)
    session_regenerate_id(true);
}

// Redirect to the main page (homePage.php) after logging out
header("Location: homePage.php");
exit();
?>

<?php

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,  // Change to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();
// Secure session settings

require 'database_connection.php';

$errors = [];

// Generate CSRF Token for the form if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));  // Secure token generation
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $username = trim($_POST["username"]);
        $firstName = trim($_POST["firstName"]);
        $lastName = trim($_POST["lastName"]);
        $password = $_POST["password"];

        // Validate firstName and lastName 
        if (!preg_match("/^[a-zA-Z]+$/", $firstName)) {
            $errors[] = "First name can only contain letters.";
        }
        if (!preg_match("/^[a-zA-Z]+$/", $lastName)) {
            $errors[] = "Last name can only contain letters.";
        }
        if (strlen($firstName) < 1 || strlen($firstName) > 50) {
            $errors[] = "First name must be between 1 and 50 characters.";
        }
        if (strlen($lastName) < 1 || strlen($lastName) > 50) {
            $errors[] = "Last name must be between 1 and 50 characters.";
        }

        // Sanitize username to prevent XSS
        $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

        // Validate username only letters, numbers, and underscores allowed
        if (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
            $errors[] = "Username can only contain letters, numbers, and underscores.";
        }

        // Password strength check
        if (strlen($password) < 8 || 
            !preg_match("/[A-Z]/", $password) || 
            !preg_match("/\d/", $password) || 
            !preg_match("/[\W_]/", $password)) {
            $errors[] = "Password must be at least 8 characters long, contain one uppercase letter, one digit, and one special character.";
        }

        // Check if fields are empty
        if (empty($username) || empty($firstName) || empty($lastName) || empty($password)) {
            $errors[] = "All fields are required.";
        }

        // Check if username already exists
        $check_user = $conn->prepare("SELECT username FROM USERS WHERE username = ?");
        $check_user->bind_param("s", $username);
        $check_user->execute();
        $check_user->store_result();

        if ($check_user->num_rows > 0) {
            $errors[] = "Username already taken.";
        }
        $check_user->close();

        // If no errors, then insert user
        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO USERS (username, first_name, last_name, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $firstName, $lastName, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION["username"] = $username;
                
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Regenerate CSRF token after successful submission
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                echo "<script>
                        alert('Registration successful! Redirecting to login page...');
                        window.location.href = 'login.php';
                      </script>";
                exit();
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #222; /* Dark background */
            color: #fff; /* Light text */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        h2 {
            color: #007bff; /* Blue header */
            margin-bottom: 20px;
        }

        form {
            width: 300px;
            padding: 20px;
            background-color: #333; /* Dark form background */
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5); /* Shadow */
        }

        label {
            color: #fff; /* White label text */
            display: block;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 20px;
            border: none;
            border-radius: 5px;
            background-color: #555; /* Dark input background */
            color: #fff; /* Light text */
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #007bff; /* Blue button */
            color: #fff; /* Light text */
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        p {
            text-align: center;
            color: #aaa; /* Light gray text */
        }

        p a {
            color: #007bff; /* Blue link */
            text-decoration: none;
        }

        p a:hover {
            text-decoration: underline; /* Underline on hover */
        }

        .error-message {
            text-align: center;
            color: #dc3545; /* Red error message */
            margin-top: 20px;
        }

        .home-button {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .home-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>User Registration</h2>
    <?php
    if (!empty($errors)) {
        echo "<p class='error-message'>";
        foreach ($errors as $error) {
            echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "<br>";
        }
        echo "</p>";
    }
    ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" autocomplete="off" required><br>
        <label for="firstName">First Name:</label>
        <input type="text" id="firstName" name="firstName" autocomplete="off" required><br>
        <label for="lastName">Last Name:</label>
        <input type="text" id="lastName" name="lastName" autocomplete="off" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" autocomplete="new-password" required><br>

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <input type="submit" value="Register">
    </form>

    <p>Already have an account? <a href="login.php">Login here</a>.</p>

    <!-- Home Page Button -->
    <a href="homePage.php">
        <button class="home-button">Go to Home Page</button>
    </a>
</body>
</html>

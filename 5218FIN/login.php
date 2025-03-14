<?php
// Secure session settings
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,  // Change to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

require 'database_connection.php';

$error = ""; 

// CSRF Token Check
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check for CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token.";
    } else {
        // Get input data and sanitize
        $username = trim($_POST["username"]);
        $password = $_POST["password"];

        // Validate input
        if (empty($username) || empty($password)) {
            $error = "Please fill in all fields.";
        } else {
            // Prepared statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT username, password FROM USERS WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($db_username, $db_password);
                $stmt->fetch();

                // Verify password
                if (password_verify($password, $db_password)) {
                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    // Store username in session
                    $_SESSION["username"] = $db_username;

                    // Redirect after successful login
                    header("Location: homePage.php");
                    exit();
                } else {
                    $error = "Invalid credentials. Please try again."; //Generic error message
                }
            } else {
                $error = "Invalid credentials. Please try again."; //Generic error message
            }
            $stmt->close();
        }
    }
}

// Session timeout 
$timeout_duration = 900; // 15 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();     // Unset session variables
    session_destroy();   // Destroy session
    header('Location: login.php'); // Redirect to login page
    exit();
}
$_SESSION['last_activity'] = time(); // Update last activity time

// Generate CSRF Token for the form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #222;
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        h2 {
            color: #007bff;
            margin-bottom: 20px;
        }

        form {
            width: 300px;
            padding: 20px;
            background-color: #333;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        label {
            color: #fff;
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
            background-color: #555;
            color: #fff;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        p {
            text-align: center;
            color: #aaa;
        }

        p a {
            color: #007bff;
            text-decoration: none;
        }

        p a:hover {
            text-decoration: underline;
        }

        .error-message {
            text-align: center;
            color: #dc3545;
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
    <h2>User Login</h2>
    <?php
    if (!empty($error)) {
        echo "<p class='error-message'>" . htmlspecialchars($error) . "</p>";
    }
    ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required autocomplete="off"><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required autocomplete="new-password"><br>
        
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <input type="submit" value="Login">
    </form>
    <p>Don't have an account? <a href="reg.php">Register here</a>.</p>

    <a href="homePage.php"><button class="home-button">Go to Home Page</button></a>
</body>
</html>

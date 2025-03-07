<?php
// Start session
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
                    $_SESSION["username"] = $db_username; // Store username in session
                    echo "<script>
                            alert('Login successful! Redirecting to Home Page...');
                            window.location.href = 'homePage.php';
                          </script>";
                    exit();
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "User not found.";
            }
            $stmt->close();
        }
    }
}

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
    <h2>User Login</h2>
    <?php
    // Display error message if any
    if (!empty($error)) {
        echo "<p class='error-message'>$error</p>";
    }
    ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <input type="submit" value="Login">
    </form>
    <p>Don't have an account? <a href="reg.php">Register here</a>.</p>

    <!-- Home Page Button -->
    <a href="homePage.php">
        <button class="home-button">Go to Home Page</button>
    </a>
</body>
</html>

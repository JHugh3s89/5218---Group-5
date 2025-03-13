<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Session timeout (e.g., 30 minutes)
$timeout_duration = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$_SESSION['last_activity'] = time();

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"];
$first_name = $last_name = $new_password = $current_password = "";
$success_message = $error_message = "";

// Fetch current user data from the database
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $stmt = $conn->prepare("SELECT first_name, last_name FROM USERS WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($first_name, $last_name);
    $stmt->fetch();
    $stmt->close();
}

// CSRF Token Handling
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //echo "Session Token: " . $_SESSION['csrf_token'] . "<br>";
    //echo "Posted Token: " . ($_POST['csrf_token'] ?? 'N/A') . "<br>";

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $first_name = trim($_POST["first_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $new_password = $_POST["new_password"] ?? "";
    $current_password = $_POST["current_password"] ?? "";

    if (empty($first_name) || empty($last_name)) {
        $error_message = "First name and last name cannot be empty.";
    } else {
        if (!empty($new_password)) {
            if (empty($current_password)) {
                $error_message = "You must provide your current password to update your details.";
            } else {
                $stmt = $conn->prepare("SELECT password FROM USERS WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->bind_result($db_password);
                $stmt->fetch();
                $stmt->close();

                if (password_verify($current_password, $db_password)) {
                    echo "Password verified successfully.<br>";

                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE USERS SET first_name = ?, last_name = ?, password = ? WHERE username = ?");
                    $stmt->bind_param("ssss", $first_name, $last_name, $hashed_password, $username);

                    if (!$stmt->execute()) {
                        echo "Error updating details: " . $stmt->error . "<br>";
                    } else {
                        $success_message = "Account details updated successfully!";
                    }

                    $stmt->close();
                } else {
                    $error_message = "Current password is incorrect.";
                }
            }
        } else {
            // If no password change, only update the name fields
            $stmt = $conn->prepare("UPDATE USERS SET first_name = ?, last_name = ? WHERE username = ?");
            $stmt->bind_param("sss", $first_name, $last_name, $username);

            if (!$stmt->execute()) {
                echo "Error updating details: " . $stmt->error . "<br>";
            } else {
                $success_message = "Account details updated successfully!";
            }

            $stmt->close();
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Page</title>
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

        h1 {
            color: #007bff;
        }

        form {
            width: 300px;
            padding: 20px;
            background-color: #333;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #fff;
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

        .message {
            text-align: center;
            color: #28a745;
            margin-top: 20px;
        }

        .error-message {
            text-align: center;
            color: #dc3545;
            margin-top: 20px;
        }

        .nav-buttons {
            margin-top: 20px;
        }

        .nav-buttons button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
        }

        .nav-buttons button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Account Page</h1>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!empty($success_message)) {
            echo "<p class='message'>$success_message</p>";
        }

        if (!empty($error_message)) {
            echo "<p class='error-message'>$error_message</p>";
        }
    }
    ?>

    <form action="account.php" method="post">

        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required><br>

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required><br>

        <label for="current_password">Current Password (required to update details):</label>
        <input type="password" id="current_password" name="current_password" required><br>

        <label for="new_password">New Password (Leave blank if not changing):</label>
        <input type="password" id="new_password" name="new_password"><br>

        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <input type="submit" value="Update Account">
    </form>

    <div class="nav-buttons">
        <button onclick="window.location.href='homePage.php'">Main Menu</button>
    </div>

</body>
</html>

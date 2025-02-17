<?php
// Start session
session_start();
require 'database_connection.php';

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}


$username = $_SESSION["username"];
$first_name = $last_name = $new_password = $current_password = $new_username = "";


if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $stmt = $conn->prepare("SELECT first_name, last_name FROM USERS WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($first_name, $last_name);
    $stmt->fetch();
    $stmt->close();
}


// Handle form submission for updating user details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = isset($_POST["new_username"]) ? trim($_POST["new_username"]) : "";
    $first_name = isset($_POST["first_name"]) ? trim($_POST["first_name"]) : "";
    $last_name = isset($_POST["last_name"]) ? trim($_POST["last_name"]) : "";
    $new_password = isset($_POST["new_password"]) ? $_POST["new_password"] : "";
    $current_password = isset($_POST["current_password"]) ? $_POST["current_password"] : "";

    // Validate and update the details
    if (empty($first_name) || empty($last_name)) {
        $error_message = "First name and last name cannot be empty.";
    } else {
        // Check if the user provided a current password for password change or other details
        if (!empty($new_password) || !empty($new_username)) {
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
                    if (!empty($new_password)) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                        // Update the password in the database
                        $stmt = $conn->prepare("UPDATE USERS SET first_name = ?, last_name = ?, password = ? WHERE username = ?");
                        $stmt->bind_param("ssss", $first_name, $last_name, $hashed_password, $username);
                        $stmt->execute();
                        $stmt->close();

                        $success_message = "Account updated successfully!";
                    } else {
                        // Just update name if password wasn't changed
                        $stmt = $conn->prepare("UPDATE USERS SET first_name = ?, last_name = ? WHERE username = ?");
                        $stmt->bind_param("sss", $first_name, $last_name, $username);
                        $stmt->execute();
                        $stmt->close();

                        $success_message = "Account details updated successfully!";
                    }

                    if (!empty($new_username) && $new_username !== $username) {
                        // Check if the new username is already taken
                        $stmt = $conn->prepare("SELECT username FROM USERS WHERE username = ?");
                        $stmt->bind_param("s", $new_username);
                        $stmt->execute();
                        $stmt->store_result();

                        if ($stmt->num_rows > 0) {
                            $error_message = "The username '$new_username' is already taken.";
                        } else {
                            // Update the username in the database
                            $stmt = $conn->prepare("UPDATE USERS SET username = ? WHERE username = ?");
                            $stmt->bind_param("ss", $new_username, $username);
                            $stmt->execute();
                            $stmt->close();

                            // Update session username
                            $_SESSION["username"] = $new_username;

                            $success_message = "Username updated successfully!";
                        }
                        $stmt->close();
                    } elseif ($new_username === $username) {
                    }
                } else {
                    $error_message = "Current password is incorrect.";
                }
            }
        }
    }
}
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
            color: #28a745; /* Green for success */
            margin-top: 20px;
        }

        .error-message {
            text-align: center;
            color: #dc3545; /* Red for error */
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Account Page</h1>

    <!-- Display success or error messages -->
    <?php
    if (isset($success_message)) {
        echo "<p class='message'>$success_message</p>";
    }

    if (isset($error_message)) {
        echo "<p class='error-message'>$error_message</p>";
    }
    ?>

    <!-- Account update form -->
    <form action="account.php" method="post">
        <!-- New Username Field -->
        <label for="new_username">New Username:</label>
        <input type="text" id="new_username" name="new_username" value="<?php echo htmlspecialchars($new_username ?: $username); ?>"><br>

        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required><br>

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required><br>

        <label for="current_password">Current Password (required to update details):</label>
        <input type="password" id="current_password" name="current_password" value=""><br>

        <label for="new_password">New Password (Leave blank if not changing):</label>
        <input type="password" id="new_password" name="new_password"><br>

        <input type="submit" value="Update Account">
    </form>
</body>
</html>

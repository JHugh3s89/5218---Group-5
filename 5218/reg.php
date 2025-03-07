<?php
session_start();
require 'database_connection.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $firstName = trim($_POST["firstName"]);
    $lastName = trim($_POST["lastName"]);
    $password = $_POST["password"];

    // Password strength check (example, at least 8 characters, one uppercase, one digit, and one special character)
    if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/\d/", $password) || !preg_match("/[\W_]/", $password)) {
        $errors[] = "Password must be at least 8 characters long, contain one uppercase letter, one digit, and one special character.";
    }

    // Check if fields are empty
    if (empty($username) || empty($firstName) || empty($lastName) || empty($password)) {
        $errors[] = "All fields are required.";
    }

    // Check if username already exists in the database
    $check_user = $conn->prepare("SELECT username FROM USERS WHERE username = ?");
    $check_user->bind_param("s", $username);
    $check_user->execute();
    $check_user->store_result();

    if ($check_user->num_rows > 0) {
        $errors[] = "Username already taken.";
    }
    $check_user->close();

    // If no errors, proceed to insert the user into the database
    if (empty($errors)) {
        // Hash password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO USERS (username, first_name, last_name, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $firstName, $lastName, $hashed_password);

        if ($stmt->execute()) {
            $_SESSION["username"] = $username; // Store username for this session
        
            // Display a popup and redirect
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
    // Display error message if any
    if (!empty($errors)) {
        echo "<p class='error-message'>";
        foreach ($errors as $error) {
            echo htmlspecialchars($error) . "<br>";
        }
        echo "</p>";
    }
    ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>
        <label for="firstName">First Name:</label>
        <input type="text" id="firstName" name="firstName" required><br>
        <label for="lastName">Last Name:</label>
        <input type="text" id="lastName" name="lastName" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        <input type="submit" value="Register">
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>

    <!-- Home Page Button -->
    <a href="homePage.php">
        <button class="home-button">Go to Home Page</button>
    </a>
</body>
</html>

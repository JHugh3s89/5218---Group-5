<?php
session_start(); 

$userAccountsFile = 'user_accounts.json';

function loadUserAccounts($filename) {
    return file_exists($filename) ? json_decode(file_get_contents($filename), true) : [];
}

// Save user accounts to JSON file
function saveUserAccounts($filename, $accounts) {
    file_put_contents($filename, json_encode($accounts, JSON_PRETTY_PRINT));
}

// Registration
if (isset($_POST['register'])) {
    // Load existing accounts
    $accounts = loadUserAccounts($userAccountsFile);
    
    // Addnew account 
    $accounts[] = [
        'username' => trim($_POST['username']),
        'password' => trim($_POST['password']), 
        'firstName' => trim($_POST['firstName']),
        'lastName' => trim($_POST['lastName']),
        'email' => trim($_POST['email']),
    ];
    
    // Save accounts
    saveUserAccounts($userAccountsFile, $accounts);
    $_SESSION['loggedin'] = true; // Indicate that user is logged in
    $_SESSION['username'] = trim($_POST['username']); // Store username 
    header('Location: account.php'); // Redirect to the same page or to a profile page if you have one
    exit();
}

// Login
if (isset($_POST['login'])) {
    $accounts = loadUserAccounts($userAccountsFile);
    foreach ($accounts as $account) {
        if ($account['username'] === trim($_POST['username']) && $account['password'] === trim($_POST['password'])) {
            $_SESSION['loggedin'] = true; // Show  users  logged in
            $_SESSION['username'] = $account['username']; // Store username 
            header('Location: account.php'); // Redirect to the same page or to a profile page
            exit();
        }
    }
    $loginError = 'Invalid username or password.';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management</title>
    <link rel="stylesheet" href="style.css"> 
    <style>
        .form-container {
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        form {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 10px;
            width: 300px; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: none;
            background-color: #5cb85c;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <!-- Header -->
        <div class="logo">
        </div>
        <nav class="navigation">
            <a href="homePage.php"><button>Home</button></a>
        </nav>
    </div>

    <div class="form-container">
        <!-- Registration Form -->
        <form method="post" action="account.php">
            <h2>Register</h2>
            <input type="text" name="username" placeholder="Username" required>
            <input type="text" name="firstName" placeholder="First Name" required>
            <input type="text" name="lastName" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Register</button>
        </form>

        <!-- Login Form -->
        <form method="post" action="account.php">
            <h2>Login</h2>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
    </div>

</body>
</html>

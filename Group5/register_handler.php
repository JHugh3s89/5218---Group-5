<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usersFile = 'users.json';
    
    // Get user data from the form
    $username = $_POST['username'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password']; 

    // Load existing users
    $usersData = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

    // Check if the username or email already exists
    foreach ($usersData as $user) {
        if ($user['username'] === $username || $user['email'] === $email) {
            exit('Username or Email already exists.');
        }
    }

    // Add the new user
    $usersData[] = [
        'username' => $username,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $email,
        'password' => $password 
    ];

    // Save user data to file
    file_put_contents($usersFile, json_encode($usersData));

    // Redirect to account page
    header('Location: homePage.php'); 
    exit();
}
?>

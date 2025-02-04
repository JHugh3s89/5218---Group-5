<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <div class="header">
       
    </div>
    <div class="main-content">
        <h1>Register</h1>
        <form action="register_handler.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" required>
            
            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <input type="submit" value="Register">
        </form>
    </div>
</body>
</html>

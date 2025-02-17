<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
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

        .header {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .logo {
            width: 50px;
            height: 50px;
            background-color: #007bff;
            border-radius: 50%;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .main-content {
            text-align: center;
        }

        .title {
            font-size: 32px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo"></div>
        <button class="account" onclick="goToAccount()">Account</button>
        <button class="login" onclick="goToLogin()">Login</button>
        <button class="register" onclick="goToRegister()">Register</button>
    </div>
    <div class="main-content">
        <h1 class="title">Shopping Site</h1> 
    </div>

    <script>
        function goToAccount() {
            window.location.href = 'account.php';
        }

        function goToLogin() {
            window.location.href = 'login.php';
        }

        function goToRegister() {
            window.location.href = 'reg.php';
        }
    </script>
</body>
</html>

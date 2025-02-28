<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Site - Home</title>
    <link rel="stylesheet" href="style.css"> 
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
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
            background-color: #333;
            position: absolute;
            top: 0;
        }

        .logo {
            width: 50px;
            height: 50px;
            background-color: #007bff;
            border-radius: 50%;
        }

        .nav-buttons {
            display: flex;
            gap: 10px;
        }

        button {
            padding: 10px 15px;
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

        .search-bar {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-bar input {
            padding: 10px;
            border-radius: 5px;
            border: none;
            width: 200px;
        }

        .main-content {
            text-align: center;
            margin-top: 80px;
        }

        .title {
            font-size: 32px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <!-- Header with Logo, Navigation Buttons, and Search Bar -->
    <div class="header">
        <div class="logo"></div>
        <form class="search-bar" action="search.php" method="POST">
            <input type="text" name="query" placeholder="Search products..." required>
            <button type="submit">Search</button>
        </form>
        <div class="nav-buttons">
            <button onclick="goToAccount()">Account</button>
            <button onclick="goToLogin()">Login</button>
            <button onclick="goToRegister()">Register</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="title">Welcome to Shopping Site</h1> 
    </div>

    <!-- JavaScript Navigation Functions -->
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

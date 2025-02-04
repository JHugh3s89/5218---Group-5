<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <div class="header">
        <div class="logo"></div>
        <button class="account" onclick="goToAccount()">Account</button>
    </div>
    <div class="main-content">
        <h1 class="title">Shopping Site</h1> 
    </div>

    <script>
        function goToAccount() {
            window.location.href = 'account.php';
        }
    </script>
</body>
</html>

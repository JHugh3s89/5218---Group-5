<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Page</title>
    <link rel="stylesheet" href="style.css"> 
   
</head>
<body>
    <div class="header">
        <div class="logo"></div>
        <form class="search-bar" action="search.php" method="POST">
            <input type="text" name="query" placeholder="Search products..." required>
            <button type="submit">Search</button>
        </form>
        <button class="account" onclick="goToAccount()">Account</button>
    </div>

    <script>
        function goToAccount() {
            window.location.href = 'account.php';
        }
    </script>
</body>
</html>

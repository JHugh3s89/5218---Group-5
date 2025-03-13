<?php
session_start();

// Secure session settings
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,  // Change to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Include the database connection file
require_once 'database_connection.php'; 

// Check for products in the database
$sql = "SELECT product_id, product_name, description, price, image_url FROM PRODUCTS";
$result = $conn->query($sql);

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

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
        }

        /* Header */
        .header {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
            background-color: #333;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
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

        /* Search Bar */
        .search-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: #444;
            padding: 5px;
            border-radius: 8px;
        }

        .search-bar input {
            padding: 10px;
            border-radius: 5px;
            border: none;
            width: 200px;
            font-size: 16px;
            background-color: #fff;
            color: #000;
        }

        .search-bar button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
        }

        .search-bar button:hover {
            background-color: #0056b3;
        }

        /* Main Content */
        .main-content {
            text-align: center;
            margin-top: 80px;
            padding: 20px;
        }

        .title {
            font-size: 32px;
            margin-bottom: 20px;
        }

        /* Product Display Section */
        .products-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 80px;
            padding: 20px;
        }

        .product-card {
            background-color: #333;
            border-radius: 10px;
            padding: 15px;
            width: 250px;
            text-align: center;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }

        .product-card h3 {
            font-size: 20px;
            margin: 10px 0;
        }

        .product-card .price {
            font-size: 18px;
            font-weight: bold;
            color: #4caf50;
            margin: 10px 0;
        }

        .view-product-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #28a745;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            margin-top: 10px;
        }

        .view-product-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <!-- Header with Logo, Navigation Buttons, and Search Bar -->
    <div class="header">
        <div class="logo"></div>

        <!-- Secure Search Bar with CSRF Token -->
<form class="search-bar" action="search.php" method="POST">
    <input type="text" name="query" placeholder="Search products..." required>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <button type="submit">Search</button>
</form>

 

        <div class="nav-buttons">
            <?php if (isset($_SESSION['username'])): ?>
                <button onclick="goToAccount()">Account</button>
                <button onclick="goToLogout()">Logout</button>
            <?php else: ?>
                <button onclick="goToLogin()">Login</button>
                <button onclick="goToRegister()">Register</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="title">Welcome to Tech Review Site</h1>
        <p>Check out our latest products below!</p>
    </div>

    <!-- Product Display Section -->
    <div class="products-container">
    <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        ?>
        <div class="product-card">
            <img src="<?php echo htmlspecialchars($row["image_url"]); ?>" alt="Product Image">
            <h3><?php echo htmlspecialchars($row["product_name"]); ?></h3>
            <p><?php echo htmlspecialchars($row["description"]); ?></p>
            <div class="price">$<?php echo number_format($row["price"], 2); ?></div>

            <!-- Secure POST Form for Viewing Product -->
            <form action="product_page.php" method="POST">
                <input type="hidden" name="id" value="<?php echo base64_encode($row["product_id"]); ?>">
                <button type="submit" class="view-product-btn">View Product</button>
            </form>
        </div>
        <?php
    }
} else {
    echo "<p>No products available.</p>";
}
?>

    </div>

    <!-- JavaScript Navigation Functions -->
    <script>
        function goToLogin() {
            window.location.href = 'login.php';
        }

        function goToRegister() {
            window.location.href = 'reg.php';
        }

        function goToLogout() {
            const confirmLogout = confirm("Are you sure you want to log out?");
            if (confirmLogout) {
                window.location.href = 'logout.php';
            }
        }

        function goToAccount() {
            window.location.href = 'account.php';
        }
    </script>
</body>
</html>

<?php
$conn->close(); // Close database connection
?>


<?php
$conn->close(); // Close database connection
?>

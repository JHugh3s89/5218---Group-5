<?php

// Secure session settings
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,  // Change to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();
require 'database_connection.php';

// Ensure the request is POST 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// Decode the product ID securely
if (isset($_POST['id']) && !empty($_POST['id'])) {
    $decoded_id = base64_decode($_POST['id'], true); // `true` ensures valid base64

if ($decoded_id === false || !is_numeric($decoded_id)) {
    die("Invalid product ID. Possible data corruption.");
}


    // Fetch product details from the database
    $product_query = $conn->prepare("SELECT * FROM PRODUCTS WHERE product_id = ?");
    $product_query->bind_param("i", $decoded_id);
    $product_query->execute();
    $product_result = $product_query->get_result();
    $product = $product_result->fetch_assoc();

    if (!$product) {
        echo "Product not found or unauthorized access.";
        exit();
    }

    // Fetch reviews for the product
    $reviews_query = $conn->prepare("SELECT * FROM REVIEWS WHERE product_id = ?");
    $reviews_query->bind_param("i", $decoded_id);
    $reviews_query->execute();
    $reviews_result = $reviews_query->get_result();

    // Check if the user has already submitted a review for this product
    $user_review_query = $conn->prepare("SELECT * FROM REVIEWS WHERE product_id = ? AND username = ?");
    $user_review_query->bind_param("is", $decoded_id, $_SESSION['username']);
    $user_review_query->execute();
    $user_review_result = $user_review_query->get_result();
    $user_review = $user_review_result->fetch_assoc();
} else {
    die("Invalid request.");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - Product Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        nav {
            width: 100%;
            background-color: #333;
            padding: 10px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        nav ul {
            list-style-type: none;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            display: inline;
            margin: 0 20px;
        }

        nav ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 18px;
        }

        nav ul li a:hover {
            text-decoration: underline;
        }

        /* Product container styling */
        .product-container {
            width: 80%;
            max-width: 900px;
            margin: 20px 0;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        .product-container img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        /* Reviews section */
        .reviews-section {
            width: 80%;
            max-width: 900px;
            margin: 20px 0;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .review {
            background-color: #f9f9f9;
            margin: 10px 0;
            padding: 10px;
            border-radius: 8px;
        }

        .review img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        /* Review form styling */
        .review-form {
            margin-top: 20px;
        }

        .review-form label {
            display: block;
            margin-bottom: 5px;
        }

        .review-form input,
        .review-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .review-form input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .review-form input[type="submit"]:hover {
            background-color: #0056b3;
        }

        /* Footer styling */
        footer {
            width: 100%;
            text-align: center;
            padding: 20px;
            background-color: #333;
            color: #fff;
        }

        /* Error message styling */
        .error-message {
            color: red;
        }

        /* Button Styling */
        .btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        /* Warning box */
        .warning-box {
            background-color: #ffcc00;
            color: #000;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        /* Response message */
        #response-message {
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav>
        <ul>
            <li><a href="homePage.php">Main Menu</a></li>
            <?php if (isset($_SESSION['username'])): ?>
                <li><a href="account.php">Account</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="reg.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="product-container">
        <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>
        <p>Price: $<?php echo number_format($product['price'], 2); ?></p>
        <img src="<?php echo htmlspecialchars($product['image_url']) ? $product['image_url'] : 'product_image_placeholder.jpg'; ?>" alt="Product Image">
        <p>Description: <?php echo htmlspecialchars($product['description']); ?></p>
    </div>

    <div class="reviews-section">
        <h3>Customer Reviews</h3>
        
        <?php
        if ($reviews_result->num_rows > 0) {
            while ($review = $reviews_result->fetch_assoc()) {
                echo "<div class='review'>";
                echo "<p><strong>" . htmlspecialchars($review['username']) . "</strong> - Rating: " . $review['rating'] . "/5</p>";
                echo "<p>" . htmlspecialchars($review['review_text']) . "</p>";
                if (!empty($review['image_data'])) {
                    echo "<img src='data:image/jpeg;base64," . base64_encode($review['image_data']) . "' alt='Review Image'>";
                }
                echo "</div>";
            }
        } else {
            echo "<p>No reviews yet. Be the first to review this product!</p>";
        }
        ?>

        <!-- Review Form (Visible to logged-in users) -->
        <?php if (isset($_SESSION['username'])): ?>
            <div class="review-form">
                <h4>Submit Your Review</h4>

                <?php if ($user_review): ?>
                    <div class="warning-box">
                        <p><strong>Warning:</strong> You already submitted a review for this product, If you choose to add another both are displayed.</p>
                    </div>
                <?php endif; ?>

                <form id="review-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?php echo $decoded_id; ?>">
                    <label for="rating">Rating (1-5):</label>
                    <input type="number" id="rating" name="rating" min="1" max="5" required><br>

                    <label for="review_text">Review:</label>
                    <textarea id="review_text" name="review_text" required></textarea><br>

                    <!-- File Upload for Review Image -->
                    <label for="file">Upload Image (Optional):</label>
                    <input type="file" id="file" name="file" accept="image/*"><br>

                    <input type="submit" value="Submit Review">
                </form>

                <div id="response-message"></div>
            </div>
        <?php else: ?>
            <p>You must be logged in to submit a review. <a href="login.php">Login here</a>.</p>
        <?php endif; ?>

    </div>

    <footer>
        <p>&copy; 2025 Your Shop. All rights reserved.</p>
    </footer>

    <script>
        document.getElementById('review-form').addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent the form from submitting normally

            var formData = new FormData(this);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'submit_review.php', true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('response-message').innerHTML = xhr.responseText;
                    // Optionally reset the form or display a success message
                    document.getElementById('review-form').reset();
                }
            };
            xhr.send(formData);
        });
    </script>

</body>
</html>

<?php
$conn->close(); // Close the connection after the page is rendered
?>

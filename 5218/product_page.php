<?php
session_start();
require 'database_connection.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details from the database
$product_query = $conn->prepare("SELECT * FROM PRODUCTS WHERE product_id = ?");
$product_query->bind_param("i", $product_id);
$product_query->execute();
$product_result = $product_query->get_result();
$product = $product_result->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit();
}

// Fetch reviews for the product
$reviews_query = $conn->prepare("SELECT * FROM REVIEWS WHERE product_id = ?");
$reviews_query->bind_param("i", $product_id);
$reviews_query->execute();
$reviews_result = $reviews_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - Product Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Navbar -->
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="product-container">
        <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>
        <p>Price: $<?php echo number_format($product['price'], 2); ?></p>
        <img src="product_image_placeholder.jpg" alt="Product Image" class="product-image">
        <p>Description: This is a placeholder for the product description.</p>
    </div>

    <div class="reviews-section">
        <h3>Customer Reviews</h3>
        
        <?php
        if ($reviews_result->num_rows > 0) {
            while ($review = $reviews_result->fetch_assoc()) {
                echo "<div class='review'>";
                echo "<p><strong>" . htmlspecialchars($review['username']) . "</strong> - Rating: " . $review['rating'] . "/5</p>";
                echo "<p>" . htmlspecialchars($review['review_text']) . "</p>";
                
                // Display review image if available
                if (!empty($review['image_path'])) {
                    echo "<p><img src='" . htmlspecialchars($review['image_path']) . "' alt='Review Image' class='review-image'></p>";
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
                <form action="submit_review.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    
                    <label for="rating">Rating (1-5):</label>
                    <input type="number" id="rating" name="rating" min="1" max="5" required><br>

                    <label for="review_text">Review:</label>
                    <textarea id="review_text" name="review_text" required></textarea><br>

                    <!-- File Upload for Review Image -->
                    <label for="file">Upload Image (Optional):</label>
                    <input type="file" id="file" name="file" accept="image/*"><br>

                    <input type="submit" value="Submit Review">
                </form>
            </div>
        <?php else: ?>
            <p class="error-message">You must be logged in to submit a review. <a href="login.php">Login here</a>.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 Your Shop. All rights reserved.</p>
    </footer>

</body>
</html>


<?php
session_start();
require 'database_connection.php';

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $search_term = trim($_GET['query']);

    // Prepare SQL query to find matching products
    $search_query = $conn->prepare("SELECT product_id, product_name FROM PRODUCTS WHERE product_name LIKE ?");
    $like_term = "%" . $search_term . "%";
    $search_query->bind_param("s", $like_term);
    $search_query->execute();
    $result = $search_query->get_result();

    // If only one product found, redirect to its page
    if ($result->num_rows == 1) {
        $product = $result->fetch_assoc();
        header("Location: product_page.php?id=" . $product['product_id']);
        exit();
    }

    // If multiple products are found, display results
    if ($result->num_rows > 1) {
        echo "<h1>Search Results for: " . htmlspecialchars($search_term) . "</h1>";
        echo "<ul>";
        while ($product = $result->fetch_assoc()) {
            echo "<li><a href='product_page.php?id=" . $product['product_id'] . "'>" . htmlspecialchars($product['product_name']) . "</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No products found for: " . htmlspecialchars($search_term) . "</p>";
    }
} else {
    echo "<p>Please enter a search term.</p>";
}
?>

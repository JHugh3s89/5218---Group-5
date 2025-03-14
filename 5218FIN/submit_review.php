<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'database_connection.php';

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo "You must be logged in to submit a review.";
    exit();
}

// Collect form data
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$review_text = isset($_POST['review_text']) ? $_POST['review_text'] : '';
$image_data = null;



// Check if the product_id exists in the PRODUCTS table
$checkProductQuery = "SELECT COUNT(*) FROM PRODUCTS WHERE product_id = ?";
$stmt = $conn->prepare($checkProductQuery);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->bind_result($product_exists);
$stmt->fetch();
$stmt->close();

// If product does not exist, show an error
if ($product_exists == 0) {
    echo "Invalid product ID. Review cannot be submitted.";
    exit();
}

// Handle file upload (image)
if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    // If there's a file uploaded, get the binary image data
    echo "File uploaded: " . $_FILES['file']['name']; // check the file name
    $image_data = file_get_contents($_FILES['file']['tmp_name']);
} else {
    echo "No image uploaded."; //  if no file is uploaded
}


// Insert a new review
$insert_query = "INSERT INTO REVIEWS (username, product_id, rating, review_text, image_data) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);
if ($stmt === false) {
    die('Error preparing the insert query: ' . $conn->error);
}

// Bind parameters
$stmt->bind_param("siiss", $_SESSION['username'], $product_id, $rating, $review_text, $image_data);

// Bind the BLOB separately if image data exists
if ($image_data !== null) {
    $stmt->send_long_data(4, $image_data); // handling for binary data
}

// Execute the insert query
if ($stmt->execute()) {
    echo "Your review has been submitted successfully!";
} else {
    echo "Error submitting your review: " . $stmt->error;
}
?>

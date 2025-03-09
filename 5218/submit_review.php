<?php
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

// Handle file upload (image)
if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    // If there's a file uploaded, get the binary image data
    $image_data = file_get_contents($_FILES['file']['tmp_name']);
} else {
    // If no file is uploaded, set image_data to null
    $image_data = null;
}

// Insert a new review (no check for existing review needed anymore)
$insert_query = "INSERT INTO REVIEWS (username, product_id, rating, review_text, image_data) VALUES (?, ?, ?, ?, ?)";

// Prepare and bind the insert query
$stmt = $conn->prepare($insert_query);
if ($stmt === false) {
    die('Error preparing the insert query: ' . $conn->error);
}

// Bind parameters
// BINDING 'image_data' as 'b' (blob) so MySQL can handle the LONGBLOB properly
$stmt->bind_param("siiss", $_SESSION['username'], $product_id, $rating, $review_text, $image_data);

// Execute the insert query
if ($stmt->execute()) {
    echo "Your review has been submitted successfully!";
} else {
    echo "Error submitting your review: " . $stmt->error;
}
?>

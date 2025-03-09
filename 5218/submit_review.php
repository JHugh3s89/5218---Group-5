<?php
session_start();
require 'database_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "<p>You must be logged in to submit a review. <a href='login.php'>Login here</a></p>";
    exit();
}

// Get the product ID and review details from the form
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

// Validate the review details
if ($product_id <= 0 || $rating < 1 || $rating > 5 || empty($review_text)) {
    echo "<p>Invalid input. Please make sure all fields are filled out correctly.</p>";
    exit();
}

// Handle file upload (if any)
$image_path = null;
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/review_images/';
    $file_name = basename($_FILES['file']['name']);
    $target_file = $upload_dir . uniqid() . '_' . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the file is an image (you can add more checks for specific image types if needed)
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_type, $allowed_types)) {
        echo "<p>Only image files are allowed (JPG, JPEG, PNG, GIF).</p>";
        exit();
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        $image_path = $target_file; // Save the image path to insert into the database
    } else {
        echo "<p>Error uploading the file. Please try again.</p>";
        exit();
    }
}

// Check if the user has already reviewed this product (limit to one review per user per product)
$check_review_query = $conn->prepare("SELECT * FROM REVIEWS WHERE username = ? AND product_id = ?");
$check_review_query->bind_param("si", $_SESSION['username'], $product_id);
$check_review_query->execute();
$check_review_result = $check_review_query->get_result();

if ($check_review_result->num_rows > 0) {
    echo "<p>You have already submitted a review for this product.</p>";
    exit();
}

// Insert the review into the database
$insert_review_query = $conn->prepare("INSERT INTO REVIEWS (username, product_id, rating, review_text, image_path) VALUES (?, ?, ?, ?, ?)");
$insert_review_query->bind_param("siiss", $_SESSION['username'], $product_id, $rating, $review_text, $image_path);

if ($insert_review_query->execute()) {
    echo "<p>Thank you for your review! It has been submitted successfully.</p>";
    echo "<p><a href='product.php?id=$product_id'>Go back to the product page</a></p>";
} else {
    echo "<p>There was an error submitting your review. Please try again later.</p>";
}

$insert_review_query->close();
?>

<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $review = htmlspecialchars($_POST['review']);
    $imagePath = null;

    // Handle file upload if an image is provided
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $imageName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $imageName;
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Validate file type (Only allow JPG, JPEG, PNG, GIF)
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            die("Only JPG, JPEG, PNG, and GIF files are allowed.");
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $imagePath = $imageName; // Store only the file name in the database
        } else {
            die("Error uploading the image.");
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO reviews (name, review, image) VALUES (:name, :review, :image)");
    $stmt->execute(['name' => $name, 'review' => $review, 'image' => $imagePath]);

    // Redirect to prevent form resubmission
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .review-form { max-width: 500px; margin: auto; }
        .review-list { max-width: 600px; margin: auto; }
        .review { border: 1px solid #ddd; padding: 15px; margin-top: 15px; border-radius: 5px; }
        .review img { max-width: 100%; height: auto; margin-top: 10px; }
    </style>
</head>
<body>

<h2>Submit a Review</h2>
<form class="review-form" action="index.php" method="POST" enctype="multipart/form-data">
    <label>Name:</label>
    <input type="text" name="name" required><br><br>

    <label>Review:</label>
    <textarea name="review" required></textarea><br><br>

    <label>Upload Image (optional):</label>
    <input type="file" name="image" accept="image/*"><br><br>

    <button type="submit">Submit Review</button>
</form>

<h2>Customer Reviews</h2>
<div class="review-list">
    <?php
    // Fetch and display reviews
    $stmt = $conn->query("SELECT * FROM reviews ORDER BY created_at DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<div class='review'>";
        echo "<strong>" . htmlspecialchars($row['name']) . "</strong> <br>";
        echo "<p>" . nl2br(htmlspecialchars($row['review'])) . "</p>";
        if ($row['image']) {
            echo "<img src='uploads/" . htmlspecialchars($row['image']) . "' alt='Review Image'>";
        }
        echo "<small>Posted on " . $row['created_at'] . "</small>";
        echo "</div>";
    }
    ?>
</div>

</body>
</html>

?php
// Start the session to store reviews
session_start();

// Initialize reviews array if it doesn't exist
if (!isset($_SESSION['reviews'])) {
    $_SESSION['reviews'] = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $review = htmlspecialchars(trim($_POST['review']));
    $rating = htmlspecialchars(trim($_POST['rating']));

    // Add the new review to the session
    $_SESSION['reviews'][] = [
        'name' => $name,
        'review' => $review,
        'rating' => $rating
    ];
}

// Fetch existing reviews
$reviews = $_SESSION['reviews'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Page</title>
    <link rel="stylesheet" href="styles.css">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self';">
    <style>
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            margin-bottom: 10px;
        }
        .rating input {
            display: none;
        }
        .rating label {
            cursor: pointer;
            font-size: 24px;
            color: lightgray;
        }
        .rating input:checked ~ label {
            color: gold;
        }
        .rating label:hover,
        .rating label:hover ~ label {
            color: gold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reviews</h1>
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Your Name" required>
            <div class="rating">
                <input type="radio" id="star5" name="rating" value="1" required>
                <label for="star5">★</label>
                <input type="radio" id="star4" name="rating" value="2">
                <label for="star4">★</label>
                <input type="radio" id="star3" name="rating" value="3">
                <label for="star3">★</label>
                <input type="radio" id="star2" name="rating" value="4">
                <label for="star2">★</label>
                <input type="radio" id="star1" name="rating" value="5">
                <label for="star1">★</label>
            </div>
            <textarea name="review" placeholder="Your Review" required></textarea>
            <button type="submit">Submit Review</button>
        </form>
        <div id="reviewsContainer">
            <h2>All Reviews</h2>
            <div id="reviews">
                <?php foreach ($reviews as $r): ?>
                    <p><strong><?php echo htmlspecialchars($r['name']); ?></strong> (Rating: <?php echo htmlspecialchars($r['rating']); ?>): <?php echo htmlspecialchars($r['review']); ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>

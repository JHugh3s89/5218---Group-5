<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Reviews</h1>
        <form id="reviewForm">
            <input type="text" id="name" placeholder="Your Name" required>
            <textarea id="review" placeholder="Your Review" required></textarea>
            <button type="submit">Submit Review</button>
        </form>
        <div id="reviewsContainer">
            <h2>All Reviews</h2>
            <div id="reviews"></div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
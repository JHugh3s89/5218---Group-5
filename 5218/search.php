<?php
session_start();
require 'database_connection.php';

// Use POST over GET for CSRF and IDOR vulnerabilities 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method. Please use the search form.");
}

// Rate limiting to prevent too many searches
if (!isset($_SESSION['search_attempts'])) {
    $_SESSION['search_attempts'] = 0;
    $_SESSION['search_reset_time'] = time() + 60; // Reset every 60 seconds
}
// Reset attempts after timeout
if (time() > $_SESSION['search_reset_time']) {
    $_SESSION['search_attempts'] = 0;
    $_SESSION['search_reset_time'] = time() + 60;
}
// Allow only 5 searches per minute
if ($_SESSION['search_attempts'] >= 5) {
    die("Too many search attempts, wait a bit before searching again x");
}
$_SESSION['search_attempts']++;


// Validate and sanitize user input
if (isset($_POST['query']) && !empty(trim($_POST['query']))) {
    $search_term = trim($_POST['query']);
// Restrict input length
if (strlen($search_term) > 50) {
    die("Search query is too long.");
}

// Block SQL wildcards to prevent search manipulation
if (preg_match('/[%_]/', $search_term)) {
    die("Invalid search characters used.");
}


    // Check CSRF token before procesing the request
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    // Prevent XSS by encoding output before displaying it
    $safe_search_term = htmlspecialchars($search_term, ENT_QUOTES, 'UTF-8');

    // Use a prepared statement to prevent SQL Injection
    $search_query = $conn->prepare("SELECT product_id, product_name FROM PRODUCTS WHERE product_name LIKE ?");
    $like_term = "%" . $search_term . "%";
    $search_query->bind_param("s", $like_term);
    $search_query->execute();
    $result = $search_query->get_result();

    // If only one product is found, auto-submit a form using POST
    if ($result->num_rows == 1) {
        $product = $result->fetch_assoc();
        $encoded_id = base64_encode($product['product_id']); // Encode ID
        ?>
        <form id="redirectForm" action="product_page.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($encoded_id, ENT_QUOTES, 'UTF-8'); ?>">
        </form>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById("redirectForm").submit(); // Auto-submit form via POST
            });
        </script>
        <?php
        exit();
    }

    // If multiple products are found, display results securely
    echo "<h1>Search Results for: " . $safe_search_term . "</h1>";
    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($product = $result->fetch_assoc()) {
            $safe_id = base64_encode($product['product_id']); // Encode ID before passing
            echo "<li>
                    <form action='product_page.php' method='POST'>
                        <input type='hidden' name='id' value='" . htmlspecialchars($safe_id, ENT_QUOTES, 'UTF-8') . "'>
                        <button type='submit'>" . htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') . "</button>
                    </form>
                  </li>";
        }
        echo "</ul>";
    } else {
        // Show a message if no products are found
        echo "<p>No products found for: " . $safe_search_term . "</p>";
    }
} else {
    // Display a message if the search term is empty
    echo "<p>Enter a search term por favor.</p>";
}
?>

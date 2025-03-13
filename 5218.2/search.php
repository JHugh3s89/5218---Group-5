<?php
session_start();
require 'database_connection.php';

// Enforce POST requests to prevent CSRF attacks
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method. Please use the search form.");
}

// CSRF Protection
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die("Invalid CSRF token.");
}

// Rate Limiting: Limit to 5 searches per minute
if (!isset($_SESSION['search_attempts'])) {
    $_SESSION['search_attempts'] = 0;
    $_SESSION['last_search_time'] = time(); // Set time when first search happens
}
if (time() - $_SESSION['last_search_time'] > 60) {
    $_SESSION['search_attempts'] = 0;
    $_SESSION['last_search_time'] = time(); 
}
if (!isset($_SESSION['search_attempts'])) {
    $_SESSION['search_attempts'] = 0;
}
if ($_SESSION['search_attempts'] >= 5) {
    die("Too many search attempts, wait a bit before searching again.");
}
$_SESSION['search_attempts']++;

// Validate and sanitize user input
if (isset($_POST['query']) && !empty(trim($_POST['query']))) {
    $search_term = trim($_POST['query']);

    // DoS protection: restrict search input length
    if (strlen($search_term) > 50) {
        die("Search query is too long.");
    }

    // Block SQL wildcard characters to prevent database scraping
    if (preg_match('/[%_]/', $search_term)) {
        die("Invalid search characters used.");
    }

    // Encode search term for output safety (prevent XSS)
    $safe_search_term = htmlspecialchars($search_term, ENT_QUOTES, 'UTF-8');

    // Use prepared statements to prevent SQL Injection
    $search_query = $conn->prepare("SELECT product_id, product_name FROM PRODUCTS WHERE product_name LIKE ?");
    $like_term = "%" . $search_term . "%";
    $search_query->bind_param("s", $like_term);
    $search_query->execute();
    $result = $search_query->get_result();

    // If exactly one result is found, redirect user automatically
    if ($result->num_rows == 1) {
        $product = $result->fetch_assoc();
        $encoded_id = base64_encode($product['product_id']);
        ?>

        <form id="redirectForm" action="product_page.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($encoded_id, ENT_QUOTES, 'UTF-8'); ?>">
        </form>
        <script>
            // Auto-submit the form to redirect user
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById("redirectForm").submit();
            });
        </script>
        <?php
        exit();
    }

    // Display multiple search results
    echo "<h1>Search Results for: " . $safe_search_term . "</h1>";
    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($product = $result->fetch_assoc()) {
            $safe_id = base64_encode($product['product_id']);

            echo "<li>
                    <form action='product_page.php' method='POST'>
                        <input type='hidden' name='id' value='" . htmlspecialchars($safe_id, ENT_QUOTES, 'UTF-8') . "'>
                        <button type='submit'>" . htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') . "</button>
                    </form>
                  </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Looks like we don’t have: " . $safe_search_term . "</p>";
    }
} else {
    echo "<p>Enter a search por favor.</p>";
}
?>

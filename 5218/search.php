<?php
session_start();
require 'database_connection.php';

// Enforce POST requests to prevent CSRF amd IDOR
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method. Please use the search form.");
}

// CSRF Protection: prevent unauthorized searches
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die("Invalid CSRF token.");
}

// Rate Limiting: prevent too many searches for anti Brute Force and DoS 
if (!isset($_SESSION['search_attempts'])) {
    $_SESSION['search_attempts'] = 0;
    $_SESSION['search_reset_time'] = time() + 60; // Reset every 60 seconds
}

// Reset search attempts after timeout
if (time() > $_SESSION['search_reset_time']) {
    $_SESSION['search_attempts'] = 0;
    $_SESSION['search_reset_time'] = time() + 60;
}

// Allow only 5 searches per min
if ($_SESSION['search_attempts'] >= 5) {
    die("Too many search attempts, wait a bit before searching again.");
}
$_SESSION['search_attempts']++;

// Validate and Sanitize user input
if (isset($_POST['query']) && !empty(trim($_POST['query']))) {
    $search_term = trim($_POST['query']);

    // Input Validation 
    if (strlen($search_term) > 50) {  // Restrict really long queries for DoS Prevention
        die("Search query is too long.");
    }
    if (preg_match('/[%_]/', $search_term)) {  // Block SQL wildcard characters to prevent products being scraped from db
        die("Invalid search characters used.");
    }

    // XSS Prevention: encode user input before displaying
    $safe_search_term = htmlspecialchars($search_term, ENT_QUOTES, 'UTF-8');

    // SQL Injection Protection: use prepared statements
    $search_query = $conn->prepare("SELECT product_id, product_name FROM PRODUCTS WHERE product_name LIKE ?");
    $like_term = "%" . $search_term . "%";
    $search_query->bind_param("s", $like_term);
    $search_query->execute();
    $result = $search_query->get_result();

    // IDOR Protection: encode product IDs before passing
    if ($result->num_rows == 1) {
        $product = $result->fetch_assoc();
        $encoded_id = base64_encode($product['product_id']); // Encode ID before passing
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

    // Display search results securely
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
        echo "<p>Looks like we dont sell: " . $safe_search_term . "</p>";
    }
} else {
    // Display a message if the search is empty
    echo "<p>Enter a search por favor.</p>";
}
?>


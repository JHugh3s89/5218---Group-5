<?php
session_start();
require 'database_connection.php';

// Enforce POST requests to prevent CSRF and IDOR
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method. Please use the search form.");
}

// CSRF Protection: no unath form submissions 
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die("Invalid CSRF token.");
}

// Rate Limiting: can only have 5 searches per min
if (!isset($_SESSION['search_attempts'])) {
    $_SESSION['search_attempts'] = 0;
    $_SESSION['search_reset_time'] = time() + 60; // Reset every 60 seconds
}
if ($_SESSION['search_attempts'] >= 5) {
    die("Too many search attempts, wait a bit before searching again.");
}
$_SESSION['search_attempts']++;

// Injection: validate and sanitize user input
if (isset($_POST['query']) && !empty(trim($_POST['query']))) {
    $search_term = trim($_POST['query']);

    // Dos: restrict search input length
    if (strlen($search_term) > 50) {
        die("Search query is too long.");
    }

    // SQL Injection: block SQL wildcard characters so no db scraping
    if (preg_match('/[%_]/', $search_term)) {
        die("Invalid search characters used.");
    }

    // XSS: encode user input before displaying
    $safe_search_term = htmlspecialchars($search_term, ENT_QUOTES, 'UTF-8');

    // SQL Injection: use prepared statements
    $search_query = $conn->prepare("SELECT product_id, product_name FROM PRODUCTS WHERE product_name LIKE ?");
    $like_term = "%" . $search_term . "%";
    $search_query->bind_param("s", $like_term);
    $search_query->execute();
    $result = $search_query->get_result();

    // IDOR: ensure prouct exists before getting ID 
    if ($result->num_rows == 1) {
        $product = $result->fetch_assoc();
        $product_id = $product['product_id'];

        // Sees if products in db
        $check_product_query = $conn->prepare("SELECT COUNT(*) FROM PRODUCTS WHERE product_id = ?");
        $check_product_query->bind_param("i", $product_id);
        $check_product_query->execute();
        $check_product_query->bind_result($product_exists);
        $check_product_query->fetch();
        $check_product_query->close();

        // Denies access if product doesnt exist 
        if ($product_exists === 0) {
            die("Invalid product. Access denied.");
        }

        // No url product ID tampering (if a product were to be sensistive)
        $encoded_id = base64_encode($product_id);

        ?>

        <form id="redirectForm" action="product_page.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($encoded_id, ENT_QUOTES, 'UTF-8'); ?>">
        </form>
        <script>
            // POST to redirect to product page 
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById("redirectForm").submit();
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
        echo "<p>Looks like we dont have: " . $safe_search_term . "</p>";
    }
} else {
    echo "<p>Enter a search por favor.</p>";
}
?>

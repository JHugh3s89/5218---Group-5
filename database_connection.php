<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "SHOPPINGWEB";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Run table creation and sample data insertion
$sql = "
CREATE TABLE IF NOT EXISTS USERS (
    username VARCHAR(30) PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS PRODUCTS (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS REVIEWS (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30),
    product_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    image_data LONGBLOB,
    FOREIGN KEY (username) REFERENCES USERS(username) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES PRODUCTS(product_id) ON DELETE CASCADE
);

INSERT INTO PRODUCTS (product_id, product_name, description, price, image_url) 
SELECT 1, 'Headphones', 'Wireless over-ear headphones with noise cancellation and deep bass.', 119.99, 
'http://www.bhphotovideo.com/images/images2500x2500/beats_by_dr_dre_900_00198_01_studio_wireless_headphones_matte_1016367.jpg'
WHERE NOT EXISTS (SELECT 1 FROM PRODUCTS WHERE product_id = 1);

INSERT INTO PRODUCTS (product_id, product_name, description, price, image_url) 
SELECT 2, 'Mouse', 'Ergonomic wireless mouse with customizable buttons and RGB lighting.', 49.99, 
'https://www.discoazul.com/uploads/media/images/raton-gaming-razer-basilisk-v2-1.png'
WHERE NOT EXISTS (SELECT 1 FROM PRODUCTS WHERE product_id = 2);

INSERT INTO PRODUCTS (product_id, product_name, description, price, image_url) 
SELECT 3, 'Mechanical Keyboard', 'RGB mechanical keyboard with blue switches for a tactile typing experience.', 149.99, 
'https://pisces.bbystatic.com/image2/BestBuy_US/images/products/6425/6425357cv13d.jpg'
WHERE NOT EXISTS (SELECT 1 FROM PRODUCTS WHERE product_id = 3);
";

// Process each query separately in multi_query()
if ($conn->multi_query($sql)) {
    do {
        // Clear each result set to avoid blocking the connection
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} else {
    die("Error creating tables or inserting data: " . $conn->error);
}
//echo "Database, tables, and sample data are ready!";
//$conn->close();
?>

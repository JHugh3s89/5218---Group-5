<?php
// Database configuration
$host = 'localhost'; // Change if your database is hosted elsewhere
$dbname = 'Shoppingweb'; // Replace with your database name
$username = 'root'; // Replace with your database username
$password = '@ButterDood123'; // Replace with your database password

// Create a connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products from the database
$sql = "SELECT product_id, product_name, price FROM PRODUCTS";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Page</title>
   <style>
    body {
    font-family: Arial, sans-serif;
    margin: 20px;
}

h1 {
    color: #333;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
}

th {
    background-color: #f2f2f2;
}
</style>
</head>
<body>
    <h1>Product List</h1>
    <table border="1">
        <tr>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Price</th>
        </tr>
        <?php
        // Check if there are results and display them
        if ($result->num_rows > 0) {
            // Output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row["product_id"] . "</td>
                        <td>" . $row["product_name"] . "</td>
                        <td>$" . number_format($row["price"], 2) . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No products found</td></tr>";
        }
        ?>
    </table>

    <?php
    // Close the database connection
    $conn->close();
    ?>
</body>
</html>


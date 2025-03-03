CREATE DATABASE IF NOT EXISTS SHOPPINGWEB;
USE SHOPPINGWEB;


CREATE TABLE USERS (
    username VARCHAR(30) PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE PRODUCTS (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL
);

CREATE TABLE REVIEWS (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30),
    product_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    FOREIGN KEY (username) REFERENCES USERS(username) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES PRODUCTS(product_id) ON DELETE CASCADE
);

INSERT INTO PRODUCTS (product_id, product_name, price) VALUES
(1, 'Headphones', 119.99),
(2, 'Mouse', 49.99),
(3, 'Mechanical Keyboard', 149.99);

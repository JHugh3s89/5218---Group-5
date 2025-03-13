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
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255)-- Storing image as binary data
);

CREATE TABLE REVIEWS (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30),
    product_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    image_data LONGBLOB,
    FOREIGN KEY (username) REFERENCES USERS(username) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES PRODUCTS(product_id) ON DELETE CASCADE
);

INSERT INTO PRODUCTS (product_id, product_name, description, price, image_url) VALUES
(1, 'Headphones', 'Wireless over-ear headphones with noise cancellation and deep bass.', 119.99, 'http://www.bhphotovideo.com/images/images2500x2500/beats_by_dr_dre_900_00198_01_studio_wireless_headphones_matte_1016367.jpg'),
(2, 'Mouse', 'Ergonomic wireless mouse with customizable buttons and RGB lighting.', 49.99, 'https://www.discoazul.com/uploads/media/images/raton-gaming-razer-basilisk-v2-1.png'),
(3, 'Mechanical Keyboard', 'RGB mechanical keyboard with blue switches for a tactile typing experience.', 149.99, 'https://pisces.bbystatic.com/image2/BestBuy_US/images/products/6425/6425357cv13d.jpg');


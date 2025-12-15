CREATE DATABASE db_footporium;
USE db_footporium;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255), -- Changed from LONGBLOB to VARCHAR for file path
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address_line TEXT NOT NULL,
    province VARCHAR(100),
    city VARCHAR(100),
    barangay VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Philippines',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL, 
    image_url VARCHAR(255), -- Changed from image_data LONGBLOB
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (address_id) REFERENCES addresses(id)
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL, 
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('cod', 'credit_card', 'gcash', 'maya') DEFAULT 'cod',
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO products (name, price, image_url, description) VALUES
('Human Foot', 50000.00, 'assets/img/human.png', 'A genuine human foot.'),
('Chicken Foot', 20.00, 'assets/img/chicken.png', 'Crispy and delicious chicken foot.'),
('Pig Foot', 100.00, 'assets/img/pig.png', 'Tender pig foot, perfect for stew.'),
('Prosthetic Foot', 100000.00, 'assets/img/prosthetic.png', 'High-quality prosthetic foot.'),
('Toasted Foot', 150000.00, 'assets/img/toasted.png', 'Premium toasted foot for collectors.'),
('Calamares', 200.00, 'assets/img/calamares.png', 'Fried squid rings, technically not a foot but close enough.'),
('Cow Foot', 175.00, 'assets/img/cow.png', 'Rich and gelatinous cow foot.'),
('Bigfoot Foot', 0.99, 'assets/img/bigfoot.png', 'The legendary foot itself. Surprisingly cheap.'),
('Duck Foot', 49.99, 'assets/img/duck.png', 'Webbed duck foot.'),
('Cat Foot', 9.11, 'assets/img/cat.png', 'Cute cat paw.');

INSERT INTO users (first_name, last_name, email, password, role) VALUES
('Admin', 'User', 'admin@footporium.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
 -- Password ng admin ay password
INSERT INTO reviews (product_id, user_id, rating, comment) VALUES
(1, 1, 5, 'Best foot I have ever bought!'),
(2, 1, 4, 'Tastes like chicken.'),
(4, 1, 5, 'Very sturdy and realistic.');

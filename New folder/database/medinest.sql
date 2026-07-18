
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    contact VARCHAR(30) NOT NULL,
    role ENUM('buyer','admin') NOT NULL DEFAULT 'buyer',
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) UNSIGNED NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    type VARCHAR(30) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    order_date DATETIME NOT NULL,
    total DECIMAL(10,2) UNSIGNED NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    PRIMARY KEY (id),
    KEY idx_orders_user (user_id),
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_log (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    activity_date DATETIME NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    activity VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_audit_date (activity_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO users (id, fullname, email, password, address, contact, role) VALUES
(1, 'MediNest Administrator', 'admin@medinest.test', '$2y$10$B3SLj9wEs7ype0QfKiJUrucBRXmjyVHklbVXE7bzJh7jQ5I4K02gi', 'MediNest Main Office', '00000000000', 'admin');

INSERT IGNORE INTO products (id, name, category, description, price, stock, type) VALUES
(1, 'Paracetamol 500 mg', 'Pain Relief', 'Trusted relief for fever and mild body pain.', 45.00, 80, 'tablet'),
(2, 'Vitamin C 500 mg', 'Vitamins', 'Daily immune-support supplement.', 120.00, 55, 'vitamin'),
(3, 'Digital Thermometer', 'Health Devices', 'Fast temperature checks at home.', 249.00, 18, 'device'),
(4, 'Alcohol 70% 250 mL', 'First Aid', 'Everyday antiseptic for external use.', 75.00, 34, 'firstaid');

ALTER TABLE users AUTO_INCREMENT = 1000;
ALTER TABLE products AUTO_INCREMENT = 1000;

-- Kundu Pharmacy - Medicine Delivery System
-- Database Schema

CREATE DATABASE IF NOT EXISTS kundu_pharmacy;
USE kundu_pharmacy;

-- Villages / Locations
CREATE TABLE villages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    village_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customers
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    village VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Medicines
CREATE TABLE medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_name VARCHAR(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    village VARCHAR(100),
    address TEXT,
    phone VARCHAR(20),
    prescription_image VARCHAR(255),
    manual_medicine TEXT,
    status ENUM('Pending','Confirmed','Out for Delivery','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order Items (selected medicines)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admins
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default admin (username: admin, password: admin123)
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$PR2FOhNEmQG6J1/7vUmQcO/XUoseN1f/ZDeLiTkudV9sHXRYDCVwq');

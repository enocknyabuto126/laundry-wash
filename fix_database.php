<?php
require_once 'config.php';

// Start transaction
$conn->begin_transaction();

try {
    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop existing tables
    $conn->query("DROP TABLE IF EXISTS order_items");
    $conn->query("DROP TABLE IF EXISTS orders");
    $conn->query("DROP TABLE IF EXISTS cart");
    
    // Create cart table
    $sql = "CREATE TABLE cart (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating cart table: " . $conn->error);
    }
    
    // Create orders table
    $sql = "CREATE TABLE orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        address TEXT NULL,
        phone VARCHAR(20) NULL,
        delivery_date DATE NULL,
        payment_method VARCHAR(50) NOT NULL,
        payment_status ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
        status ENUM('pending','processing','completed','delivered','cancelled') NOT NULL DEFAULT 'pending',
        total_price DECIMAL(10,2) NOT NULL,
        service_fee DECIMAL(10,2) NOT NULL DEFAULT 50.00,
        delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 100.00,
        grand_total DECIMAL(10,2) NOT NULL,
        expected_delivery DATE NULL,
        notes TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating orders table: " . $conn->error);
    }
    
    // Create order_items table
    $sql = "CREATE TABLE order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating order_items table: " . $conn->error);
    }
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    // Commit transaction
    $conn->commit();
    echo "Successfully recreated all required tables!<br>";
    echo "<a href='checkout.php'>Return to checkout</a>";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    // Re-enable foreign key checks even if there's an error
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    echo "Error: " . $e->getMessage();
} 
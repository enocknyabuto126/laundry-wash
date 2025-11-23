<?php
require_once 'config.php';

// SQL updates
$sql_updates = [
    "ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL AFTER address,
    ADD COLUMN IF NOT EXISTS delivery_date DATETIME NULL AFTER phone,
    ADD COLUMN IF NOT EXISTS notes TEXT NULL AFTER expected_delivery,
    ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending' AFTER status,
    ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(100) NULL AFTER payment_status,
    ADD COLUMN IF NOT EXISTS payment_date DATETIME NULL AFTER payment_reference",
    
    "ALTER TABLE order_items
    CHANGE COLUMN product_id service_id INT NOT NULL,
    DROP FOREIGN KEY IF EXISTS order_items_ibfk_2,
    ADD CONSTRAINT order_items_ibfk_2 FOREIGN KEY (service_id) REFERENCES products(id)"
];

// Execute each update
foreach ($sql_updates as $sql) {
    try {
        if ($conn->query($sql)) {
            echo "Successfully executed: " . substr($sql, 0, 50) . "...<br>";
        } else {
            echo "Error executing: " . substr($sql, 0, 50) . "... Error: " . $conn->error . "<br>";
        }
    } catch (Exception $e) {
        echo "Exception while executing: " . substr($sql, 0, 50) . "... Error: " . $e->getMessage() . "<br>";
    }
}

echo "<br>Database update completed. <a href='checkout.php'>Return to checkout</a>"; 
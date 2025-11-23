<?php
require_once 'config.php';

// Start transaction
$conn->begin_transaction();

try {
    // Get existing columns
    $result = $conn->query("SHOW COLUMNS FROM orders");
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    // Define columns to add
    $columns_to_add = [
        'phone' => "ADD COLUMN phone VARCHAR(20) NULL AFTER address",
        'delivery_date' => "ADD COLUMN delivery_date DATETIME NULL AFTER phone",
        'notes' => "ADD COLUMN notes TEXT NULL AFTER expected_delivery",
        'payment_status' => "ADD COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending' AFTER status",
        'payment_reference' => "ADD COLUMN payment_reference VARCHAR(100) NULL AFTER payment_status",
        'payment_date' => "ADD COLUMN payment_date DATETIME NULL AFTER payment_reference"
    ];
    
    // Add each missing column
    foreach ($columns_to_add as $column => $sql_part) {
        if (!in_array($column, $existing_columns)) {
            $sql = "ALTER TABLE orders " . $sql_part;
            if (!$conn->query($sql)) {
                throw new Exception("Error adding column {$column}: " . $conn->error);
            }
            echo "Added column: {$column}<br>";
        } else {
            echo "Column already exists: {$column}<br>";
        }
    }
    
    // Commit transaction
    $conn->commit();
    echo "<br>Successfully updated orders table structure!<br>";
    echo "<a href='checkout.php'>Return to checkout</a>";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
} 
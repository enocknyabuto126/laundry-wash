<?php
require_once 'config.php';

// Start transaction
$conn->begin_transaction();

try {
    // Update all existing orders with the new fee values
    $sql = "UPDATE orders SET 
            service_fee = 50.00,
            delivery_fee = 100.00,
            grand_total = total_price + 50.00 + 100.00
            WHERE service_fee != 50.00 OR delivery_fee != 100.00";
    
    if ($conn->query($sql)) {
        $affected_rows = $conn->affected_rows;
        echo "Successfully updated {$affected_rows} orders with new fee values:<br>";
        echo "- Service Fee: Ksh 50.00<br>";
        echo "- Delivery Fee: Ksh 100.00<br>";
        echo "- Grand Total: Updated to include new fees<br>";
    } else {
        throw new Exception("Error updating orders: " . $conn->error);
    }
    
    // Commit transaction
    $conn->commit();
    echo "<br>All orders have been updated successfully!<br>";
    echo "<a href='checkout.php'>Return to checkout</a>";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
} 
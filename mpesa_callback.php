<?php
require_once 'config.php';

// Log the callback data
$logFile = 'mpesa_callback.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Callback received\n" . file_get_contents('php://input') . "\n\n", FILE_APPEND);

// Get the callback data
$callbackData = json_decode(file_get_contents('php://input'), true);

if (!$callbackData) {
    http_response_code(400);
    exit('Invalid callback data');
}

// Extract the relevant data
$resultCode = $callbackData['Body']['stkCallback']['ResultCode'] ?? null;
$resultDesc = $callbackData['Body']['stkCallback']['ResultDesc'] ?? null;
$checkoutRequestId = $callbackData['Body']['stkCallback']['CheckoutRequestID'] ?? null;
$merchantRequestId = $callbackData['Body']['stkCallback']['MerchantRequestID'] ?? null;

// Get the order ID from the merchant request ID
$orderId = explode('_', $merchantRequestId)[1] ?? null;

if (!$orderId) {
    http_response_code(400);
    exit('Invalid order ID');
}

// Start transaction
$conn->begin_transaction();

try {
    if ($resultCode === 0) {
        // Payment successful
        $mpesaReceiptNumber = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'] ?? null;
        $amount = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'] ?? null;
        $transactionDate = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'] ?? null;
        $phoneNumber = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'] ?? null;
        
        // Update order status
        $sql = "UPDATE orders SET 
                status = 'processing',
                payment_status = 'paid',
                payment_method = 'mpesa',
                payment_reference = ?,
                payment_date = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $mpesaReceiptNumber, $transactionDate, $orderId);
        $stmt->execute();
        $stmt->close();
        
        // Add order status history
        $sql = "INSERT INTO order_status_history (order_id, status, note) VALUES (?, 'processing', 'Payment received via M-Pesa')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();
        
        // Get user ID for notification
        $sql = "SELECT user_id FROM orders WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $userId = $stmt->get_result()->fetch_assoc()['user_id'];
        $stmt->close();
        
        // Create notification
        $message = "Payment received for order #$orderId. Your order is now being processed.";
        $link = "order.php?id=$orderId";
        createNotification($userId, $message, $link);
        
        // Commit transaction
        $conn->commit();
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Payment processed successfully']);
    } else {
        // Payment failed
        $sql = "UPDATE orders SET 
                payment_status = 'failed',
                payment_method = 'mpesa'
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();
        
        // Get user ID for notification
        $sql = "SELECT user_id FROM orders WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $userId = $stmt->get_result()->fetch_assoc()['user_id'];
        $stmt->close();
        
        // Create notification
        $message = "Payment failed for order #$orderId. Please try again.";
        $link = "order.php?id=$orderId";
        createNotification($userId, $message, $link);
        
        // Commit transaction
        $conn->commit();
        
        http_response_code(200);
        echo json_encode(['success' => false, 'message' => 'Payment failed: ' . $resultDesc]);
    }
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log the error
    error_log("M-Pesa callback error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
} 
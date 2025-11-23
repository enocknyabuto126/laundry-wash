<?php
require_once 'config.php';
require_once 'includes/mpesa.php';

// Require login
requireLogin();

$user_id = getCurrentUserId();
$response = ['success' => false, 'message' => '', 'status' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $checkout_request_id = isset($_GET['checkout_request_id']) ? sanitize($_GET['checkout_request_id']) : '';
    
    if (!$checkout_request_id) {
        $response['message'] = 'Invalid checkout request ID';
        echo json_encode($response);
        exit;
    }
    
    // Get payment details
    $sql = "SELECT p.*, o.id as order_id, o.status as order_status 
            FROM payments p 
            JOIN orders o ON p.order_id = o.id 
            WHERE p.transaction_id = ? AND o.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $checkout_request_id, $user_id);
    $stmt->execute();
    $payment = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$payment) {
        $response['message'] = 'Payment not found';
        echo json_encode($response);
        exit;
    }
    
    // If payment is already completed or failed, return status
    if ($payment['status'] === 'completed') {
        $response['success'] = true;
        $response['status'] = 'completed';
        $response['message'] = 'Payment completed successfully';
        echo json_encode($response);
        exit;
    } elseif ($payment['status'] === 'failed') {
        $response['status'] = 'failed';
        $response['message'] = 'Payment failed';
        echo json_encode($response);
        exit;
    }
    
    // Check M-Pesa status
    try {
        $mpesa = new MpesaAPI(MPESA_ENV);
        $result = $mpesa->confirmTransaction($checkout_request_id);
        
        // Log the check
        $sql = "INSERT INTO payment_logs (payment_id, order_id, log_type, message, data) 
                VALUES (?, ?, 'status_check', 'Payment status checked', ?)";
        $stmt = $conn->prepare($sql);
        $log_data = json_encode($result);
        $stmt->bind_param("iis", $payment['id'], $payment['order_id'], $log_data);
        $stmt->execute();
        $stmt->close();
        
        if (isset($result['ResultCode'])) {
            if ($result['ResultCode'] === 0) {
                // Payment successful
                $response['success'] = true;
                $response['status'] = 'completed';
                $response['message'] = 'Payment completed successfully';
                
                // Update payment status
                $sql = "UPDATE payments SET 
                        status = 'completed',
                        payment_date = NOW()
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $payment['id']);
                $stmt->execute();
                $stmt->close();
                
                // Update order status
                $sql = "UPDATE orders SET 
                        status = 'processing',
                        payment_status = 'paid',
                        payment_date = NOW()
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $payment['order_id']);
                $stmt->execute();
                $stmt->close();
                
                // Add order status history
                $sql = "INSERT INTO order_status_history (order_id, status, note) 
                        VALUES (?, 'processing', 'Payment received via M-Pesa')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $payment['order_id']);
                $stmt->execute();
                $stmt->close();
                
                // Create notification
                $message = "Payment received for order #{$payment['order_id']}. Your order is now being processed.";
                $link = "order.php?id={$payment['order_id']}";
                createNotification($user_id, $message, $link);
            } else {
                // Payment failed
                $response['status'] = 'failed';
                $response['message'] = 'Payment failed: ' . ($result['ResultDesc'] ?? 'Unknown error');
                
                // Update payment status
                $sql = "UPDATE payments SET status = 'failed' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $payment['id']);
                $stmt->execute();
                $stmt->close();
                
                // Update order status
                $sql = "UPDATE orders SET payment_status = 'failed' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $payment['order_id']);
                $stmt->execute();
                $stmt->close();
                
                // Create notification
                $message = "Payment failed for order #{$payment['order_id']}. Please try again.";
                $link = "order.php?id={$payment['order_id']}";
                createNotification($user_id, $message, $link);
            }
        } else {
            // Still pending
            $response['status'] = 'pending';
            $response['message'] = 'Payment is still being processed';
        }
    } catch (Exception $e) {
        $response['message'] = 'Error checking payment status: ' . $e->getMessage();
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response); 
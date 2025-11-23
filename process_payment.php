<?php
require_once 'config.php';
require_once 'includes/mpesa.php';

// Require login
requireLogin();

$user_id = getCurrentUserId();
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $payment_method = isset($_POST['payment_method']) ? sanitize($_POST['payment_method']) : '';
    
    if (!$order_id || !$payment_method) {
        $response['message'] = 'Invalid request parameters';
        echo json_encode($response);
        exit;
    }
    
    // Get order details
    $sql = "SELECT * FROM orders WHERE id = ? AND user_id = ? AND payment_status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$order) {
        $response['message'] = 'Order not found or already paid';
        echo json_encode($response);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        switch ($payment_method) {
            case 'mpesa':
                // Get user's phone number
                $sql = "SELECT phone FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $phone = $stmt->get_result()->fetch_assoc()['phone'];
                $stmt->close();
                
                if (!$phone) {
                    throw new Exception('Phone number not found. Please update your profile.');
                }
                
                // Initialize M-Pesa API
                $mpesa = new MpesaAPI(MPESA_ENV);
                
                // Initiate STK Push
                $result = $mpesa->initiateSTKPush($phone, $order['grand_total'], $order_id);
                
                if (isset($result['CheckoutRequestID'])) {
                    // Create payment record
                    $sql = "INSERT INTO payments (order_id, amount, payment_method, transaction_id, status) 
                            VALUES (?, ?, 'mpesa', ?, 'pending')";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ids", $order_id, $order['grand_total'], $result['CheckoutRequestID']);
                    $stmt->execute();
                    $payment_id = $conn->insert_id;
                    $stmt->close();
                    
                    // Log payment initiation
                    $sql = "INSERT INTO payment_logs (payment_id, order_id, log_type, message, data) 
                            VALUES (?, ?, 'initiation', 'M-Pesa payment initiated', ?)";
                    $stmt = $conn->prepare($sql);
                    $log_data = json_encode($result);
                    $stmt->bind_param("iis", $payment_id, $order_id, $log_data);
                    $stmt->execute();
                    $stmt->close();
                    
                    $response['success'] = true;
                    $response['message'] = 'Please check your phone for the M-Pesa prompt';
                    $response['checkout_request_id'] = $result['CheckoutRequestID'];
                } else {
                    throw new Exception('Failed to initiate M-Pesa payment: ' . ($result['errorMessage'] ?? 'Unknown error'));
                }
                break;
                
            case 'card':
                // TODO: Implement card payment integration
                throw new Exception('Card payment not implemented yet');
                break;
                
            case 'bank':
                // TODO: Implement bank transfer integration
                throw new Exception('Bank transfer not implemented yet');
                break;
                
            case 'cod':
                // Update order for cash on delivery
                $sql = "UPDATE orders SET 
                        payment_method = 'cod',
                        payment_status = 'pending'
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                $stmt->close();
                
                // Create payment record
                $sql = "INSERT INTO payments (order_id, amount, payment_method, status) 
                        VALUES (?, ?, 'cod', 'pending')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("id", $order_id, $order['grand_total']);
                $stmt->execute();
                $stmt->close();
                
                $response['success'] = true;
                $response['message'] = 'Order placed successfully. Pay on delivery.';
                break;
                
            default:
                throw new Exception('Invalid payment method');
        }
        
        // Commit transaction
        $conn->commit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $response['message'] = $e->getMessage();
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response); 
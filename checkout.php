<?php
require_once 'config.php';

// Require login
requireLogin();

$user_id = getCurrentUserId();
$error = '';
$success = '';

// Get user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get cart items
$sql = "SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();
$stmt->close();

// Calculate totals
$subtotal = 0;
$service_fee = 50;
$delivery_fee = 100;
$item_count = 0;

// Check if cart is empty
if ($cart_items->num_rows === 0) {
    header('Location: cart.php');
    exit;
}

// Process items to get totals
$items_array = [];
while ($item = $cart_items->fetch_assoc()) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
    $item_count += $item['quantity'];
    $items_array[] = $item;
}

$grand_total = $subtotal + $service_fee + $delivery_fee;

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = isset($_POST['address']) ? sanitize($_POST['address']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $delivery_date = isset($_POST['delivery_date']) ? sanitize($_POST['delivery_date']) : '';
    $payment_method = isset($_POST['payment_method']) ? sanitize($_POST['payment_method']) : '';
    $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';
    
    // Validate required fields
    if (empty($address) || empty($phone) || empty($delivery_date) || empty($payment_method)) {
        $error = "All fields are required";
    } else {
        // Calculate expected delivery date (2 days after delivery date)
        $expected_delivery = date('Y-m-d', strtotime($delivery_date . ' +2 days'));
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $sql = "INSERT INTO orders (user_id, address, phone, delivery_date, expected_delivery, 
                    payment_method, payment_status, status, notes, total_price, service_fee, 
                    delivery_fee, grand_total, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssssdddd", 
                $user_id, 
                $address, 
                $phone, 
                $delivery_date, 
                $expected_delivery, 
                $payment_method,
                $notes,
                $subtotal,
                $service_fee,
                $delivery_fee,
                $grand_total
            );
            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();
            
            // Update user's phone number
            $sql = "UPDATE users SET phone = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $phone, $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Add order items
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                    SELECT ?, c.product_id, c.quantity, p.price 
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $order_id, $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Add order status history
            $sql = "INSERT INTO order_status_history (order_id, status, note) VALUES (?, 'pending', 'Order created')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $stmt->close();
            
            // Clear cart
            $sql = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Create notification
            $message = "Your order #{$order_id} has been placed successfully.";
            $link = "order.php?id={$order_id}";
            createNotification($user_id, $message, $link);
            
            // Handle payment based on method
            if ($payment_method === 'mpesa') {
                // Initialize M-Pesa
                $mpesa = new MpesaAPI(MPESA_ENV);
                
                // Initiate payment
                $result = $mpesa->initiateSTKPush($phone, $grand_total, $order_id);
                
                if (isset($result['CheckoutRequestID'])) {
                    // Create payment record
                    $sql = "INSERT INTO payments (order_id, amount, payment_method, status, transaction_id, created_at) 
                            VALUES (?, ?, 'mpesa', 'pending', ?, NOW())";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ids", $order_id, $grand_total, $result['CheckoutRequestID']);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Log payment initiation
                    $sql = "INSERT INTO payment_logs (payment_id, order_id, log_type, message, data) 
                            VALUES (LAST_INSERT_ID(), ?, 'initiation', 'M-Pesa payment initiated', ?)";
                    $stmt = $conn->prepare($sql);
                    $log_data = json_encode($result);
                    $stmt->bind_param("is", $order_id, $log_data);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Commit transaction
                    $conn->commit();
                    
                    // Redirect to payment status page
                    header("Location: payment_status.php?checkout_request_id=" . $result['CheckoutRequestID']);
                    exit;
                } else {
                    throw new Exception("Failed to initiate M-Pesa payment: " . ($result['errorMessage'] ?? 'Unknown error'));
                }
            } else {
                // For other payment methods, redirect to order confirmation
                $conn->commit();
                header("Location: order_confirmation.php?id=" . $order_id);
                exit;
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error processing order: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Wash & Fold Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .checkout-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }
        .payment-method-card {
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #dee2e6;
        }
        .payment-method-card.selected {
            border-color: #5D5CDE;
            background-color: rgba(93, 92, 222, 0.05);
        }
        .payment-method-card:hover {
            border-color: #5D5CDE;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <h1 class="mb-4">Checkout</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="row g-5">
                <div class="col-md-7 order-md-1">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="mb-0">Delivery Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" value="<?php echo $user['name']; ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" value="<?php echo $user['email']; ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number*</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone'] ?? ''; ?>" required>
                                <div class="form-text">We'll contact you on this number for delivery coordination</div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Delivery Address*</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                                <div class="form-text">Please provide complete address with building name, floor, landmark, etc.</div>
                            </div>
                            <div class="mb-3">
                                <label for="delivery_date" class="form-label">Preferred Delivery Date*</label>
                                <?php 
                                    // Set min date to 2 days from now (processing time)
                                    $min_date = date('Y-m-d', strtotime('+2 days'));
                                    // Set max date to 7 days from now
                                    $max_date = date('Y-m-d', strtotime('+7 days'));
                                ?>
                                <input type="date" class="form-control" id="delivery_date" name="delivery_date" 
                                       min="<?php echo $min_date; ?>" max="<?php echo $max_date; ?>" 
                                       value="<?php echo $min_date; ?>" required>
                                <div class="form-text">Standard processing time is 48 hours</div>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Special Instructions (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                                <div class="form-text">Any specific requirements for your laundry or delivery</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="mb-0">Payment Method</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Select your preferred payment method:</p>
                            
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" checked>
                                    <label class="form-check-label" for="cash">
                                        Cash on Delivery
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="mpesa" value="mpesa">
                                    <label class="form-check-label" for="mpesa">
                                        M-Pesa
                                    </label>
                                </div>
                            </div>
                            
                            <div id="mpesaDetails" class="mb-3" style="display: none;">
                                <div class="alert alert-info">
                                    <p class="mb-1">To pay with M-Pesa:</p>
                                    <ol class="mb-0">
                                        <li>Go to your M-Pesa menu</li>
                                        <li>Select "Pay Bill"</li>
                                        <li>Enter Business Number: <strong>123456</strong></li>
                                        <li>Enter Account Number: <strong>Your Phone Number</strong></li>
                                        <li>Enter Amount: <strong>Ksh <?php echo number_format($grand_total, 2); ?></strong></li>
                                        <li>Enter your M-Pesa PIN</li>
                                        <li>Confirm the payment</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-5 order-md-2">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="mb-0">Order Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h5>Items (<?php echo $item_count; ?>)</h5>
                                <hr>
                                <div class="items-container">
                                    <?php foreach ($items_array as $item): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['image']): ?>
                                                    <img src="uploaded_images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="checkout-item-image me-3">
                                                <?php else: ?>
                                                    <div class="bg-light me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                        <i class="bi bi-basket2-fill" style="font-size: 24px;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <p class="mb-0"><?php echo $item['name']; ?></p>
                                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <p class="mb-0">Ksh <?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                                <small class="text-muted">Ksh <?php echo number_format($item['price'], 2); ?> each</small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>Ksh <?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Service Fee:</span>
                                <span>Ksh <?php echo number_format($service_fee, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Delivery Fee:</span>
                                <span>Ksh <?php echo number_format($delivery_fee, 2); ?></span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-3 fw-bold">
                                <span>Grand Total:</span>
                                <span>Ksh <?php echo number_format($grand_total, 2); ?></span>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                </label>
                            </div>
                            
                            <button type="submit" name="place_order" class="btn btn-primary w-100 btn-lg">
                                Place Order
                            </button>
                            
                            <p class="text-center text-muted small mt-2">
                                You can track your order status in your account after placing the order
                            </p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5>Need Help?</h5>
                            <p class="text-muted mb-0">If you have any questions about checkout, please contact our customer support:</p>
                            <p class="mb-2"><i class="bi bi-telephone me-2"></i> (254) 123-456-789</p>
                            <p class="mb-0"><i class="bi bi-envelope me-2"></i> support@washandfold.co.ke</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Service Agreement</h6>
                    <p>By placing an order with Wash & Fold Laundry, you agree to these terms and conditions which constitute a binding agreement between you and our company.</p>
                    
                    <h6>2. Service Description</h6>
                    <p>We provide laundry washing, drying, folding, and delivery services as described on our website and order confirmation.</p>
                    
                    <h6>3. Pricing and Payment</h6>
                    <p>Prices are as listed at the time of order. Payment is due at the time specified based on your selected payment method. Late payments may incur additional fees.</p>
                    
                    <h6>4. Delivery</h6>
                    <p>We aim to deliver within the scheduled timeframe. Delays due to unforeseen circumstances may occur and we will notify you promptly.</p>
                    
                    <h6>5. Item Handling</h6>
                    <p>While we take utmost care, we are not responsible for damages that may occur during normal washing processes for items unsuitable for machine washing.</p>
                    
                    <h6>6. Lost Items</h6>
                    <p>Please report any missing items within 24 hours of delivery. Claims made after this period may not be honored.</p>
                    
                    <h6>7. Cancellation</h6>
                    <p>Orders can be cancelled free of charge if laundry processing has not yet begun. Cancellation fees may apply otherwise.</p>
                    
                    <h6>8. Privacy</h6>
                    <p>Your personal information will be handled according to our Privacy Policy.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mpesaRadio = document.getElementById('mpesa');
            const cashRadio = document.getElementById('cash');
            const mpesaDetails = document.getElementById('mpesaDetails');
            
            mpesaRadio.addEventListener('change', function() {
                mpesaDetails.style.display = this.checked ? 'block' : 'none';
            });
            
            cashRadio.addEventListener('change', function() {
                mpesaDetails.style.display = 'none';
            });
        });
    </script>
</body>
</html>
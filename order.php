<?php
require_once 'config.php';

// Require login
requireLogin();

$user_id = getCurrentUserId();
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if order exists and belongs to the user
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Order doesn't exist or doesn't belong to the user
    header('Location: orders.php');
    exit;
}

$order = $result->fetch_assoc();
$stmt->close();

// Get order items
$sql = "SELECT oi.*, p.name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();
$stmt->close();

// Get order status history
$sql = "SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$status_history = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> - Wash & Fold Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .tracking-progress {
            position: relative;
            max-width: 100%;
            margin: 0 auto;
        }
        
        .tracking-progress-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .tracking-progress-bar::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            height: 4px;
            width: 100%;
            background-color: #ddd;
            z-index: 1;
        }
        
        .tracking-progress-bar-fill {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            height: 4px;
            background-color: #5D5CDE;
            z-index: 2;
            transition: width 0.5s ease;
        }
        
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 3;
            position: relative;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .step.active {
            background-color: #5D5CDE;
            color: white;
        }
        
        .step-label {
            position: absolute;
            top: 40px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            font-size: 12px;
            width: 100px;
        }
        
        .laundry-status {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        
        .status-pending { background-color: #ffc107; }
        .status-processing { background-color: #0d6efd; }
        .status-completed { background-color: #198754; }
        .status-delivered { background-color: #20c997; }
        .status-cancelled { background-color: #dc3545; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="orders.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
            <h1>Order #<?php echo $order_id; ?> Details</h1>
            <div></div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Order Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Order Date:</strong> <?php echo formatDate($order['created_at']); ?></p>
                                <p><strong>Last Updated:</strong> <?php echo formatDate($order['updated_at']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p>
                                    <strong>Status:</strong> 
                                    <span class="badge bg-<?php 
                                        echo $order['status'] === 'pending' ? 'warning' : 
                                            ($order['status'] === 'processing' ? 'primary' : 
                                            ($order['status'] === 'completed' ? 'success' : 
                                            ($order['status'] === 'delivered' ? 'info' : 'danger'))); 
                                    ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </p>
                                <p><strong>Expected Delivery:</strong> <?php echo formatDate($order['expected_delivery']); ?></p>
                            </div>
                        </div>
                        
                        <h6 class="mb-3">Order Items</h6>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $order_items->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $item['name']; ?></td>
                                            <td>Ksh <?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>Ksh <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                        <td>Ksh <?php echo number_format($order['total_price'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Service Fee:</strong></td>
                                        <td>Ksh <?php echo number_format($order['service_fee'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Delivery Fee:</strong></td>
                                        <td>Ksh <?php echo number_format($order['delivery_fee'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                                        <td><strong>Ksh <?php echo number_format($order['grand_total'], 2); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h6 class="mb-3">Delivery Address</h6>
                        <p><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Order Tracking</h5>
                    </div>
                    <div class="card-body">
                        <div class="tracking-progress mb-4">
                            <div class="tracking-progress-bar">
                                <div class="tracking-progress-bar-fill" style="width: <?php 
                                    echo $order['status'] === 'pending' ? '0%' : 
                                        ($order['status'] === 'processing' ? '33%' : 
                                        ($order['status'] === 'completed' ? '67%' : 
                                        ($order['status'] === 'delivered' ? '100%' : '0%'))); 
                                ?>"></div>
                                <div class="step <?php echo $order['status'] !== 'cancelled' ? 'active' : ''; ?>">
                                    1
                                    <div class="step-label">Order Received</div>
                                </div>
                                <div class="step <?php echo in_array($order['status'], ['processing', 'completed', 'delivered']) ? 'active' : ''; ?>">
                                    2
                                    <div class="step-label">Processing</div>
                                </div>
                                <div class="step <?php echo in_array($order['status'], ['completed', 'delivered']) ? 'active' : ''; ?>">
                                    3
                                    <div class="step-label">Completed</div>
                                </div>
                                <div class="step <?php echo $order['status'] === 'delivered' ? 'active' : ''; ?>">
                                    4
                                    <div class="step-label">Delivered</div>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mb-3">Order Timeline</h6>
                        <div class="timeline">
                            <?php 
                            $history_array = array();
                            while ($history = $status_history->fetch_assoc()) {
                                $history_array[] = $history;
                            }
                            
                            for ($i = 0; $i < count($history_array); $i++) {
                                $history = $history_array[$i];
                                $is_last = ($i === count($history_array) - 1);
                            ?>
                                <div class="timeline-item pb-3 <?php echo !$is_last ? 'border-start border-2 border-primary ps-3 ms-2' : ''; ?>">
                                    <div class="d-flex">
                                        <div class="timeline-icon me-3">
                                            <span class="laundry-status status-<?php echo $history['status']; ?>"></span>
                                        </div>
                                        <div>
                                            <p class="mb-0 fw-bold"><?php echo ucfirst($history['status']); ?></p>
                                            <p class="mb-0"><?php echo $history['note']; ?></p>
                                            <small class="text-muted"><?php echo formatDate($history['created_at']); ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        
                        <?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
                            <div class="alert alert-info mt-3">
                                <p class="mb-0"><i class="bi bi-info-circle"></i> We'll notify you when your order status changes.</p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'delivered'): ?>
                            <div class="mt-3">
                                <a href="review.php?order_id=<?php echo $order_id; ?>" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-star"></i> Leave a Review
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Need Help?</h5>
                    </div>
                    <div class="card-body">
                        <p>If you have any questions or concerns about your order, please contact our customer support.</p>
                        <a href="contact.php" class="btn btn-outline-primary w-100">
                            <i class="bi bi-chat-dots"></i> Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
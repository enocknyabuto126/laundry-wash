<?php
require_once 'config.php';

// Require login
requireLogin();

$user_id = getCurrentUserId();

// Get user orders
$sql = "SELECT o.*, 
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count 
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Wash & Fold Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-md-3">
                <?php include 'includes/user_sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>My Orders</h1>
                    <a href="booking.php" class="btn btn-primary">
                        <i class="bi bi-plus"></i> New Order
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order History</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($orders->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($order = $orders->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo formatDate($order['created_at']); ?></td>
                                                <td><?php echo $order['item_count']; ?> item<?php echo $order['item_count'] != 1 ? 's' : ''; ?></td>
                                                <td>Ksh <?php echo number_format($order['grand_total'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $order['status'] === 'pending' ? 'warning' : 
                                                            ($order['status'] === 'processing' ? 'primary' : 
                                                            ($order['status'] === 'completed' ? 'success' : 
                                                            ($order['status'] === 'delivered' ? 'info' : 'danger'))); 
                                                    ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                    <?php if ($order['status'] === 'delivered'): ?>
                                                        <a href="reorder.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-success ms-1">
                                                            <i class="bi bi-arrow-repeat"></i> Reorder
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="mb-4">
                                    <i class="bi bi-bag" style="font-size: 64px;"></i>
                                </div>
                                <h5>You haven't placed any orders yet</h5>
                                <p>Once you place an order, it will appear here for you to track.</p>
                                <a href="booking.php" class="btn btn-primary">Start Ordering</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
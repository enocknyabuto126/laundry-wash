<?php
require_once 'config.php';

// Require login
requireLogin();

$user_id = getCurrentUserId();

// Get user's orders count by status
$sql = "SELECT status, COUNT(*) as count FROM orders WHERE user_id = ? GROUP BY status";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$status_counts = $stmt->get_result();
$stmt->close();

// Initialize counts
$pending_count = 0;
$processing_count = 0;
$completed_count = 0;
$delivered_count = 0;

// Process status counts
while ($row = $status_counts->fetch_assoc()) {
    switch ($row['status']) {
        case 'pending':
            $pending_count = $row['count'];
            break;
        case 'processing':
            $processing_count = $row['count'];
            break;
        case 'completed':
            $completed_count = $row['count'];
            break;
        case 'delivered':
            $delivered_count = $row['count'];
            break;
    }
}

// Get recent orders (limit to 5)
$sql = "SELECT o.*, 
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count 
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_orders = $stmt->get_result();
$stmt->close();

// Get most recent order
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$latest_order = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get user info
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Wash & Fold Laundry</title>
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
    </style>
</head>
<body>
    <?php include './includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-md-3">
                <?php include 'includes/user_sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <div class="py-4">
                    <h1>Welcome, <?php echo explode(' ', $user['name'])[0]; ?>!</h1>
                    <p class="lead">Track your laundry orders and manage your account.</p>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-3 mb-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-hourglass text-warning mb-3" style="font-size: 48px;"></i>
                                <h3><?php echo $pending_count; ?></h3>
                                <p>Pending Orders</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-activity text-primary mb-3" style="font-size: 48px;"></i>
                                <h3><?php echo $processing_count; ?></h3>
                                <p>Processing Orders</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-check-circle text-success mb-3" style="font-size: 48px;"></i>
                                <h3><?php echo $completed_count; ?></h3>
                                <p>Completed Orders</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-truck text-info mb-3" style="font-size: 48px;"></i>
                                <h3><?php echo $delivered_count; ?></h3>
                                <p>Delivered Orders</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Orders</h5>
                                <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if ($recent_orders->num_rows > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                    <th>Total</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                                    <tr>
                                                        <td>#<?php echo $order['id']; ?></td>
                                                        <td><?php echo formatDate($order['created_at']); ?></td>
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
                                                        <td>Ksh <?php echo number_format($order['grand_total'], 2); ?></td>
                                                        <td>
                                                            <a href="order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <p>You haven't placed any orders yet.</p>
                                        <a href="booking.php" class="btn btn-primary">Start Ordering</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Account Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Name:</strong> <?php echo $user['name']; ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Email:</strong> <?php echo $user['email']; ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Phone:</strong> <?php echo $user['phone']; ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Account Type:</strong> <?php echo ucfirst($user['role']); ?>
                                </div>
                                <a href="profile.php" class="btn btn-outline-primary w-100">Edit Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($latest_order): ?>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Track Your Latest Order</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h6 class="mb-0">Order #<?php echo $latest_order['id']; ?></h6>
                                            <small class="text-muted">Placed on <?php echo formatDate($latest_order['created_at']); ?></small>
                                        </div>
                                        <span class="badge bg-<?php 
                                            echo $latest_order['status'] === 'pending' ? 'warning' : 
                                                ($latest_order['status'] === 'processing' ? 'primary' : 
                                                ($latest_order['status'] === 'completed' ? 'success' : 
                                                ($latest_order['status'] === 'delivered' ? 'info' : 'danger'))); 
                                        ?> px-3 py-2">
                                            <?php echo ucfirst($latest_order['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="tracking-progress mt-4 mb-5">
                                        <div class="tracking-progress-bar">
                                            <div class="tracking-progress-bar-fill" style="width: <?php 
                                                echo $latest_order['status'] === 'pending' ? '0%' : 
                                                    ($latest_order['status'] === 'processing' ? '33%' : 
                                                    ($latest_order['status'] === 'completed' ? '67%' : 
                                                    ($latest_order['status'] === 'delivered' ? '100%' : '0%'))); 
                                            ?>"></div>
                                            <div class="step <?php echo $latest_order['status'] !== 'cancelled' ? 'active' : ''; ?>">
                                                1
                                                <div class="step-label">Order Received</div>
                                            </div>
                                            <div class="step <?php echo in_array($latest_order['status'], ['processing', 'completed', 'delivered']) ? 'active' : ''; ?>">
                                                2
                                                <div class="step-label">Processing</div>
                                            </div>
                                            <div class="step <?php echo in_array($latest_order['status'], ['completed', 'delivered']) ? 'active' : ''; ?>">
                                                3
                                                <div class="step-label">Completed</div>
                                            </div>
                                            <div class="step <?php echo $latest_order['status'] === 'delivered' ? 'active' : ''; ?>">
                                                4
                                                <div class="step-label">Delivered</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center">
                                        <p>Expected delivery: <strong><?php echo formatDate($latest_order['expected_delivery']); ?></strong></p>
                                        <a href="order.php?id=<?php echo $latest_order['id']; ?>" class="btn btn-primary">View Order Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="ht://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.jstps"></script>
</body>
</html>
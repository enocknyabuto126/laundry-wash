<?php
require_once '../config.php';

// Require admin access
requireAdmin();

// Get orders count by status
$sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$result = $conn->query($sql);

// Initialize counts
$pending_count = 0;
$processing_count = 0;
$completed_count = 0;
$delivered_count = 0;
$cancelled_count = 0;

// Process status counts
while ($row = $result->fetch_assoc()) {
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
        case 'cancelled':
            $cancelled_count = $row['count'];
            break;
    }
}

// Get total orders
$total_orders = $pending_count + $processing_count + $completed_count + $delivered_count + $cancelled_count;

// Get total sales
$sql = "SELECT SUM(grand_total) as total_sales FROM orders WHERE status != 'cancelled'";
$total_sales = $conn->query($sql)->fetch_assoc()['total_sales'] ?? 0;

// Get total users
$sql = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
$total_users = $conn->query($sql)->fetch_assoc()['total_users'];

// Get total products
$sql = "SELECT COUNT(*) as total_products FROM products";
$total_products = $conn->query($sql)->fetch_assoc()['total_products'];

// Get recent orders
$sql = "SELECT o.*, u.name as customer_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 10";
$recent_orders = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Wash & Fold Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/user_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                            <i class="bi bi-calendar"></i> This week
                        </button>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Orders</h5>
                                        <h2 class="mb-0"><?php echo $total_orders; ?></h2>
                                    </div>
                                    <div>
                                        <i class="bi bi-bag-check" style="font-size: 48px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Sales</h5>
                                        <h2 class="mb-0">Ksh <?php echo number_format($total_sales, 2); ?></h2>
                                    </div>
                                    <div>
                                        <i class="bi bi-cash-stack" style="font-size: 48px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Users</h5>
                                        <h2 class="mb-0"><?php echo $total_users; ?></h2>
                                    </div>
                                    <div>
                                        <i class="bi bi-people" style="font-size: 48px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Products</h5>
                                        <h2 class="mb-0"><?php echo $total_products; ?></h2>
                                    </div>
                                    <div>
                                        <i class="bi bi-box" style="font-size: 48px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Orders by Status</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="ordersChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Order Status</h5>
                                <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning p-3 me-3 rounded">
                                                <i class="bi bi-hourglass text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Pending</h6>
                                                <h4 class="mb-0"><?php echo $pending_count; ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary p-3 me-3 rounded">
                                                <i class="bi bi-activity text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Processing</h6>
                                                <h4 class="mb-0"><?php echo $processing_count; ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success p-3 me-3 rounded">
                                                <i class="bi bi-check-circle text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Completed</h6>
                                                <h4 class="mb-0"><?php echo $completed_count; ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info p-3 me-3 rounded">
                                                <i class="bi bi-truck text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Delivered</h6>
                                                <h4 class="mb-0"><?php echo $delivered_count; ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Orders</h5>
                                <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Customer</th>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                                <tr>
                                                    <td>#<?php echo $order['id']; ?></td>
                                                    <td><?php echo $order['customer_name']; ?></td>
                                                    <td><?php echo formatDate($order['created_at']); ?></td>
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
                                                        <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Orders by status chart
        const ctx = document.getElementById('ordersChart').getContext('2d');
        const ordersChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Processing', 'Completed', 'Delivered', 'Cancelled'],
                datasets: [{
                    data: [
                        <?php echo $pending_count; ?>, 
                        <?php echo $processing_count; ?>, 
                        <?php echo $completed_count; ?>, 
                        <?php echo $delivered_count; ?>, 
                        <?php echo $cancelled_count; ?>
                    ],
                    backgroundColor: [
                        '#ffc107', // warning
                        '#0d6efd', // primary
                        '#198754', // success
                        '#0dcaf0', // info
                        '#dc3545'  // danger
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</body>
</html>
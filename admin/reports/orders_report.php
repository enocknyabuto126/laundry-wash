<?php
require_once '../../config.php';

// Require admin access
requireAdmin();

// Get date range from request or default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get orders by status
$sql = "SELECT 
    status,
    COUNT(*) as order_count,
    SUM(grand_total) as total_amount,
    AVG(grand_total) as average_amount
FROM orders 
WHERE created_at BETWEEN ? AND ?
GROUP BY status";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$status_stats = $stmt->get_result();
$stmt->close();

// Get orders by delivery date
$sql = "SELECT 
    DATE(delivery_date) as date,
    COUNT(*) as order_count,
    SUM(grand_total) as total_amount
FROM orders 
WHERE delivery_date BETWEEN ? AND ?
AND status != 'cancelled'
GROUP BY DATE(delivery_date)
ORDER BY date";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$delivery_stats = $stmt->get_result();
$stmt->close();

// Get recent orders
$sql = "SELECT 
    o.*,
    u.name as customer_name,
    u.phone as customer_phone,
    (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
FROM orders o
JOIN users u ON o.user_id = u.id
WHERE o.created_at BETWEEN ? AND ?
ORDER BY o.created_at DESC
LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$recent_orders = $stmt->get_result();
$stmt->close();

// Get total summary
$sql = "SELECT 
    COUNT(*) as total_orders,
    COUNT(DISTINCT user_id) as unique_customers,
    AVG(grand_total) as average_order_value,
    MIN(grand_total) as min_order_value,
    MAX(grand_total) as max_order_value
FROM orders 
WHERE created_at BETWEEN ? AND ?
AND status != 'cancelled'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Report - Wash & Fold Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Orders Report</h1>
            <form class="d-flex gap-2">
                <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="index.php" class="btn btn-outline-secondary">Back to Reports</a>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Orders</h5>
                        <h2><?php echo number_format($summary['total_orders']); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Unique Customers</h5>
                        <h2><?php echo number_format($summary['unique_customers']); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Average Order Value</h5>
                        <h2>Ksh <?php echo number_format($summary['average_order_value'], 2); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order Value Range</h5>
                        <h2>Ksh <?php echo number_format($summary['min_order_value'], 2); ?> - <?php echo number_format($summary['max_order_value'], 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Orders by Status -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Orders by Status</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Orders by Delivery Date -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Orders by Delivery Date</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="deliveryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Delivery Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($order['customer_phone']); ?></small>
                                </td>
                                <td><?php echo $order['item_count']; ?> items</td>
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
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['delivery_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Status Chart
        const statusData = {
            labels: [<?php 
                $labels = [];
                $data = [];
                $status_stats->data_seek(0);
                while ($status = $status_stats->fetch_assoc()) {
                    $labels[] = "'" . ucfirst($status['status']) . "'";
                    $data[] = $status['order_count'];
                }
                echo implode(',', $labels);
            ?>],
            datasets: [{
                data: [<?php echo implode(',', $data); ?>],
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
            }]
        };

        new Chart(document.getElementById('statusChart'), {
            type: 'pie',
            data: statusData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Delivery Chart
        const deliveryData = {
            labels: [<?php 
                $labels = [];
                $data = [];
                $delivery_stats->data_seek(0);
                while ($delivery = $delivery_stats->fetch_assoc()) {
                    $labels[] = "'" . date('M d', strtotime($delivery['date'])) . "'";
                    $data[] = $delivery['order_count'];
                }
                echo implode(',', $labels);
            ?>],
            datasets: [{
                label: 'Orders',
                data: [<?php echo implode(',', $data); ?>],
                borderColor: '#36A2EB',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                fill: true
            }]
        };

        new Chart(document.getElementById('deliveryChart'), {
            type: 'line',
            data: deliveryData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
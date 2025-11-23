<?php
require_once '../../config.php';

// Require admin access
requireAdmin();

// Get date range from request or default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get daily sales data
$sql = "SELECT 
    DATE(created_at) as date,
    COUNT(*) as order_count,
    SUM(grand_total) as total_revenue,
    SUM(service_fee) as total_service_fee,
    SUM(delivery_fee) as total_delivery_fee
FROM orders 
WHERE created_at BETWEEN ? AND ? 
AND status != 'cancelled'
GROUP BY DATE(created_at)
ORDER BY date";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$daily_sales = $stmt->get_result();
$stmt->close();

// Get sales by payment method
$sql = "SELECT 
    payment_method,
    COUNT(*) as order_count,
    SUM(grand_total) as total_amount,
    AVG(grand_total) as average_amount
FROM orders 
WHERE created_at BETWEEN ? AND ? 
AND status != 'cancelled'
GROUP BY payment_method";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$payment_methods = $stmt->get_result();
$stmt->close();

// Get total summary
$sql = "SELECT 
    COUNT(*) as total_orders,
    SUM(grand_total) as total_revenue,
    SUM(service_fee) as total_service_fee,
    SUM(delivery_fee) as total_delivery_fee,
    AVG(grand_total) as average_order_value
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
    <title>Sales Report - Wash & Fold Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Sales Report</h1>
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
                        <h5 class="card-title">Total Revenue</h5>
                        <h2>Ksh <?php echo number_format($summary['total_revenue'], 2); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Service Fees</h5>
                        <h2>Ksh <?php echo number_format($summary['total_service_fee'], 2); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Delivery Fees</h5>
                        <h2>Ksh <?php echo number_format($summary['total_delivery_fee'], 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Daily Sales Chart -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Daily Sales</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailySalesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Methods</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Method</th>
                                        <th>Orders</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($method = $payment_methods->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($method['payment_method']); ?></td>
                                        <td><?php echo number_format($method['order_count']); ?></td>
                                        <td>Ksh <?php echo number_format($method['total_amount'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Sales Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daily Sales Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                                <th>Service Fees</th>
                                <th>Delivery Fees</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($day = $daily_sales->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                                <td><?php echo number_format($day['order_count']); ?></td>
                                <td>Ksh <?php echo number_format($day['total_revenue'], 2); ?></td>
                                <td>Ksh <?php echo number_format($day['total_service_fee'], 2); ?></td>
                                <td>Ksh <?php echo number_format($day['total_delivery_fee'], 2); ?></td>
                                <td>Ksh <?php echo number_format($day['total_revenue'] + $day['total_service_fee'] + $day['total_delivery_fee'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Daily Sales Chart
        const dailySalesData = {
            labels: [<?php 
                $labels = [];
                $revenue = [];
                $service_fee = [];
                $delivery_fee = [];
                $daily_sales->data_seek(0);
                while ($day = $daily_sales->fetch_assoc()) {
                    $labels[] = "'" . date('M d', strtotime($day['date'])) . "'";
                    $revenue[] = $day['total_revenue'];
                    $service_fee[] = $day['total_service_fee'];
                    $delivery_fee[] = $day['total_delivery_fee'];
                }
                echo implode(',', $labels);
            ?>],
            datasets: [{
                label: 'Revenue',
                data: [<?php echo implode(',', $revenue); ?>],
                borderColor: '#36A2EB',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                fill: true
            }, {
                label: 'Service Fees',
                data: [<?php echo implode(',', $service_fee); ?>],
                borderColor: '#FF6384',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                fill: true
            }, {
                label: 'Delivery Fees',
                data: [<?php echo implode(',', $delivery_fee); ?>],
                borderColor: '#FFCE56',
                backgroundColor: 'rgba(255, 206, 86, 0.1)',
                fill: true
            }]
        };

        new Chart(document.getElementById('dailySalesChart'), {
            type: 'line',
            data: dailySalesData,
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
                            callback: function(value) {
                                return 'Ksh ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
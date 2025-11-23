<?php
require_once '../../config.php';

// Require admin access
requireAdmin();

// Get date range from request or default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get summary statistics
$sql = "SELECT 
    COUNT(*) as total_orders,
    SUM(grand_total) as total_revenue,
    AVG(grand_total) as average_order_value,
    COUNT(DISTINCT user_id) as unique_customers
FROM orders 
WHERE created_at BETWEEN ? AND ? 
AND status != 'cancelled'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get sales by payment method
$sql = "SELECT 
    payment_method,
    COUNT(*) as order_count,
    SUM(grand_total) as total_amount
FROM orders 
WHERE created_at BETWEEN ? AND ? 
AND status != 'cancelled'
GROUP BY payment_method";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$payment_methods = $stmt->get_result();
$stmt->close();

// Get top selling products
$sql = "SELECT 
    p.name,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.quantity * oi.price) as total_revenue
FROM order_items oi
JOIN products p ON oi.product_id = p.id
JOIN orders o ON oi.order_id = o.id
WHERE o.created_at BETWEEN ? AND ?
AND o.status != 'cancelled'
GROUP BY p.id
ORDER BY total_quantity DESC
LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$top_products = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Wash & Fold Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Reports</h1>
            <div class="d-flex gap-2">
                <form class="d-flex gap-2">
                    <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                    <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                    <button type="submit" class="btn btn-primary">Apply</button>
                </form>
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="export.php?type=sales&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">Export Sales Report</a></li>
                        <li><a class="dropdown-item" href="export.php?type=orders&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">Export Orders Report</a></li>
                        <li><a class="dropdown-item" href="export.php?type=customers&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">Export Customers Report</a></li>
                        <li><a class="dropdown-item" href="export.php?type=products&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">Export Products Report</a></li>
                    </ul>
                </div>
            </div>
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
                        <h5 class="card-title">Average Order Value</h5>
                        <h2>Ksh <?php echo number_format($summary['average_order_value'], 2); ?></h2>
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
        </div>

        <div class="row">
            <!-- Sales by Payment Method -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Sales by Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentMethodChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Selling Products -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Top Selling Products</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($product = $top_products->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo number_format($product['total_quantity']); ?></td>
                                        <td>Ksh <?php echo number_format($product['total_revenue'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Links -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Available Reports</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="sales_report.php" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-graph-up"></i> Sales Report
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="orders_report.php" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-cart"></i> Orders Report
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="customers_report.php" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-people"></i> Customers Report
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="products_report.php" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-box"></i> Products Report
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Payment Method Chart
        const paymentMethodData = {
            labels: [<?php 
                $labels = [];
                $data = [];
                while ($row = $payment_methods->fetch_assoc()) {
                    $labels[] = "'" . $row['payment_method'] . "'";
                    $data[] = $row['total_amount'];
                }
                echo implode(',', $labels);
            ?>],
            datasets: [{
                data: [<?php echo implode(',', $data); ?>],
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
            }]
        };

        new Chart(document.getElementById('paymentMethodChart'), {
            type: 'pie',
            data: paymentMethodData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
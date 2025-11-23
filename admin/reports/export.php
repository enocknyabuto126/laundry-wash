<?php
require_once '../config.php';

// Require admin access
requireAdmin();

// Get parameters
$type = $_GET['type'] ?? '';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

switch ($type) {
    case 'sales':
        // Sales Report
        fputcsv($output, ['Date', 'Orders', 'Revenue', 'Service Fees', 'Delivery Fees', 'Total']);
        
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
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                date('M d, Y', strtotime($row['date'])),
                $row['order_count'],
                $row['total_revenue'],
                $row['total_service_fee'],
                $row['total_delivery_fee'],
                $row['total_revenue'] + $row['total_service_fee'] + $row['total_delivery_fee']
            ]);
        }
        break;

    case 'orders':
        // Orders Report
        fputcsv($output, ['Order ID', 'Customer', 'Phone', 'Items', 'Total', 'Status', 'Date', 'Delivery Date']);
        
        $sql = "SELECT 
            o.*,
            u.name as customer_name,
            u.phone as customer_phone,
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.created_at BETWEEN ? AND ?
        ORDER BY o.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['customer_name'],
                $row['customer_phone'],
                $row['item_count'],
                $row['grand_total'],
                $row['status'],
                date('M d, Y', strtotime($row['created_at'])),
                date('M d, Y', strtotime($row['delivery_date']))
            ]);
        }
        break;

    case 'customers':
        // Customers Report
        fputcsv($output, ['Customer', 'Phone', 'Orders', 'Total Spent', 'Last Order']);
        
        $sql = "SELECT 
            u.name,
            u.phone,
            COUNT(o.id) as order_count,
            SUM(o.grand_total) as total_spent,
            MAX(o.created_at) as last_order
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id
        WHERE o.created_at BETWEEN ? AND ?
        AND o.status != 'cancelled'
        GROUP BY u.id
        ORDER BY total_spent DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['name'],
                $row['phone'],
                $row['order_count'],
                $row['total_spent'],
                date('M d, Y', strtotime($row['last_order']))
            ]);
        }
        break;

    case 'products':
        // Products Report
        fputcsv($output, ['Product', 'Category', 'Quantity Sold', 'Revenue', 'Average Price']);
        
        $sql = "SELECT 
            p.name,
            c.name as category,
            SUM(oi.quantity) as total_quantity,
            SUM(oi.quantity * oi.price) as total_revenue,
            AVG(oi.price) as avg_price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.created_at BETWEEN ? AND ?
        AND o.status != 'cancelled'
        GROUP BY p.id
        ORDER BY total_quantity DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['name'],
                $row['category'],
                $row['total_quantity'],
                $row['total_revenue'],
                $row['avg_price']
            ]);
        }
        break;

    default:
        // Invalid report type
        fputcsv($output, ['Error: Invalid report type']);
        break;
}

// Close the output stream
fclose($output);
?> 
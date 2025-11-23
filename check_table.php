<?php
require_once 'config.php';

// Get table structure
$result = $conn->query("SHOW CREATE TABLE orders");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<h3>Table Structure:</h3>";
    echo "<pre>";
    print_r($row['Create Table']);
    echo "</pre>";
} else {
    echo "Error: " . $conn->error;
}

// Get column list
$result = $conn->query("SHOW COLUMNS FROM orders");
if ($result) {
    echo "<h3>Current Columns:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

// Check if the table exists
$result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'orders'");
$row = $result->fetch_assoc();
echo "<h3>Table Exists:</h3>";
echo $row['count'] > 0 ? "Yes" : "No"; 
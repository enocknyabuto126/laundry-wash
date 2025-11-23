<?php
require_once 'config.php';

// Check if phone column exists
$result = $conn->query("SHOW COLUMNS FROM orders LIKE 'phone'");
if ($result->num_rows > 0) {
    $column = $result->fetch_assoc();
    echo "<h3>Phone Column Details:</h3>";
    echo "<pre>";
    print_r($column);
    echo "</pre>";
} else {
    echo "<h3>Phone Column Status:</h3>";
    echo "The phone column does not exist in the orders table.<br>";
}

// Show all columns for reference
$result = $conn->query("SHOW COLUMNS FROM orders");
echo "<h3>All Columns in Orders Table:</h3>";
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

// Try to add the phone column specifically
echo "<h3>Attempting to Add Phone Column:</h3>";
try {
    $sql = "ALTER TABLE orders ADD COLUMN phone VARCHAR(20) NULL AFTER address";
    if ($conn->query($sql)) {
        echo "Successfully added phone column!";
    } else {
        echo "Error adding phone column: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Exception while adding phone column: " . $e->getMessage();
} 
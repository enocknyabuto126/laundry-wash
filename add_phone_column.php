<?php
require_once 'config.php';

// Add phone column if it doesn't exist
$sql = "SHOW COLUMNS FROM users LIKE 'phone'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    // Add phone column
    $sql = "ALTER TABLE users ADD COLUMN phone varchar(20) DEFAULT NULL AFTER password";
    if ($conn->query($sql)) {
        echo "Successfully added phone column to users table";
    } else {
        echo "Error adding phone column: " . $conn->error;
    }
} else {
    echo "Phone column already exists";
} 
<?php
require_once 'config.php';

// Require login
requireLogin();

$user_id = getCurrentUserId();
$notification_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$response = ['success' => false];

if ($notification_id > 0) {
    // Mark notification as read
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $notification_id, $user_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
    }
    
    $stmt->close();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
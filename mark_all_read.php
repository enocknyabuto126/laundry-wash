<?php
require_once 'config.php';

// Require login
requireLogin();

$user_id = getCurrentUserId();

// Mark all notifications as read
$sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Redirect back to notifications page
header('Location: notifications.php');
exit;
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'laundry_db');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Site configuration
define('SITE_NAME', 'Wash & Fold Laundry');
define('SITE_URL', 'http://localhost/laundrymat');

// Helper functions
function formatDate($date) {
    return date('M d, Y h:i A', strtotime($date));
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Utility functions
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Create notification
function createNotification($userId, $message, $link = null) {
    global $conn;
    
    $userId = (int)$userId;
    $message = sanitize($message);
    $link = $link ? sanitize($link) : null;
    
    $sql = "INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $userId, $message, $link);
    $stmt->execute();
    $stmt->close();
}
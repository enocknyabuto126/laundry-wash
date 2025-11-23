<?php
// Get unread notifications count for badge
$notification_count = 0;
if (isLoggedIn()) {
    $user_id = getCurrentUserId();
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $notification_count = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
}
?>
<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="images/logo.png" alt="Wash & Fold Laundry">
                <span class="ms-2">Wash & Fold</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="booking.php">Book Now</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>                    
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/dashboard.php">Admin</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="bi bi-cart"></i>
                                <?php
                                // Get cart count
                                $user_id = getCurrentUserId();
                                $sql = "SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $cart_count = $stmt->get_result()->fetch_assoc()['cart_count'] ?? 0;
                                $stmt->close();
                                
                                if ($cart_count > 0):
                                ?>
                                    <span class="badge bg-danger rounded-pill"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="notifications.php">
                                <i class="bi bi-bell"></i>
                                <?php if ($notification_count > 0): ?>
                                    <span class="badge bg-danger rounded-pill" id="notification-badge"><?php echo $notification_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> <?php echo explode(' ', $_SESSION['user_name'])[0]; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                                <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Toast Container for Notifications -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>
</header>
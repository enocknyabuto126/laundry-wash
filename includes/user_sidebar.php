<?php
// Get current page file name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="user-sidebar mb-4">
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">My Account</h5>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="orders.php" class="list-group-item list-group-item-action <?php echo $current_page === 'orders.php' || $current_page === 'order.php' ? 'active' : ''; ?>">
                    <i class="bi bi-bag"></i> My Orders
                </a>
                <a href="profile.php" class="list-group-item list-group-item-action <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
                    <i class="bi bi-person"></i> My Profile
                </a>
                <a href="notifications.php" class="list-group-item list-group-item-action <?php echo $current_page === 'notifications.php' ? 'active' : ''; ?>">
                    <i class="bi bi-bell"></i> Notifications
                    <?php if ($notification_count > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-2"><?php echo $notification_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="booking.php" class="list-group-item list-group-item-action <?php echo $current_page === 'booking.php' ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-plus"></i> Book Service
                </a>
                <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-body">
            <h6 class="mb-3">Need Help?</h6>
            <p class="small mb-3">If you have any questions or need assistance, our support team is here to help.</p>
            <a href="contact.php" class="btn btn-outline-primary btn-sm w-100">Contact Support</a>
        </div>
    </div>
</div>
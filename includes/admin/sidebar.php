<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'orders.php' || $current_page === 'view_order.php' ? 'active' : ''; ?>" href="orders.php">
                    <i class="bi bi-file-earmark"></i>
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'products.php' ? 'active' : ''; ?>" href="products.php">
                    <i class="bi bi-box"></i>
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="bi bi-people"></i>
                    Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                    <i class="bi bi-bar-chart"></i>
                    Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    <i class="bi bi-gear"></i>
                    Settings
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Quick Actions</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="add_product.php">
                    <i class="bi bi-plus-circle"></i>
                    Add Product
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php" target="_blank">
                    <i class="bi bi-house"></i>
                    View Site
                </a>
            </li>
        </ul>
    </div>
</nav>
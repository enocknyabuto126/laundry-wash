<?php
require_once 'config.php';

// Initialize variables
$error_message = [];
$products = [];

// Get products from database
$select_products = $conn->query("SELECT * FROM products");
if (!$select_products) {
    $error_message[] = "Error fetching products: " . $conn->error;
} else {
    while ($product = $select_products->fetch_assoc()) {
        $products[] = $product;
    }
}

// Get cart count
$cart_count = 0;
if (isLoggedIn()) {
    $user_id = getCurrentUserId();
    $cart_query = "SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $cart_count = $row['cart_count'] ?: 0;
    }
    $stmt->close();
}

// Process add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        // Redirect to login if not logged in
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }

    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($product_id > 0 && $quantity > 0) {
        $user_id = getCurrentUserId();
        
        // Check if product already in cart
        $check_sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update quantity
            $cart_item = $check_result->fetch_assoc();
            $new_quantity = $cart_item['quantity'] + $quantity;
            
            $update_sql = "UPDATE cart SET quantity = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $new_quantity, $cart_item['id']);
            
            if ($update_stmt->execute()) {
                $error_message[] = 'Item quantity updated in cart successfully';
            } else {
                $error_message[] = 'Failed to update cart: ' . $conn->error;
            }
            $update_stmt->close();
        } else {
            // Add new item
            $insert_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
            
            if ($insert_stmt->execute()) {
                $error_message[] = 'Item added to cart successfully';
            } else {
                $error_message[] = 'Failed to add to cart: ' . $conn->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
        
        // Refresh cart count
        $cart_query = "SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($cart_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $cart_count = $row['cart_count'] ?: 0;
        }
        $stmt->close();
    }
}

// Define static product categories since we don't have a category column
$static_categories = [
    ['id' => 'all', 'name' => 'All Items', 'icon' => 'grid-3x3-gap', 'description' => 'Browse our complete selection'],
    ['id' => 'clothing', 'name' => 'Clothing', 'icon' => 'basket', 'description' => 'Everyday clothing items'],
    ['id' => 'specialcare', 'name' => 'Special Care', 'icon' => 'stars', 'description' => 'Special care for delicate fabrics'],
    ['id' => 'household', 'name' => 'Household', 'icon' => 'house', 'description' => 'Bedding, curtains and more']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Now - Wash & Fold Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .service-icon {
            font-size: 48px;
            color: #5D5CDE;
        }
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .product-image {
            height: 120px;
            object-fit: contain;
            padding: 10px;
        }
        .quantity-input {
            width: 80px;
        }
        .message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 15px 25px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 400px;
        }
        .message.success {
            border-left: 5px solid #28a745;
        }
        .message.error {
            border-left: 5px solid #dc3545;
        }
        .category-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .category-card:hover {
            transform: translateY(-5px);
        }
        .category-card.active {
            border-color: #5D5CDE;
            background-color: rgba(93, 92, 222, 0.05);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="py-5 text-center">
        <img class="d-block mx-auto mb-4" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Ccircle cx='40' cy='40' r='30' fill='%235D5CDE'/%3E%3Cpath d='M40,20 C40,20 30,30 30,40 C30,50 40,60 40,60 C40,60 50,50 50,40 C50,30 40,20 40,20 Z' fill='white'/%3E%3C/svg%3E" alt="Laundry" width="80" height="80">
        <h2>My Laundry Basket</h2>
        <p class="lead">Pick any item below that is in your laundry basket. Thank you for choosing our laundry services. We appreciate your business and would love to hear your feedback.</p>
    </div>

    <!-- Display error messages -->
    <?php if (!empty($error_message)): ?>
        <div class="container mb-4">
            <?php foreach ($error_message as $message): ?>
                <div class="alert alert-<?php echo (strpos($message, 'successfully') !== false) ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="container mb-5">
        <!-- Category filters -->
        <div class="row g-4 mb-4">
            <?php foreach ($static_categories as $category): ?>
                <div class="col-md-3">
                    <div class="card h-100 category-card <?php echo $category['id'] === 'all' ? 'active' : ''; ?>" data-category="<?php echo $category['id']; ?>">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-<?php echo $category['icon']; ?> service-icon mb-3"></i>
                            <h5><?php echo $category['name']; ?></h5>
                            <p class="text-muted mb-0"><?php echo $category['description']; ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Search bar -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchProducts" class="form-control" placeholder="Search for items...">
                </div>
            </div>
        </div>

        <!-- Products display -->
        <div class="row g-4" id="productsContainer">
            <?php 
            if (empty($products)): 
            ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-exclamation-circle" style="font-size: 48px;"></i>
                    <h3 class="mt-3">No products available</h3>
                    <p>Please check back later or contact admin.</p>
                </div>
            <?php 
            else:
                foreach ($products as $product): 
                    // Assign a default category based on product name or type
                    // This is just for demonstration - in real application, you might want a more sophisticated way
                    // to categorize products or add a category column to your database
                    $category = 'clothing'; // Default category
                    $name = strtolower($product['name'] ?? '');
                    
                    if (strpos($name, 'sheet') !== false || strpos($name, 'duvet') !== false || 
                        strpos($name, 'curtain') !== false || strpos($name, 'towel') !== false) {
                        $category = 'household';
                    } elseif (strpos($name, 'silk') !== false || strpos($name, 'formal') !== false || 
                             strpos($name, 'suit') !== false || strpos($name, 'dress') !== false) {
                        $category = 'specialcare';
                    }
            ?>
                <div class="col-6 col-md-4 col-lg-3 product-item" data-category="<?php echo $category; ?>">
                    <div class="card h-100 product-card shadow-sm">
                        <?php if (!empty($product['image'])): ?>
                            <img src="uploaded_images/<?php echo $product['image']; ?>" class="card-img-top product-image" alt="<?php echo $product['name']; ?>">
                        <?php else: ?>
                            <div class="text-center p-3">
                                <i class="bi bi-basket2-fill text-primary" style="font-size: 64px;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                            <p class="card-text text-primary fw-bold">Ksh <?php echo number_format($product['price'], 2); ?></p>
                            <form method="POST" action="">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="input-group input-group-sm quantity-input">
                                        <button class="btn btn-outline-secondary quantity-btn" type="button" onclick="decreaseQuantity(this)">-</button>
                                        <input type="number" class="form-control text-center" name="quantity" value="1" min="1" max="100">
                                        <button class="btn btn-outline-secondary quantity-btn" type="button" onclick="increaseQuantity(this)">+</button>
                                    </div>
                                    <button type="submit" name="add_to_cart" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php 
                endforeach; 
            endif; 
            ?>
        </div>

        <div class="text-center mt-4" id="noProductsMessage" style="display: none;">
            <div class="py-5">
                <i class="bi bi-search mb-3" style="font-size: 48px;"></i>
                <h4>No items found</h4>
                <p>Try a different search term or category</p>
            </div>
        </div>

        <!-- View cart button -->
        <div class="mt-5 pt-5 text-center">
            <a href="cart.php" class="btn btn-lg btn-primary">
                <i class="bi bi-cart"></i> View My Cart 
                <?php if ($cart_count > 0): ?>
                    <span class="badge bg-white text-primary"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript for quantity buttons
        function increaseQuantity(button) {
            const input = button.previousElementSibling;
            input.value = parseInt(input.value) + 1;
        }

        function decreaseQuantity(button) {
            const input = button.nextElementSibling;
            const value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
            }
        }

        // Category filtering
        const categoryCards = document.querySelectorAll('.category-card');
        categoryCards.forEach(card => {
            card.addEventListener('click', function() {
                // Remove active class from all cards
                categoryCards.forEach(c => c.classList.remove('active'));
                // Add active class to clicked card
                this.classList.add('active');
                
                const category = this.dataset.category;
                filterProducts(category);
            });
        });

        // Search functionality
        const searchInput = document.getElementById('searchProducts');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const activeCategory = document.querySelector('.category-card.active').dataset.category;
            filterProducts(activeCategory, searchTerm);
        });

        function filterProducts(category, searchTerm = '') {
            const products = document.querySelectorAll('.product-item');
            let visibleCount = 0;
            
            products.forEach(product => {
                const productCategory = product.dataset.category;
                const productName = product.querySelector('.card-title').textContent.toLowerCase();
                
                const categoryMatch = category === 'all' || productCategory === category;
                const searchMatch = !searchTerm || productName.includes(searchTerm);
                
                if (categoryMatch && searchMatch) {
                    product.style.display = 'block';
                    visibleCount++;
                } else {
                    product.style.display = 'none';
                }
            });
            
            // Show/hide no products message
            const noProductsMessage = document.getElementById('noProductsMessage');
            noProductsMessage.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }, 5000);
        });
    </script>
</body>
</html>
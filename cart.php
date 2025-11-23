<?php
require_once 'config.php';

// Require login
requireLogin();

$user_id = getCurrentUserId();
$success = '';
$error = '';

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Check if product already in cart
    $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update quantity
        $cart_item = $result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        $update_sql = "UPDATE cart SET quantity = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $new_quantity, $cart_item['id']);
        
        if ($update_stmt->execute()) {
            $success = 'Cart updated successfully';
        } else {
            $error = 'Failed to update cart';
        }
        
        $update_stmt->close();
    } else {
        // Add new item
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        
        if ($stmt->execute()) {
            $success = 'Item added to cart successfully';
        } else {
            $error = 'Failed to add item to cart';
        }
    }
    
    $stmt->close();
}

// Remove from cart
if (isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    
    $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cart_id, $user_id);
    
    if ($stmt->execute()) {
        $success = 'Item removed from cart';
    } else {
        $error = 'Failed to remove item from cart';
    }
    
    $stmt->close();
}

// Update cart quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cart_id => $quantity) {
        $cart_id = (int)$cart_id;
        $quantity = (int)$quantity;
        
        if ($quantity > 0) {
            $sql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $cart_id, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    $success = 'Cart updated successfully';
}

// Clear cart
if (isset($_GET['clear'])) {
    $sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $success = 'Cart cleared successfully';
    } else {
        $error = 'Failed to clear cart';
    }
    
    $stmt->close();
}

// Get cart items
$sql = "SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();
$stmt->close();

// Calculate totals
$subtotal = 0;
$service_fee = 50;
$delivery_fee = 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Wash & Fold Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <h1>Shopping Cart</h1>
        <p class="lead">Review and update your laundry items before checkout.</p>
        
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Cart Items</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($cart_items->num_rows > 0): ?>
                            <form method="POST" action="">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Total</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($item = $cart_items->fetch_assoc()): ?>
                                                <?php 
                                                $item_total = $item['price'] * $item['quantity'];
                                                $subtotal += $item_total;
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if ($item['image']): ?>
                                                                <img src="uploaded_images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                            <?php else: ?>
                                                                <div class="bg-light me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                                                    <i class="bi bi-basket2-fill" style="font-size: 24px;"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div>
                                                                <h6 class="mb-0"><?php echo $item['name']; ?></h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>Ksh <?php echo number_format($item['price'], 2); ?></td>
                                                    <td>
                                                        <div class="input-group input-group-sm" style="width: 120px;">
                                                            <button class="btn btn-outline-secondary qty-btn" type="button" data-action="decrease" data-target="qty-<?php echo $item['id']; ?>">-</button>
                                                            <input type="number" class="form-control text-center" name="quantity[<?php echo $item['id']; ?>]" id="qty-<?php echo $item['id']; ?>" value="<?php echo $item['quantity']; ?>" min="1" max="100">
                                                            <button class="btn btn-outline-secondary qty-btn" type="button" data-action="increase" data-target="qty-<?php echo $item['id']; ?>">+</button>
                                                        </div>
                                                    </td>
                                                    <td>Ksh <?php echo number_format($item_total, 2); ?></td>
                                                    <td>
                                                        <a href="cart.php?remove=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to remove this item?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <a href="booking.php" class="btn btn-outline-primary">
                                        <i class="bi bi-arrow-left"></i> Continue Shopping
                                    </a>
                                    <div>
                                        <a href="cart.php?clear=1" class="btn btn-outline-danger me-2" onclick="return confirm('Are you sure you want to clear your cart?')">
                                            Clear Cart
                                        </a>
                                        <button type="submit" name="update_cart" class="btn btn-primary">
                                            Update Cart
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="mb-4">
                                    <i class="bi bi-basket2" style="font-size: 64px;"></i>
                                </div>
                                <h5>Your cart is empty</h5>
                                <p>Looks like you haven't added any items to your cart yet.</p>
                                <a href="booking.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal:</span>
                            <span>Ksh <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Service Fee:</span>
                            <span>Ksh <?php echo number_format($service_fee, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Delivery Fee:</span>
                            <span>Ksh <?php echo number_format($delivery_fee, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3 fw-bold">
                            <span>Grand Total:</span>
                            <span>Ksh <?php echo number_format($subtotal + $service_fee + $delivery_fee, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-primary w-100 <?php echo ($cart_items->num_rows == 0) ? 'disabled' : ''; ?>">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Quantity buttons functionality
        document.querySelectorAll('.qty-btn').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.dataset.action;
                const targetId = this.dataset.target;
                const input = document.getElementById(targetId);
                let value = parseInt(input.value);
                
                if (action === 'decrease') {
                    if (value > 1) {
                        input.value = value - 1;
                    }
                } else {
                    input.value = value + 1;
                }
            });
        });
    </script>
</body>
</html>
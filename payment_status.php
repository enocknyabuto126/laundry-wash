<?php
require_once 'config.php';
require_once 'includes/mpesa.php';

// Require login
requireLogin();

$user_id = getCurrentUserId();
$checkout_request_id = isset($_GET['checkout_request_id']) ? sanitize($_GET['checkout_request_id']) : '';

if (!$checkout_request_id) {
    header('Location: dashboard.php');
    exit;
}

// Get payment details
$sql = "SELECT p.*, o.id as order_id, o.status as order_status, o.total_amount 
        FROM payments p 
        JOIN orders o ON p.order_id = o.id 
        WHERE p.transaction_id = ? AND o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $checkout_request_id, $user_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$payment) {
    header('Location: dashboard.php');
    exit;
}

$page_title = "Payment Status";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Payment Status</h2>
                    
                    <div class="text-center mb-4">
                        <div id="payment-status-icon" class="mb-3">
                            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                        </div>
                        <h4 id="payment-status-message">Checking payment status...</h4>
                    </div>
                    
                    <div class="payment-details">
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Order ID:</strong>
                            </div>
                            <div class="col-6 text-end">
                                #<?php echo $payment['order_id']; ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Amount:</strong>
                            </div>
                            <div class="col-6 text-end">
                                KES <?php echo number_format($payment['amount'], 2); ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Payment Method:</strong>
                            </div>
                            <div class="col-6 text-end">
                                <?php echo ucfirst($payment['payment_method']); ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Date:</strong>
                            </div>
                            <div class="col-6 text-end">
                                <?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <div id="payment-actions" style="display: none;">
                            <a href="order.php?id=<?php echo $payment['order_id']; ?>" class="btn btn-primary">
                                View Order
                            </a>
                            <a href="dashboard.php" class="btn btn-outline-secondary ms-2">
                                Back to Dashboard
                            </a>
                        </div>
                        
                        <div id="payment-retry" style="display: none;">
                            <a href="checkout.php?order_id=<?php echo $payment['order_id']; ?>" class="btn btn-warning">
                                Retry Payment
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let checkCount = 0;
const maxChecks = 30; // Maximum number of status checks (30 * 2 seconds = 1 minute)

function updatePaymentStatus() {
    fetch(`check_payment_status.php?checkout_request_id=<?php echo $checkout_request_id; ?>`)
        .then(response => response.json())
        .then(data => {
            const statusIcon = document.getElementById('payment-status-icon');
            const statusMessage = document.getElementById('payment-status-message');
            const paymentActions = document.getElementById('payment-actions');
            const paymentRetry = document.getElementById('payment-retry');
            
            if (data.status === 'completed') {
                statusIcon.innerHTML = '<i class="fas fa-check-circle fa-3x text-success"></i>';
                statusMessage.textContent = 'Payment completed successfully!';
                paymentActions.style.display = 'block';
                return true;
            } else if (data.status === 'failed') {
                statusIcon.innerHTML = '<i class="fas fa-times-circle fa-3x text-danger"></i>';
                statusMessage.textContent = data.message;
                paymentRetry.style.display = 'block';
                return true;
            } else if (data.status === 'pending') {
                statusIcon.innerHTML = '<i class="fas fa-spinner fa-spin fa-3x text-primary"></i>';
                statusMessage.textContent = 'Payment is still being processed...';
                
                // Continue checking if we haven't reached the maximum number of checks
                if (checkCount < maxChecks) {
                    checkCount++;
                    setTimeout(updatePaymentStatus, 2000); // Check every 2 seconds
                } else {
                    statusIcon.innerHTML = '<i class="fas fa-exclamation-circle fa-3x text-warning"></i>';
                    statusMessage.textContent = 'Payment status check timed out. Please check your order status.';
                    paymentActions.style.display = 'block';
                }
            }
        })
        .catch(error => {
            console.error('Error checking payment status:', error);
            const statusIcon = document.getElementById('payment-status-icon');
            const statusMessage = document.getElementById('payment-status-message');
            const paymentActions = document.getElementById('payment-actions');
            
            statusIcon.innerHTML = '<i class="fas fa-exclamation-circle fa-3x text-warning"></i>';
            statusMessage.textContent = 'Error checking payment status. Please check your order status.';
            paymentActions.style.display = 'block';
        });
}

// Start checking payment status
updatePaymentStatus();
</script>

<?php include 'includes/footer.php'; ?> 
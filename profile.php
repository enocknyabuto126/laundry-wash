<?php
require_once 'config.php';

// Require login
requireLogin();

$user_id = getCurrentUserId();
$success = '';
$error = '';

// Get user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        
        // Check if email is changed and already exists
        if ($email !== $user['email']) {
            $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("si", $email, $user_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                $error = 'Email already exists';
                $check_stmt->close();
            } else {
                $check_stmt->close();
                
                // Update profile
                $update_sql = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sssi", $name, $email, $phone, $user_id);
                
                if ($update_stmt->execute()) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $success = 'Profile updated successfully';
                    
                    // Refresh user data
                    $user['name'] = $name;
                    $user['email'] = $email;
                    $user['phone'] = $phone;
                } else {
                    $error = 'Failed to update profile';
                }
                
                $update_stmt->close();
            }
        } else {
            // Email not changed, just update other fields
            $update_sql = "UPDATE users SET name = ?, phone = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $name, $phone, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['user_name'] = $name;
                $success = 'Profile updated successfully';
                
                // Refresh user data
                $user['name'] = $name;
                $user['phone'] = $phone;
            } else {
                $error = 'Failed to update profile';
            }
            
            $update_stmt->close();
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_sql = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $success = 'Password updated successfully';
            } else {
                $error = 'Failed to update password';
            }
            
            $update_stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Wash & Fold Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-md-3">
                <?php include 'includes/user_sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <h1>My Profile</h1>
                <p class="lead">Manage your account information and preferences.</p>
                
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
                                <h5 class="mb-0">Personal Information</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
                                    </div>
                                    <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Account Information</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Account Type:</strong> <?php echo ucfirst($user['role']); ?></p>
                                <p><strong>Member Since:</strong> <?php echo formatDate($user['created_at']); ?></p>
                                <?php
                                // Get order count
                                $sql = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $order_count = $result->fetch_assoc()['order_count'];
                                $stmt->close();
                                ?>
                                <p><strong>Total Orders:</strong> <?php echo $order_count; ?></p>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Notification Preferences</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="update_preferences.php">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" checked>
                                        <label class="form-check-label" for="email_notifications">Email Notifications</label>
                                        <small class="form-text text-muted d-block">Receive updates on your orders via email</small>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="sms_notifications" name="sms_notifications" checked>
                                        <label class="form-check-label" for="sms_notifications">SMS Notifications</label>
                                        <small class="form-text text-muted d-block">Receive updates on your orders via SMS</small>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="promotional_emails" name="promotional_emails">
                                        <label class="form-check-label" for="promotional_emails">Promotional Emails</label>
                                        <small class="form-text text-muted d-block">Receive special offers and promotions</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Save Preferences</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
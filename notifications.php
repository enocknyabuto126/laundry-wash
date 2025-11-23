<?php
require_once 'config.php';

// Require login
requireLogin();

$user_id = getCurrentUserId();

// Get notifications
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
$stmt->close();

// Count unread notifications
$sql = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['unread_count'];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Wash & Fold Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .notification-item {
            transition: background-color 0.3s;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
        }
        
        .unread {
            background-color: #f0f7ff;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-md-3">
                <?php include 'includes/user_sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Notifications</h1>
                    <?php if ($unread_count > 0): ?>
                        <a href="mark_all_read.php" class="btn btn-outline-primary">Mark All as Read</a>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <?php if ($notifications->num_rows > 0): ?>
                            <div id="notificationsList">
                                <?php while ($notification = $notifications->fetch_assoc()): ?>
                                    <div class="notification-item p-3 border-bottom d-flex align-items-start <?php echo $notification['is_read'] ? '' : 'unread'; ?>" data-id="<?php echo $notification['id']; ?>">
                                        <div class="me-3">
                                            <div class="notification-icon bg-primary text-white rounded-circle p-2">
                                                <i class="bi bi-bell"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1"><?php echo $notification['message']; ?></p>
                                            <small class="text-muted"><?php echo formatDate($notification['created_at']); ?></small>
                                        </div>
                                        <?php if (!$notification['is_read']): ?>
                                            <button class="btn btn-sm mark-read-btn" data-id="<?php echo $notification['id']; ?>">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="mb-4">
                                    <i class="bi bi-bell" style="font-size: 64px;"></i>
                                </div>
                                <h5>No notifications yet</h5>
                                <p>When you receive notifications, they will appear here.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mark notification as read
        document.querySelectorAll('.mark-read-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const notificationId = this.dataset.id;
                markAsRead(notificationId, this.closest('.notification-item'));
            });
        });
        
        // Click on notification
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                const link = '<?php echo isset($notification['link']) ? $notification['link'] : ''; ?>';
                
                // Mark as read if unread
                if (this.classList.contains('unread')) {
                    markAsRead(notificationId, this);
                }
                
                // Navigate to link if available
                if (link) {
                    window.location.href = link;
                }
            });
        });
        
        function markAsRead(id, element) {
            fetch('mark_read.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        element.classList.remove('unread');
                        const markReadBtn = element.querySelector('.mark-read-btn');
                        if (markReadBtn) {
                            markReadBtn.remove();
                        }
                        
                        // Update badge count
                        const badge = document.getElementById('notification-badge');
                        if (badge) {
                            const count = parseInt(badge.textContent) - 1;
                            if (count > 0) {
                                badge.textContent = count;
                            } else {
                                badge.style.display = 'none';
                            }
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
<?php
require_once 'config.php';

// Initialize variables
$name = $email = $phone = $subject = $message = '';
$success_message = '';
$error_message = '';

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

// Process contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    // Get form data
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message']);
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($message)) {
        $error_message = 'Name, email, and message are required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Insert into database
        $sql = "INSERT INTO contact_messages (name, email, phone, subject, message, newsletter) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $name, $email, $phone, $subject, $message, $newsletter);
        
        if ($stmt->execute()) {
            $success_message = 'Your message has been sent successfully! We\'ll get back to you as soon as possible.';
            // Reset form fields
            $name = $email = $phone = $subject = $message = '';
        } else {
            $error_message = 'Failed to send message. Please try again later or contact us directly by phone.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Wash & Fold Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .service-icon {
            font-size: 48px;
            color: #5D5CDE;
        }
        .contact-info-card {
            border-left: 4px solid #5D5CDE;
            transition: transform 0.3s;
        }
        .contact-info-card:hover {
            transform: translateY(-5px);
        }
        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #5D5CDE;
            color: white;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        .social-icon:hover {
            background-color: #4b4aad;
            transform: scale(1.1);
        }
        .accordion-button:not(.collapsed) {
            background-color: rgba(93, 92, 222, 0.1);
            color: #5D5CDE;
        }
        .form-control:focus, .form-check-input:focus {
            border-color: #5D5CDE;
            box-shadow: 0 0 0 0.25rem rgba(93, 92, 222, 0.25);
        }
        .form-check-input:checked {
            background-color: #5D5CDE;
            border-color: #5D5CDE;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-5 fw-bold mb-3">Contact Us</h1>
                <p class="lead">We'd love to hear from you! Reach out with any questions, concerns, or feedback.</p>
            </div>
        </div>
        
        <div class="row g-5">
            <div class="col-md-5">
                <h3 class="mb-4">Get In Touch</h3>
                <div class="card contact-info-card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <i class="bi bi-geo-alt text-primary fs-3 me-3"></i>
                            </div>
                            <div>
                                <h5>Our Location</h5>
                                <p class="text-muted mb-0">123 Laundry Lane<br>Nairobi, Kenya</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card contact-info-card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <i class="bi bi-telephone text-primary fs-3 me-3"></i>
                            </div>
                            <div>
                                <h5>Phone Numbers</h5>
                                <p class="text-muted mb-1">Main: (254) 741-667-115</p>
                                <p class="text-muted mb-0">Customer Support: (254) 787-200-001</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card contact-info-card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <i class="bi bi-envelope text-primary fs-3 me-3"></i>
                            </div>
                            <div>
                                <h5>Email Addresses</h5>
                                <p class="text-muted mb-1">General Inquiries: info@washandfold.co.ke</p>
                                <p class="text-muted mb-0">Customer Support: support@washandfold.co.ke</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card contact-info-card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <i class="bi bi-clock text-primary fs-3 me-3"></i>
                            </div>
                            <div>
                                <h5>Business Hours</h5>
                                <p class="text-muted mb-1">Monday - Friday: 7:00 AM - 8:00 PM</p>
                                <p class="text-muted mb-1">Saturday: 8:00 AM - 6:00 PM</p>
                                <p class="text-muted mb-0">Sunday: 10:00 AM - 4:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card contact-info-card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <i class="bi bi-people text-primary fs-3 me-3"></i>
                            </div>
                            <div>
                                <h5>Connect With Us</h5>
                                <div class="d-flex mt-3">
                                    <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                                    <a href="#" class="social-icon"><i class="bi bi-twitter"></i></a>
                                    <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                                    <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-7">
                <h3 class="mb-4">Send Us a Message</h3>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name*</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" placeholder="Enter your full name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address*</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" placeholder="Enter your email address" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $phone; ?>" placeholder="Enter your phone number">
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo $subject; ?>" placeholder="Enter message subject">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message*</label>
                                <textarea class="form-control" id="message" name="message" rows="5" placeholder="Enter your message" required><?php echo $message; ?></textarea>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                <label class="form-check-label text-muted" for="newsletter">
                                    Subscribe to our newsletter for updates and special offers
                                </label>
                            </div>
                            <button type="submit" name="contact_submit" class="btn btn-primary w-100">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="text-center mb-4">Frequently Asked Questions</h3>
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        How does your pickup and delivery service work?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Our pickup and delivery service is simple and convenient. When you place an order, you can select a preferred pickup date and time. Our driver will arrive within the selected time window to collect your laundry. Once your clothes are cleaned, we'll deliver them back to your door at the scheduled delivery time.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        What areas do you service in Nairobi?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        We currently offer our services throughout Nairobi and its suburbs, including Westlands, Kileleshwa, Kilimani, Karen, Lavington, South B, South C, Parklands, Gigiri, and more. If you're unsure whether we service your area, please contact our customer service team.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        What payment methods do you accept?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        We accept various payment methods for your convenience. These include M-Pesa, credit/debit cards, and cash on delivery. For recurring customers, we also offer prepaid packages at discounted rates.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        How long does it take to get my laundry back?
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Our standard turnaround time is 48 hours. However, we also offer express service with same-day or next-day delivery for an additional fee. Please note that turnaround times may vary during peak periods or for special items that require extra care.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFive">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                        What if I'm not satisfied with the service?
                                    </button>
                                </h2>
                                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Your satisfaction is our priority. If you're not completely satisfied with our service, please contact us within 24 hours of receiving your order. We'll arrange to pick up the items and re-clean them at no additional cost. In case we still can't meet your expectations, we'll offer you a refund or credit for future services.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-lg-10 mx-auto">
                <div class="card border-0 bg-light shadow-sm rounded-3">
                    <div class="card-body p-5 text-center">
                        <h2 class="mb-3">Ready to Get Started?</h2>
                        <p class="lead mb-4">Experience the convenience of our professional laundry services today.</p>
                        <a href="booking.php" class="btn btn-primary btn-lg px-5">Book Our Services</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    </script>
</body>
</html>
<?php
require_once 'config.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Wash & Fold Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .service-icon {
            font-size: 48px;
            color: #5D5CDE;
        }
        .about-image {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .value-card {
            height: 100%;
            transition: transform 0.3s ease;
        }
        .value-card:hover {
            transform: translateY(-5px);
        }
        .team-member-card {
            transition: transform 0.3s ease;
            height: 100%;
        }
        .team-member-card:hover {
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
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline-item {
            padding-bottom: 2rem;
            position: relative;
        }
        .timeline-item:not(:last-child)::before {
            content: "";
            position: absolute;
            left: -18px;
            top: 0;
            height: 100%;
            width: 2px;
            background-color: #5D5CDE;
        }
        .timeline-icon {
            position: absolute;
            left: -30px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: #5D5CDE;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <h1 class="display-5 fw-bold mb-3">About Wash & Fold Laundry</h1>
                <p class="lead mb-4">We're more than just a laundry service - we're your partner for clean, fresh clothing without the hassle.</p>
                <p>Founded with a mission to give people back their time, Wash & Fold has grown into Nairobi's most trusted laundry service provider, combining quality cleaning with convenient pickup and delivery.</p>
            </div>
            <div class="col-lg-6">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='600' height='400' viewBox='0 0 600 400'%3E%3Crect fill='%23f8f9fa' width='600' height='400'/%3E%3Cpath fill='%235D5CDE' d='M200,100 L400,100 A50,50 0 0,1 450,150 L450,300 A50,50 0 0,1 400,350 L200,350 A50,50 0 0,1 150,300 L150,150 A50,50 0 0,1 200,100 Z'/%3E%3Ccircle fill='white' cx='200' cy='150' r='20'/%3E%3Ccircle fill='white' cx='250' cy='150' r='20'/%3E%3Ccircle fill='white' cx='300' cy='150' r='20'/%3E%3Cpath fill='white' d='M225,200 L375,200 L375,300 L225,300 Z'/%3E%3C/svg%3E" class="img-fluid about-image" alt="Laundry Machine">
            </div>
        </div>
        
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="text-center mb-4">Our Story</h2>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="bi bi-1-circle-fill"></i>
                        </div>
                        <h5>2021: The Beginning</h5>
                        <p>Wash & Fold Laundry started with a simple mission and just two washing machines. Our founder, Hezekiah Nyabuto, noticed how much time people spent on laundry and wanted to create a solution.</p>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="bi bi-2-circle-fill"></i>
                        </div>
                        <h5>2022: Expanding Services</h5>
                        <p>We added dry cleaning capabilities and began our first pickup and delivery services in select Nairobi neighborhoods. Our customer base grew steadily through word-of-mouth.</p>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="bi bi-3-circle-fill"></i>
                        </div>
                        <h5>2023: Digital Transformation</h5>
                        <p>We launched our website and mobile booking system, making it easier than ever for customers to schedule their laundry services online and track their orders.</p>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="bi bi-4-circle-fill"></i>
                        </div>
                        <h5>Today: Nairobi's Most Trusted Laundry Service</h5>
                        <p>We now serve hundreds of customers across Nairobi, from busy professionals and families to students and businesses. Our commitment to quality, convenience, and customer satisfaction remains at the heart of everything we do.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-5 bg-light py-5 rounded">
            <div class="col-12 text-center mb-4">
                <h2>Our Values</h2>
                <p class="lead">The principles that guide everything we do</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 value-card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-stars service-icon"></i>
                        </div>
                        <h4>Quality</h4>
                        <p class="text-muted mb-0">We never compromise on the quality of our service. Your clothes deserve the best care, and we deliver exactly that with our meticulous attention to detail.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 value-card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-stopwatch service-icon"></i>
                        </div>
                        <h4>Reliability</h4>
                        <p class="text-muted mb-0">We understand that timely service is crucial. When we make a commitment to a pickup or delivery time, we honor it – consistently and without fail.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 value-card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-heart service-icon"></i>
                        </div>
                        <h4>Care</h4>
                        <p class="text-muted mb-0">We treat every garment with the care and respect it deserves, using the right techniques and eco-friendly products to extend the life of your clothes.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-5">
            <div class="col-12 text-center mb-4">
                <h2>Why Choose Us</h2>
                <p class="lead">Reasons our customers trust us with their laundry</p>
            </div>
            <div class="col-md-6 mb-4">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="bi bi-clock-history service-icon me-3"></i>
                    </div>
                    <div>
                        <h4>Time-Saving</h4>
                        <p class="text-muted">Our service saves you an average of 5-7 hours per week - time you can spend on things that matter most to you.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="bi bi-truck service-icon me-3"></i>
                    </div>
                    <div>
                        <h4>Free Pickup & Delivery</h4>
                        <p class="text-muted">We offer free pickup and delivery services within Nairobi, making laundry day completely hassle-free.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="bi bi-shield-check service-icon me-3"></i>
                    </div>
                    <div>
                        <h4>Quality Guarantee</h4>
                        <p class="text-muted">If you're not completely satisfied with our service, we'll redo it at no additional cost to ensure your satisfaction.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="bi bi-flower1 service-icon me-3"></i>
                    </div>
                    <div>
                        <h4>Eco-Friendly</h4>
                        <p class="text-muted">We use biodegradable, phosphate-free detergents and energy-efficient machines to minimize our environmental impact.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-5">
            <div class="col-12 text-center mb-4">
                <h2>Meet Our Team</h2>
                <p class="lead">The dedicated professionals behind our service</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card team-member-card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px;">
                                <span class="fw-bold fs-1">JK</span>
                            </div>
                        </div>
                        <h4>James Kamau</h4>
                        <p class="text-muted mb-3">Founder & CEO</p>
                        <p class="small text-muted mb-3">"We founded this company with the belief that everyone deserves high-quality laundry service that fits seamlessly into their busy lives."</p>
                        <div class="d-flex justify-content-center">
                            <a href="#" class="social-icon small"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="social-icon small"><i class="bi bi-twitter"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card team-member-card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px;">
                                <span class="fw-bold fs-1">NO</span>
                            </div>
                        </div>
                        <h4>Nancy Otieno</h4>
                        <p class="text-muted mb-3">Operations Manager</p>
                        <p class="small text-muted mb-3">"My goal is to ensure every piece of clothing is treated with the utmost care and returned to our customers in perfect condition."</p>
                        <div class="d-flex justify-content-center">
                            <a href="#" class="social-icon small"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="social-icon small"><i class="bi bi-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card team-member-card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px;">
                                <span class="fw-bold fs-1">DM</span>
                            </div>
                        </div>
                        <h4>David Mwangi</h4>
                        <p class="text-muted mb-3">Customer Relations</p>
                        <p class="small text-muted mb-3">"I believe exceptional customer service is as important as the quality of our laundry service. Our customers' satisfaction is my top priority."</p>
                        <div class="d-flex justify-content-center">
                            <a href="#" class="social-icon small"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="social-icon small"><i class="bi bi-facebook"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-5">
            <div class="col-lg-10 mx-auto">
                <div class="card border-0 bg-primary text-white shadow rounded-3">
                    <div class="card-body p-5 text-center">
                        <h2 class="mb-3">Experience the Difference</h2>
                        <p class="lead mb-4">Join hundreds of satisfied customers who have made Wash & Fold Laundry their trusted laundry service provider.</p>
                        <a href="booking.php" class="btn btn-light btn-lg px-5">Book Our Services</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
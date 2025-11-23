<?php
require_once 'config.php';

// Get products for featured section
$sql = "SELECT * FROM products LIMIT 12";
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wash & Fold Laundry - Professional Laundry Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --primary-color: #5D5CDE;
            --primary-hover: #4a49b8;
            --text-color: #333;
            --bg-color: #fff;
            --card-bg: #fff;
            --border-color: #eee;
            --shadow-color: rgba(0,0,0,0.1);
        }
        
        .dark {
            --text-color: #f8f9fa;
            --bg-color: #181818;
            --card-bg: #222;
            --border-color: #333;
            --shadow-color: rgba(0,0,0,0.25);
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
        }
        
        .card {
            background-color: var(--card-bg);
            border-color: var(--border-color);
            transition: all 0.3s ease;
        }
        
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .hero-section {
            position: relative;
            overflow: hidden;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 5px 15px var(--shadow-color);
        }
        
        .hero-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 20%;
            background: linear-gradient(to top, rgba(255,255,255,0.1), transparent);
        }
        
        .card {
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px var(--shadow-color);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px var(--shadow-color);
        }
        
        .service-card {
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
        }
        
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px var(--shadow-color) !important;
        }
        
        .how-it-works .card {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .testimonial-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
        }
        
        .loader {
            display: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            animation: spin 1.2s linear infinite;
            margin: 30px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .featured-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--primary-color);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animation-fadeInUp {
            animation: fadeInUp 0.5s ease-out forwards;
        }
        
        /* Better product display */
        .product-image {
            height: 150px;
            object-fit: contain;
            padding: 10px;
            transition: all 0.3s ease;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .product-card {
                margin-bottom: 15px;
            }
            
            .service-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section bg-primary text-white py-5 mb-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 animation-fadeInUp" style="animation-delay: 0.1s;">
                    <h1 class="display-4 fw-bold mb-3">Wash & Fold Laundry</h1>
                    <p class="lead mb-4">Leave Your Laundry Woes to Us</p>
                    <p class="mb-4">We offer professional laundry services with a personal touch. Experience the convenience of our pickup and delivery service.</p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <a href="booking.php" class="btn btn-light btn-lg px-4 me-md-2">Book Now</a>
                        <a href="about.php" class="btn btn-outline-light btn-lg px-4">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block text-center animation-fadeInUp" style="animation-delay: 0.3s;">
                    <img src="images/img1.jpg" class="img-fluid" alt="Laundry Service" width="400">
                </div>
            </div>
        </div>
    </section>
    
    <!-- How It Works Section -->
    <section class="how-it-works py-5 mb-5">
        <div class="container">
            <div class="text-center mb-5 animation-fadeInUp">
                <h2 class="display-6 fw-bold section-title">Get your clothes clean with these three easy steps</h2>
                <p class="lead text-muted">We make laundry day a breeze!</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4 animation-fadeInUp" style="animation-delay: 0.1s;">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-basket2-fill text-primary" style="font-size: 64px;"></i>
                            </div>
                            <h3 class="fs-4 mb-3">My Laundry Basket</h3>
                            <p class="text-muted mb-0">Book by identifying and selecting the type of clothes in your dirty basket and let us take care of the rest.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 animation-fadeInUp" style="animation-delay: 0.2s;">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-water text-primary" style="font-size: 64px;"></i>
                            </div>
                            <h3 class="fs-4 mb-3">Usafi Ianze</h3>
                            <p class="text-muted mb-0">Adjust the number of clothes and proceed to checkout. Give us your drop off location and our team will be on the way.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 animation-fadeInUp" style="animation-delay: 0.3s;">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-truck text-primary" style="font-size: 64px;"></i>
                            </div>
                            <h3 class="fs-4 mb-3">Pickup</h3>
                            <p class="text-muted mb-0">Pickup your clothes once they are cleaned and give us your honest opinion. Pay upon delivery.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4 animation-fadeInUp" style="animation-delay: 0.4s;">
                <a href="booking.php" class="btn btn-primary btn-lg">Start Cleaning With Us</a>
            </div>
        </div>
    </section>
    
    <!-- Services Section -->
    <section class="services bg-light py-5 mb-5">
        <div class="container">
            <div class="text-center mb-5 animation-fadeInUp">
                <h2 class="display-6 fw-bold section-title">Our Services</h2>
                <p class="lead text-muted">Looking for a hassle-free way to get your laundry done? We are committed to providing high-quality services that are convenient, reliable, and affordable.</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4 animation-fadeInUp" style="animation-delay: 0.1s;">
                    <div class="card service-card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-basket2-fill text-primary" style="font-size: 48px;"></i>
                            </div>
                            <h3 class="fs-4 mb-3">Wash And Fold Service</h3>
                            <p class="text-muted mb-4">Our team of experienced professionals will wash, dry, and fold your clothes to perfection.</p>
                            <a href="booking.php" class="btn btn-outline-primary">Book Now</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 animation-fadeInUp" style="animation-delay: 0.2s;">
                    <div class="card service-card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-stars text-primary" style="font-size: 48px;"></i>
                            </div>
                            <h3 class="fs-4 mb-3">Dry Cleaning</h3>
                            <p class="text-muted mb-4">We offer dry cleaning services for delicate fabrics or clothes. This also comes with stain removal.</p>
                            <a href="booking.php" class="btn btn-outline-primary">Book Now</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 animation-fadeInUp" style="animation-delay: 0.3s;">
                    <div class="card service-card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-truck text-primary" style="font-size: 48px;"></i>
                            </div>
                            <h3 class="fs-4 mb-3">Pickup And Delivery</h3>
                            <p class="text-muted mb-4">Say goodbye to the hassle of washing your own clothes and let us take care of everything for you.</p>
                            <a href="booking.php" class="btn btn-outline-primary">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Featured Products Section -->
    <section class="featured-products py-5 mb-5">
        <div class="container">
            <div class="text-center mb-5 animation-fadeInUp">
                <h2 class="display-6 fw-bold section-title">Laundry Items</h2>
                <p class="lead text-muted">Check out our laundry options for different types of clothes</p>
            </div>

            <!-- Loading indicator -->
            <div id="productsLoading" class="text-center py-4">
                <div class="loader"></div>
                <p class="mt-3">Loading laundry items...</p>
            </div>

            <div class="row g-4" id="productsContainer">
                <!-- Product items will be loaded here -->
            </div>

            <div class="text-center mt-4 animation-fadeInUp" style="animation-delay: 0.5s;">
                <a href="booking.php" class="btn btn-primary">View All Items</a>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="testimonials bg-light py-5 mb-5">
        <div class="container">
            <div class="text-center mb-5 animation-fadeInUp">
                <h2 class="display-6 fw-bold section-title">What Our Customers Say</h2>
                <p class="lead text-muted">Don't just take our word for it</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4 animation-fadeInUp" style="animation-delay: 0.1s;">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="mb-3 text-warning">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <p class="card-text mb-4">"Wow, your laundry services are a lifesaver! I've never seen my clothes so clean and fresh before. The convenience of being able to drop off my dirty laundry and pick it up perfectly folded and smelling amazing is unbeatable. Keep up the great work!"</p>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="testimonial-avatar">
                                        <span>RM</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">Richard Maina</h5>
                                    <small class="text-muted">Regular Customer</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 animation-fadeInUp" style="animation-delay: 0.2s;">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="mb-3 text-warning">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                            </div>
                            <p class="card-text mb-4">"I've been using this service for months now and it has completely changed my laundry routine. The app is so easy to use, and the pickup and delivery options are super convenient. My clothes always come back perfectly clean. Highly recommend!"</p>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="testimonial-avatar">
                                        <span>EM</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">Eva Mwangi</h5>
                                    <small class="text-muted">Busy Professional</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 animation-fadeInUp" style="animation-delay: 0.3s;">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="mb-3 text-warning">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <p class="card-text mb-4">"As a university student, doing laundry was always a hassle. Discovering this service has been game-changing. Affordable prices, quick turnaround, and excellent customer service. I'm never going back to doing my own laundry again!"</p>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="testimonial-avatar">
                                        <span>OM</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">Owen Mworia</h5>
                                    <small class="text-muted">Student</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta py-5 mb-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 animation-fadeInUp">
                    <div class="card border-0 shadow">
                        <div class="card-body p-5 text-center">
                            <h2 class="display-6 fw-bold mb-3">Ready to simplify your laundry routine?</h2>
                            <p class="lead mb-4">Join thousands of satisfied customers who have made laundry day stress-free.</p>
                            <a href="booking.php" class="btn btn-primary btn-lg">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check for dark mode preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
        
        // Listen for changes in dark mode preference
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
            if (event.matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
        
        // Sample product data
        const products = [
            <?php 
            // First add products from database if available
            if (!empty($products)):
                foreach ($products as $index => $product): 
                    // Add a featured badge to some products
                    $featured = ($index % 4 === 0) ? 'true' : 'false';
            ?>
            {
                id: <?php echo $product['id']; ?>,
                name: "<?php echo addslashes($product['name']); ?>",
                price: <?php echo (float)$product['price']; ?>,
                image: "<?php echo !empty($product['image']) ? 'uploaded_images/' . $product['image'] : ''; ?>",
                featured: <?php echo $featured; ?>
            }
            <?php 
                endforeach; 
            endif; 
            ?>
            // Additional sample products in case the database doesn't have enough
            {id: 1001, name: "T-shirt Wash", price: 200.00, image: "", featured: true},
            {id: 1002, name: "Suit Cleaning", price: 500.00, image: "", featured: false},
            {id: 1003, name: "Dress Cleaning", price: 350.00, image: "", featured: false},
            {id: 1004, name: "Curtains (per pair)", price: 400.00, image: "", featured: true},
            {id: 1005, name: "Bedsheets (Single)", price: 250.00, image: "", featured: false},
            {id: 1006, name: "Bedsheets (Double)", price: 350.00, image: "", featured: false},
            {id: 1007, name: "Jeans", price: 300.00, image: "", featured: true},
            {id: 1008, name: "Winter Coat", price: 600.00, image: "", featured: false},
            {id: 1009, name: "Silk Blouse", price: 400.00, image: "", featured: false},
            {id: 1010, name: "Bath Towels (Set of 3)", price: 300.00, image: "", featured: true},
            {id: 1011, name: "Designer Dress", price: 800.00, image: "", featured: false},
            {id: 1012, name: "Formal Shirts", price: 250.00, image: "", featured: false}
        ];
        
        // Function to create product card HTML
        function createProductCardHTML(product) {
            return `
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 animation-fadeInUp" style="animation-delay: 0.${Math.floor(Math.random() * 5) + 1}s;">
                    <div class="card product-card h-100 border-0 shadow-sm">
                        ${product.featured ? '<span class="featured-badge">Featured</span>' : ''}
                        ${product.image ? 
                            `<img src="${product.image}" class="card-img-top product-image" alt="${product.name}">` : 
                            `<div class="text-center p-3">
                                <i class="bi bi-basket2-fill text-primary" style="font-size: 64px;"></i>
                            </div>`
                        }
                        <div class="card-body text-center">
                            <h5 class="card-title">${product.name}</h5>
                            <p class="card-text text-primary fw-bold">Ksh ${product.price.toFixed(2)}</p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Function to load products with animation
        function loadProducts() {
            const container = document.getElementById('productsContainer');
            const loader = document.getElementById('productsLoading');
            
            // Show loader
            loader.style.display = 'block';
            container.innerHTML = '';
            
            // Simulate loading delay
            setTimeout(() => {
                // Hide loader
                loader.style.display = 'none';
                
                // Get 6 random products
                const shuffled = [...products].sort(() => 0.5 - Math.random());
                const selectedProducts = shuffled.slice(0, 6);
                
                // Add products to container
                let productsHTML = '';
                selectedProducts.forEach(product => {
                    productsHTML += createProductCardHTML(product);
                });
                
                container.innerHTML = productsHTML;
            }, 800);
        }
        
        // Load products when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
            
            // Refresh products every 30 seconds for demo purposes
            setInterval(loadProducts, 30000);
        });
    </script>
</body>
</html>
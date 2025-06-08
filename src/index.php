<?php

require_once 'config/database.php';
require_once 'classes/Pizza.php';
require_once 'includes/functions.php';

startSession();

$database = new Database();
$db = $database->getConnection();
$pizza = new Pizza($db);

// Get featured pizzas (first 6 pizzas for homepage)
$featured_pizzas = array_slice($pizza->getAllPizzas(), 0, 6);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crust Pizza - Gourmet Pizza Delivered</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Enhanced Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice"></i>
                <p><a href="index.php" style="text-decoration: none; color: inherit;">Crust Pizza</a></p>
            </div>
            <button class="nav-toggle" onclick="toggleNavMenu()" aria-label="Toggle Navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="nav-menu" id="navMenu">
                <a href="index.php" class="nav-link active">Home</a>
                <a href="menu.php" class="nav-link">Menu</a>
                <a href="build-pizza.php" class="nav-link">Build Your Pizza</a>
                <a href="track-order.php" class="nav-link">Track Order</a>
                <div class="dropdown">
                    <button class="dropdown-toggle" onclick="toggleDropdown()" aria-label="User Menu" aria-expanded="false" title="User Menu">
                        <span class="user-icon"><i class="fas fa-user"></i></span>
                        <span class="dropdown-arrow"></span>
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <?php if (isLoggedIn()): ?>
                            <a class="dropdown-item" href="profile.php">Profile</a>
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        <?php else: ?>
                            <a class="dropdown-item" href="login.php">Login</a>
                            <a class="dropdown-item" href="signup.php">Sign Up</a>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="cart.php" class="nav-link cart-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Enhanced Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Gourmet Pizza Perfection</h1>
            <p>Experience award-winning flavors crafted with premium ingredients and innovative recipes. From our iconic Peri Peri Chicken to our plant-based masterpieces - every bite tells a story of culinary excellence.</p>
            <div class="hero-buttons">
                <a href="menu.php" class="btn btn-primary">Order Now</a>
                <a href="build-pizza.php" class="btn btn-secondary">Create Your Own</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1506354666786-959d6d497f1a?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MzV8fHBpenphfGVufDB8fDB8fHww" alt="Delicious Crust Pizza" />
        </div>
    </section>

    <!-- Featured Pizzas Section -->
    <section class="featured-pizzas">
        <div class="container">
            <div class="section-header">
                <h2>Signature Creations</h2>
                <p class="section-subtitle">Discover our award-winning pizzas that have made Crust Pizza a household name across Australia. Each recipe is crafted with passion and perfected over years of culinary excellence.</p>
            </div>

            <div class="pizza-grid">
                <?php if (!empty($featured_pizzas)): ?>
                    <?php foreach ($featured_pizzas as $index => $pizza_item): ?>
                        <?php
                        // Ensure index is an integer
                        $index = (int)$index;

                        // Ensure we have all required fields with default values if missing
                        $pizza_id = isset($pizza_item['pizza_id']) ? $pizza_item['pizza_id'] : 0;
                        $name = isset($pizza_item['name']) ? htmlspecialchars($pizza_item['name']) : 'Pizza';
                        $description = isset($pizza_item['description']) ? htmlspecialchars($pizza_item['description']) : 'Delicious pizza with our signature sauce and cheese.';
                        $image_url = isset($pizza_item['image_url']) && !empty($pizza_item['image_url']) ? $pizza_item['image_url'] : '/placeholder.svg?height=280&width=400';

                        // Set default prices if not available
                        $price_small = isset($pizza_item['base_price_small']) ? (float)$pizza_item['base_price_small'] : 15.90;
                        $price_medium = isset($pizza_item['base_price_medium']) ? (float)$pizza_item['base_price_medium'] : 21.90;
                        $price_large = isset($pizza_item['base_price_large']) ? (float)$pizza_item['base_price_large'] : 27.90;

                        // Add badges for first few pizzas
                        $badges = ['Award Winner', 'Popular', 'Chef\'s Choice', 'Customer Favorite', 'New', 'Signature'];
                        $badge_index = $index % count($badges);
                        $badge = $badges[$badge_index];
                        ?>
                        <div class="pizza-card fade-in-up" style="animation-delay: <?php echo $index * 0.2; ?>s;" data-price-small="<?php echo $price_small; ?>" data-price-medium="<?php echo $price_medium; ?>" data-price-large="<?php echo $price_large; ?>">
                            <div class="pizza-image">
                                <img src="<?php echo $image_url; ?>" alt="<?php echo $name; ?>">
                                <div class="pizza-badge"><?php echo $badge; ?></div>
                            </div>
                            <div class="pizza-info">
                                <h3><?php echo $name; ?></h3>
                                <p><?php echo $description; ?></p>
                                <div class="pizza-prices">
                                    <div class="price-item">
                                        <span class="price-label">Small</span>
                                        <span class="price-value"><?php echo formatCurrency($price_small); ?></span>
                                    </div>
                                    <div class="price-item">
                                        <span class="price-label">Medium</span>
                                        <span class="price-value"><?php echo formatCurrency($price_medium); ?></span>
                                    </div>
                                    <div class="price-item">
                                        <span class="price-label">Large</span>
                                        <span class="price-value"><?php echo formatCurrency($price_large); ?></span>
                                    </div>
                                </div>
                                <div class="pizza-actions">
                                    <a href="pizza-details.php?id=<?php echo $pizza_id; ?>" class="btn btn-outline">View Details</a>
                                    <button class="btn btn-add-cart" onclick="addToCart(<?php echo $pizza_id; ?>, 'medium')">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback if no pizzas found -->
                    <div class="text-center" style="grid-column: 1 / -1; padding: 40px;">
                        <h3>No featured pizzas available</h3>
                        <p>Check back soon for our signature creations!</p>
                        <a href="menu.php" class="btn btn-primary">Browse Full Menu</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="text-center" style="margin-top: 3rem;">
                <a href="menu.php" class="btn btn-primary" style="padding: 18px 50px; font-size: 1.2rem;">
                    <i class="fas fa-utensils"></i> Explore Full Menu
                </a>
            </div>
        </div>
    </section>

    <!-- Enhanced Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-header">
                <h2 style="color: white;">Why Choose Crust Pizza?</h2>
                <p class="section-subtitle" style="color: rgba(255, 255, 255, 0.9);">Over 20 years of pizza perfection with 130+ stores across Australia. We're not just making pizza - we're crafting experiences.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-rocket"></i>
                    <h3>Lightning Fast Delivery</h3>
                    <p>Fresh, hot pizzas delivered in 30 minutes or less, guaranteed. Track your order in real-time from kitchen to your door.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-award"></i>
                    <h3>Award-Winning Recipes</h3>
                    <p>International Pizza Award winners with signature flavors you won't find anywhere else. Taste the difference quality makes.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-seedling"></i>
                    <h3>Premium Ingredients</h3>
                    <p>Locally sourced, fresh ingredients with options for everyone - gluten-free, low-carb, vegan, and healthy choice ranges.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Smart Ordering</h3>
                    <p>Seamless online experience with easy customization, saved favorites, and intelligent recommendations just for you.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" style="padding: 100px 20px; background: #f8f9fa;">
        <div class="container">
            <div class="section-header">
                <h2>Our Story Since 2001</h2>
                <p class="section-subtitle">From a single store dream to Australia's gourmet pizza leader</p>
            </div>

            <div class="about-content" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 50px; align-items: center;">
                <div class="about-text">
                    <div class="timeline-item" style="margin-bottom: 40px; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-left: 5px solid #ff6b35;">
                        <h3 style="color: #ff6b35; margin-bottom: 15px; font-size: 1.5rem;">The Beginning (2001)</h3>
                        <p style="line-height: 1.6; color: #666;">Born from a dream to make gourmet pizzas accessible to everyone, our first Crust Pizza store opened in Annandale, New South Wales. What started as a simple vision has grown into something extraordinary.</p>
                    </div>

                    <div class="timeline-item" style="margin-bottom: 40px; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-left: 5px solid #f7931e;">
                        <h3 style="color: #f7931e; margin-bottom: 15px; font-size: 1.5rem;">Expansion & Innovation</h3>
                        <p style="line-height: 1.6; color: #666;">Our second store in Richmond, Victoria marked the beginning of our national expansion. Today, we operate 130+ stores across Australia, each maintaining our commitment to gourmet excellence and innovative flavors.</p>
                    </div>

                    <div class="timeline-item" style="padding: 30px; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-left: 5px solid #ff6b35;">
                        <h3 style="color: #ff6b35; margin-bottom: 15px; font-size: 1.5rem;">Award-Winning Excellence</h3>
                        <p style="line-height: 1.6; color: #666;">Our innovative approach has earned us International Pizza Awards, with signature flavors like Peri Peri Chicken becoming household favorites. We continue leading industry trends with plant-based options and healthy alternatives.</p>
                    </div>
                </div>

                <div class="about-image" style="text-align: center;">
                    <img src="https://images.unsplash.com/photo-1571407970349-bc81e7e96d47?w=500&h=600&fit=crop&crop=center" alt="Crust Pizza Story" style="border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); max-width: 100%; height: auto;">
                </div>
            </div>

            <!-- Awards Section -->
            <div class="awards-section" style="margin-top: 80px; text-align: center;">
                <h3 style="font-size: 2.5rem; margin-bottom: 40px; color: #333;">Industry Recognition</h3>
                <div class="awards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                    <div class="award-item" style="padding: 30px; background: linear-gradient(135deg, #ff6b35, #f7931e); color: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(255,107,53,0.3);">
                        <i class="fas fa-trophy" style="font-size: 3rem; margin-bottom: 20px;"></i>
                        <h4 style="margin-bottom: 10px;">International Pizza Awards</h4>
                        <p>Multiple wins for innovative flavors</p>
                    </div>
                    <div class="award-item" style="padding: 30px; background: linear-gradient(135deg, #28a745, #20c997); color: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(40,167,69,0.3);">
                        <i class="fas fa-leaf" style="font-size: 3rem; margin-bottom: 20px;"></i>
                        <h4 style="margin-bottom: 10px;">Vegan Nourish Awards</h4>
                        <p>2022 Finalist for Plant-Based Excellence</p>
                    </div>
                    <div class="award-item" style="padding: 30px; background: linear-gradient(135deg, #6f42c1, #e83e8c); color: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(111,66,193,0.3);">
                        <i class="fas fa-star" style="font-size: 3rem; margin-bottom: 20px;"></i>
                        <h4 style="margin-bottom: 10px;">QSR Industry Leader</h4>
                        <p>First to offer comprehensive healthy options</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Innovation Timeline -->
    <section class="innovation-timeline" style="padding: 100px 20px; background: white; color: black;">
        <div class="container">
            <div class="section-header">
                <h2 style="color: black;">Innovation Timeline</h2>
                <p class="section-subtitle" style="color: black;">Leading the industry with groundbreaking offerings</p>
            </div>

            <div class="timeline" style="position: relative; max-width: 800px; margin: 0 auto;">
                <!-- Timeline Line -->
                <div style="position: absolute; left: 50%; top: 0; bottom: 0; width: 4px; background: linear-gradient(180deg, #ff6b35, #f7931e); transform: translateX(-50%);"></div>

                <!-- Timeline Items -->
                <div class="timeline-items">
                    <div class="timeline-item-horizontal" style="display: flex; align-items: center; margin-bottom: 60px; position: relative;">
                        <div style="flex: 1; text-align: right; padding-right: 40px;">
                            <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 25px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.2);">
                                <h4 style="color: #ff6b35; margin-bottom: 10px; font-size: 1.3rem;">2001</h4>
                                <h5 style="margin-bottom: 15px;">First Store Opens</h5>
                                <p style="opacity: 0.9; line-height: 1.5;">Annandale, NSW - Where it all began</p>
                            </div>
                        </div>
                        <div style="width: 20px; height: 20px; background: #ff6b35; border-radius: 50%; position: relative; z-index: 2; border: 4px solid white;" class="timeline-dot"></div>
                        <div style="flex: 1; padding-left: 40px;"></div>
                    </div>

                    <div class="timeline-item-horizontal" style="display: flex; align-items: center; margin-bottom: 60px; position: relative;">
                        <div style="flex: 1; padding-right: 40px;"></div>
                        <div style="width: 20px; height: 20px; background: #f7931e; border-radius: 50%; position: relative; z-index: 2; border: 4px solid white;" class="timeline-dot"></div>
                        <div style="flex: 1; text-align: left; padding-left: 40px;">
                            <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 25px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.2);">
                                <h4 style="color: #f7931e; margin-bottom: 10px; font-size: 1.3rem;">Early 2000s</h4>
                                <h5 style="margin-bottom: 15px;">Gluten Free & Low Carb Pioneer</h5>
                                <p style="opacity: 0.9; line-height: 1.5;">First QSR to offer comprehensive dietary alternatives</p>
                            </div>
                        </div>
                    </div>

                    <div class="timeline-item-horizontal" style="display: flex; align-items: center; margin-bottom: 60px; position: relative;">
                        <div style="flex: 1; text-align: right; padding-right: 40px;">
                            <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 25px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.2);">
                                <h4 style="color: #28a745; margin-bottom: 10px; font-size: 1.3rem;">2015</h4>
                                <h5 style="margin-bottom: 15px;">Healthy Choice Range</h5>
                                <p style="opacity: 0.9; line-height: 1.5;">Introducing nutritious options without compromising taste</p>
                            </div>
                        </div>
                        <div style="width: 20px; height: 20px; background: #28a745; border-radius: 50%; position: relative; z-index: 2; border: 4px solid white;" class="timeline-dot"></div>
                        <div style="flex: 1; padding-left: 40px;"></div>
                    </div>

                    <div class="timeline-item-horizontal" style="display: flex; align-items: center; margin-bottom: 60px; position: relative;">
                        <div style="flex: 1; padding-right: 40px;"></div>
                        <div style="width: 20px; height: 20px; background: #6f42c1; border-radius: 50%; position: relative; z-index: 2; border: 4px solid white;" class="timeline-dot"></div>
                        <div style="flex: 1; text-align: left; padding-left: 40px;">
                            <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 25px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.2);">
                                <h4 style="color: #6f42c1; margin-bottom: 10px; font-size: 1.3rem;">2016</h4>
                                <h5 style="margin-bottom: 15px;">Vegan Cheese Revolution</h5>
                                <p style="opacity: 0.9; line-height: 1.5;">Leading the plant-based movement in QSR</p>
                            </div>
                        </div>
                    </div>

                    <div class="timeline-item-horizontal" style="display: flex; align-items: center; position: relative;">
                        <div style="flex: 1; text-align: right; padding-right: 40px;">
                            <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 25px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.2);">
                                <h4 style="color: #e83e8c; margin-bottom: 10px; font-size: 1.3rem;">2022</h4>
                                <h5 style="margin-bottom: 15px;">Vegan Nourish Awards Finalist</h5>
                                <p style="opacity: 0.9; line-height: 1.5;">Recognized for Plant-Based Protein leadership</p>
                            </div>
                        </div>
                        <div style="width: 20px; height: 20px; background: #e83e8c; border-radius: 50%; position: relative; z-index: 2; border: 4px solid white;" class="timeline-dot"></div>
                        <div style="flex: 1; padding-left: 40px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Crust Pizza</h3>
                    <p>Australia's favorite gourmet pizza destination since 2001. From our family to yours, we're committed to delivering exceptional taste and quality in every bite.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="menu.php"><i class="fas fa-pizza-slice"></i> Our Menu</a></li>
                        <li><a href="build-pizza.php"><i class="fas fa-tools"></i> Build Your Pizza</a></li>
                        <li><a href="track-order.php"><i class="fas fa-truck"></i> Track Your Order</a></li>
                        <li><a href="locations.php"><i class="fas fa-map-marker-alt"></i> Find a Store</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Customer Care</h4>
                    <ul>
                        <li><a href="#"><i class="fas fa-phone"></i> Contact Us</a></li>
                        <li><a href="#"><i class="fas fa-question-circle"></i> FAQ</a></li>
                        <li><a href="#"><i class="fas fa-comment"></i> Feedback</a></li>
                        <li><a href="#"><i class="fas fa-file-contract"></i> Terms & Conditions</a></li>
                        <li><a href="#"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> <strong>1300 278 787</strong></li>
                        <li><i class="fas fa-envelope"></i> info@crustpizza.com.au</li>
                        <li><i class="fas fa-clock"></i> Mon-Sun: 11AM - 11PM</li>
                        <li><i class="fas fa-map-marker-alt"></i> 130+ locations across Australia</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>© 2024 Crust Pizza. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update cart count on page load
            updateCartCount();

            // Add scroll effect to navbar
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                    navbar.style.boxShadow = '0 4px 25px rgba(0, 0, 0, 0.15)';
                } else {
                    navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                    navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
                }
            });

            // Add loading animation to Add to Cart buttons
            document.querySelectorAll('.btn-add-cart').forEach(button => {
                button.addEventListener('click', function() {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                    this.disabled = true;

                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }, 1000);
                });
            });

            // Intersection Observer for fade-in animations
            if ('IntersectionObserver' in window) {
                const observerOptions = {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                };

                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }
                    });
                }, observerOptions);

                document.querySelectorAll('.fade-in-up').forEach(el => {
                    observer.observe(el);
                });
            }
        });

        function toggleDropdown() {
            const dropdownMenu = document.getElementById('dropdownMenu');
            const isOpen = dropdownMenu.classList.toggle('show');
            document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', isOpen);
        }

        function toggleNavMenu() {
            const navMenu = document.getElementById('navMenu');
            navMenu.classList.toggle('active');
        }

        // Close dropdown and nav menu when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.querySelector('.dropdown');
            const dropdownMenu = document.getElementById('dropdownMenu');
            const navMenu = document.getElementById('navMenu');
            const navToggle = document.querySelector('.nav-toggle');
            if (!dropdown.contains(event.target) && !navToggle.contains(event.target)) {
                dropdownMenu.classList.remove('show');
                navMenu.classList.remove('active');
                document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
            }
        });

        // Close dropdown and nav menu on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.getElementById('dropdownMenu').classList.remove('show');
                document.getElementById('navMenu').classList.remove('active');
                document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
            }
        });

        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
            const cartCount = cart.reduce((total, item) => total + (item.quantity || 1), 0);
            document.getElementById('cartCount').textContent = cartCount;
        }

        function addToCart(pizzaId, size = "medium", quantity = 1) {
            // Check if user is logged in first
            if (!isUserLoggedIn()) {
                showNotification("Please log in to add items to your cart", "warning");
                return false;
            }

            // Get pizza details from the card
            const pizzaCard = document.querySelector(`[data-price-small][data-price-medium][data-price-large]`);
            if (!pizzaCard) {
                showNotification('Pizza details not found', 'error');
                return false;
            }

            const pizzaName = pizzaCard.querySelector('h3').textContent;
            const priceData = {
                small: parseFloat(pizzaCard.dataset.priceSmall),
                medium: parseFloat(pizzaCard.dataset.priceMedium),
                large: parseFloat(pizzaCard.dataset.priceLarge)
            };

            const cartItem = {
                pizza_id: pizzaId,
                name: pizzaName,
                size: size,
                price: priceData[size],
                quantity: quantity,
                item_type: 'pizza',
                custom_ingredients: null,
                special_instructions: null
            };

            const userId = getUserId();
            if (!userId) {
                showNotification('User session not found. Please log in again.', 'error');
                return false;
            }

            const data = {
                ...cartItem,
                user_id: userId,
                csrf_token: getCSRFToken()
            };

            fetch('api/cart_api.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        updateCartCount();
                        showNotification(`${pizzaName} (${size}) added to cart!`, 'success');
                    } else {
                        showNotification(result.message || 'Failed to add item to cart', 'error');
                    }
                })
                .catch(error => {
                    showNotification('An error occurred while adding to cart', 'error');
                    console.error('Error adding to cart:', error);
                });

            return true;
        }

        function isUserLoggedIn() {
            // Check if logout link exists (indicates user is logged in)
            const logoutLink = document.querySelector('a[href="logout.php"]');
            const loginLink = document.querySelector('a.dropdown-item[href="login.php"]');

            // User is logged in if logout link exists and login link doesn't
            return logoutLink !== null && loginLink === null;
        }

        function showNotification(message, type = "info") {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll(".cart-notification");
            existingNotifications.forEach((notification) => notification.remove());

            // Create notification element
            const notification = document.createElement("div");
            notification.className = `cart-notification ${type}`;

            // Set icon based on notification type
            let icon = 'info-circle';
            if (type === 'success') icon = 'check-circle';
            if (type === 'error') icon = 'exclamation-circle';
            if (type === 'warning') icon = 'exclamation-triangle';

            notification.innerHTML = `
                <i class="fas fa-${icon}"></i> 
                ${message}
            `;

            document.body.appendChild(notification);

            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.classList.add("slide-out");
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }, 3000);
        }

        function getUserId() {
            // Retrieve user ID from session or localStorage
            // This is a placeholder - implement your actual logic
            return 1; // Example: return a hardcoded user ID
        }

        function getCSRFToken() {
            // Retrieve CSRF token from a hidden field or session
            // This is a placeholder - implement your actual logic
            return 'your_csrf_token'; // Example: return a hardcoded token
        }
    </script>
    <style>
        /* Cart notification styles */
        .cart-notification {
            position: fixed;
            top: 100px;
            right: 20px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
            z-index: 9999;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
        }

        .cart-notification.error {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
        }

        .cart-notification.warning {
            background: linear-gradient(135deg, #ffc107, #f39c12);
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
        }

        .cart-notification.info {
            background: linear-gradient(135deg, #17a2b8, #3498db);
            box-shadow: 0 8px 25px rgba(23, 162, 184, 0.4);
        }

        .cart-notification.slide-out {
            animation: slideOut 0.3s ease-in;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Fade-in animation for pizza cards */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
    </style>
</body>

</html>
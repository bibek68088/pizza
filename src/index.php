<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crust Pizza - Gourmet Pizza Delivered</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <!-- Enhanced Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice"></i>
                <p><a href="index.php" style="text-decoration: none; color: inherit;">Crust Pizza</a></p>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link active">Home</a>
                <a href="menu.php" class="nav-link">Menu</a>
                <a href="build-pizza.php" class="nav-link">Build Your Pizza</a>
                <a href="track-order.php" class="nav-link">Track Order</a>
                <a href="profile.php" class="nav-link">Profile</a>
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
                <!-- Pizza Card 1 -->
                <div class="pizza-card fade-in-up">
                    <div class="pizza-image">
                        <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=400&h=280&fit=crop&crop=center" alt="Peri Peri Chicken Pizza">
                        <div class="pizza-badge">Award Winner</div>
                    </div>
                    <div class="pizza-info">
                        <h3>Peri Peri Chicken</h3>
                        <p>Our signature pizza featuring tender chicken, roasted capsicum, red onion, and our famous peri peri sauce on a crispy base with mozzarella.</p>
                        <div class="pizza-prices">
                            <div class="price-item">
                                <span class="price-label">Small</span>
                                <span class="price-value">$18.90</span>
                            </div>
                            <div class="price-item">
                                <span class="price-label">Medium</span>
                                <span class="price-value">$24.90</span>
                            </div>
                            <div class="price-item">
                                <span class="price-label">Large</span>
                                <span class="price-value">$32.90</span>
                            </div>
                        </div>
                        <div class="pizza-actions">
                            <a href="pizza-details.php?id=1" class="btn btn-outline">View Details</a>
                            <button class="btn btn-add-cart" onclick="addToCart(1, 'medium')">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pizza Card 2 -->
                <div class="pizza-card fade-in-up" style="animation-delay: 0.2s;">
                    <div class="pizza-image">
                        <img src="https://images.unsplash.com/photo-1571407970349-bc81e7e96d47?w=400&h=280&fit=crop&crop=center" alt="Mediterranean Delight">
                        <div class="pizza-badge">Popular</div>
                    </div>
                    <div class="pizza-info">
                        <h3>Mediterranean Delight</h3>
                        <p>A fresh combination of sun-dried tomatoes, olives, feta cheese, spinach, and herbs on our signature thin crust.</p>
                        <div class="pizza-prices">
                            <div class="price-item">
                                <span class="price-label">Small</span>
                                <span class="price-value">$17.90</span>
                            </div>
                            <div class="price-item">
                                <span class="price-label">Medium</span>
                                <span class="price-value">$23.90</span>
                            </div>
                            <div class="price-item">
                                <span class="price-label">Large</span>
                                <span class="price-value">$31.90</span>
                            </div>
                        </div>
                        <div class="pizza-actions">
                            <a href="pizza-details.php?id=2" class="btn btn-outline">View Details</a>
                            <button class="btn btn-add-cart" onclick="addToCart(2, 'medium')">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pizza Card 3 -->
                <div class="pizza-card fade-in-up" style="animation-delay: 0.4s;">
                    <div class="pizza-image">
                        <img src="https://images.unsplash.com/photo-1595708684082-a173bb3a06c5?w=400&h=280&fit=crop&crop=center" alt="Plant-Based Supreme">
                        <div class="pizza-badge">Vegan</div>
                    </div>
                    <div class="pizza-info">
                        <h3>Plant-Based Supreme</h3>
                        <p>Our innovative vegan pizza with plant-based pepperoni, mushrooms, capsicum, and dairy-free cheese on a wholesome base.</p>
                        <div class="pizza-prices">
                            <div class="price-item">
                                <span class="price-label">Small</span>
                                <span class="price-value">$19.90</span>
                            </div>
                            <div class="price-item">
                                <span class="price-label">Medium</span>
                                <span class="price-value">$25.90</span>
                            </div>
                            <div class="price-item">
                                <span class="price-label">Large</span>
                                <span class="price-value">$33.90</span>
                            </div>
                        </div>
                        <div class="pizza-actions">
                            <a href="pizza-details.php?id=3" class="btn btn-outline">View Details</a>
                            <button class="btn btn-add-cart" onclick="addToCart(3, 'medium')">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="menu.php" class="btn btn-primary">Explore Full Menu</a>
            </div>
        </div>
    </section>

    <!-- Enhanced Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose Crust Pizza?</h2>
                <p class="section-subtitle">Over 20 years of pizza perfection with 130+ stores across Australia. We're not just making pizza - we're crafting experiences.</p>
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
    <section class="about">
        <div class="container">
            <div class="section-header">
                <h2>Our Story Since 2001</h2>
                <p class="section-subtitle">From a single store dream to Australia's gourmet pizza leader</p>
            </div>

            <div class="about-content">
                <div class="about-text">
                    <div class="timeline-item">
                        <h3>The Beginning (2001)</h3>
                        <p>Born from a dream to make gourmet pizzas accessible to everyone, our first Crust Pizza store opened in Annandale, New South Wales. What started as a simple vision has grown into something extraordinary.</p>
                    </div>

                    <div class="timeline-item">
                        <h3>Expansion & Innovation</h3>
                        <p>Our second store in Richmond, Victoria marked the beginning of our national expansion. Today, we operate 130+ stores across Australia, each maintaining our commitment to gourmet excellence and innovative flavors.</p>
                    </div>

                    <div class="timeline-item">
                        <h3>Award-Winning Excellence</h3>
                        <p>Our innovative approach has earned us International Pizza Awards, with signature flavors like Peri Peri Chicken becoming household favorites. We continue leading industry trends with plant-based options and healthy alternatives.</p>
                    </div>
                </div>

                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1571407970349-bc81e7e96d47?w=500&h=600&fit=crop&crop=center" alt="Crust Pizza Story">
                </div>
            </div>

            <!-- Awards Section -->
            <div class="awards-section">
                <h3>Industry Recognition</h3>
                <div class="awards-grid">
                    <div class="award-item">
                        <i class="fas fa-trophy"></i>
                        <h4>International Pizza Awards</h4>
                        <p>Multiple wins for innovative flavors</p>
                    </div>
                    <div class="award-item">
                        <i class="fas fa-leaf"></i>
                        <h4>Vegan Nourish Awards</h4>
                        <p>2022 Finalist for Plant-Based Excellence</p>
                    </div>
                    <div class="award-item">
                        <i class="fas fa-star"></i>
                        <h4>QSR Industry Leader</h4>
                        <p>First to offer comprehensive healthy options</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Innovation Timeline -->
    <section class="innovation-timeline">
        <div class="container">
            <div class="section-header">
                <h2>Innovation Timeline</h2>
                <p class="section-subtitle">Leading the industry with groundbreaking offerings</p>
            </div>

            <div class="timeline">
                <div class="timeline-items">
                    <div class="timeline-item-horizontal">
                        <div>
                            <div>
                                <h4>2001</h4>
                                <h5>First Store Opens</h5>
                                <p>Annandale, NSW - Where it all began</p>
                            </div>
                        </div>
                        <div></div>
                        <div></div>
                    </div>

                    <div class="timeline-item-horizontal">
                        <div></div>
                        <div></div>
                        <div>
                            <div>
                                <h4>Early 2000s</h4>
                                <h5>Gluten Free & Low Carb Pioneer</h5>
                                <p>First QSR to offer comprehensive dietary alternatives</p>
                            </div>
                        </div>
                    </div>

                    <div class="timeline-item-horizontal">
                        <div>
                            <div>
                                <h4>2015</h4>
                                <h5>Healthy Choice Range</h5>
                                <p>Introducing nutritious options without compromising taste</p>
                            </div>
                        </div>
                        <div></div>
                        <div></div>
                    </div>

                    <div class="timeline-item-horizontal">
                        <div></div>
                        <div></div>
                        <div>
                            <div>
                                <h4>2016</h4>
                                <h5>Vegan Cheese Revolution</h5>
                                <p>Leading the plant-based movement in QSR</p>
                            </div>
                        </div>
                    </div>

                    <div class="timeline-item-horizontal">
                        <div>
                            <div>
                                <h4>2022</h4>
                                <h5>Vegan Nourish Awards Finalist</h5>
                                <p>Recognized for Plant-Based Protein leadership</p>
                            </div>
                        </div>
                        <div></div>
                        <div></div>
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
                        <li><a href="careers.php"><i class="fas fa-briefcase"></i> Careers</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Customer Care</h4>
                    <ul>
                        <li><a href="contact.php"><i class="fas fa-phone"></i> Contact Us</a></li>
                        <li><a href="faq.php"><i class="fas fa-question-circle"></i> FAQ</a></li>
                        <li><a href="feedback.php"><i class="fas fa-comment"></i> Feedback</a></li>
                        <li><a href="terms.php"><i class="fas fa-file-contract"></i> Terms & Conditions</a></li>
                        <li><a href="privacy.php"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
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

    <script>
        let cart = [];

        function addToCart(pizzaId, size) {
            const pizzas = {
                1: {
                    name: 'Peri Peri Chicken',
                    prices: {
                        small: 18.90,
                        medium: 24.90,
                        large: 32.90
                    }
                },
                2: {
                    name: 'Mediterranean Delight',
                    prices: {
                        small: 17.90,
                        medium: 23.90,
                        large: 31.90
                    }
                },
                3: {
                    name: 'Plant-Based Supreme',
                    prices: {
                        small: 19.90,
                        medium: 25.90,
                        large: 33.90
                    }
                }
            };

            const pizza = pizzas[pizzaId];
            if (pizza) {
                const item = {
                    id: pizzaId,
                    name: pizza.name,
                    size: size,
                    price: pizza.prices[size],
                    quantity: 1
                };

                const existingItem = cart.find(cartItem =>
                    cartItem.id === pizzaId && cartItem.size === size
                );

                if (existingItem) {
                    existingItem.quantity += 1;
                } else {
                    cart.push(item);
                }

                updateCartCount();
                showAddToCartNotification(pizza.name, size);
            }
        }

        function updateCartCount() {
            const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cartCount').textContent = cartCount;
        }

        function showAddToCartNotification(pizzaName, size) {
            const notification = document.createElement('div');
            notification.className = 'cart-notification';
            notification.innerHTML = `
                <i class="fas fa-check-circle"></i> 
                ${pizzaName} (${size.charAt(0).toUpperCase() + size.slice(1)}) added to cart!
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.className = 'cart-notification slide-out';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

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
    </script>
</body>

</html>
<?php

require_once 'config/database.php';
require_once 'classes/Pizza.php';
require_once 'includes/functions.php';

startSession();

$database = new Database();
$db = $database->getConnection();
$pizza = new Pizza($db);

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : '';

// Get pizzas based on search/filter
if (!empty($search)) {
    $pizzas = $pizza->searchPizzas($search);
} elseif (!empty($category)) {
    $pizzas = $pizza->getPizzasByCategory($category);
} else {
    $pizzas = $pizza->getAllPizzas();
}

// Get categories for filter
$categories_query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Crust Pizza</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Notification styles from index.php */
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
    </style>
</head>

<body>
    <!-- Navigation from index.php -->
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
                <a href="index.php" class="nav-link">Home</a>
                <a href="menu.php" class="nav-link active">Menu</a>
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
                            <a class="dropdown-item" href="register.php">Sign Up</a>
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

    <main style="margin-top: 70px; padding: 40px 20px;">
        <div class="container">
            <h1>Our Menu</h1>

            <!-- Search and Filter -->
            <div class="menu-filters" style="margin-bottom: 30px;">
                <form method="GET" class="filter-form" style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Search pizzas..." value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="width: 300px;">
                    </div>

                    <div class="category-filter">
                        <select name="category" class="form-control" style="width: 200px;">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="menu.php" class="btn btn-outline">Clear</a>
                </form>
            </div>

            <!-- Flash Messages -->
            <?php displayFlashMessages(); ?>

            <!-- Pizza Grid -->
            <?php if (empty($pizzas)): ?>
                <div class="text-center" style="padding: 40px;">
                    <h3>No pizzas found</h3>
                    <p>Try adjusting your search or filter criteria.</p>
                    <a href="menu.php" class="btn btn-primary">View All Pizzas</a>
                </div>
            <?php else: ?>
                <div class="pizza-grid">
                    <?php foreach ($pizzas as $pizza_item): ?>
                        <?php
                        // Ensure we have all required fields with default values if missing
                        $pizza_id = isset($pizza_item['pizza_id']) ? $pizza_item['pizza_id'] : 0;
                        $name = isset($pizza_item['name']) ? htmlspecialchars($pizza_item['name']) : 'Pizza';
                        $description = isset($pizza_item['description']) ? htmlspecialchars($pizza_item['description']) : 'Delicious pizza with our signature sauce and cheese.';
                        $image_url = isset($pizza_item['image_url']) && !empty($pizza_item['image_url']) ? $pizza_item['image_url'] : '/placeholder.svg?height=250&width=300';

                        // Set default prices if not available
                        $price_small = isset($pizza_item['base_price_small']) ? (float)$pizza_item['base_price_small'] : 15.90;
                        $price_medium = isset($pizza_item['base_price_medium']) ? (float)$pizza_item['base_price_medium'] : 21.90;
                        $price_large = isset($pizza_item['base_price_large']) ? (float)$pizza_item['base_price_large'] : 27.90;
                        ?>
                        <div class="pizza-card" data-price-small="<?php echo $price_small; ?>" data-price-medium="<?php echo $price_medium; ?>" data-price-large="<?php echo $price_large; ?>">
                            <div class="pizza-image">
                                <img src="<?php echo $image_url; ?>" alt="<?php echo $name; ?>">
                            </div>
                            <div class="pizza-info">
                                <h3><?php echo $name; ?></h3>
                                <p><?php echo $description; ?></p>

                                <div class="pizza-options" style="margin-bottom: 15px;">
                                    <label for="size-<?php echo $pizza_id; ?>">Size:</label>
                                    <select id="size-<?php echo $pizza_id; ?>" class="size-selector form-control" style="width: 100%;">
                                        <option value="small">Small - <?php echo formatCurrency($price_small); ?></option>
                                        <option value="medium" selected>Medium - <?php echo formatCurrency($price_medium); ?></option>
                                        <option value="large">Large - <?php echo formatCurrency($price_large); ?></option>
                                    </select>
                                </div>

                                <div class="current-price" style="font-size: 1.2rem; font-weight: bold; color: #ff6b35; margin-bottom: 15px;">
                                    <?php echo formatCurrency($price_medium); ?>
                                </div>

                                <div class="pizza-actions">
                                    <a href="pizza-details.php?id=<?php echo $pizza_id; ?>" class="btn btn-outline">
                                        View Details
                                    </a>
                                    <button class="btn btn-primary" onclick="addToCartFromMenu(<?php echo $pizza_id; ?>)">
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer from index.php -->
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
                <p>© <span id="currentYear"></span> Crust Pizza. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();
        document.addEventListener('DOMContentLoaded', function() {
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
        });

        function addToCartFromMenu(pizzaId) {
            if (!isUserLoggedIn()) {
                showNotification("Please log in to add items to your cart", "warning");
                setTimeout(() => {
                    window.location.href = 'login.php?redirect=menu.php';
                }, 1500);
                return;
            }

            const sizeSelector = document.getElementById(`size-${pizzaId}`);
            const size = sizeSelector.value;
            addToCart(pizzaId, size, 1);
        }

        function toggleDropdown() {
            const dropdownMenu = document.getElementById('dropdownMenu');
            const isOpen = dropdownMenu.classList.toggle('show');
            document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', isOpen);
        }

        function toggleNavMenu() {
            const navMenu = document.getElementById('navMenu');
            navMenu.classList.toggle('active');
        }

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

        function isUserLoggedIn() {
            const logoutLink = document.querySelector('a[href="logout.php"]');
            const loginLink = document.querySelector('a.dropdown-item[href="login.php"]');
            return logoutLink !== null && loginLink === null;
        }

        function showNotification(message, type = "info") {
            const existingNotifications = document.querySelectorAll(".cart-notification");
            existingNotifications.forEach((notification) => notification.remove());
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
            setTimeout(() => {
                    notification.classList.add("slide-out");
                    setTimeout(() => {
                        if (notification.parentElement) {
                            notification.remove();
                        }
                    }, 300);
            }, 3000);
        }
    </script>
</body>

</html>
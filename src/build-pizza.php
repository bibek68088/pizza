<?php

/**
 * Build Your Pizza Page
 * Custom pizza builder interface
 * Crust Pizza Online Ordering System
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

startSession();

$database = new Database();
$db = $database->getConnection();

// Get ingredients by category
$ingredients_query = "SELECT * FROM ingredients WHERE is_available = 1 ORDER BY category, name";
$ingredients_stmt = $db->prepare($ingredients_query);
$ingredients_stmt->execute();
$all_ingredients = $ingredients_stmt->fetchAll(PDO::FETCH_ASSOC);

// Group ingredients by category
$ingredients = [];
foreach ($all_ingredients as $ingredient) {
    $ingredients[$ingredient['category']][] = $ingredient;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Build Your Pizza - Crust Pizza</title>
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

        /* Step number and responsive styles */
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #ff6b35;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            margin-right: 0.5rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .pizza-builder {
                grid-template-columns: 1fr !important;
            }

            .pizza-summary {
                order: -1;
            }

            .pizza-summary .card {
                position: static !important;
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
                <a href="menu.php" class="nav-link">Menu</a>
                <a href="build-pizza.php" class="nav-link active">Build Your Pizza</a>
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
            <h1><i class="fas fa-magic"></i> Build Your Perfect Pizza</h1>
            <p>Create your custom pizza with our premium ingredients</p>

            <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem; margin-top: 2rem;" class="pizza-builder">
                <!-- Pizza Builder Steps -->
                <div class="builder-steps">
                    <!-- Step 1: Size -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3 style="margin: 0;"><span class="step-number">1</span> Choose Your Size</h3>
                        </div>
                        <div class="card-body">
                            <div class="size-options" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                                <label class="size-option" style="display: block; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; text-align: center; cursor: pointer;">
                                    <input type="radio" name="pizza_size" value="small" data-price="12.90" style="margin-bottom: 0.5rem;">
                                    <div style="font-weight: 600;">Small (10")</div>
                                    <div style="color: #ff6b35; font-weight: 600;">$12.90</div>
                                </label>
                                <label class="size-option" style="display: block; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; text-align: center; cursor: pointer;">
                                    <input type="radio" name="pizza_size" value="medium" data-price="16.90" checked style="margin-bottom: 0.5rem;">
                                    <div style="font-weight: 600;">Medium (12")</div>
                                    <div style="color: #ff6b35; font-weight: 600;">$16.90</div>
                                </label>
                                <label class="size-option" style="display: block; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; text-align: center; cursor: pointer;">
                                    <input type="radio" name="pizza_size" value="large" data-price="20.90" style="margin-bottom: 0.5rem;">
                                    <div style="font-weight: 600;">Large (14")</div>
                                    <div style="color: #ff6b35; font-weight: 600;">$20.90</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Crust -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3 style="margin: 0;"><span class="step-number">2</span> Choose Your Crust</h3>
                        </div>
                        <div class="card-body">
                            <div class="ingredient-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                <?php foreach ($ingredients['crust'] as $crust): ?>
                                    <label class="ingredient-option" style="display: flex; align-items: center; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; cursor: pointer;">
                                        <input type="radio" name="crust" value="<?php echo $crust['ingredient_id']; ?>" data-price="<?php echo $crust['price']; ?>" data-name="<?php echo htmlspecialchars($crust['name']); ?>" <?php echo $crust['name'] === 'Classic Crust' ? 'checked' : ''; ?> style="margin-right: 0.5rem;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($crust['name']); ?></div>
                                            <div style="color: #ff6b35; font-weight: 600;">
                                                <?php echo $crust['price'] > 0 ? '+' . formatCurrency($crust['price']) : 'Free'; ?>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Sauce -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3 style="margin: 0;"><span class="step-number">3</span> Choose Your Sauce</h3>
                        </div>
                        <div class="card-body">
                            <div class="ingredient-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                <?php foreach ($ingredients['sauce'] as $sauce): ?>
                                    <label class="ingredient-option" style="display: flex; align-items: center; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; cursor: pointer;">
                                        <input type="radio" name="sauce" value="<?php echo $sauce['ingredient_id']; ?>" data-price="<?php echo $sauce['price']; ?>" data-name="<?php echo htmlspecialchars($sauce['name']); ?>" <?php echo $sauce['name'] === 'Tomato Base' ? 'checked' : ''; ?> style="margin-right: 0.5rem;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($sauce['name']); ?></div>
                                            <div style="color: #ff6b35; font-weight: 600;">
                                                <?php echo $sauce['price'] > 0 ? '+' . formatCurrency($sauce['price']) : 'Free'; ?>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Cheese -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3 style="margin: 0;"><span class="step-number">4</span> Choose Your Cheese</h3>
                        </div>
                        <div class="card-body">
                            <div class="ingredient-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                <?php foreach ($ingredients['cheese'] as $cheese): ?>
                                    <label class="ingredient-option" style="display: flex; align-items: center; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; cursor: pointer;">
                                        <input type="radio" name="cheese" value="<?php echo $cheese['ingredient_id']; ?>" data-price="<?php echo $cheese['price']; ?>" data-name="<?php echo htmlspecialchars($cheese['name']); ?>" <?php echo $cheese['name'] === 'Mozzarella' ? 'checked' : ''; ?> style="margin-right: 0.5rem;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($cheese['name']); ?></div>
                                            <div style="color: #ff6b35; font-weight: 600;">
                                                <?php echo $cheese['price'] > 0 ? '+' . formatCurrency($cheese['price']) : ($cheese['price'] < 0 ? formatCurrency($cheese['price']) : 'Free'); ?>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Toppings -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3 style="margin: 0;"><span class="step-number">5</span> Choose Your Toppings</h3>
                            <p style="margin: 0; color: #666; font-size: 0.9rem;">Select as many as you like</p>
                        </div>
                        <div class="card-body">
                            <!-- Meat Toppings -->
                            <h4 style="margin-bottom: 1rem; color: #ff6b35;">Meat Toppings</h4>
                            <div class="ingredient-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                                <?php foreach ($ingredients['meat'] as $meat): ?>
                                    <label class="ingredient-option" style="display: flex; align-items: center; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; cursor: pointer;">
                                        <input type="checkbox" name="toppings[]" value="<?php echo $meat['ingredient_id']; ?>" data-price="<?php echo $meat['price']; ?>" data-name="<?php echo htmlspecialchars($meat['name']); ?>" style="margin-right: 0.5rem;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($meat['name']); ?></div>
                                            <div style="color: #ff6b35; font-weight: 600;">+<?php echo formatCurrency($meat['price']); ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <!-- Vegetable Toppings -->
                            <h4 style="margin-bottom: 1rem; color: #ff6b35;">Vegetable Toppings</h4>
                            <div class="ingredient-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                <?php foreach ($ingredients['vegetable'] as $vegetable): ?>
                                    <label class="ingredient-option" style="display: flex; align-items: center; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; cursor: pointer;">
                                        <input type="checkbox" name="toppings[]" value="<?php echo $vegetable['ingredient_id']; ?>" data-price="<?php echo $vegetable['price']; ?>" data-name="<?php echo htmlspecialchars($vegetable['name']); ?>" style="margin-right: 0.5rem;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($vegetable['name']); ?></div>
                                            <div style="color: #ff6b35; font-weight: 600;">+<?php echo formatCurrency($vegetable['price']); ?></div>
                                            <?php if ($vegetable['is_vegan']): ?>
                                                <span class="badge badge-success" style="font-size: 0.7rem;">Vegan</span>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Special Instructions -->
                    <div class="card">
                        <div class="card-header">
                            <h3 style="margin: 0;"><span class="step-number">6</span> Special Instructions</h3>
                        </div>
                        <div class="card-body">
                            <textarea id="specialInstructions" placeholder="Any special requests for your pizza?" style="width: 100%; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; resize: vertical;" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pizza Summary -->
                <div class="pizza-summary">
                    <div class="card" style="position: sticky; top: 2rem;">
                        <div class="card-header">
                            <h3 style="margin: 0;">Your Custom Pizza</h3>
                        </div>
                        <div class="card-body">
                            <!-- Pizza Visual -->
                            <div class="pizza-visual" style="width: 200px; height: 200px; border-radius: 50%; background: linear-gradient(45deg, #ff6b35, #f7931e); margin: 0 auto 2rem; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-pizza-slice" style="font-size: 4rem; color: white; opacity: 0.8;"></i>
                            </div>

                            <!-- Pizza Details -->
                            <div class="pizza-details">
                                <div class="detail-item" style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Size:</span>
                                    <span id="selectedSize">Medium</span>
                                </div>
                                <div class="detail-item" style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Crust:</span>
                                    <span id="selectedCrust">Classic Crust</span>
                                </div>
                                <div class="detail-item" style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Sauce:</span>
                                    <span id="selectedSauce">Tomato Base</span>
                                </div>
                                <div class="detail-item" style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Cheese:</span>
                                    <span id="selectedCheese">Mozzarella</span>
                                </div>
                                <div class="detail-item" style="margin-bottom: 1rem;">
                                    <span>Toppings:</span>
                                    <div id="selectedToppings" style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">
                                        None selected
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Price Breakdown -->
                            <div class="price-breakdown">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Base Price:</span>
                                    <span id="basePrice">$16.90</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Extras:</span>
                                    <span id="extrasPrice">$0.00</span>
                                </div>
                                <hr>
                                <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 1.1rem;">
                                    <span>Total:</span>
                                    <span id="totalPrice">$16.90</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button id="addToCartBtn" class="btn btn-primary" style="width: 100%;" onclick="addCustomPizzaToCart()">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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
        // Set the current year in the copyright notice
        document.getElementById('currentYear').textContent = new Date().getFullYear();

        document.addEventListener('DOMContentLoaded', function() {
            setupPizzaBuilder();
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

        function setupPizzaBuilder() {
            // Add event listeners to all inputs
            document.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach(input => {
                input.addEventListener('change', updatePizzaSummary);
            });

            // Initial update
            updatePizzaSummary();
        }

        function updatePizzaSummary() {
            // Get selected size
            const selectedSizeInput = document.querySelector('input[name="pizza_size"]:checked');
            const size = selectedSizeInput ? selectedSizeInput.value : 'medium';
            const basePrice = selectedSizeInput ? parseFloat(selectedSizeInput.dataset.price) : 16.90;

            // Get selected crust
            const selectedCrustInput = document.querySelector('input[name="crust"]:checked');
            const crust = selectedCrustInput ? selectedCrustInput.dataset.name : 'Classic Crust';
            const crustPrice = selectedCrustInput ? parseFloat(selectedCrustInput.dataset.price) : 0;

            // Get selected sauce
            const selectedSauceInput = document.querySelector('input[name="sauce"]:checked');
            const sauce = selectedSauceInput ? selectedSauceInput.dataset.name : 'Tomato Base';
            const saucePrice = selectedSauceInput ? parseFloat(selectedSauceInput.dataset.price) : 0;

            // Get selected cheese
            const selectedCheeseInput = document.querySelector('input[name="cheese"]:checked');
            const cheese = selectedCheeseInput ? selectedCheeseInput.dataset.name : 'Mozzarella';
            const cheesePrice = selectedCheeseInput ? parseFloat(selectedCheeseInput.dataset.price) : 0;

            // Get selected toppings
            const selectedToppings = document.querySelectorAll('input[name="toppings[]"]:checked');
            let toppingsText = 'None selected';
            let toppingsPrice = 0;

            if (selectedToppings.length > 0) {
                const toppingNames = [];
                selectedToppings.forEach(topping => {
                    toppingNames.push(topping.dataset.name);
                    toppingsPrice += parseFloat(topping.dataset.price);
                });
                toppingsText = toppingNames.join(', ');
            }

            // Update display
            document.getElementById('selectedSize').textContent = size.charAt(0).toUpperCase() + size.slice(1);
            document.getElementById('selectedCrust').textContent = crust;
            document.getElementById('selectedSauce').textContent = sauce;
            document.getElementById('selectedCheese').textContent = cheese;
            document.getElementById('selectedToppings').textContent = toppingsText;

            // Update prices
            const extrasPrice = crustPrice + saucePrice + cheesePrice + toppingsPrice;
            const totalPrice = basePrice + extrasPrice;

            document.getElementById('basePrice').textContent = formatCurrency(basePrice);
            document.getElementById('extrasPrice').textContent = formatCurrency(extrasPrice);
            document.getElementById('totalPrice').textContent = formatCurrency(totalPrice);

            // Update visual selection styles
            updateSelectionStyles();
        }

        function updateSelectionStyles() {
            // Update size options
            document.querySelectorAll('.size-option').forEach(option => {
                const input = option.querySelector('input');
                if (input.checked) {
                    option.style.borderColor = '#ff6b35';
                    option.style.backgroundColor = '#fff5f2';
                } else {
                    option.style.borderColor = '#ddd';
                    option.style.backgroundColor = 'white';
                }
            });

            // Update ingredient options
            document.querySelectorAll('.ingredient-option').forEach(option => {
                const input = option.querySelector('input');
                if (input.checked) {
                    option.style.borderColor = '#ff6b35';
                    option.style.backgroundColor = '#fff5f2';
                } else {
                    option.style.borderColor = '#ddd';
                    option.style.backgroundColor = 'white';
                }
            });
        }

        function addCustomPizzaToCart() {
            // Check if user is logged in
            if (!isUserLoggedIn()) {
                showNotification("Please log in to add items to your cart", "warning");
                return;
            }

            // Get all selected options
            const selectedSize = document.querySelector('input[name="pizza_size"]:checked');
            const selectedCrust = document.querySelector('input[name="crust"]:checked');
            const selectedSauce = document.querySelector('input[name="sauce"]:checked');
            const selectedCheese = document.querySelector('input[name="cheese"]:checked');
            const selectedToppings = document.querySelectorAll('input[name="toppings[]"]:checked');
            const specialInstructions = document.getElementById('specialInstructions').value;

            if (!selectedSize || !selectedCrust || !selectedSauce || !selectedCheese) {
                showNotification('Please complete all pizza selections', 'warning');
                return;
            }

            // Calculate total price
            const basePrice = parseFloat(selectedSize.dataset.price);
            const crustPrice = parseFloat(selectedCrust.dataset.price);
            const saucePrice = parseFloat(selectedSauce.dataset.price);
            const cheesePrice = parseFloat(selectedCheese.dataset.price);

            let toppingsPrice = 0;
            const toppingNames = [];
            selectedToppings.forEach(topping => {
                toppingsPrice += parseFloat(topping.dataset.price);
                toppingNames.push(topping.dataset.name);
            });

            const totalPrice = basePrice + crustPrice + saucePrice + cheesePrice + toppingsPrice;

            // Create pizza name
            let pizzaName = `Custom ${selectedSize.value.charAt(0).toUpperCase() + selectedSize.value.slice(1)} Pizza`;

            // Create custom ingredients list
            const customIngredients = [
                selectedCrust.dataset.name,
                selectedSauce.dataset.name,
                selectedCheese.dataset.name,
                ...toppingNames
            ];

            // Create cart item
            const cartItem = {
                id: 'custom_' + Date.now(),
                name: pizzaName,
                price: totalPrice,
                size: selectedSize.value,
                type: 'pizza',
                quantity: 1,
                customIngredients: customIngredients,
                specialInstructions: specialInstructions
            };

            // Add to cart
            if (CrustPizza.addToCart(cartItem)) {
                showNotification('Custom pizza added to cart!', 'success');
                // Reset form
                resetPizzaBuilder();
            }
        }

        function resetPizzaBuilder() {
            // Reset to default selections
            document.querySelector('input[name="pizza_size"][value="medium"]').checked = true;
            document.querySelector('input[name="crust"][data-name="Classic Crust"]').checked = true;
            document.querySelector('input[name="sauce"][data-name="Tomato Base"]').checked = true;
            document.querySelector('input[name="cheese"][data-name="Mozzarella"]').checked = true;

            // Uncheck all toppings
            document.querySelectorAll('input[name="toppings[]"]').forEach(topping => {
                topping.checked = false;
            });

            // Clear special instructions
            document.getElementById('specialInstructions').value = '';

            // Update summary
            updatePizzaSummary();
        }

        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
            const cartCount = cart.reduce((total, item) => total + (item.quantity || 1), 0);
            document.getElementById('cartCount').textContent = cartCount;
        }

        function isUserLoggedIn() {
            // Check if logout link exists (indicates user is logged in)
            const logoutLink = document.querySelector('a[href="logout.php"]');
            const loginLink = document.querySelector('a.dropdown-item[href="login.php"]');
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
            setTimeout(() => {
                notification.classList.add("slide-out");
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }, 3000);
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
    </script>
</body>

</html>
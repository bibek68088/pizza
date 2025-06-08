<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once 'config/database.php';
require_once 'classes/Pizza.php';
require_once 'header.php';
require_once 'includes/functions.php';

// Start session for CSRF token
session_start();

// Instantiate the Pizza class
$database = Database::getInstance();
$db = $database->getConnection();
$pizzaModel = new Pizza($db);

// Get pizza ID from URL
$pizza_id = isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0 ? (int)$_GET['id'] : 0;

// Fetch pizza details
$pizza = $pizzaModel->getPizzaById($pizza_id);

if (!$pizza || $pizza_id <= 0) {
    error_log("Invalid or missing pizza_id in pizza-details.php: $pizza_id");
    echo '<div class="container text-center" style="padding: 50px;">';
    echo '<h3>Pizza Not Found</h3>';
    echo '<p>The requested pizza is not available. Please check our menu for other options.</p>';
    echo '<a href="menu.php" class="btn btn-primary">Browse Menu</a>';
    echo '</div>';
    require_once 'footer.php';
    exit;
}

// Sanitize data
$name = htmlspecialchars($pizza['name']);
$description = htmlspecialchars($pizza['description']);
$image_url = !empty($pizza['image_url']) ? htmlspecialchars($pizza['image_url']) : '/assets/public/uploads/placeholder.jpg';

// Debug: Log the image path and check existence
if (!empty($pizza['image_url'])) {
    $full_path = BASE_PATH . ltrim($pizza['image_url'], '/');
    if (!file_exists($full_path)) {
        error_log("Image not found for pizza_id $pizza_id in pizza-details.php: $full_path");
    } else {
        error_log("Image found for pizza_id $pizza_id: $full_path");
    }
}

$price_small = (float)$pizza['base_price_small'];
$price_medium = (float)$pizza['base_price_medium'];
$price_large = (float)$pizza['base_price_large'];

// Get size and quantity from POST or default
$size = isset($_POST['size']) ? htmlspecialchars($_POST['size']) : 'medium';
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

// Generate CSRF token
$csrf_token = function_exists('generateCSRFToken') ? generateCSRFToken() : '';
if (empty($csrf_token)) {
    error_log('generateCSRFToken function not found or failed in pizza-details.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $name; ?> - Crust Pizza</title>
    <link rel="preload" href="/<?php echo ltrim($image_url, '/'); ?>" as="image">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        /* Disable animations to prevent shuttering */
        .pizza-details,
        .pizza-details * {
            transition: none !important;
            animation: none !important;
            transform: none !important;
            opacity: 1 !important;
            will-change: none !important;
        }

        .pizza-details .pizza-image img {
            transition: none !important;
            transform: none !important;
        }

        /* Ensure layout consistency */
        .pizza-details .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            align-items: start;
        }

        @media (max-width: 768px) {
            .pizza-details .row {
                grid-template-columns: 1fr;
            }
        }

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
    </style>
</head>

<body>
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

    <section class="pizza-details" style="padding: 50px 20px;" aria-labelledby="pizza-title">
        <div class="container">
            <h2 id="pizza-title"><?php echo $name; ?></h2>
            <div class="row">
                <div class="col-md-6 pizza-image">
                    <img src="/<?php echo ltrim($image_url, '/'); ?>" alt="<?php echo $name; ?>" style="max-width: 100%; height: 300px; object-fit: cover; border-radius: 10px;" loading="lazy" onerror="this.src='/assets/public/uploads/placeholder.jpg';">
                </div>
                <div class="col-md-6">
                    <p><?php echo $description; ?></p>
                    <form id="add-to-cart-form" data-validate>
                        <div class="form-group">
                            <label for="size-selector">Size</label>
                            <select id="size-selector" name="size" class="form-control size-selector" required aria-describedby="size-help">
                                <option value="small" <?php echo $size === 'small' ? 'selected' : ''; ?>>Small (<?php echo formatCurrency($price_small); ?>)</option>
                                <option value="medium" <?php echo $size === 'medium' ? 'selected' : ''; ?>>Medium (<?php echo formatCurrency($price_medium); ?>)</option>
                                <option value="large" <?php echo $size === 'large' ? 'selected' : ''; ?>>Large (<?php echo formatCurrency($price_large); ?>)</option>
                            </select>
                            <small id="size-help" class="form-text text-muted">Select your preferred pizza size.</small>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" id="quantity" name="quantity" value="<?php echo $quantity; ?>" min="1" max="99" class="form-control" required aria-describedby="quantity-help">
                            <small id="quantity-help" class="form-text text-muted">Enter the number of pizzas.</small>
                        </div>
                        <div class="pizza-prices mb-3">
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
                            <button type="submit" class="btn btn-add-cart" aria-label="Add <?php echo $name; ?> to cart">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                            <a href="build-pizza.php?pizza_id=<?php echo $pizza_id; ?>" class="btn btn-outline" aria-label="Customize <?php echo $name; ?>">Customize</a>
                            <a href="menu.php" class="btn btn-outline" aria-label="Back to menu">Back to Menu</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php require_once 'footer.php'; ?>

    <script src="/assets/js/main.js"></script>
    <script>
        // Override addToCart to use form submission
        function addToCart(pizzaId, size = "medium", quantity = 1, event) {
            try {
                // Prevent default if called via button click
                if (event && typeof event.preventDefault === 'function') {
                    event.preventDefault();
                }

                // Validate pizzaId
                if (!pizzaId || pizzaId <= 0) {
                    console.error('Invalid pizzaId:', pizzaId);
                    showNotification('Invalid pizza selection. Please try again.', 'error');
                    return Promise.resolve(false);
                }

                if (!isUserLoggedIn()) {
                    showNotification("Please log in to add items to your cart", 'warning');
                    return Promise.resolve(false);
                }

                // Get pizza details
                const pizzaName = "<?php echo addslashes($name); ?>" || 'Unknown Pizza';
                const priceData = {
                    small: <?php echo $price_small; ?>,
                    medium: <?php echo $price_medium; ?>,
                    large: <?php echo $price_large; ?>
                };

                if (!priceData[size]) {
                    console.error('Invalid size or price for pizzaId:', pizzaId, 'size:', size);
                    showNotification('Invalid pizza size or price', 'error');
                    return Promise.resolve(false);
                }

                const cartItem = {
                    pizza_id: pizzaId,
                    name: pizzaName,
                    size: size,
                    price: priceData[size],
                    quantity: Number(quantity) || 1,
                    item_type: 'pizza',
                    custom_ingredients: null,
                    special_instructions: null
                };

                const userId = getUserId();
                if (!userId) {
                    console.error('User ID not found');
                    showNotification('User session not found. Please log in again.', 'error');
                    return Promise.resolve(false);
                }

                const csrfToken = getCSRFToken();
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    showNotification('Security token missing. Please refresh the page.', 'error');
                    return Promise.resolve(false);
                }

                const data = {
                    ...cartItem,
                    user_id: userId,
                    csrf_token: csrfToken
                };

                // Apply loading animation
                const button = event ? (event.currentTarget || (event.target && event.target.closest('button'))) : null;
                let originalText;
                if (button) {
                    originalText = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                    button.disabled = true;
                }

                return fetch('api/cart_api.php?action=add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(result => {
                        if (button) {
                            button.innerHTML = originalText;
                            button.disabled = false;
                        }
                        if (result.success) {
                            // Update localStorage
                            let cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
                            if (!Array.isArray(cart)) {
                                cart = [];
                            }
                            cart.push({
                                ...cartItem,
                                cart_id: result.cart_id || Date.now()
                            });
                            localStorage.setItem('crustPizzaCart', JSON.stringify(cart));
                            updateCartCount();
                            showNotification(`${pizzaName} (${size}) added to cart!`, 'success');
                            return true;
                        } else {
                            console.error('API Error:', result.message || 'Unknown error');
                            showNotification(result.message || 'Failed to add item to cart', 'error');
                            return false;
                        }
                    })
                    .catch(error => {
                        if (button) {
                            button.innerHTML = originalText;
                            button.disabled = false;
                        }
                        console.error('Error adding to cart:', error.message);
                        showNotification('An error occurred while adding to cart', 'error');
                        return false;
                    });
            } catch (error) {
                console.error('Unexpected error in addToCart:', error);
                showNotification('Unexpected error occurred', 'error');
                return Promise.resolve(false);
            }
        }

        function isUserLoggedIn() {
            const logoutLink = document.querySelector('a[href="logout.php"]');
            const loginLink = document.querySelector('a.dropdown-item[href="login.php"]');
            return logoutLink !== null && loginLink === null;
        }

        function updatePizzaPrice(selector) {
            const priceData = {
                small: <?php echo $price_small; ?>,
                medium: <?php echo $price_medium; ?>,
                large: <?php echo $price_large; ?>
            };
            const size = selector.value;
            const priceDisplay = document.querySelector('.current-price');
            if (priceDisplay && priceData[size]) {
                priceDisplay.textContent = formatCurrency(priceData[size]);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();

            // Initialize size selector
            const sizeSelector = document.querySelector('#size-selector');
            if (sizeSelector) {
                sizeSelector.addEventListener('change', function() {
                    updatePizzaPrice(this);
                });
                // Set initial price
                updatePizzaPrice(sizeSelector);
            }

            // Handle form submission
            const form = document.getElementById('add-to-cart-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const size = document.getElementById('size-selector').value;
                    const quantity = parseInt(document.getElementById('quantity').value) || 1;
                    if (quantity < 1 || quantity > 99) {
                        showNotification('Quantity must be between 1 and 99', 'error');
                        return;
                    }
                    addToCart(<?php echo $pizza_id; ?>, size, quantity);
                });
            }
        });

        function formatCurrency(amount) {
            return '$' + parseFloat(amount).toFixed(2);
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

        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
            const cartCount = cart.reduce((total, item) => total + (item.quantity || 1), 0);
            const cartCountElement = document.getElementById('cartCount');
            if (cartCountElement) {
                cartCountElement.textContent = cartCount;
            }
        }

        function getUserId() {
            const userIdMeta = document.querySelector('meta[name="user-id"]');
            return userIdMeta ? userIdMeta.getAttribute('content') : null;
        }

        function getCSRFToken() {
            const tokenInput = document.querySelector('input[name="csrf_token"]');
            return tokenInput ? tokenInput.value : '';
        }
    </script>
</body>

</html>
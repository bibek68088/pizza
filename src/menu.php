<?php

/**
 * Menu Page - Display all available pizzas
 * Crust Pizza Online Ordering System
 */

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

<?php include 'includes/header.php'; ?>

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
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>
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

<?php include 'includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
    // Update the addToCartFromMenu function to check login status
    function addToCartFromMenu(pizzaId) {
        <?php if (!isLoggedIn()): ?>
            // Show login required notification
            showNotification("Please log in to add items to your cart", "warning");
            return;
        <?php else: ?>
            const sizeSelector = document.getElementById(`size-${pizzaId}`);
            const size = sizeSelector.value;

            // Get current cart from localStorage
            const cart = JSON.parse(localStorage.getItem("crustPizzaCart")) || [];

            // Get pizza card to extract price data and name
            const pizzaCard = sizeSelector.closest('.pizza-card');
            const priceData = {
                small: parseFloat(pizzaCard.dataset.priceSmall),
                medium: parseFloat(pizzaCard.dataset.priceMedium),
                large: parseFloat(pizzaCard.dataset.priceLarge)
            };

            // Get pizza name from the card
            const pizzaName = pizzaCard.querySelector('h3').textContent;

            // Create cart item with consistent structure
            const cartItem = {
                id: pizzaId,
                name: pizzaName,
                size: size,
                price: priceData[size],
                quantity: 1,
                added_at: new Date().toISOString(),
                item_type: "pizza"
            };

            // Check if item already exists
            const existingItemIndex = cart.findIndex(item =>
                item.id == pizzaId && item.size === size
            );

            if (existingItemIndex > -1) {
                cart[existingItemIndex].quantity += 1;
                showNotification(`Updated ${size} ${pizzaName} quantity in cart`, "success");
            } else {
                cart.push(cartItem);
                showNotification(`${pizzaName} (${size}) added to cart!`, "success");
            }

            // Save to localStorage
            localStorage.setItem("crustPizzaCart", JSON.stringify(cart));

            // Update cart count
            updateCartCount();
        <?php endif; ?>
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
        if (!isUserLoggedIn()) return;
        const cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
        const cartCount = cart.reduce((total, item) => total + (item.quantity || 1), 0);
        const cartCountElement = document.getElementById('cartCount');
        if (cartCountElement) {
            cartCountElement.textContent = cartCount;
        }
    }

    // Initialize cart count on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateCartCount();
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
</script>
<style>
    /* Notification styles */
    .cart-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slide-in 0.3s ease-out forwards;
        max-width: 350px;
    }

    .cart-notification.warning {
        background: #ff9800;
    }

    .cart-notification.error {
        background: #f44336;
    }

    .cart-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .cart-notification i {
        font-size: 1.2rem;
    }

    .cart-notification.slide-out {
        animation: slide-out 0.3s ease-in forwards;
    }

    @keyframes slide-in {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slide-out {
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
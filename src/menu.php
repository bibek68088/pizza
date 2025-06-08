<?php

/**
 * Menu Page - Display all available pizzas
 * Crust Pizza Online Ordering System
 */


// Include necessary files
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'classes/Pizza.php';

// Start session
startSession();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate Pizza object
$pizza = new Pizza($db);

// Include the header
require_once 'header.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0; // Changed to 0 for "All Categories"

// Get pizzas based on search/filter using the consistent getAllPizzas method
$page = 1;
$limit = 50; // Show more pizzas per page for menu

$result = $pizza->getAllPizzas($page, $limit, $search, $category);
$pizzas = $result['pizzas'];

// Get categories for filter
$categories_query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

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
                    <select name="category" class="form-control" style="width: 200px;" onchange="this.form.submit()">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo $category === (int)$cat['category_id'] ? 'selected' : ''; ?>>
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

        <!-- Results Info -->
        <?php if (!empty($search) || $category !== 0): ?>
            <div class="results-info" style="margin-bottom: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                <?php
                $resultCount = count($pizzas);
                if (!empty($search) && $category !== 0) {
                    $categoryName = '';
                    foreach ($categories as $cat) {
                        if ($cat['category_id'] == $category) {
                            $categoryName = $cat['name'];
                            break;
                        }
                    }
                    echo "Found {$resultCount} pizza(s) matching '{$search}' in category '{$categoryName}'";
                } elseif (!empty($search)) {
                    echo "Found {$resultCount} pizza(s) matching '{$search}'";
                } elseif ($category !== 0) {
                    $categoryName = '';
                    foreach ($categories as $cat) {
                        if ($cat['category_id'] == $category) {
                            $categoryName = $cat['name'];
                            break;
                        }
                    }
                    echo "Showing {$resultCount} pizza(s) in category: {$categoryName}";
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Pizza Grid -->
        <?php if (empty($pizzas)): ?>
            <div class="text-center" style="padding: 40px;">
                <h3>No pizzas found</h3>
                <?php if (!empty($search) || $category !== 0): ?>
                    <p>Try adjusting your search or filter criteria.</p>
                    <a href="menu.php" class="btn btn-primary">View All Pizzas</a>
                <?php else: ?>
                    <p>No pizzas are currently available.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="pizza-grid">
                <?php foreach ($pizzas as $pizza_item): ?>
                    <?php
                    // Ensure we have all required fields with default values if missing
                    $pizza_id = isset($pizza_item['pizza_id']) ? $pizza_item['pizza_id'] : 0;
                    $name = isset($pizza_item['name']) ? htmlspecialchars($pizza_item['name']) : 'Pizza';
                    $description = isset($pizza_item['description']) ? htmlspecialchars($pizza_item['description']) : 'Delicious pizza with our signature sauce and cheese.';
                    $image_url = isset($pizza_item['image_url']) && !empty($pizza_item['image_url'])
                        ? 'assets/public/uploads/' . htmlspecialchars(basename($pizza_item['image_url']))
                        : '/placeholder.svg?height=250&width=300';

                    // Set default prices if not available
                    $price_small = isset($pizza_item['base_price_small']) ? (float)$pizza_item['base_price_small'] : 15.90;
                    $price_medium = isset($pizza_item['base_price_medium']) ? (float)$pizza_item['base_price_medium'] : 21.90;
                    $price_large = isset($pizza_item['base_price_large']) ? (float)$pizza_item['base_price_large'] : 27.90;

                    // Additional info for display
                    $category_name = isset($pizza_item['category_name']) ? htmlspecialchars($pizza_item['category_name']) : '';
                    $ingredient_list = isset($pizza_item['ingredient_list']) ? htmlspecialchars($pizza_item['ingredient_list']) : '';
                    $is_featured = isset($pizza_item['is_featured']) && $pizza_item['is_featured'] == 1;
                    $is_vegan = isset($pizza_item['is_vegan']) && $pizza_item['is_vegan'] == 1;
                    $is_gluten_free = isset($pizza_item['is_gluten_free_available']) && $pizza_item['is_gluten_free_available'] == 1;
                    $allergens = isset($pizza_item['allergens']) ? htmlspecialchars($pizza_item['allergens']) : '';
                    ?>
                    <div class="pizza-card" data-price-small="<?php echo $price_small; ?>" data-price-medium="<?php echo $price_medium; ?>" data-price-large="<?php echo $price_large; ?>">
                        <?php if ($is_featured): ?>
                            <div class="featured-badge">Featured</div>
                        <?php endif; ?>

                        <div class="pizza-image">
                            <img src="<?php echo $image_url; ?>" alt="<?php echo $name; ?>" loading="lazy">
                            <?php if ($is_vegan): ?>
                                <div class="vegan-badge">🌱 Vegan</div>
                            <?php endif; ?>
                            <?php if ($is_gluten_free): ?>
                                <div class="gluten-free-badge">🌾 Gluten-Free</div>
                            <?php endif; ?>
                        </div>

                        <div class="pizza-info">
                            <div class="pizza-header">
                                <h3><?php echo $name; ?></h3>
                                <?php if (!empty($category_name)): ?>
                                    <span class="category-tag"><?php echo $category_name; ?></span>
                                <?php endif; ?>
                            </div>

                            <p class="pizza-description"><?php echo $description; ?></p>

                            <?php if (!empty($ingredient_list)): ?>
                                <p class="ingredients"><strong>Ingredients:</strong> <?php echo $ingredient_list; ?></p>
                            <?php endif; ?>

                            <?php if (!empty($allergens)): ?>
                                <p class="allergens"><strong>Allergens:</strong> <?php echo $allergens; ?></p>
                            <?php endif; ?>

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

<!-- Include the footer -->
<?php require_once 'footer.php'; ?>

<!-- JavaScript and Styles -->
<script src="assets/js/main.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Update cart count on page load
        updateCartCount();

        // Update price when size changes
        document.querySelectorAll('.size-selector').forEach(selector => {
            selector.addEventListener('change', function() {
                const pizzaCard = this.closest('.pizza-card');
                const size = this.value;
                const priceElement = pizzaCard.querySelector('.current-price');

                let price;
                switch (size) {
                    case 'small':
                        price = parseFloat(pizzaCard.dataset.priceSmall);
                        break;
                    case 'large':
                        price = parseFloat(pizzaCard.dataset.priceLarge);
                        break;
                    default:
                        price = parseFloat(pizzaCard.dataset.priceMedium);
                }

                priceElement.textContent = formatCurrency(price);
            });
        });

        // Add loading animation to Add to Cart buttons
        document.querySelectorAll('.btn-primary').forEach(button => {
            button.addEventListener('click', function() {
                if (this.textContent.includes('Add to Cart')) {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                    this.disabled = true;

                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }, 1000);
                }
            });
        });
    });

    function formatCurrency(amount) {
        return '$' + parseFloat(amount).toFixed(2);
    }

    function addToCartFromMenu(pizzaId) {
        // Check if user is logged in first
        if (!isUserLoggedIn()) {
            showNotification("Please log in to add items to your cart", "warning");
            return false;
        }

        // Get size from selector
        const sizeSelector = document.getElementById(`size-${pizzaId}`);
        const size = sizeSelector.value;

        // Get pizza card to extract price data and name
        const pizzaCard = sizeSelector.closest('.pizza-card');
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

        // Create cart item with consistent structure
        const cartItem = {
            pizza_id: pizzaId,
            name: pizzaName,
            size: size,
            price: priceData[size],
            quantity: 1,
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

        if (dropdown && dropdownMenu && !dropdown.contains(event.target)) {
            dropdownMenu.classList.remove('show');
            document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
        }

        if (navMenu && navToggle && !navToggle.contains(event.target)) {
            navMenu.classList.remove('active');
        }
    });

    // Close dropdown and nav menu on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const dropdownMenu = document.getElementById('dropdownMenu');
            const navMenu = document.getElementById('navMenu');

            if (dropdownMenu) {
                dropdownMenu.classList.remove('show');
                document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
            }

            if (navMenu) {
                navMenu.classList.remove('active');
            }
        }
    });

    function isUserLoggedIn() {
        const userId = getUserId();
        return !!userId;
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

<style>
    /* Pizza grid and card styles */
    .pizza-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }

    .pizza-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
    }

    .pizza-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .featured-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: linear-gradient(45deg, #ff6b35, #ff8c42);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: bold;
        z-index: 2;
    }

    .pizza-image {
        position: relative;
        height: 250px;
        overflow: hidden;
    }

    .pizza-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .pizza-card:hover .pizza-image img {
        transform: scale(1.05);
    }

    .vegan-badge,
    .gluten-free-badge {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(76, 175, 80, 0.9);
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: bold;
    }

    .gluten-free-badge {
        bottom: 40px;
        background: rgba(33, 150, 243, 0.9);
    }

    .pizza-info {
        padding: 25px;
    }

    .pizza-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .pizza-header h3 {
        margin: 0;
        color: #333;
        font-size: 1.4rem;
        font-weight: bold;
    }

    .category-tag {
        background: #f8f9fa;
        color: #6c757d;
        padding: 4px 8px;
        border-radius: 10px;
        font-size: 0.8rem;
        white-space: nowrap;
    }

    .pizza-description,
    .ingredients,
    .allergens {
        color: #666;
        margin-bottom: 15px;
        line-height: 1.5;
    }

    .pizza-actions {
        display: flex;
        gap: 10px;
    }

    .pizza-actions .btn {
        flex: 1;
        text-align: center;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(45deg, #ff6b35, #ff8c42);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(45deg, #e55a2b, #e57a32);
        transform: translateY(-2px);
    }

    .btn-outline {
        background: transparent;
        color: #ff6b35;
        border: 2px solid #ff6b35;
    }

    .btn-outline:hover {
        background: #ff6b35;
        color: white;
    }

    .results-info {
        font-style: italic;
        color: #666;
    }

    /* Notification styles */
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
        animation: slide-in 0.3s ease-out;
        max-width: 350px;
    }

    .cart-notification.warning {
        background: linear-gradient(135deg, #ffc107, #f39c12);
        box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
    }

    .cart-notification.error {
        background: linear-gradient(135deg, #dc3545, #e74c3c);
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
    }

    .cart-notification i {
        font-size: 1.2rem;
    }

    .cart-notification.slide-out {
        animation: slide-out 0.3s ease-in;
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

    /* Responsive design */
    @media (max-width: 768px) {
        .pizza-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .filter-form {
            flex-direction: column;
            align-items: stretch !important;
        }

        .search-box input,
        .category-filter select {
            width: 100% !important;
        }
    }
</style>

</body>

</html>
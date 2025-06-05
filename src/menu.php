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

// Debug: Add error checking and logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get pizzas based on search/filter with error handling
try {
    if (!empty($search)) {
        $pizzas = $pizza->searchPizzas($search);
    } elseif (!empty($category)) {
        $pizzas = $pizza->getPizzasByCategory($category);
    } else {
        $pizzas = $pizza->getAllPizzas();
    }

    // Debug: Check what we actually got
    if (isset($_GET['debug'])) {
        echo "<pre>Debug - Pizzas data:\n";
        var_dump($pizzas);
        echo "</pre>";
        exit;
    }

    // Ensure $pizzas is an array
    if (!is_array($pizzas)) {
        $pizzas = [];
    }

    // Check if we have valid pizza data
    if (!empty($pizzas)) {
        $first_pizza = reset($pizzas);
        if (!is_array($first_pizza) || !isset($first_pizza['pizza_id'])) {
            // Something is wrong with the data structure
            error_log("Invalid pizza data structure: " . print_r($pizzas, true));
            $pizzas = [];
        }
    }
} catch (Exception $e) {
    error_log("Error fetching pizzas: " . $e->getMessage());
    $pizzas = [];
}

// Get categories for filter with error handling
try {
    $categories_query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
    $categories_stmt = $db->prepare($categories_query);
    $categories_stmt->execute();
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!is_array($categories)) {
        $categories = [];
    }
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Crust Pizza</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice"></i>
                <p><a href="index.php" style="text-decoration: none; color: inherit;">Crust Pizza</a></p>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="menu.php" class="nav-link active">Menu</a>
                <a href="build-pizza.php" class="nav-link">Build Your Pizza</a>
                <a href="track-order.php" class="nav-link">Track Order</a>
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php" class="nav-link">Profile</a>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                <?php endif; ?>
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

            <!-- Debug link (remove in production) -->
            <?php if (isset($_GET['show_debug'])): ?>
                <p><a href="menu.php?debug=1" style="color: red;">Debug: View Raw Data</a></p>
            <?php endif; ?>

            <!-- Search and Filter -->
            <div class="menu-filters" style="margin-bottom: 30px;">
                <form method="GET" class="filter-form" style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Search pizzas..."
                            value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="width: 300px;">
                    </div>

                    <div class="category-filter">
                        <select name="category" class="form-control" style="width: 200px;">
                            <option value="">All Categories</option>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <?php if (is_array($cat) && isset($cat['category_id'], $cat['name'])): ?>
                                        <option value="<?php echo $cat['category_id']; ?>"
                                            <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
                        if (!is_array($pizza_item) || !isset($pizza_item['pizza_id'])) {
                            continue;
                        }

                        $pizza_data = array_merge([
                            'pizza_id' => 0,
                            'name' => 'Unknown Pizza',
                            'description' => 'No description available',
                            'image_url' => '',
                            'base_price_small' => 0.00,
                            'base_price_medium' => 0.00,
                            'base_price_large' => 0.00
                        ], $pizza_item);
                        ?>
                        <div class="pizza-card"
                            data-price-small="<?php echo number_format($pizza_data['base_price_small'], 2); ?>"
                            data-price-medium="<?php echo number_format($pizza_data['base_price_medium'], 2); ?>"
                            data-price-large="<?php echo number_format($pizza_data['base_price_large'], 2); ?>">
                            <div class="pizza-image">
                                <img src="<?php echo !empty($pizza_data['image_url']) ? htmlspecialchars($pizza_data['image_url']) : '/placeholder.svg?height=250&width=300'; ?>"
                                    alt="<?php echo htmlspecialchars($pizza_data['name']); ?>">
                            </div>
                            <div class="pizza-info">
                                <h3><?php echo htmlspecialchars($pizza_data['name']); ?></h3>
                                <p><?php echo htmlspecialchars($pizza_data['description']); ?></p>

                                <div class="pizza-options" style="margin-bottom: 15px;">
                                    <label for="size-<?php echo $pizza_data['pizza_id']; ?>">Size:</label>
                                    <select id="size-<?php echo $pizza_data['pizza_id']; ?>" class="size-selector form-control" style="width: 100%;">
                                        <option value="small">Small - <?php echo formatCurrency($pizza_data['base_price_small']); ?></option>
                                        <option value="medium" selected>Medium - <?php echo formatCurrency($pizza_data['base_price_medium']); ?></option>
                                        <option value="large">Large - <?php echo formatCurrency($pizza_data['base_price_large']); ?></option>
                                    </select>
                                </div>

                                <div class="current-price" style="font-size: 1.2rem; font-weight: bold; color: #ff6b35; margin-bottom: 15px;">
                                    <?php echo formatCurrency($pizza_data['base_price_medium']); ?>
                                </div>

                                <div class="pizza-actions">
                                    <a href="pizza-details.php?id=<?php echo $pizza_data['pizza_id']; ?>" class="btn btn-outline">
                                        View Details
                                    </a>
                                    <button class="btn btn-primary"
                                        onclick="addToCartFromMenu(<?php echo $pizza_data['pizza_id']; ?>)">
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

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Crust Pizza</h3>
                    <p>Delivering gourmet pizza experiences since 2024</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="build-pizza.php">Build Your Pizza</a></li>
                        <li><a href="track-order.php">Track Order</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-phone"></i> 1300 CRUST (1300 278 787)</p>
                    <p><i class="fas fa-envelope"></i> info@crustpizza.com.au</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <span id="copyright-year"></span> Crust Pizza. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        document.getElementById('copyright-year').textContent = new Date().getFullYear();

        function addToCartFromMenu(pizzaId) {
            const sizeSelector = document.getElementById(`size-${pizzaId}`);
            const size = sizeSelector.value;
            addToCart(pizzaId, size, 1);
        }
    </script>
</body>

</html>
<?php

require_once 'config/database.php';
require_once 'classes/Order.php';
require_once 'classes/User.php';
require_once 'includes/functions.php';

startSession();

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('Please log in to place an order', 'warning');
    redirect('login.php?redirect=checkout.php');
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$user = new User($db);

// Get user details
$user->getUserById($_SESSION['user_id']);

$error = '';
$success = '';

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $order_type = sanitizeInput($_POST['order_type']);
    $payment_method = sanitizeInput($_POST['payment_method']);
    $delivery_address = sanitizeInput($_POST['delivery_address']);
    $delivery_instructions = sanitizeInput($_POST['delivery_instructions']);
    $customer_name = sanitizeInput($_POST['customer_name']);
    $customer_phone = sanitizeInput($_POST['customer_phone']);
    $customer_email = sanitizeInput($_POST['customer_email']);

    // Get cart from session/localStorage (simulated)
    $cart_items = json_decode($_POST['cart_data'], true);

    if (empty($cart_items)) {
        $error = 'Your cart is empty';
    } else {
        // Calculate totals
        $subtotal = 0;
        foreach ($cart_items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $tax = $subtotal * 0.1;
        $delivery_fee = ($order_type === 'delivery') ? ($subtotal > 30 ? 0 : 5.99) : 0;
        $total = $subtotal + $tax + $delivery_fee;

        // Create order
        $order->user_id = $_SESSION['user_id'];
        $order->store_id = 1;
        $order->order_type = $order_type;
        $order->subtotal = $subtotal;
        $order->tax = $tax;
        $order->delivery_fee = $delivery_fee;
        $order->total = $total;
        $order->payment_method = $payment_method;
        $order->customer_name = $customer_name;
        $order->customer_phone = $customer_phone;
        $order->customer_email = $customer_email;
        $order->delivery_address = $delivery_address;
        $order->delivery_instructions = $delivery_instructions;

        if ($order->create()) {
            // Add order items
            foreach ($cart_items as $item) {
                $item_data = [
                    'order_id' => $order->order_id,
                    'item_type' => $item['type'] ?? 'pizza',
                    'pizza_id' => (strpos($item['id'], 'pizza_') === 0) ? explode('_', $item['id'])[1] : null,
                    'menu_item_id' => (strpos($item['id'], 'menu_') === 0) ? explode('_', $item['id'])[1] : null,
                    'size' => $item['size'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'special_instructions' => $item['specialInstructions'] ?? ''
                ];

                $order->addOrderItem($item_data);
            }

            setFlashMessage('Order placed successfully! Order ID: ' . $order->order_id, 'success');
            redirect('track-order.php?order_id=' . $order->order_id);
        } else {
            $error = 'Error placing order. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Crust Pizza</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 20px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 3rem;
            margin-top: 2rem;
        }

        .checkout-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .section-header {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 1.5rem 2rem;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-content {
            padding: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff6b35;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .option-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .option-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
            text-align: center;
            position: relative;
        }

        .option-card:hover {
            border-color: #ff6b35;
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.15);
        }

        .option-card.selected {
            border-color: #ff6b35;
            background: linear-gradient(135deg, #fff5f2, #ffffff);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.2);
        }

        .option-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .option-icon {
            font-size: 2.5rem;
            color: #ff6b35;
            margin-bottom: 1rem;
        }

        .option-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .option-description {
            color: #666;
            font-size: 0.9rem;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .order-summary {
            position: sticky;
            top: 100px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .item-meta {
            font-size: 0.9rem;
            color: #666;
        }

        .item-price {
            font-weight: 600;
            color: #ff6b35;
            font-size: 1.1rem;
        }

        .summary-totals {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1rem;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .total-line.final {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ff6b35;
            border-top: 2px solid #ff6b35;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
        }

        .place-order-btn {
            width: 100%;
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            border: none;
            padding: 1.25rem 2rem;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 1.5rem;
        }

        .place-order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.4);
        }

        .place-order-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .delivery-note {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
            color: #2e7d32;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-error {
            background: #ffebee;
            border: 1px solid #f44336;
            color: #c62828;
        }

        .alert-success {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            color: #2e7d32;
        }

        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .order-summary {
                position: static;
                order: -1;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .option-grid,
            .payment-grid {
                grid-template-columns: 1fr;
            }

            .checkout-container {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
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
                <a href="build-pizza.php" class="nav-link">Build Your Pizza</a>
                <a href="track-order.php" class="nav-link">Track Order</a>
                <div class="dropdown">
                    <button class="dropdown-toggle" onclick="toggleDropdown()" aria-label="User Menu" aria-expanded="false" title="User Menu">
                        <span class="user-icon"><i class="fas fa-user"></i></span>
                        <span class="dropdown-arrow"></span>
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a class="dropdown-item" href="profile.php">Profile</a>
                        <a class="dropdown-item" href="logout.php">Logout</a>
                    </div>
                </div>
                <a href="cart.php" class="nav-link cart-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </nav>

    <main style="margin-top: 80px;">
        <div class="checkout-container">
            <div class="page-header" style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 2.5rem; color: #333; margin-bottom: 0.5rem;">
                    <i class="fas fa-credit-card" style="color: #ff6b35;"></i> Secure Checkout
                </h1>
                <p style="color: #666; font-size: 1.1rem;">Complete your order in just a few steps</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="checkoutForm">
                <div class="checkout-grid">
                    <!-- Left Column - Order Details -->
                    <div class="checkout-details">
                        <!-- Customer Information -->
                        <div class="checkout-section">
                            <div class="section-header">
                                <i class="fas fa-user"></i>
                                Customer Information
                            </div>
                            <div class="section-content">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="customer_name">Full Name *</label>
                                        <input type="text" id="customer_name" name="customer_name" class="form-control"
                                            value="<?php echo htmlspecialchars($user->full_name); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="customer_phone">Phone Number *</label>
                                        <input type="tel" id="customer_phone" name="customer_phone" class="form-control"
                                            value="<?php echo htmlspecialchars($user->phone); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="customer_email">Email Address *</label>
                                    <input type="email" id="customer_email" name="customer_email" class="form-control"
                                        value="<?php echo htmlspecialchars($user->email); ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Order Type -->
                        <div class="checkout-section">
                            <div class="section-header">
                                <i class="fas fa-shipping-fast"></i>
                                Order Type
                            </div>
                            <div class="section-content">
                                <div class="option-grid">
                                    <label class="option-card" for="delivery">
                                        <input type="radio" name="order_type" value="delivery" id="delivery" checked>
                                        <div class="option-icon">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                        <div class="option-title">Delivery</div>
                                        <div class="option-description">Delivered to your door</div>
                                    </label>
                                    <label class="option-card" for="pickup">
                                        <input type="radio" name="order_type" value="pickup" id="pickup">
                                        <div class="option-icon">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <div class="option-title">Pickup</div>
                                        <div class="option-description">Collect from store</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Address -->
                        <div class="checkout-section" id="deliverySection">
                            <div class="section-header">
                                <i class="fas fa-map-marker-alt"></i>
                                Delivery Address
                            </div>
                            <div class="section-content">
                                <div class="form-group">
                                    <label for="delivery_address">Address *</label>
                                    <textarea id="delivery_address" name="delivery_address" class="form-control" rows="3"
                                        placeholder="Enter your delivery address"><?php echo htmlspecialchars($user->address); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="delivery_instructions">Delivery Instructions</label>
                                    <textarea id="delivery_instructions" name="delivery_instructions" class="form-control" rows="2"
                                        placeholder="e.g., Leave at front door, Ring doorbell"></textarea>
                                </div>
                                <div class="delivery-note">
                                    <i class="fas fa-info-circle"></i>
                                    Free delivery on orders over $30. Delivery fee: $5.99
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="checkout-section">
                            <div class="section-header">
                                <i class="fas fa-credit-card"></i>
                                Payment Method
                            </div>
                            <div class="section-content">
                                <div class="payment-grid">
                                    <label class="option-card" for="cash">
                                        <input type="radio" name="payment_method" value="cash" id="cash" checked>
                                        <div class="option-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div class="option-title">Cash on Delivery</div>
                                        <div class="option-description">Pay when you receive</div>
                                    </label>
                                    <label class="option-card" for="card">
                                        <input type="radio" name="payment_method" value="card" id="card">
                                        <div class="option-icon">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <div class="option-title">Credit Card</div>
                                        <div class="option-description">Pay on delivery</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Order Summary -->
                    <div class="order-summary">
                        <div class="checkout-section">
                            <div class="section-header">
                                <i class="fas fa-receipt"></i>
                                Order Summary
                            </div>
                            <div class="section-content">
                                <div id="orderItems">
                                    <!-- Order items will be populated here -->
                                </div>

                                <div class="summary-totals">
                                    <div class="total-line">
                                        <span>Subtotal:</span>
                                        <span id="subtotal">$0.00</span>
                                    </div>
                                    <div class="total-line">
                                        <span>GST (10%):</span>
                                        <span id="tax">$0.00</span>
                                    </div>
                                    <div class="total-line">
                                        <span>Delivery Fee:</span>
                                        <span id="deliveryFee">$5.99</span>
                                    </div>
                                    <div class="total-line final">
                                        <span>Total:</span>
                                        <span id="total">$0.00</span>
                                    </div>
                                </div>

                                <input type="hidden" name="cart_data" id="cartData">
                                <button type="submit" name="place_order" class="place-order-btn" id="placeOrderBtn">
                                    <i class="fas fa-lock"></i> Place Secure Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Crust Pizza</h3>
                    <p>Gourmet pizza delivered fresh since 2001</p>
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
                <p>&copy; 2024 Crust Pizza. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadOrderSummary();
            setupOrderTypeToggle();
            setupPaymentOptions();
            updateCartCount();
        });

        function loadOrderSummary() {
            const cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
            const orderItemsDiv = document.getElementById('orderItems');

            if (cart.length === 0) {
                window.location.href = 'cart.php';
                return;
            }

            let itemsHTML = '';
            let subtotal = 0;

            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;

                itemsHTML += `
                    <div class="summary-item">
                        <div class="item-details">
                            <div class="item-name">${item.name}</div>
                            <div class="item-meta">
                                ${item.size ? `Size: ${item.size.charAt(0).toUpperCase() + item.size.slice(1)}` : ''}
                                ${item.customIngredients ? `<br>Ingredients: ${item.customIngredients.join(', ')}` : ''}
                                ${item.specialInstructions ? `<br>Instructions: ${item.specialInstructions}` : ''}
                                <br>Qty: ${item.quantity}
                            </div>
                        </div>
                        <div class="item-price">${formatCurrency(itemTotal)}</div>
                    </div>
                `;
            });

            orderItemsDiv.innerHTML = itemsHTML;

            // Update totals
            updateTotals(subtotal);

            // Store cart data in hidden field
            document.getElementById('cartData').value = JSON.stringify(cart);
        }

        function updateTotals(subtotal) {
            const tax = subtotal * 0.1;
            const orderType = document.querySelector('input[name="order_type"]:checked').value;
            const deliveryFee = orderType === 'delivery' ? (subtotal > 30 ? 0 : 5.99) : 0;
            const total = subtotal + tax + deliveryFee;

            document.getElementById('subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('tax').textContent = formatCurrency(tax);
            document.getElementById('deliveryFee').textContent = formatCurrency(deliveryFee);
            document.getElementById('total').textContent = formatCurrency(total);
        }

        function setupOrderTypeToggle() {
            const orderTypeInputs = document.querySelectorAll('input[name="order_type"]');
            const deliverySection = document.getElementById('deliverySection');

            orderTypeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Update option card selection
                    document.querySelectorAll('.option-card').forEach(card => {
                        card.classList.remove('selected');
                    });
                    this.closest('.option-card').classList.add('selected');

                    // Show/hide delivery section
                    if (this.value === 'delivery') {
                        deliverySection.style.display = 'block';
                        document.getElementById('delivery_address').required = true;
                    } else {
                        deliverySection.style.display = 'none';
                        document.getElementById('delivery_address').required = false;
                    }

                    // Update totals
                    const cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
                    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                    updateTotals(subtotal);
                });
            });

            // Set initial state
            document.querySelector('input[name="order_type"]:checked').closest('.option-card').classList.add('selected');
        }

        function setupPaymentOptions() {
            const paymentInputs = document.querySelectorAll('input[name="payment_method"]');
            paymentInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Update option card selection
                    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                        radio.closest('.option-card').classList.remove('selected');
                    });
                    this.closest('.option-card').classList.add('selected');
                });
            });

            // Set initial state
            document.querySelector('input[name="payment_method"]:checked').closest('.option-card').classList.add('selected');
        }

        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
            const cartCount = cart.reduce((total, item) => total + (item.quantity || 1), 0);
            document.getElementById('cartCount').textContent = cartCount;
        }

        // Form submission
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
            if (cart.length === 0) {
                e.preventDefault();
                alert('Your cart is empty');
                return;
            }

            // Show loading state
            const submitBtn = document.getElementById('placeOrderBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Order...';
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
</body>

</html>
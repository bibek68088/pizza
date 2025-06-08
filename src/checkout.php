<?php
require_once 'config/database.php';
require_once 'classes/Order.php';
require_once 'classes/User.php';
require_once 'classes/Cart.php';
require_once 'includes/functions.php';

startSession();

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('Your session has expired. Please log in again.', 'warning');
    header('Location: login.php?redirect=checkout.php');
    exit();
}

// Check if user_id is set
if (!isset($_SESSION['user_id'])) {
    error_log("Session user_id not set in checkout.php");
    setFlashMessage('Session error. Please log in again.', 'error');
    header('Location: login.php?redirect=checkout.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$user = new User($db);
$cart = new Cart($db);

// Get user details
$user_id = $_SESSION['user_id'];
$user_details = $user->getUserById($user_id);

// Debug user details
if (empty($user_details)) {
    error_log("User details not found for user_id: $user_id");
    $user_details = [
        'full_name' => '',
        'email' => $_SESSION['email'] ?? '',
        'phone' => '',
        'address_line_1' => '',
        'suburb' => '',
        'state' => '',
        'postcode' => ''
    ];
}

// Fetch default address from user_addresses table
$addresses = $user->getUserAddresses($user_id);
$default_address = !empty($addresses) ? array_filter($addresses, function ($addr) {
    return $addr['is_default'];
}) : [];
$default_address = !empty($default_address) ? reset($default_address) : [];

// Merge user details with default address
$user_details = array_merge($user_details, [
    'address_line_1' => $default_address['address_line_1'] ?? '',
    'suburb' => $default_address['suburb'] ?? '',
    'state' => $default_address['state'] ?? '',
    'postcode' => $default_address['postcode'] ?? ''
]);

// Check for incomplete profile
if (empty($user_details['full_name']) || empty($user_details['email'])) {
    setFlashMessage('Please complete your profile details before checking out.', 'warning');
    header('Location: profile.php?redirect=checkout.php');
    exit();
}

// Get user's cart items
$cart_items = $cart->getUserCart($user_id);
$cart_total = $cart->getCartTotal($user_id);

// If cart is empty, redirect to cart page
if (empty($cart_items)) {
    setFlashMessage('Your cart is empty. Please add items before checkout.', 'warning');
    header('Location: cart.php');
    exit();
}

// Process order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!validateCSRFToken($_POST['csrf_token'])) {
            throw new Exception('Invalid CSRF token.');
        }

        // Validate session
        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== $user_id) {
            throw new Exception('Session mismatch. Please log in again.');
        }

        // Get form data
        $order_type = sanitizeInput($_POST['order_type']);
        $payment_method = sanitizeInput($_POST['payment_method']);
        $customer_name = sanitizeInput($_POST['customer_name']);
        $customer_phone = sanitizeInput($_POST['customer_phone']);
        $customer_email = sanitizeInput($_POST['customer_email']);

        // Validate required fields
        if (empty($customer_name) || empty($customer_phone) || empty($customer_email)) {
            throw new Exception('Please fill in all required customer details.');
        }

        // Address fields (only for delivery)
        $delivery_address = '';
        $street = null;
        $suburb = null;
        $postcode = null;
        $state = null;
        if ($order_type === 'delivery') {
            $street = sanitizeInput($_POST['street']);
            $suburb = sanitizeInput($_POST['suburb']);
            $postcode = sanitizeInput($_POST['postcode']);
            $state = sanitizeInput($_POST['state']);

            // Validate address fields
            if (empty($street) || empty($suburb) || empty($state) || empty($postcode)) {
                throw new Exception('Please fill in all required delivery address fields.');
            }
            if (!preg_match('/^[0-9]{4}$/', $postcode)) {
                throw new Exception('Invalid postcode format.');
            }

            $delivery_address = "$street, $suburb, $state $postcode";
        }

        $special_instructions = sanitizeInput($_POST['special_instructions'] ?? '');

        // Calculate totals
        $subtotal = floatval($cart_total['subtotal']);
        $tax = $subtotal * 0.1;
        $delivery_fee = ($order_type === 'delivery' && $subtotal < 30) ? 5.99 : 0;
        $total = $subtotal + $tax + $delivery_fee;

        // Create order
        $order->user_id = $user_id;
        $order->store_id = 1;
        $order->order_type = $order_type;
        $order->payment_method = $payment_method;
        $order->customer_name = $customer_name;
        $order->customer_phone = $customer_phone;
        $order->customer_email = $customer_email;
        $order->delivery_address = $delivery_address;
        $order->delivery_instructions = $special_instructions; // Align with Order.php
        $order->special_requests = $special_instructions;
        $order->subtotal = $subtotal;
        $order->tax = $tax;
        $order->delivery_fee = $delivery_fee;
        $order->discount_amount = 0;
        $order->total = $total;
        $order->priority = 'normal';
        $order->estimated_prep_time = 30;

        if (!$order->create()) {
            throw new Exception('Failed to create order. Please try again.');
        }

        $order_id = $order->order_id;
        if (!$order_id) {
            throw new Exception('Order ID not generated.');
        }

        // Process cart items
        foreach ($cart_items as $item) {
            $pizza_id = !empty($item['pizza_id']) ? $item['pizza_id'] : null;
            $menu_item_id = !empty($item['menu_item_id']) ? $item['menu_item_id'] : null;

            $item_data = [
                'order_id' => $order_id,
                'item_type' => $item['item_type'] ?? 'pizza',
                'pizza_id' => $pizza_id,
                'menu_item_id' => $menu_item_id,
                'size' => $item['size'] ?? null,
                'quantity' => (int)$item['quantity'],
                'unit_price' => floatval($item['unit_price']),
                'total_price' => floatval($item['total_price']),
                'special_instructions' => $item['special_instructions'] ?? ''
            ];

            $order_item_id = $order->addOrderItem($item_data);
            if (!$order_item_id) {
                error_log("Failed to add order item: {$item['item_name']} for order_id: $order_id");
                throw new Exception("Failed to add item: {$item['item_name']}");
            }
        }

        // Update user profile
        $current_user = $user->getUserById($user_id);
        if ($current_user) {
            $user->user_id = $user_id;
            $user->full_name = $customer_name;
            $user->email = $customer_email;
            $user->phone = $customer_phone;
            $user->address = $street ?? $current_user['address'] ?? '';
            $user->date_of_birth = $current_user['date_of_birth'] ?? null;
            $user->role = $current_user['role'] ?? 'customer';
            $user->store_id = $current_user['store_id'] ?? null;
            $user->hire_date = $current_user['hire_date'] ?? null;
            $user->salary = $current_user['salary'] ?? null;
            $user->is_active = $current_user['is_active'] ?? 1;
            $user->email_verified = $current_user['email_verified'] ?? 0;

            if (!$user->update()) {
                error_log("Failed to update user details for user_id: $user_id");
            }
        } else {
            error_log("Failed to fetch current user details for update, user_id: $user_id");
        }

        // Update or add address if delivery
        if ($order_type === 'delivery') {
            $address_data = [
                'user_id' => $user_id,
                'address_type' => 'delivery',
                'address_line_1' => $street,
                'address_line_2' => '',
                'suburb' => $suburb,
                'state' => $state,
                'postcode' => $postcode,
                'country' => 'Australia',
                'is_default' => empty($default_address) ? 1 : 0,
                'delivery_instructions' => $special_instructions
            ];

            try {
                if (!$user->addAddress($address_data)) {
                    error_log("Failed to save delivery address for user_id: $user_id");
                }
            } catch (PDOException $e) {
                error_log("PDOException in addAddress for user_id: $user_id: " . $e->getMessage());
            }
        }

        // Clear user's cart
        if (!$cart->clearUserCart($user_id)) {
            error_log("Failed to clear cart for user_id: $user_id");
        }

        // Set success message and redirect
        setFlashMessage('Order placed successfully! Order ID: #' . $order_id, 'success');
        header('Location: order-confirmation.php?order_id=' . $order_id);
        exit();
    } catch (Exception $e) {
        error_log("Order processing error for user_id: $user_id: " . $e->getMessage());
        setFlashMessage('Error processing order: ' . $e->getMessage(), 'error');
    }
}

// Helper function to safely get user data
function getUserData($user_details, $key, $default = '')
{
    return isset($user_details[$key]) && $user_details[$key] !== null ? htmlspecialchars($user_details[$key]) : $default;
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
    <script>
        const isLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
    </script>
</head>

<body class="<?php echo isLoggedIn() ? 'logged-in' : ''; ?>">
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
                    <button class="dropdown-toggle" onclick="toggleDropdown()" aria-label="User Menu" aria-expanded="false">
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
                    <span class="cart-count" id="cartCount"><?php echo $cart_total['total_items'] ?? 0; ?></span>
                </a>
            </div>
        </div>
    </nav>

    <main style="margin-top: 80px; padding: 40px 20px;">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-credit-card"></i> Checkout</h1>
                <p>Complete your order</p>
            </div>

            <?php displayFlashMessages(); ?>

            <form id="checkoutForm" method="POST" style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem; margin-top: 2rem;">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="checkout-details">
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3><i class="fas fa-truck"></i> Order Type</h3>
                        </div>
                        <div class="card-body">
                            <div class="order-type-options" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <label class="option-card" style="padding: 1.5rem; border: 2px solid #ddd; border-radius: 12px; cursor: pointer; text-align: center; transition: all 0.3s ease;">
                                    <input type="radio" name="order_type" value="delivery" checked style="margin-bottom: 1rem;">
                                    <i class="fas fa-truck" style="font-size: 2rem; color: #ff6b35; margin-bottom: 0.5rem; display: block;"></i>
                                    <strong>Delivery</strong>
                                    <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.9rem;">Delivered to your door</p>
                                </label>
                                <label class="option-card" style="padding: 1.5rem; border: 2px solid #ddd; border-radius: 12px; cursor: pointer; text-align: center; transition: all 0.3s ease;">
                                    <input type="radio" name="order_type" value="pickup" style="margin-bottom: 1rem;">
                                    <i class="fas fa-store" style="font-size: 2rem; color: #ff6b35; margin-bottom: 0.5rem; display: block;"></i>
                                    <strong>Pickup</strong>
                                    <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.9rem;">Collect from store</p>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3><i class="fas fa-user"></i> Customer Details</h3>
                        </div>
                        <div class="card-body">
                            <div style="margin-bottom: 1rem;">
                                <label for="customer_name">Full Name *</label>
                                <input type="text" id="customer_name" name="customer_name" class="form-control"
                                    value="<?php echo getUserData($user_details, 'full_name'); ?>" required>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label for="customer_phone">Phone Number *</label>
                                    <input type="tel" id="customer_phone" name="customer_phone" class="form-control"
                                        value="<?php echo getUserData($user_details, 'phone'); ?>" required>
                                </div>
                                <div>
                                    <label for="customer_email">Email Address *</label>
                                    <input type="email" id="customer_email" name="customer_email" class="form-control"
                                        value="<?php echo getUserData($user_details, 'email'); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card delivery-section" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3><i class="fas fa-map-marker-alt"></i> Delivery Address</h3>
                        </div>
                        <div class="card-body">
                            <div style="margin-bottom: 1rem;">
                                <label for="street">Street Address *</label>
                                <input type="text" id="street" name="street" class="form-control"
                                    value="<?php echo getUserData($user_details, 'address_line_1'); ?>"
                                    placeholder="123 Main Street" required>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 100px; gap: 1rem;">
                                <div>
                                    <label for="suburb">Suburb *</label>
                                    <input type="text" id="suburb" name="suburb" class="form-control"
                                        value="<?php echo getUserData($user_details, 'suburb'); ?>"
                                        placeholder="Annandale" required>
                                </div>
                                <div>
                                    <label for="state">State *</label>
                                    <select id="state" name="state" class="form-control" required>
                                        <option value="">Select State</option>
                                        <option value="NSW" <?php echo (getUserData($user_details, 'state') === 'NSW') ? 'selected' : ''; ?>>NSW</option>
                                        <option value="VIC" <?php echo (getUserData($user_details, 'state') === 'VIC') ? 'selected' : ''; ?>>VIC</option>
                                        <option value="QLD" <?php echo (getUserData($user_details, 'state') === 'QLD') ? 'selected' : ''; ?>>QLD</option>
                                        <option value="WA" <?php echo (getUserData($user_details, 'state') === 'WA') ? 'selected' : ''; ?>>WA</option>
                                        <option value="SA" <?php echo (getUserData($user_details, 'state') === 'SA') ? 'selected' : ''; ?>>SA</option>
                                        <option value="TAS" <?php echo (getUserData($user_details, 'state') === 'TAS') ? 'selected' : ''; ?>>TAS</option>
                                        <option value="ACT" <?php echo (getUserData($user_details, 'state') === 'ACT') ? 'selected' : ''; ?>>ACT</option>
                                        <option value="NT" <?php echo (getUserData($user_details, 'state') === 'NT') ? 'selected' : ''; ?>>NT</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="postcode">Postcode *</label>
                                    <input type="text" id="postcode" name="postcode" class="form-control"
                                        value="<?php echo getUserData($user_details, 'postcode'); ?>"
                                        placeholder="2038" pattern="[0-9]{4}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                        </div>
                        <div class="card-body">
                            <div class="payment-options" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <label class="option-card" style="padding: 1.5rem; border: 2px solid #ddd; border-radius: 12px; cursor: pointer; text-align: center; transition: all 0.3s ease;">
                                    <input type="radio" name="payment_method" value="cash_on_delivery" checked style="margin-bottom: 1rem;">
                                    <i class="fas fa-money-bill-wave" style="font-size: 2rem; color: #28a745; margin-bottom: 0.5rem; display: block;"></i>
                                    <strong>Cash on Delivery</strong>
                                    <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.9rem;">Pay when you receive</p>
                                </label>
                                <label class="option-card" style="padding: 1.5rem; border: 2px solid #ddd; border-radius: 12px; cursor: pointer; text-align: center; transition: all 0.3s ease;">
                                    <input type="radio" name="payment_method" value="card_on_delivery" style="margin-bottom: 1rem;">
                                    <i class="fas fa-credit-card" style="font-size: 2rem; color: #007bff; margin-bottom: 0.5rem; display: block;"></i>
                                    <strong>Card on Delivery</strong>
                                    <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.9rem;">Pay by card on delivery</p>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-comment"></i> Special Instructions</h3>
                        </div>
                        <div class="card-body">
                            <textarea name="special_instructions" placeholder="Any special requests for your order..."
                                style="width: 100%; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; resize: vertical;" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="order-summary">
                    <div class="card" style="position: sticky; top: 2rem;">
                        <div class="card-header">
                            <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                        </div>
                        <div class="card-body">
                            <div id="orderItems" style="margin-bottom: 1.5rem;">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="order-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #eee;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                            <?php if (!empty($item['size'])): ?>
                                                <div style="font-size: 0.9rem; color: #666;">Size: <?php echo ucfirst(htmlspecialchars($item['size'])); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['custom_ingredients'])): ?>
                                                <div style="font-size: 0.8rem; color: #666;">Ingredients: <?php echo htmlspecialchars($item['custom_ingredients']); ?></div>
                                            <?php endif; ?>
                                            <div style="font-size: 0.9rem; color: #666;">Qty: <?php echo $item['quantity']; ?></div>
                                        </div>
                                        <div style="font-weight: 600; color: #ff6b35;">
                                            <?php echo formatCurrency($item['total_price']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <hr>

                            <div class="summary-details">
                                <div class="summary-line">
                                    <span>Subtotal:</span>
                                    <span id="summarySubtotal"><?php echo formatCurrency($cart_total['subtotal'] ?? 0); ?></span>
                                </div>
                                <div class="summary-line">
                                    <span>GST (10%):</span>
                                    <span id="summaryTax"><?php echo formatCurrency(($cart_total['subtotal'] ?? 0) * 0.1); ?></span>
                                </div>
                                <div class="summary-line delivery-fee-line">
                                    <span>Delivery Fee:</span>
                                    <span id="summaryDeliveryFee"><?php echo formatCurrency(($cart_total['subtotal'] ?? 0) < 30 ? 5.99 : 0); ?></span>
                                </div>
                                <div class="delivery-note">
                                    <small><i class="fas fa-info-circle"></i> Free delivery on orders over $30</small>
                                </div>
                                <hr>
                                <div class="summary-line total-line">
                                    <span><strong>Total:</strong></span>
                                    <span id="summaryTotal"><strong><?php
                                                                    $subtotal = $cart_total['subtotal'] ?? 0;
                                                                    $tax = $subtotal * 0.1;
                                                                    $delivery = $subtotal < 30 ? 5.99 : 0;
                                                                    echo formatCurrency($subtotal + $tax + $delivery);
                                                                    ?></strong></span>
                                </div>
                            </div>

                            <input type="hidden" name="subtotal" id="hiddenSubtotal" value="<?php echo $cart_total['subtotal'] ?? 0; ?>">
                            <input type="hidden" name="tax" id="hiddenTax" value="<?php echo ($cart_total['subtotal'] ?? 0) * 0.1; ?>">
                            <input type="hidden" name="delivery_fee" id="hiddenDeliveryFee" value="<?php echo ($cart_total['subtotal'] ?? 0) < 30 ? 5.99 : 0; ?>">
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: 600;">
                                <i class="fas fa-check"></i> Place Order
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setupFormHandlers();
            console.log('Customer Name:', document.getElementById('customer_name').value);
            console.log('Customer Phone:', document.getElementById('customer_phone').value);
            console.log('Customer Email:', document.getElementById('customer_email').value);
            console.log('Street:', document.getElementById('street').value);
            console.log('Suburb:', document.getElementById('suburb').value);
            console.log('State:', document.getElementById('state').value);
            console.log('Postcode:', document.getElementById('postcode').value);
        });

        function setupFormHandlers() {
            document.querySelectorAll('input[name="order_type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    updateOrderTotals();
                    toggleDeliverySection();
                    updateOptionCardStyles();
                });
            });

            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.addEventListener('change', updateOptionCardStyles);
            });

            toggleDeliverySection();
            updateOptionCardStyles();

            document.getElementById('checkoutForm').addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
            });
        }

        function updateOrderTotals() {
            const orderType = document.querySelector('input[name="order_type"]:checked').value;
            const subtotal = <?php echo $cart_total['subtotal'] ?? 0; ?>;
            const tax = subtotal * 0.1;
            const deliveryFee = (orderType === 'delivery' && subtotal < 30) ? 5.99 : 0;
            const total = subtotal + tax + deliveryFee;

            document.getElementById('summaryDeliveryFee').textContent = formatCurrency(deliveryFee);
            document.getElementById('summaryTotal').textContent = formatCurrency(total);
            document.getElementById('hiddenDeliveryFee').value = deliveryFee.toFixed(2);

            const deliveryFeeLine = document.querySelector('.delivery-fee-line');
            const deliveryNote = document.querySelector('.delivery-note');
            if (orderType === 'pickup') {
                deliveryFeeLine.style.display = 'none';
                deliveryNote.style.display = 'none';
            } else {
                deliveryFeeLine.style.display = 'flex';
                deliveryNote.style.display = 'block';
            }
        }

        function toggleDeliverySection() {
            const orderType = document.querySelector('input[name="order_type"]:checked').value;
            const deliverySection = document.querySelector('.delivery-section');
            const deliveryInputs = deliverySection.querySelectorAll('input, select');

            if (orderType === 'delivery') {
                deliverySection.style.display = 'block';
                deliveryInputs.forEach(input => input.required = true);
            } else {
                deliverySection.style.display = 'none';
                deliveryInputs.forEach(input => input.required = false);
            }
        }

        function updateOptionCardStyles() {
            document.querySelectorAll('input[name="order_type"]').forEach(radio => {
                const card = radio.closest('.option-card');
                if (radio.checked) {
                    card.style.borderColor = '#ff6b35';
                    card.style.backgroundColor = '#fff5f2';
                    card.style.transform = 'translateY(-2px)';
                    card.style.boxShadow = '0 8px 25px rgba(255, 107, 53, 0.2)';
                } else {
                    card.style.borderColor = '#ddd';
                    card.style.backgroundColor = 'white';
                    card.style.transform = 'translateY(0)';
                    card.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
                }
            });

            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                const card = radio.closest('.option-card');
                if (radio.checked) {
                    card.style.borderColor = '#ff6b35';
                    card.style.backgroundColor = '#fff5f2';
                    card.style.transform = 'translateY(-2px)';
                    card.style.boxShadow = '0 8px 25px rgba(255, 107, 53, 0.2)';
                } else {
                    card.style.borderColor = '#ddd';
                    card.style.backgroundColor = 'white';
                    card.style.transform = 'translateY(0)';
                    card.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
                }
            });
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-AU', {
                style: 'currency',
                currency: 'AUD'
            }).format(amount);
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
    </script>

    <style>
        .summary-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding: 0.25rem 0;
        }

        .total-line {
            font-size: 1.2rem;
            padding: 0.5rem 0;
            border-top: 2px solid #ff6b35;
            margin-top: 0.5rem;
        }

        .total-line span {
            color: #ff6b35;
        }

        .delivery-note {
            margin: 0.5rem 0;
            padding: 0.5rem;
            background: #e8f5e8;
            border-radius: 5px;
            text-align: center;
        }

        .delivery-note small {
            color: #28a745;
            font-weight: 500;
        }

        .option-card {
            transition: all 0.3s ease;
        }

        .option-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            form {
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
            }

            .order-summary {
                order: -1;
            }

            .order-type-options,
            .payment-options {
                grid-template-columns: 1fr !important;
            }

            .card {
                margin-bottom: 1.5rem !important;
            }
        }
    </style>
</body>

</html>
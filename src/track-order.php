<?php
require_once 'config/database.php';
require_once 'classes/Order.php';
require_once 'includes/functions.php';

startSession();

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

$order_details = null;
$order_items = [];
$error = '';

// Get order ID from URL or form
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : (isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0);

if ($order_id) {
    $order_details = $order->getOrderById($order_id);

    if ($order_details) {
        // Check if user owns this order (if logged in)
        if (isLoggedIn() && $order_details['user_id'] != $_SESSION['user_id']) {
            $error = 'Order not found or access denied';
            $order_details = null;
        } else {
            $order_items = $order->getOrderItems($order_id);
        }
    } else {
        $error = 'Order not found';
    }
}

// Handle order lookup form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['lookup_order'])) {
    $lookup_order_id = (int)$_POST['lookup_order_id'];
    $lookup_phone = sanitizeInput($_POST['lookup_phone']);

    if ($lookup_order_id && $lookup_phone) {
        $order_details = $order->getOrderById($lookup_order_id);

        if ($order_details && $order_details['customer_phone'] === $lookup_phone) {
            $order_items = $order->getOrderItems($lookup_order_id);
            $order_id = $lookup_order_id;
        } else {
            $error = 'Order not found or phone number does not match';
        }
    } else {
        $error = 'Please enter both order ID and phone number';
    }
}

?>

<?php include 'header.php'; ?>

<main style="margin-top: 70px; padding: 40px 20px;">
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-search"></i> Track Your Order</h1>
            <p>Enter your order details to track your pizza</p>
        </div>

        <?php displayFlashMessages(); ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!$order_details): ?>
            <!-- Order Lookup Form -->
            <div class="card" style="max-width: 500px; margin: 2rem auto;">
                <div class="card-header">
                    <h3 style="margin: 0;">Find Your Order</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="lookup_order_id">Order ID</label>
                            <input type="number" id="lookup_order_id" name="lookup_order_id" class="form-control"
                                placeholder="Enter your order ID" required>
                        </div>
                        <div class="form-group">
                            <label for="lookup_phone">Phone Number</label>
                            <input type="tel" id="lookup_phone" name="lookup_phone" class="form-control"
                                placeholder="Enter your phone number" required>
                        </div>
                        <button type="submit" name="lookup_order" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-search"></i> Track Order
                        </button>
                    </form>
                </div>
            </div>

            <?php if (isLoggedIn()): ?>
                <!-- Recent Orders for Logged-in Users -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3 style="margin: 0;">Your Recent Orders</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $user_orders = $order->getOrdersByUserId($_SESSION['user_id']);
                        if (empty($user_orders)):
                        ?>
                            <p style="text-align: center; color: #666;">No orders found</p>
                        <?php else: ?>
                            <div class="orders-list">
                                <?php foreach (array_slice($user_orders, 0, 5) as $user_order): ?>
                                    <div class="order-item fade-in-up">
                                        <div class="order-info">
                                            <div class="order-details">
                                                <h4>Order #<?php echo htmlspecialchars($user_order['order_id']); ?></h4>
                                                <p><?php echo date('M j, Y g:i A', strtotime($user_order['created_at'])); ?></p>
                                            </div>

                                            <div class="order-status">
                                                <div class="badge badge-<?php echo $user_order['status'] === 'completed' ? 'success' : 'primary'; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($user_order['status']))); ?>
                                                </div>
                                                <div class="order-total">
                                                    <?php echo formatCurrency($user_order['total']); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <a href="track-order.php?order_id=<?php echo htmlspecialchars($user_order['order_id']); ?>"
                                            class="btn-track">
                                            <i class="fas fa-search"></i>
                                            Track
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Order Details -->
            <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem; margin-top: 2rem;">
                <!-- Order Status and Timeline -->
                <div>
                    <!-- Order Status -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3 style="margin: 0;">Order #<?php echo $order_details['order_id']; ?></h3>
                            <div class="badge badge-<?php echo $order_details['status'] === 'completed' ? 'success' : 'primary'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $order_details['status'])); ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                <div>
                                    <h4>Order Type</h4>
                                    <p><i class="fas fa-<?php echo $order_details['order_type'] === 'delivery' ? 'truck' : 'store'; ?>"></i>
                                        <?php echo ucfirst($order_details['order_type']); ?></p>
                                </div>
                                <div>
                                    <h4>Order Date</h4>
                                    <p><?php echo date('M j, Y g:i A', strtotime($order_details['created_at'])); ?></p>
                                </div>
                                <div>
                                    <h4>Payment Method</h4>
                                    <p><i class="fas fa-<?php echo $order_details['payment_method'] === 'cash' ? 'money-bill' : 'credit-card'; ?>"></i>
                                        <?php echo ucfirst($order_details['payment_method']); ?></p>
                                </div>
                                <div>
                                    <h4>Total Amount</h4>
                                    <p style="font-weight: 600; color: #ff6b35;"><?php echo formatCurrency($order_details['total']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Timeline -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3 style="margin: 0;">Order Progress</h3>
                        </div>
                        <div class="card-body">
                            <div class="order-timeline">
                                <?php
                                $statuses = [
                                    'pending' => ['icon' => 'clock', 'label' => 'Order Received'],
                                    'confirmed' => ['icon' => 'check', 'label' => 'Order Confirmed'],
                                    'preparing' => ['icon' => 'fire', 'label' => 'Preparing Your Order'],
                                    'prepared' => ['icon' => 'check-circle', 'label' => 'Order Ready'],
                                    'out_for_delivery' => ['icon' => 'truck', 'label' => 'Out for Delivery'],
                                    'ready_for_pickup' => ['icon' => 'store', 'label' => 'Ready for Pickup'],
                                    'delivered' => ['icon' => 'home', 'label' => 'Delivered'],
                                    'completed' => ['icon' => 'star', 'label' => 'Order Completed']
                                ];

                                $current_status = $order_details['status'];
                                $status_order = array_keys($statuses);
                                $current_index = array_search($current_status, $status_order);

                                foreach ($statuses as $status => $info):
                                    $status_index = array_search($status, $status_order);
                                    $is_completed = $status_index <= $current_index;
                                    $is_current = $status === $current_status;

                                    // Skip delivery-specific statuses for pickup orders
                                    if ($order_details['order_type'] === 'pickup' && in_array($status, ['out_for_delivery', 'delivered'])) {
                                        continue;
                                    }
                                    // Skip pickup-specific statuses for delivery orders
                                    if ($order_details['order_type'] === 'delivery' && $status === 'ready_for_pickup') {
                                        continue;
                                    }
                                ?>
                                    <div class="timeline-item" style="display: flex; align-items: center; margin-bottom: 1rem; opacity: <?php echo $is_completed ? '1' : '0.5'; ?>;">
                                        <div class="timeline-icon" style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $is_completed ? '#ff6b35' : '#ddd'; ?>; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                                            <i class="fas fa-<?php echo $info['icon']; ?>" style="color: white; font-size: 0.9rem;"></i>
                                        </div>
                                        <div>
                                            <h4 style="margin: 0; color: <?php echo $is_completed ? '#333' : '#999'; ?>;">
                                                <?php echo $info['label']; ?>
                                                <?php if ($is_current): ?>
                                                    <span class="badge badge-primary" style="margin-left: 0.5rem;">Current</span>
                                                <?php endif; ?>
                                            </h4>
                                            <?php if ($is_completed): ?>
                                                <p style="margin: 0; color: #666; font-size: 0.9rem;">
                                                    <?php echo timeAgo($order_details['created_at']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card">
                        <div class="card-header">
                            <h3 style="margin: 0;">Order Items</h3>
                        </div>
                        <div class="card-body">
                            <?php foreach ($order_items as $item): ?>
                                <div class="order-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid #eee;">
                                    <div>
                                        <h4 style="margin: 0;">
                                            <?php echo $item['pizza_name'] ?: $item['menu_item_name']; ?>
                                            <?php if ($item['size']): ?>
                                                <span style="color: #666;">(<?php echo ucfirst($item['size']); ?>)</span>
                                            <?php endif; ?>
                                        </h4>
                                        <?php if ($item['special_instructions']): ?>
                                            <p style="margin: 0; color: #666; font-size: 0.9rem;">
                                                Instructions: <?php echo htmlspecialchars($item['special_instructions']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div style="text-align: right;">
                                        <div>Qty: <?php echo $item['quantity']; ?></div>
                                        <div style="font-weight: 600;"><?php echo formatCurrency($item['total_price']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div>
                    <!-- Customer Details -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3 style="margin: 0;">Customer Details</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($order_details['customer_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order_details['customer_phone']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order_details['customer_email']); ?></p>

                            <?php if ($order_details['order_type'] === 'delivery' && $order_details['delivery_address']): ?>
                                <p><strong>Delivery Address:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($order_details['delivery_address'])); ?></p>

                                <?php if ($order_details['delivery_instructions']): ?>
                                    <p><strong>Instructions:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($order_details['delivery_instructions'])); ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Order Total -->
                    <div class="card">
                        <div class="card-header">
                            <h3 style="margin: 0;">Order Total</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Subtotal:</span>
                                <span><?php echo formatCurrency($order_details['subtotal']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>GST (10%):</span>
                                <span><?php echo formatCurrency($order_details['tax']); ?></span>
                            </div>
                            <?php if ($order_details['delivery_fee'] > 0): ?>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Delivery Fee:</span>
                                    <span><?php echo formatCurrency($order_details['delivery_fee']); ?></span>
                                </div>
                            <?php endif; ?>
                            <hr>
                            <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 1.1rem;">
                                <span>Total:</span>
                                <span><?php echo formatCurrency($order_details['total']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Store Information -->
                    <div class="card" style="margin-top: 2rem;">
                        <div class="card-header">
                            <h3 style="margin: 0;">Store Information</h3>
                        </div>
                        <div class="card-body">
                            <p><strong><?php echo htmlspecialchars($order_details['store_name']); ?></strong></p>
                            <p><?php echo htmlspecialchars($order_details['store_address']); ?></p>
                            <p><i class="fas fa-phone"></i> 1300 CRUST (1300 278 787)</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
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

    // Auto-refresh order status every 30 seconds if order is active
    <?php if ($order_details && !in_array($order_details['status'], ['completed', 'cancelled'])): ?>
        setInterval(function() {
            location.reload();
        }, 30000);
    <?php endif; ?>

    function updateCartCount() {
        if (!isUserLoggedIn()) return;
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
    /* General Reset */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    /* Container */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }

    /* Section Header */
    .section-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .section-header h2 {
        font-size: 2.25rem;
        font-weight: 700;
        color: #2d2d2d;
        letter-spacing: -0.5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .section-header h2 i {
        color: #ff6b35;
    }

    .section-subtitle {
        font-size: 1rem;
        color: #555;
        font-weight: 400;
        max-width: 500px;
        margin: 0.5rem auto 0;
        line-height: 1.5;
    }

    /* Cards */
    .card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

    .card-header {
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: white;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h3 {
        font-size: 1.25rem;
        font-weight: 600;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Fade-in Animation */
    .fade-in-up {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }

    /* Primary Buttons */
    .btn-primary {
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: #fff;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 500;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.3);
    }

    /* General Outline Button (for other parts of the site) */
    .btn-outline {
        background: transparent;
        border: 2px solid #ff6b35;
        color: #ff6b35;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.9rem;
        text-align: center;
        text-decoration: none;
        transition: background 0.3s ease, color 0.3s ease, transform 0.3s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-outline:hover {
        background: #ff6b35;
        color: #fff;
        transform: translateY(-2px);
    }

    /* Recent Orders List Styling */
    .orders-list {
        border-radius: 8px;
        overflow: hidden;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #eee;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .order-item:hover {
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
        transform: translateX(5px);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .order-item:last-child {
        border-bottom: none;
    }

    /* Order Item Content Layout */
    .order-info {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .order-details {
        flex: 1;
    }

    .order-details h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: #2d2d2d;
    }

    .order-details p {
        margin: 0.25rem 0 0 0;
        color: #666;
        font-size: 0.85rem;
    }

    .order-status {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.5rem;
        margin-right: 1rem;
    }

    .order-total {
        font-weight: 600;
        font-size: 0.95rem;
        color: #ff6b35;
    }

    /* Improved Track Button - Small and Stylish */
    .btn-track {
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: #fff;
        border: none;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        min-width: 70px;
        box-shadow: 0 2px 8px rgba(255, 107, 53, 0.2);
        cursor: pointer;
    }

    .btn-track:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.35);
        background: linear-gradient(135deg, #f7931e, #ff6b35);
        color: #fff;
    }

    .btn-track:active {
        transform: translateY(0) scale(0.98);
        transition: transform 0.1s ease;
    }

    .btn-track i {
        font-size: 0.7rem;
    }

    /* Alternative Track Button Style (Outline) */
    .btn-track-outline {
        background: transparent;
        color: #ff6b35;
        border: 1.5px solid #ff6b35;
        padding: 0.35rem 0.75rem;
        border-radius: 18px;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        min-width: 70px;
        cursor: pointer;
    }

    .btn-track-outline:hover {
        background: #ff6b35;
        color: #fff;
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }

    .btn-track-outline:active {
        transform: translateY(0) scale(0.98);
        transition: transform 0.1s ease;
    }

    .btn-track-outline i {
        font-size: 0.7rem;
    }

    /* Badges */
    .badge {
        padding: 0.3rem 0.6rem;
        border-radius: 15px;
        font-size: 0.7rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: #fff;
        box-shadow: 0 2px 6px rgba(0, 123, 255, 0.2);
    }

    .badge-success {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: #fff;
        box-shadow: 0 2px 6px rgba(40, 167, 69, 0.2);
    }

    .badge-warning {
        background: linear-gradient(135deg, #ffc107, #f39c12);
        color: #fff;
        box-shadow: 0 2px 6px rgba(255, 193, 7, 0.2);
    }

    /* Notifications */
    .cart-notification {
        position: fixed;
        top: 90px;
        right: 20px;
        background: linear-gradient(135deg, #28a745, #20c997);
        color: #fff;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        z-index: 1000;
        font-size: 0.9rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        animation: slideIn 0.4s ease-out;
    }

    .cart-notification.error {
        background: linear-gradient(135deg, #dc3545, #e74c3c);
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
    }

    .cart-notification.warning {
        background: linear-gradient(135deg, #ffc107, #f39c12);
        box-shadow: 0 6px 20px rgba(255, 193, 7, 0.3);
    }

    .cart-notification.info {
        background: linear-gradient(135deg, #17a2b8, #3498db);
        box-shadow: 0 6px 20px rgba(23, 162, 184, 0.3);
    }

    .cart-notification.slide-out {
        animation: slideOut 0.4s ease-in;
    }

    @keyframes slideIn {
        from {
            transform: translateX(120%);
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
            transform: translateX(120%);
            opacity: 0;
        }
    }

    /* Form Controls */
    .form-group {
        margin-bottom: 1.2rem;
    }

    .form-group label {
        font-size: 0.9rem;
        font-weight: 500;
        color: #2d2d2d;
        margin-bottom: 0.4rem;
        display: block;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        font-size: 0.95rem;
        color: #333;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #ff6b35;
        box-shadow: 0 0 8px rgba(255, 107, 53, 0.2);
    }

    /* Alerts */
    .alert-error {
        background: linear-gradient(135deg, #dc3545, #e74c3c);
        color: #fff;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
    }

    /* Timeline */
    .timeline-item {
        transition: opacity 0.3s ease;
    }

    .timeline-icon {
        flex-shrink: 0;
        transition: background 0.3s ease;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .section-header h2 {
            font-size: 1.75rem;
        }

        .section-subtitle {
            font-size: 0.9rem;
        }

        .card {
            margin-bottom: 1.5rem;
        }

        .card-header h3 {
            font-size: 1.1rem;
        }

        .btn-primary {
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
        }

        .form-control {
            font-size: 0.9rem;
            padding: 0.6rem;
        }

        .grid {
            grid-template-columns: 1fr !important;
        }

        /* Mobile layout for order items */
        .order-item {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
            padding: 1rem 0.75rem;
        }

        .order-info {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }

        .order-status {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            margin-right: 0;
        }

        .btn-track,
        .btn-track-outline {
            align-self: flex-end;
            min-width: 80px;
        }
    }

    @media (max-width: 576px) {
        .cart-notification {
            top: 80px;
            right: 10px;
            left: 10px;
            font-size: 0.85rem;
            padding: 0.6rem 1rem;
        }

        .card-body {
            padding: 1rem;
        }

        .order-item {
            padding: 0.75rem 0.5rem;
        }

        .order-details h4 {
            font-size: 0.95rem;
        }

        .order-details p {
            font-size: 0.8rem;
        }

        .btn-track,
        .btn-track-outline {
            font-size: 0.7rem;
            padding: 0.35rem 0.7rem;
            min-width: 65px;
        }
    }
</style>
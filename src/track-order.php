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
                                    <div class="order-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid #eee;">
                                        <div>
                                            <h4 style="margin: 0;">Order #<?php echo $user_order['order_id']; ?></h4>
                                            <p style="margin: 0; color: #666; font-size: 0.9rem;">
                                                <?php echo date('M j, Y g:i A', strtotime($user_order['created_at'])); ?>
                                            </p>
                                        </div>
                                        <div style="text-align: right;">
                                            <div class="badge badge-<?php echo $user_order['status'] === 'completed' ? 'success' : 'primary'; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $user_order['status'])); ?>
                                            </div>
                                            <div style="font-weight: 600; margin-top: 0.25rem;">
                                                <?php echo formatCurrency($user_order['total']); ?>
                                            </div>
                                        </div>
                                        <a href="track-order.php?order_id=<?php echo $user_order['order_id']; ?>" class="btn btn-outline">
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

    .cart-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
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
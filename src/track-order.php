<?php

/**
 * Order Tracking Page
 * Allows customers to track their orders
 * Crust Pizza Online Ordering System
 */

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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - Crust Pizza</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Header -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice"></i>
                <p><a href="index.php" style="text-decoration: none; color: inherit;">Crust Pizza</a></p>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="menu.php" class="nav-link">Menu</a>
                <a href="build-pizza.php" class="nav-link">Build Your Pizza</a>
                <a href="track-order.php" class="nav-link active">Track Order</a>
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

    <main style="margin-top: 70px;">
        <div class="container" style="padding: 2rem 20px;">
            <div class="page-header" style="text-align: center; margin-bottom: 2rem;">
                <h2><i class="fas fa-search"></i> Track Your Order</h2>
                <p>Enter your order details to track your pizza</p>
            </div>

            <!-- Flash Messages -->
            <?php displayFlashMessages(); ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!$order_details): ?>
                <!-- Order Lookup Form -->
                <div class="card" style="max-width: 500px; margin: 2rem auto; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); overflow: hidden;">
                    <div class="card-header" style="background: #f8f9fa; padding: 1rem; border-bottom: 1px solid #ddd;">
                        <h3 style="margin: 0;">Find Your Order</h3>
                    </div>
                    <div class="card-body" style="padding: 1.5rem;">
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
                    <div class="card" style="margin-top: 2rem; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); overflow: hidden;">
                        <div class="card-header" style="background: #f8f9fa; padding: 1rem; border-bottom: 1px solid #ddd;">
                            <h3 style="margin: 0;">Your Recent Orders</h3>
                        </div>
                        <div class="card-body" style="padding: 1.5rem;">
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
                                                <div class="status-badge status-<?php echo $user_order['status']; ?>">
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
                <div class="order-tracking-layout">
                    <!-- Order Status and Timeline -->
                    <div class="order-main">
                        <!-- Order Status -->
                        <div class="card mb-4">
                            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                <h3 style="margin: 0;">Order #<?php echo $order_details['order_id']; ?></h3>
                                <div class="status-badge status-<?php echo $order_details['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $order_details['status'])); ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="order-info-grid">
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
                        <div class="card mb-4">
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
                                                        <span class="status-badge" style="margin-left: 0.5rem; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; background: #ff6b35; color: white;">Current</span>
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
                    <div class="order-sidebar">
                        <!-- Customer Details -->
                        <div class="card mb-4">
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
                        <div class="card mb-4">
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
                        <div class="card">
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
                <p>&copy; 2024 Crust Pizza. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        // Auto-refresh order status every 30 seconds if order is active
        <?php if ($order_details && !in_array($order_details['status'], ['completed', 'cancelled'])): ?>
            setInterval(function() {
                location.reload();
            }, 30000);
        <?php endif; ?>
    </script>

    <style>
        /* Track Order Page Specific Styles */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #ddd;
        }

        .card-body {
            padding: 1.5rem;
        }

        .order-tracking-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            margin-top: 2rem;
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .order-item:last-child {
            border-bottom: none !important;
        }

        @media (max-width: 768px) {
            .order-tracking-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>

</html>
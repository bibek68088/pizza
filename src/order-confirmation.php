<?php
require_once 'config/database.php';
require_once 'classes/Order.php';
require_once 'includes/functions.php';

startSession();

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('Please log in to view your order.', 'warning');
    header('Location: login.php?redirect=order-confirmation.php?order_id=' . ($_GET['order_id'] ?? ''));
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    setFlashMessage('Invalid order ID.', 'error');
    header('Location: index.php');
    exit();
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

// Fetch order details
$order_details = $order->getOrderById($order_id);

// Check if order exists and belongs to the user
if (!$order_details || $order_details['user_id'] !== $user_id) {
    setFlashMessage('Order not found or you do not have permission to view it.', 'error');
    header('Location: index.php');
    exit();
}

// Fetch order items
$order_items = $order->getOrderItems($order_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Crust Pizza</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="logged-in">
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
                    <span class="cart-count" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </nav>

    <main style="margin-top: 80px; padding: 40px 20px;">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-check-circle"></i> Order Confirmation</h1>
                <p>Thank you for your order!</p>
            </div>

            <?php displayFlashMessages(); ?>

            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3>Order #<?php echo htmlspecialchars($order_details['order_number']); ?></h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div>
                            <h4>Order Details</h4>
                            <p><strong>Order Date:</strong> <?php echo date('d/m/Y H:i', strtotime($order_details['created_at'])); ?></p>
                            <p><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($order_details['status'])); ?></p>
                            <p><strong>Order Type:</strong> <?php echo ucfirst(htmlspecialchars($order_details['order_type'])); ?></p>
                            <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($order_details['payment_method']))); ?></p>
                            <?php if ($order_details['order_type'] === 'delivery'): ?>
                                <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($order_details['delivery_address']); ?></p>
                                <?php if (!empty($order_details['delivery_instructions'])): ?>
                                    <p><strong>Delivery Instructions:</strong> <?php echo htmlspecialchars($order_details['delivery_instructions']); ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (!empty($order_details['special_requests'])): ?>
                                <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($order_details['special_requests']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4>Customer Details</h4>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($order_details['customer_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order_details['customer_phone']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order_details['customer_email']); ?></p>
                        </div>
                    </div>

                    <hr style="margin: 1.5rem 0;">

                    <h4>Order Items</h4>
                    <div style="margin-bottom: 1.5rem;">
                        <?php foreach ($order_items as $item): ?>
                            <div class="order-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #eee;">
                                <div style="flex: 1;">
                                    <div style="font-weight: 600;">
                                        <?php echo htmlspecialchars($item['pizza_name'] ?? $item['menu_item_name'] ?? 'Custom Item'); ?>
                                    </div>
                                    <?php if (!empty($item['size'])): ?>
                                        <div style="font-size: 0.9rem; color: #666;">Size: <?php echo ucfirst(htmlspecialchars($item['size'])); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['special_instructions'])): ?>
                                        <div style="font-size: 0.8rem; color: #666;">Instructions: <?php echo htmlspecialchars($item['special_instructions']); ?></div>
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
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                            <span>Subtotal:</span>
                            <span><?php echo formatCurrency($order_details['subtotal']); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                            <span>GST (10%):</span>
                            <span><?php echo formatCurrency($order_details['tax']); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                            <span>Delivery Fee:</span>
                            <span><?php echo formatCurrency($order_details['delivery_fee']); ?></span>
                        </div>
                        <?php if ($order_details['discount_amount'] > 0): ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                                <span>Discount:</span>
                                <span>-<?php echo formatCurrency($order_details['discount_amount']); ?></span>
                            </div>
                        <?php endif; ?>
                        <hr>
                        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 600; color: #ff6b35;">
                            <span>Total:</span>
                            <span><?php echo formatCurrency($order_details['total']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="track-order.php?order_id=<?php echo $order_id; ?>" class="btn btn-primary" style="padding: 1rem;">
                        <i class="fas fa-map-marker-alt"></i> Track Order
                    </a>
                    <a href="index.php" class="btn btn-secondary" style="padding: 1rem; margin-left: 1rem;">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
    <script>
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
        .card {
            border: 2px solid #ddd;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .card-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-footer {
            padding: 1rem;
            text-align: right;
        }

        .btn-primary {
            background-color: #ff6b35;
            border: none;
            color: white;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #e55a2e;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
            color: white;
            transition: background-color 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        @media (max-width: 768px) {
            .card-body>div {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</body>

</html>
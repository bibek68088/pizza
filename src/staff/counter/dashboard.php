<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../classes/Order.php';
require_once '../../classes/Pizza.php';
require_once '../../includes/functions.php';

startSession();

if (!hasPermission('counter_access')) {
    setFlashMessage('Access denied.', 'error');
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$pizza = new Pizza($db);

// Define BASE_PATH
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR);
}

// Fetch orders
$preparedOrders = $order->getOrdersByStatus('prepared', null, ['order_type' => 'pickup']);
$pickupOrders = $order->getOrdersByStatus('ready_for_pickup');

// Debug: Log and display prepared orders
error_log("Prepared Orders: " . print_r($preparedOrders, true));
// echo "<pre>Prepared Orders: " . print_r($preparedOrders, true) . "</pre>"; // Uncomment for browser output, remove after testing

// Helper function to get custom ingredients
function getOrderItemCustomIngredients($db, $order_item_id) {
    $query = "SELECT i.name, oii.quantity 
              FROM order_item_ingredients oii 
              JOIN ingredients i ON oii.ingredient_id = i.ingredient_id 
              WHERE oii.order_item_id = :order_item_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':order_item_id', $order_item_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $order_id = (int)$_POST['order_id'];
        $status = sanitizeInput($_POST['status']);
        $allowedStatuses = ['ready_for_pickup', 'received_by_customer'];

        if (!in_array($status, $allowedStatuses)) {
            setFlashMessage('Invalid status update.', 'error');
        } elseif ($order->updateStatus($order_id, $status, getCurrentStaffId())) {
            $message = match ($status) {
                'ready_for_pickup' => 'Your order is ready for pickup.',
                'received_by_customer' => 'Thank you for picking up your order!',
                default => ''
            };
            sendNotification($order_id, $status, $message);
            setFlashMessage("Order status updated to $status.", 'success');
            logActivity('update_order_status', "Counter staff updated order ID: $order_id to $status", getCurrentStaffId());
        } else {
            setFlashMessage('Failed to update order status.', 'error');
        }
    }
    redirect('dashboard.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counter Dashboard - Crust Pizza</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff6b35;
            --hover-color: #f7931e;
            --success-color: #28a745;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }

        .navbar {
            background: #fff;
            color: #333;
            padding: 1rem 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: var(--shadow);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            color: #ff6b35;
        }

        .nav-brand a {
            color: #ff6b35;
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-link {
            color: #333;
            text-decoration: none;
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: color 0.2s ease, background 0.2s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color);
        }

        .nav-toggle {
            display: none;
            background: none;
            border: none;
            color: #333;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 8px;
        }

        main {
            margin-top: 80px;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .page-header p {
            color: #666;
            font-size: 1.1rem;
            margin: 0;
        }

        .orders-section h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
        }

        .order-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .order-header h3 {
            color: var(--primary-color);
            font-size: 1.4rem;
            margin: 0;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-prepared {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-ready {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .order-details p {
            margin: 0.5rem 0;
            color: #333;
            font-size: 1rem;
        }

        .order-details p strong {
            color: var(--primary-color);
        }

        .order-items h4 {
            color: #333;
            font-size: 1.2rem;
            margin: 1rem 0 0.5rem;
        }

        .item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .item-list li {
            margin-bottom: 1rem;
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .item-list li strong {
            color: #333;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .item-details {
            color: #666;
            font-size: 0.9rem;
        }

        .ingredients, .special-instructions {
            margin: 0.5rem 0;
            padding: 0.5rem;
            background: white;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .ingredients strong, .special-instructions strong {
            color: var(--primary-color);
        }

        .order-actions {
            margin-top: 1rem;
            text-align: right;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: var(--hover-color);
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--success-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-success:hover {
            background: #34ce57;
            transform: translateY(-2px);
        }

        .btn-primary i, .btn-success i {
            font-size: 0.9rem;
        }

        .no-orders {
            text-align: center;
            padding: 3rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .no-orders i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .no-orders p {
            color: #666;
            font-size: 1.2rem;
            margin: 0;
        }

        .flash-message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .flash-message.success {
            background: #e8f5e8;
            color: #28a745;
        }

        .flash-message.error {
            background: #f8d7da;
            color: #dc3545;
        }

        .flash-message i {
            font-size: 1.2rem;
        }

        .footer {
            background: #333;
            color: white;
            padding: 1rem 0;
            margin-top: 2rem;
        }

        .footer-bottom {
            text-align: center;
        }

        .footer-bottom p {
            margin: 0;
            font-size: 0.9rem;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            color: #333;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .dropdown-toggle:hover, .dropdown-toggle:focus {
            color: var(--primary-color);
        }

        .user-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            background: #fff;
            border: 2px solid var(--primary-color);
            border-radius: 50%;
            box-shadow: var(--shadow);
        }

        .user-icon i {
            font-size: 1rem;
            color: var(--primary-color);
        }

        .dropdown-arrow::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 0.7rem;
            color: #333;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: -12px;
            background: #fff;
            min-width: 120px;
            border-radius: 4px;
            box-shadow: var(--shadow);
            z-index: 1001;
            padding: 4px 0;
            margin-top: 10px;
            opacity: 0;
            transform: translateY(-8px);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .dropdown-menu.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown-item {
            display: block;
            padding: 6px 12px;
            color: #333;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .dropdown-item:hover, .dropdown-item:focus {
            background: var(--primary-color);
            color: #fff;
        }

        @media (max-width: 767px) {
            .nav-toggle {
                display: block;
            }

            .nav-menu {
                display: none;
                position: absolute;
                top: 80px;
                left: 0;
                background: #fff;
                width: 100%;
                box-shadow: var(--shadow);
                flex-direction: column;
                padding: 10px 0;
                z-index: 1000;
            }

            .nav-menu.active {
                display: flex;
            }

            .nav-link, .dropdown {
                padding: 0.5rem 1rem;
                width: 100%;
                text-align: left;
            }

            .dropdown-menu {
                position: static;
                width: 100%;
                box-shadow: none;
                margin-top: 0;
                padding: 0 0 0 20px;
                background: #f8f9fa;
                border-radius: 0;
            }

            .container {
                padding: 0 1rem;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .item-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-actions {
                text-align: center;
            }

            .btn-primary, .btn-success {
                width: 100%;
                justify-content: center;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .orders-section h2 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .order-card {
                padding: 1rem;
            }

            .btn-primary, .btn-success {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            .item-list li {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                    <i class="fas fa-pizza-slice"></i>
                    <p><a href="../../index.php" style="text-decoration: none; color: inherit;">Crust Pizza</a></p>
                </div>
                <button class="nav-toggle" aria-label="Toggle Navigation">
                    <i class="fas fa-bars"></i>
                </button>
            <div class="nav-menu" id="navMenu">
                <a href="dashboard.php" class="nav-link active">Kitchen Dashboard</a>
                <a href="../../logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-cash-register"></i> Counter Dashboard</h1>
                <p>Manage pickup orders and customer service</p>
            </div>

            <?php displayFlashMessages(); ?>

            <div class="orders-section">
                <h2>Prepared Pickup Orders</h2>
                <?php if (empty($preparedOrders) || !is_array($preparedOrders)): ?>
                    <div class="no-orders">
                        <i class="fas fa-clipboard-check"></i>
                        <p>No prepared pickup orders available.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($preparedOrders as $orderData): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h3>Order #<?php echo htmlspecialchars($orderData['order_number'] ?? 'N/A'); ?></h3>
                                <span class="status-badge status-prepared">Prepared</span>
                            </div>
                            <div class="order-details">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($orderData['customer_name'] ?? 'Unknown'); ?></p>
                                <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($orderData['special_requests'] ?? 'None'); ?></p>
                                <p><strong>Placed:</strong> <?php echo htmlspecialchars(timeAgo($orderData['created_at'] ?? date('Y-m-d H:i:s'))); ?></p>
                            </div>
                            <div class="order-items">
                                <h4>Items</h4>
                                <ul class="item-list">
                                    <?php 
                                    $orderItems = $order->getOrderItems($orderData['order_id'] ?? 0);
                                    if (!empty($orderItems) && is_array($orderItems)):
                                        foreach ($orderItems as $item): ?>
                                            <li>
                                                <div class="item-header">
                                                    <strong><?php echo htmlspecialchars($item['pizza_name'] ?? $item['menu_item_name'] ?? 'Unknown Item'); ?></strong>
                                                    <span class="item-details">(Size: <?php echo htmlspecialchars($item['size'] ?? 'N/A'); ?>, Qty: <?php echo htmlspecialchars($item['quantity'] ?? 1); ?>)</span>
                                                </div>
                                                <?php if (!empty($item['pizza_id'])): ?>
                                                    <?php
                                                    $ingredients = $pizza->getPizzaIngredients($item['pizza_id']);
                                                    if (!empty($ingredients) && is_array($ingredients)):
                                                        echo '<div class="ingredients"><strong>Ingredients:</strong> ';
                                                        $ingredientList = [];
                                                        foreach ($ingredients as $ingredient) {
                                                            $ingredientList[] = htmlspecialchars($ingredient['name'] ?? 'Unknown') . ' (' . htmlspecialchars($ingredient['quantity'] ?? 'N/A') . ')';
                                                        }
                                                        echo htmlspecialchars(implode(', ', $ingredientList)) . '</div>';
                                                    endif; ?>
                                                <?php endif; ?>
                                                <?php
                                                $customIngredients = getOrderItemCustomIngredients($db, $item['order_item_id'] ?? 0);
                                                if (!empty($customIngredients) && is_array($customIngredients)):
                                                    echo '<div class="ingredients"><strong>Custom Ingredients:</strong> ';
                                                    $customList = [];
                                                    foreach ($customIngredients as $ingredient) {
                                                        $customList[] = htmlspecialchars($ingredient['name'] ?? 'Unknown') . ' (' . htmlspecialchars($ingredient['quantity'] ?? 'N/A') . ')';
                                                    }
                                                    echo htmlspecialchars(implode(', ', $customList)) . '</div>';
                                                endif; ?>
                                                <?php if (!empty($item['special_instructions'])): ?>
                                                    <div class="special-instructions">
                                                        <strong>Instructions:</strong> <?php echo htmlspecialchars($item['special_instructions']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li>No items found for this order.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <form method="POST" class="order-actions">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($orderData['order_id'] ?? 0); ?>">
                                <input type="hidden" name="status" value="ready_for_pickup">
                                <button type="submit" name="update_status" class="btn-primary">
                                    <i class="fas fa-check"></i> Mark as Ready for Pickup
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="orders-section">
                <h2>Orders Ready for Pickup</h2>
                <?php if (empty($pickupOrders) || !is_array($pickupOrders)): ?>
                    <div class="no-orders">
                        <i class="fas fa-box-open"></i>
                        <p>No orders ready for pickup.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pickupOrders as $orderData): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h3>Order #<?php echo htmlspecialchars($orderData['order_number'] ?? 'N/A'); ?></h3>
                                <span class="status-badge status-ready">Ready for Pickup</span>
                            </div>
                            <div class="order-details">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($orderData['customer_name'] ?? 'Unknown'); ?></p>
                                <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($orderData['special_requests'] ?? 'None'); ?></p>
                                <p><strong>Placed:</strong> <?php echo htmlspecialchars(timeAgo($orderData['created_at'] ?? date('Y-m-d H:i:s'))); ?></p>
                            </div>
                            <form method="POST" class="order-actions">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($orderData['order_id'] ?? 0); ?>">
                                <input type="hidden" name="status" value="received_by_customer">
                                <button type="submit" name="update_status" class="btn-success">
                                    <i class="fas fa-handshake"></i> Mark as Received by Customer
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>© 2025 Crust Pizza. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function toggleDropdown() {
                const dropdownMenu = document.getElementById('dropdownMenu');
                const isOpen = dropdownMenu.classList.toggle('show');
                document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', isOpen);
            }

            function toggleNavMenu() {
                const navMenu = document.getElementById('navMenu');
                navMenu.classList.toggle('active');
            }

            const navToggle = document.querySelector('.nav-toggle');
            if (navToggle) {
                navToggle.addEventListener('click', toggleNavMenu);
            }

            document.addEventListener('click', function(event) {
                const dropdown = document.querySelector('.dropdown');
                const dropdownMenu = document.getElementById('dropdownMenu');
                const navMenu = document.getElementById('navMenu');
                if (dropdown && !dropdown.contains(event.target) && navToggle && !navToggle.contains(event.target)) {
                    if (dropdownMenu) dropdownMenu.classList.remove('show');
                    if (navMenu) navMenu.classList.remove('active');
                    const dropdownToggle = document.querySelector('.dropdown-toggle');
                    if (dropdownToggle) dropdownToggle.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const dropdownMenu = document.getElementById('dropdownMenu');
                    const navMenu = document.getElementById('navMenu');
                    if (dropdownMenu) dropdownMenu.classList.remove('show');
                    if (navMenu) navMenu.classList.remove('active');
                    const dropdownToggle = document.querySelector('.dropdown-toggle');
                    if (dropdownToggle) dropdownToggle.setAttribute('aria-expanded', 'false');
                }
            });

            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (navbar) {
                    if (window.scrollY > 50) {
                        navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                        navbar.style.boxShadow = '0 4px 25px rgba(0, 0, 0, 0.15)';
                    } else {
                        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                        navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
                    }
                }
            });

            // Auto-refresh orders every 30 seconds
            setInterval(function() {
                window.location.reload();
            }, 30000);
        });
    </script>
</body>

</html>
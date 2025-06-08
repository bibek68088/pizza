<?php
// Define BASE_PATH only if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
}

require_once BASE_PATH . 'config/database.php';
require_once BASE_PATH . 'classes/Order.php';
require_once BASE_PATH . 'classes/Pizza.php';
require_once BASE_PATH . 'includes/functions.php';

startSession();

if (!hasPermission('kitchen_access')) {
    setFlashMessage('Access denied.', 'error');
    redirect(BASE_PATH . 'views/login.php');
}

$database = new Database();
$db = $database->getConnection();
$orderManager = new Order($db);
$pizza = new Pizza($db);

$pendingOrders = $orderManager->getOrdersByStatus('pending');

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $order_id = (int)$_POST['order_id'];
        $status = 'prepared';
        if ($orderManager->updateStatus($order_id, $status, getCurrentStaffId())) {
            sendNotification($order_id, $status, 'Your order has been prepared and will be delivered soon.');
            setFlashMessage('Order marked as Prepared.', 'success');
            logActivity('update_order_status', "Kitchen staff marked order ID: $order_id as Prepared", getCurrentStaffId());
        } else {
            setFlashMessage('Failed to update order status.', 'error');
        }
    }
    redirect('dashboard.php');
}

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Dashboard - Crust Pizza</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff6b35;
            --hover-color: #f7931e;
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
            color:#ff6b35;
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

        .order-time {
            color: #666;
            font-size: 0.9rem;
            font-style: italic;
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
        }

        .item-list li strong {
            color: #333;
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

        .btn-primary i {
            font-size: 0.9rem;
        }

        .no-orders {
            text-align: center;
            padding: 3rem;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .no-orders i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .no-orders p {
            font-size: 1.2rem;
            color: #666;
            margin: 0;
        }

        .flash-message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
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

            .nav-link {
                padding: 0;
                width: 100%;
                text-align: left;
            }

            .container {
                padding: 0 1rem;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .order-actions {
                text-align: center;
            }

            .btn-primary {
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

            .btn-primary {
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
                <h1><i class="fas fa-utensils"></i> Kitchen Dashboard</h1>
                <p>Manage pending orders</p>
            </div>

            <?php displayFlashMessages(); ?>

            <div class="orders-section">
                <h2>Pending Orders</h2>
                <?php if (empty($pendingOrders) || !is_array($pendingOrders)): ?>
                    <div class="no-orders">
                        <i class="fas fa-box-open"></i>
                        <p>No orders at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingOrders as $orderData): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h3>Order #<?php echo htmlspecialchars($orderData['order_number'] ?? 'N/A'); ?></h3>
                                <span class="order-time"><?php echo htmlspecialchars(timeAgo($orderData['created_at'] ?? date('Y-m-d H:i:s'))); ?></span>
                            </div>
                            <div class="order-details">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($orderData['customer_name'] ?? 'Unknown'); ?></p>
                                <p><strong>Order Type:</strong> <?php echo htmlspecialchars($orderData['order_type'] ?? 'N/A'); ?></p>
                                <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($orderData['special_requests'] ?? 'None'); ?></p>
                            </div>
                            <div class="order-items">
                                <h4>Items</h4>
                                <ul class="item-list">
                                    <?php 
                                    $orderItems = $orderManager->getOrderItems($orderData['order_id'] ?? 0);
                                    if (!empty($orderItems) && is_array($orderItems)):
                                        foreach ($orderItems as $item): ?>
                                            <li>
                                                <strong><?php echo htmlspecialchars($item['pizza_name'] ?? $item['menu_item_name'] ?? 'Unknown Item'); ?></strong>
                                                (Size: <?php echo htmlspecialchars($item['size'] ?? 'N/A'); ?>, Qty: <?php echo htmlspecialchars($item['quantity'] ?? 1); ?>)
                                                <?php if (!empty($item['pizza_id'])): ?>
                                                    <?php 
                                                    $ingredients = $pizza->getPizzaIngredients($item['pizza_id']);
                                                    if (!empty($ingredients) && is_array($ingredients)):
                                                        echo '<br><strong>Ingredients:</strong> ';
                                                        $ingredientList = [];
                                                        foreach ($ingredients as $ingredient) {
                                                            $ingredientList[] = htmlspecialchars($ingredient['name'] ?? 'Unknown') . ' (' . htmlspecialchars($ingredient['quantity'] ?? 'N/A') . ')';
                                                        }
                                                        echo htmlspecialchars(implode(', ', $ingredientList));
                                                    endif; ?>
                                                <?php endif; ?>
                                                <?php 
                                                $customIngredients = getOrderItemCustomIngredients($db, $item['order_item_id'] ?? 0);
                                                if (!empty($customIngredients) && is_array($customIngredients)):
                                                    echo '<br><strong>Custom Ingredients:</strong> ';
                                                    $customList = [];
                                                    foreach ($customIngredients as $ingredient) {
                                                        $customList[] = htmlspecialchars($ingredient['name'] ?? 'Unknown') . ' (' . htmlspecialchars($ingredient['quantity'] ?? 'N/A') . ')';
                                                    }
                                                    echo htmlspecialchars(implode(', ', $customList));
                                                endif; ?>
                                                <?php if (!empty($item['special_instructions'])): ?>
                                                    <br><strong>Instructions:</strong> <?php echo htmlspecialchars($item['special_instructions']); ?>
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
                                <button type="submit" name="update_status" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Mark as Prepared
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
            function toggleNavMenu() {
                const navMenu = document.getElementById('navMenu');
                navMenu.classList.toggle('active');
            }

            const navToggle = document.querySelector('.nav-toggle');
            if (navToggle) {
                navToggle.addEventListener('click', toggleNavMenu);
            }

            document.addEventListener('click', function(event) {
                const navMenu = document.getElementById('navMenu');
                if (navMenu && !navMenu.contains(event.target) && !navToggle.contains(event.target)) {
                    navMenu.classList.remove('active');
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const navMenu = document.getElementById('navMenu');
                    if (navMenu) {
                        navMenu.classList.remove('active');
                    }
                }
            });
        });
    </script>
</body>
</html>
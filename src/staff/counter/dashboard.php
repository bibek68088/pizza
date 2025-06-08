<?php
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

$preparedOrders = $order->getOrdersByStatus('prepared', null, ['order_type' => 'pickup']);
$pickupOrders = $order->getOrdersByStatus('ready_for_pickup');

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
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include '../../header.php'; ?>

    <main>
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-cash-register"></i> Counter Dashboard</h1>
                <p>Manage pickup orders and customer service</p>
            </div>

            <?php displayFlashMessages(); ?>

            <h2>Prepared Pickup Orders</h2>
            <?php if (empty($preparedOrders)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-check"></i>
                    <p>No prepared pickup orders available.</p>
                </div>
            <?php else: ?>
                <?php foreach ($preparedOrders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <h3>Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                            <span class="status-badge status-prepared">Prepared</span>
                        </div>
                        <div class="order-details">
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($order['special_requests'] ?? 'None'); ?></p>
                            <p><strong>Placed:</strong> <?php echo timeAgo($order['created_at']); ?></p>
                        </div>
                        <h4>Items</h4>
                        <ul class="item-list">
                            <?php $orderItems = $order->getOrderItems($order['order_id']); ?>
                            <?php foreach ($orderItems as $item): ?>
                                <li class="order-item">
                                    <div class="item-header">
                                        <strong><?php echo htmlspecialchars($item['pizza_name'] ?? $item['menu_item_name'] ?? 'Custom Item'); ?></strong>
                                        <span class="item-details">(Size: <?php echo htmlspecialchars($item['size']); ?>, Qty: <?php echo htmlspecialchars($item['quantity']); ?>)</span>
                                    </div>
                                    <?php if ($item['pizza_id']): ?>
                                        <?php
                                        $ingredients = $pizza->getPizzaIngredients($item['pizza_id']);
                                        if (!empty($ingredients)):
                                        ?>
                                            <div class="ingredients">
                                                <strong>Ingredients:</strong>
                                                <?php
                                                $ingredientList = [];
                                                foreach ($ingredients as $ingredient) {
                                                    $ingredientList[] = htmlspecialchars($ingredient['name']) . ' (' . htmlspecialchars($ingredient['quantity']) . ')';
                                                }
                                                echo implode(', ', $ingredientList);
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php
                                    $query = "SELECT i.name, oii.quantity 
                                              FROM order_item_ingredients oii 
                                              JOIN ingredients i ON oii.ingredient_id = i.ingredient_id 
                                              WHERE oii.order_item_id = :order_item_id";
                                    $stmt = $db->prepare($query);
                                    $stmt->bindParam(':order_item_id', $item['order_item_id'], PDO::PARAM_INT);
                                    $stmt->execute();
                                    $customIngredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    if (!empty($customIngredients)):
                                    ?>
                                        <div class="ingredients">
                                            <strong>Custom Ingredients:</strong>
                                            <?php
                                            $customList = [];
                                            foreach ($customIngredients as $ingredient) {
                                                $customList[] = htmlspecialchars($ingredient['name']) . ' (' . htmlspecialchars($ingredient['quantity']) . ')';
                                            }
                                            echo implode(', ', $customList);
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($item['special_instructions']): ?>
                                        <div class="special-instructions">
                                            <strong>Instructions:</strong> <?php echo htmlspecialchars($item['special_instructions']); ?>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="POST" class="order-action">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <input type="hidden" name="status" value="ready_for_pickup">
                            <button type="submit" name="update_status" class="btn btn-primary">
                                <i class="fas fa-check"></i> Mark as Ready for Pickup
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <h2>Orders Ready for Pickup</h2>
            <?php if (empty($pickupOrders)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>No orders ready for pickup.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pickupOrders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <h3>Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                            <span class="status-badge status-ready">Ready for Pickup</span>
                        </div>
                        <div class="order-details">
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($order['special_requests'] ?? 'None'); ?></p>
                            <p><strong>Placed:</strong> <?php echo timeAgo($order['created_at']); ?></p>
                        </div>
                        <form method="POST" class="order-action">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <input type="hidden" name="status" value="received_by_customer">
                            <button type="submit" name="update_status" class="btn btn-success">
                                <i class="fas fa-handshake"></i> Mark as Received by Customer
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../../footer.php'; ?>

    <script src="../assets/js/main.js"></script>
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

        // Close dropdown and nav menu when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.querySelector('.dropdown');
            const dropdownMenu = document.getElementById('dropdownMenu');
            const navMenu = document.getElementById('navMenu');
            const navToggle = document.querySelector('.nav-toggle');
            if (dropdown && !dropdown.contains(event.target) && navToggle && !navToggle.contains(event.target)) {
                if (dropdownMenu) dropdownMenu.classList.remove('show');
                if (navMenu) navMenu.classList.remove('active');
                const dropdownToggle = document.querySelector('.dropdown-toggle');
                if (dropdownToggle) dropdownToggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Close dropdown and nav menu on Escape key
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

        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
            const cartCount = cart.reduce((total, item) => total + (item.quantity || 1), 0);
            const cartCountElement = document.getElementById('cartCount');
            if (cartCountElement) {
                cartCountElement.textContent = cartCount;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
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
        });

        // Auto-refresh orders every 30 seconds
        setInterval(function() {
            window.location.reload();
        }, 30000);
    </script>

    <style>
        :root {
            --primary-color: #ff6b35;
            --hover-color: #f7931e;
            --success-color: #28a745;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .page-header {
            text-align: center;
            margin: 2rem 0;
            padding-top: 80px;
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .order-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            border: 1px solid #f0f0f0;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f8f9fa;
        }

        .order-header h3 {
            margin: 0;
            color: #333;
            font-weight: 700;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .order-details {
            margin-bottom: 1rem;
        }

        .order-details p {
            margin: 0.5rem 0;
            color: #555;
        }

        .order-details strong {
            color: #333;
        }

        .item-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .order-item {
            background: #f8f9fa;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
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

        .ingredients,
        .special-instructions {
            margin: 0.5rem 0;
            padding: 0.5rem;
            background: white;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .ingredients strong,
        .special-instructions strong {
            color: var(--primary-color);
        }

        .order-action {
            margin-top: 1rem;
            text-align: right;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--hover-color));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .btn-success {
            background: linear-gradient(45deg, var(--success-color), #34ce57);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #666;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ddd;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin: 0;
        }

        /* Override style.css nav-link */
        .dropdown .nav-link::after,
        .dropdown-toggle::after {
            display: none !important;
        }

        /* Navbar Toggle */
        .nav-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #333;
            cursor: pointer;
            padding: 8px;
        }

        .dropdown {
            position: relative;
            display: inline-flex;
            align-items: center;
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
            transition: color 0.3s ease;
        }

        .dropdown-toggle:hover,
        .dropdown-toggle:focus {
            color: var(--primary-color);
            outline: none;
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .user-icon i {
            font-size: 1rem;
            color: var(--primary-color);
        }

        .dropdown-toggle:hover .user-icon,
        .dropdown-toggle:focus .user-icon {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .dropdown-arrow::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 0.7rem;
            color: #333;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .dropdown-toggle:hover .dropdown-arrow::after {
            color: var(--primary-color);
        }

        .dropdown-toggle[aria-expanded="true"] .dropdown-arrow::after {
            transform: rotate(180deg);
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
            text-align: left;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background: linear-gradient(45deg, var(--primary-color, #ff6b35), var(--hover-color));
            color: #fff;
            outline: none;
        }

        @media (max-width: 767px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .page-header p {
                font-size: 14px;
            }

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
                flex-grow: 0;
            }

            .nav-link,
            .dropdown {
                padding: 0;
                width: 100% !important;
                text-align: left;
            }

            .dropdown-menu {
                position: static;
                width: 100%;
                min-width: 0px !important;
                box-shadow: none;
                margin-top: 0;
                padding: 0 0 0 20px;
                background: #f8f9fa;
                border-radius: 0;
            }

            .dropdown-item {
                padding: 6px 12px;
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

            .order-action {
                text-align: center;
            }
        }
    </style>
</body>

</html>
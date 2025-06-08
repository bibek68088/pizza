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
    <style>
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .order-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }

        .item-list {
            list-style: none;
            padding: 0;
        }

        .item-list li {
            margin-bottom: 0.5rem;
        }

        .btn-primary {
            background: #ff6b35;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice"></i>
                <p><a href="../index.php" style="text-decoration: none; color: inherit;">Crust Pizza</a></p>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-link active">Counter Dashboard</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Counter Dashboard</h1>
        <?php displayFlashMessages(); ?>

        <h2>Prepared Pickup Orders</h2>
        <?php if (empty($preparedOrders)): ?>
            <p>No prepared pickup orders available.</p>
        <?php else: ?>
            <?php foreach ($preparedOrders as $order): ?>
                <div class="order-card">
                    <h3>Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($order['special_requests'] ?? 'None'); ?></p>
                    <p><strong>Placed:</strong> <?php echo timeAgo($order['created_at']); ?></p>
                    <h4>Items</h4>
                    <ul class="item-list">
                        <?php $orderItems = $order->getOrderItems($order['order_id']); ?>
                        <?php foreach ($orderItems as $item): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($item['pizza_name'] ?? $item['menu_item_name'] ?? 'Custom Item'); ?></strong>
                                (Size: <?php echo htmlspecialchars($item['size']); ?>, Qty: <?php echo htmlspecialchars($item['quantity']); ?>)
                                <?php if ($item['pizza_id']): ?>
                                    <?php
                                    $ingredients = $pizza->getPizzaIngredients($item['pizza_id']);
                                    if (!empty($ingredients)):
                                        echo '<br><strong>Ingredients:</strong> ';
                                        $ingredientList = [];
                                        foreach ($ingredients as $ingredient) {
                                            $ingredientList[] = htmlspecialchars($ingredient['name']) . ' (' . htmlspecialchars($ingredient['quantity']) . ')';
                                        }
                                        echo implode(', ', $ingredientList);
                                    endif;
                                    ?>
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
                                    echo '<br><strong>Custom Ingredients:</strong> ';
                                    $customList = [];
                                    foreach ($customIngredients as $ingredient) {
                                        $customList[] = htmlspecialchars($ingredient['name']) . ' (' . htmlspecialchars($ingredient['quantity']) . ')';
                                    }
                                    echo implode(', ', $customList);
                                endif;
                                ?>
                                <?php if ($item['special_instructions']): ?>
                                    <br><strong>Instructions:</strong> <?php echo htmlspecialchars($item['special_instructions']); ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <input type="hidden" name="status" value="ready_for_pickup">
                        <button type="submit" name="update_status" class="btn-primary">Mark as Ready for Pickup</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h2>Orders Ready for Pickup</h2>
        <?php if (empty($pickupOrders)): ?>
            <p>No orders ready for pickup.</p>
        <?php else: ?>
            <?php foreach ($pickupOrders as $order): ?>
                <div class="order-card">
                    <h3>Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($order['special_requests'] ?? 'None'); ?></p>
                    <p><strong>Placed:</strong> <?php echo timeAgo($order['created_at']); ?></p>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <input type="hidden" name="status" value="received_by_customer">
                        <button type="submit" name="update_status" class="btn-primary">Mark as Received by Customer</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>© 2024 Crust Pizza. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>
?>
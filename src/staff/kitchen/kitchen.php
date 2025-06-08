<?php
require_once '../../config/database.php';
require_once '../../classes/Order.php';
require_once '../../includes/functions.php';

startSession();

if (!hasPermission('kitchen_access')) {
    setFlashMessage('Access denied.', 'error');
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

$pendingOrders = $order->getOrdersByStatus('pending');

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $order_id = (int)$_POST['order_id'];
        if ($order->updateStatus($order_id, 'prepared', getCurrentStaffId())) {
            sendNotification($order_id, 'prepared');
            setFlashMessage('Order marked as prepared.', 'success');
        } else {
            setFlashMessage('Failed to update order status.', 'error');
        }
    }
    redirect('kitchen.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Dashboard - Crust Pizza</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .order-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            padding: 1.5rem;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .order-details {
            margin-bottom: 1rem;
        }

        .item-list {
            list-style: none;
            padding: 0;
        }

        .item-list li {
            margin-bottom: 0.5rem;
        }

        .btn-prepared {
            background: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-prepared:hover {
            background: #218838;
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
                <a href="kitchen.php" class="nav-link active">Kitchen Dashboard</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>Kitchen Dashboard</h1>
        <?php displayFlashMessages(); ?>

        <h2>Pending Orders</h2>
        <?php if (empty($pendingOrders)): ?>
            <p>No pending orders at the moment.</p>
        <?php else: ?>
            <?php foreach ($pendingOrders as $pendingOrder): ?>
                <div class="order-card">
                    <div class="order-header">
                        <h3>Order #<?php echo htmlspecialchars($pendingOrder['order_number']); ?></h3>
                        <span>Placed: <?php echo timeAgo($pendingOrder['created_at']); ?></span>
                    </div>
                    <div class="order-details">
                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($pendingOrder['customer_name']); ?></p>
                        <p><strong>Type:</strong> <?php echo htmlspecialchars($pendingOrder['order_type']); ?></p>
                        <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($pendingOrder['special_requests'] ?? 'None'); ?></p>
                        <h4>Items:</h4>
                        <ul class="item-list">
                            <?php
                            $items = $order->getOrderItems($pendingOrder['order_id']);
                            foreach ($items as $item):
                            ?>
                                <li>
                                    <strong><?php echo htmlspecialchars($item['pizza_name'] ?? $item['menu_item_name'] ?? 'Custom Item'); ?></strong>
                                    (Size: <?php echo htmlspecialchars($item['size']); ?>, Qty: <?php echo $item['quantity']; ?>)
                                    <?php
                                    // Get ingredients for pizza items
                                    if ($item['pizza_id']):
                                        $pizza = new Pizza($db);
                                        $ingredients = $pizza->getPizzaIngredients($item['pizza_id']);
                                        if (!empty($ingredients)):
                                            echo '<br><strong>Ingredients:</strong> ';
                                            $ingredientList = [];
                                            foreach ($ingredients as $ingredient) {
                                                $ingredientList[] = htmlspecialchars($ingredient['name']) . ' (' . $ingredient['quantity'] . ')';
                                            }
                                            echo implode(', ', $ingredientList);
                                        endif;
                                    endif;
                                    // Custom ingredients
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
                                            $customList[] = htmlspecialchars($ingredient['name']) . ' (' . $ingredient['quantity'] . ')';
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
                    </div>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="order_id" value="<?php echo $pendingOrder['order_id']; ?>">
                        <button type="submit" name="update_status" class="btn-prepared">Mark as Prepared</button>
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
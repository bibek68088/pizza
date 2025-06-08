<?php
require_once '../../config/database.php';
require_once '../../classes/Order.php';
require_once '../../includes/functions.php';

startSession();

if (!hasPermission('counter_access')) {
    setFlashMessage('Access denied.', 'error');
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

$preparedOrders = $order->getOrdersByStatus('prepared', null, 50, ['order_type' => 'pickup']);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $order_id = (int)$_POST['order_id'];
        $new_status = $_POST['status'];
        if (in_array($new_status, ['ready_for_pickup', 'received_by_customer'])) {
            if ($order->updateStatus($order_id, $new_status, getCurrentStaffId())) {
                if ($new_status === 'ready_for_pickup') {
                    sendNotification($order_id, $new_status);
                }
                setFlashMessage("Order status updated to $new_status.", 'success');
            } else {
                setFlashMessage('Failed to update order status.', 'error');
            }
        } else {
            setFlashMessage('Invalid status.', 'error');
        }
    }
    redirect('counter.php');
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

        .btn-status {
            background: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 0.5rem;
        }

        .btn-status:hover {
            background: #0056b3;
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
                <a href="counter.php" class="nav-link active">Counter Dashboard</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>Counter Dashboard</h1>
        <?php displayFlashMessages(); ?>

        <h2>Prepared Pickup Orders</h2>
        <?php if (empty($preparedOrders)): ?>
            <p>No orders ready for pickup.</p>
        <?php else: ?>
            <?php foreach ($preparedOrders as $preparedOrder): ?>
                <div class="order-card">
                    <div class="order-header">
                        <h3>Order #<?php echo htmlspecialchars($preparedOrder['order_number']); ?></h3>
                        <span>Prepared: <?php echo timeAgo($preparedOrder['updated_at']); ?></span>
                    </div>
                    <div class="order-details">
                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($preparedOrder['customer_name']); ?></p>
                        <p><strong>Phone:</strong> <?php echo formatPhone($preparedOrder['customer_phone']); ?></p>
                        <h4>Items:</h4>
                        <ul>
                            <?php
                            $items = $order->getOrderItems($preparedOrder['order_id']);
                            foreach ($items as $item):
                            ?>
                                <li>
                                    <?php echo htmlspecialchars($item['pizza_name'] ?? $item['menu_item_name'] ?? 'Custom Item'); ?>
                                    (Size: <?php echo htmlspecialchars($item['size']); ?>, Qty: <?php echo $item['quantity']; ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="order_id" value="<?php echo $preparedOrder['order_id']; ?>">
                        <button type="submit" name="update_status" value="status" class="btn-status" onclick="this.form.status.value='ready_for_pickup'">Ready for Pickup</button>
                        <button type="submit" name="update_status" value="status" class="btn-status" onclick="this.form.status.value='received_by_customer'">Received by Customer</button>
                        <input type="hidden" name="status">
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
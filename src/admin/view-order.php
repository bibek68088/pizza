<?php
require_once '../config/database.php';
require_once '../classes/Order.php';
require_once '../classes/Pizza.php';
require_once '../includes/functions.php';

startSession();

if (!hasPermission('admin_access')) {
    setFlashMessage('Access denied.', 'error');
    redirect('../login.php');
}

if (!isset($_GET['id'])) {
    setFlashMessage('Order ID not provided.', 'error');
    redirect('manage-orders.php');
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);
$pizza = new Pizza($db);

$order_id = (int)$_GET['id'];
$orderDetails = $order->getOrderById($order_id);
$orderItems = $order->getOrderItems($order_id);

if (!$orderDetails) {
    setFlashMessage('Order not found.', 'error');
    redirect('manage-orders.php');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $status = sanitizeInput($_POST['status']);
        if ($order->updateStatus($order_id, $status, getCurrentStaffId())) {
            sendNotification($order_id, $status);
            setFlashMessage('Order status updated successfully.', 'success');
            logActivity('update_order_status', "Updated status for order ID: $order_id to $status", getCurrentStaffId());
        } else {
            setFlashMessage('Failed to update order status.', 'error');
        }
    }
    redirect("view-order.php?id=$order_id");
}
?>

<?php include './admin-header.php'; ?>

<main style="margin-top: 70px; padding: 40px 20px;">
    <div class="container">
        <h1>Order #<?php echo htmlspecialchars($orderDetails['order_number']); ?></h1>
        <?php displayFlashMessages(); ?>

        <div class="order-card">
            <div class="order-details">
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($orderDetails['customer_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($orderDetails['customer_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo formatPhone($orderDetails['customer_phone']); ?></p>
                <p><strong>Order Type:</strong> <?php echo htmlspecialchars($orderDetails['order_type']); ?></p>
                <?php if ($orderDetails['order_type'] === 'delivery'): ?>
                    <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($orderDetails['delivery_address']); ?></p>
                    <p><strong>Delivery Instructions:</strong> <?php echo htmlspecialchars($orderDetails['delivery_instructions'] ?? 'None'); ?></p>
                <?php endif; ?>
                <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($orderDetails['special_requests'] ?? 'None'); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($orderDetails['status']); ?></p>
                <p><strong>Total:</strong> <?php echo formatCurrency($orderDetails['total']); ?></p>
                <p><strong>Placed:</strong> <?php echo timeAgo($orderDetails['created_at']); ?></p>
                <h2>Items</h2>
                <ul class="item-list">
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
                        </li>
                    <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
            <h2>Update Status</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                <div class="form-group">
                    <label for="status">New Status</label>
                    <select name="status" class="form-control" required>
                        <option value="pending" <?php echo $orderDetails['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="prepared" <?php echo $orderDetails['status'] === 'prepared' ? 'selected' : ''; ?>>Prepared</option>
                        <option value="out_for_delivery" <?php echo $orderDetails['status'] === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                        <option value="delivered" <?php echo $orderDetails['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="delivery_failure" <?php echo $orderDetails['status'] === 'delivery_failure' ? 'selected' : ''; ?>>Delivery Failure</option>
                        <option value="ready_for_pickup" <?php echo $orderDetails['status'] === 'ready_for_pickup' ? 'selected' : ''; ?>>Ready for Pickup</option>
                        <option value="received_by_customer" <?php echo $orderDetails['status'] === 'received_by_customer' ? 'selected' : ''; ?>>Received by Customer</option>
                    </select>
                </div>
                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
            </form>
        </div>
    </div>
</main>

<?php include './admin-footer.php'; ?>

<style>
    .container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .order-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .order-details {
        margin-bottom: 1.5rem;
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

    .btn-primary:hover {
        background: #e55a2b;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    .form-control:focus {
        outline: none;
        border-color: #ff6b35;
        box-shadow: 0 0 0 2px rgba(255, 107, 53, 0.2);
    }
</style>
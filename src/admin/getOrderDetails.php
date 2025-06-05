<?php
/**
 * AJAX endpoint for order details
 * Returns order details as HTML
 */

require_once '../config/database.php';
require_once '../classes/Order.php';
require_once '../includes/functions.php';

startSession();

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    echo 'Invalid order ID';
    exit;
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

$order_details = $order->getOrderById($order_id);
$order_items = $order->getOrderItems($order_id);

if (!$order_details) {
    echo 'Order not found';
    exit;
}
?>

<div class="order-details">
    <!-- Order Information -->
    <div style="margin-bottom: 2rem;">
        <h4>Order #<?php echo $order_details['order_id']; ?></h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
            <div>
                <strong>Customer:</strong> <?php echo htmlspecialchars($order_details['customer_name']); ?><br>
                <strong>Phone:</strong> <?php echo htmlspecialchars($order_details['customer_phone']); ?><br>
                <strong>Email:</strong> <?php echo htmlspecialchars($order_details['customer_email']); ?>
            </div>
            <div>
                <strong>Order Type:</strong> <?php echo ucfirst($order_details['order_type']); ?><br>
                <strong>Status:</strong> <span class="badge badge-primary"><?php echo ucfirst(str_replace('_', ' ', $order_details['status'])); ?></span><br>
                <strong>Date:</strong> <?php echo date('M j, Y g:i A', strtotime($order_details['created_at'])); ?>
            </div>
        </div>
        
        <?php if ($order_details['order_type'] === 'delivery' && $order_details['delivery_address']): ?>
            <div style="margin-top: 1rem;">
                <strong>Delivery Address:</strong><br>
                <?php echo nl2br(htmlspecialchars($order_details['delivery_address'])); ?>
                
                <?php if ($order_details['delivery_instructions']): ?>
                    <br><br><strong>Instructions:</strong><br>
                    <?php echo nl2br(htmlspecialchars($order_details['delivery_instructions'])); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Order Items -->
    <div style="margin-bottom: 2rem;">
        <h4>Order Items</h4>
        <table class="table" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Size</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($item['pizza_name'] ?: $item['menu_item_name']); ?>
                            <?php if ($item['special_instructions']): ?>
                                <br><small style="color: #666;">Instructions: <?php echo htmlspecialchars($item['special_instructions']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $item['size'] ? ucfirst($item['size']) : '-'; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo formatCurrency($item['unit_price']); ?></td>
                        <td><?php echo formatCurrency($item['total_price']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Order Total -->
    <div style="border-top: 1px solid #ddd; padding-top: 1rem;">
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
        <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 1.1rem; border-top: 1px solid #ddd; padding-top: 0.5rem;">
            <span>Total:</span>
            <span><?php echo formatCurrency($order_details['total']); ?></span>
        </div>
    </div>

    <!-- Payment Information -->
    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #ddd;">
        <strong>Payment Method:</strong> <?php echo ucfirst($order_details['payment_method']); ?><br>
        <strong>Payment Status:</strong> <span class="badge badge-<?php echo $order_details['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
            <?php echo ucfirst($order_details['payment_status']); ?>
        </span>
    </div>
</div>

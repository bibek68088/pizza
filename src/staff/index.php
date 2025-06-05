<?php
require_once '../config/database.php';
require_once '../classes/Order.php';
require_once '../includes/functions.php';

startSession();

// Check if user is staff
if (!isStaff()) {
    setFlashMessage('Access denied. Staff privileges required.', 'error');
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

$message = '';
$error = '';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = sanitizeInput($_POST['new_status']);
    $notes = sanitizeInput($_POST['notes']);
    
    if ($order->updateStatus($order_id, $new_status, $_SESSION['staff_id'], $notes)) {
        $message = 'Order status updated successfully';
    } else {
        $error = 'Failed to update order status';
    }
}

// Get staff role and store
$staff_role = $_SESSION['staff_role'];
$staff_store_id = $_SESSION['staff_store_id'] ?? null;

// Get orders based on staff role
$orders = [];
switch ($staff_role) {
    case 'kitchen':
        $orders = $order->getOrdersByStatus('pending', $staff_store_id);
        $orders = array_merge($orders, $order->getOrdersByStatus('confirmed', $staff_store_id));
        $orders = array_merge($orders, $order->getOrdersByStatus('preparing', $staff_store_id));
        break;
    case 'delivery':
        $orders = $order->getOrdersByStatus('prepared', $staff_store_id);
        $orders = array_merge($orders, $order->getOrdersByStatus('out_for_delivery', $staff_store_id));
        // Filter only delivery orders
        $orders = array_filter($orders, function($o) { return $o['order_type'] === 'delivery'; });
        break;
    case 'counter':
        $orders = $order->getOrdersByStatus('prepared', $staff_store_id);
        $orders = array_merge($orders, $order->getOrdersByStatus('ready_for_pickup', $staff_store_id));
        // Filter only pickup orders
        $orders = array_filter($orders, function($o) { return $o['order_type'] === 'pickup'; });
        break;
    case 'admin':
        $orders = $order->getRecentOrders(20, $staff_store_id);
        break;
}

// Get today's stats for this staff member's role
$today_stats = ['orders_handled' => 0, 'avg_time' => '18 min'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Crust Pizza</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Staff Header -->
    <header class="header" style="background: #2c3e50;">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice" style="color: #ff6b35;"></i>
                <h1 style="color: white;"><a href="../index.php" style="color: white; text-decoration: none;">Crust Pizza Staff</a></h1>
            </div>
            <nav class="nav-menu">
                <span style="color: #ff6b35; font-weight: 600;">
                    <i class="fas fa-user-tie"></i> <?php echo ucfirst($staff_role); ?> Dashboard
                </span>
                <a href="../logout.php" class="nav-link" style="color: white;">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container" style="padding: 2rem 20px;">
            <div class="page-header">
                <h1>
                    <?php
                    $icons = [
                        'kitchen' => 'fire',
                        'delivery' => 'truck',
                        'counter' => 'store',
                        'admin' => 'tachometer-alt'
                    ];
                    ?>
                    <i class="fas fa-<?php echo $icons[$staff_role]; ?>"></i> 
                    <?php echo ucfirst($staff_role); ?> Dashboard
                </h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin: 2rem 0;">
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <i class="fas fa-list" style="font-size: 3rem; color: #ff6b35; margin-bottom: 1rem;"></i>
                        <h3 style="margin: 0; font-size: 2rem;"><?php echo count($orders); ?></h3>
                        <p style="margin: 0; color: #666;">
                            <?php
                            switch ($staff_role) {
                                case 'kitchen': echo 'Orders to Prepare'; break;
                                case 'delivery': echo 'Delivery Queue'; break;
                                case 'counter': echo 'Pickup Queue'; break;
                                case 'admin': echo 'Recent Orders'; break;
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <i class="fas fa-clock" style="font-size: 3rem; color: #17a2b8; margin-bottom: 1rem;"></i>
                        <h3 style="margin: 0; font-size: 2rem;"><?php echo $today_stats['avg_time']; ?></h3>
                        <p style="margin: 0; color: #666;">Average Time</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745; margin-bottom: 1rem;"></i>
                        <h3 style="margin: 0; font-size: 2rem;"><?php echo $today_stats['orders_handled']; ?></h3>
                        <p style="margin: 0; color: #666;">Completed Today</p>
                    </div>
                </div>
            </div>

            <!-- Orders List -->
            <div class="card">
                <div class="card-header">
                    <h3 style="margin: 0;">
                        <?php
                        switch ($staff_role) {
                            case 'kitchen': echo 'Kitchen Orders'; break;
                            case 'delivery': echo 'Delivery Orders'; break;
                            case 'counter': echo 'Pickup Orders'; break;
                            case 'admin': echo 'All Orders'; break;
                        }
                        ?>
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <div style="text-align: center; padding: 3rem; color: #666;">
                            <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <h3>No orders in queue</h3>
                            <p>Great job! All caught up.</p>
                        </div>
                    <?php else: ?>
                        <div class="orders-grid" style="display: grid; gap: 1.5rem;">
                            <?php foreach ($orders as $order_item): ?>
                                <div class="order-card" style="border: 2px solid #ddd; border-radius: 8px; padding: 1.5rem; background: white;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                        <div>
                                            <h3 style="margin: 0; color: #ff6b35;">Order #<?php echo $order_item['order_id']; ?></h3>
                                            <p style="margin: 0; color: #666;"><?php echo htmlspecialchars($order_item['customer_name']); ?></p>
                                            <p style="margin: 0; font-size: 0.9rem; color: #999;">
                                                <?php echo timeAgo($order_item['created_at']); ?>
                                            </p>
                                        </div>
                                        <div style="text-align: right;">
                                            <div class="badge badge-<?php echo $order_item['status'] === 'completed' ? 'success' : 'primary'; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $order_item['status'])); ?>
                                            </div>
                                            <div style="margin-top: 0.5rem;">
                                                <span class="badge badge-<?php echo $order_item['order_type'] === 'delivery' ? 'info' : 'secondary'; ?>">
                                                    <i class="fas fa-<?php echo $order_item['order_type'] === 'delivery' ? 'truck' : 'store'; ?>"></i>
                                                    <?php echo ucfirst($order_item['order_type']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Order Items Preview -->
                                    <div style="margin-bottom: 1rem;">
                                        <h4 style="margin: 0 0 0.5rem 0; font-size: 1rem;">Items:</h4>
                                        <?php
                                        $order_items = $order->getOrderItems($order_item['order_id']);
                                        foreach (array_slice($order_items, 0, 3) as $item):
                                        ?>
                                            <div style="font-size: 0.9rem; color: #666; margin-bottom: 0.25rem;">
                                                • <?php echo htmlspecialchars($item['pizza_name'] ?: $item['menu_item_name']); ?>
                                                <?php if ($item['size']): ?>
                                                    (<?php echo ucfirst($item['size']); ?>)
                                                <?php endif; ?>
                                                x<?php echo $item['quantity']; ?>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (count($order_items) > 3): ?>
                                            <div style="font-size: 0.9rem; color: #999;">
                                                +<?php echo count($order_items) - 3; ?> more items
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Contact & Address Info -->
                                    <div style="margin-bottom: 1rem; font-size: 0.9rem;">
                                        <div><strong>Phone:</strong> <?php echo htmlspecialchars($order_item['customer_phone']); ?></div>
                                        <?php if ($order_item['order_type'] === 'delivery' && $order_item['delivery_address']): ?>
                                            <div><strong>Address:</strong> <?php echo htmlspecialchars($order_item['delivery_address']); ?></div>
                                            <?php if ($order_item['delivery_instructions']): ?>
                                                <div><strong>Instructions:</strong> <?php echo htmlspecialchars($order_item['delivery_instructions']); ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Total -->
                                    <div style="margin-bottom: 1rem;">
                                        <strong style="font-size: 1.1rem;">Total: <?php echo formatCurrency($order_item['total']); ?></strong>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <?php
                                        $available_actions = [];
                                        
                                        switch ($staff_role) {
                                            case 'kitchen':
                                                if ($order_item['status'] === 'pending') {
                                                    $available_actions[] = ['label' => 'Start Preparing', 'status' => 'preparing', 'class' => 'btn-primary'];
                                                } elseif ($order_item['status'] === 'preparing') {
                                                    $available_actions[] = ['label' => 'Mark as Prepared', 'status' => 'prepared', 'class' => 'btn-primary'];
                                                }
                                                break;
                                                
                                            case 'delivery':
                                                if ($order_item['status'] === 'prepared') {
                                                    $available_actions[] = ['label' => 'Out for Delivery', 'status' => 'out_for_delivery', 'class' => 'btn-primary'];
                                                } elseif ($order_item['status'] === 'out_for_delivery') {
                                                    $available_actions[] = ['label' => 'Mark as Delivered', 'status' => 'delivered', 'class' => 'btn-primary'];
                                                }
                                                break;
                                                
                                            case 'counter':
                                                if ($order_item['status'] === 'prepared') {
                                                    $available_actions[] = ['label' => 'Ready for Pickup', 'status' => 'ready_for_pickup', 'class' => 'btn-primary'];
                                                } elseif ($order_item['status'] === 'ready_for_pickup') {
                                                    $available_actions[] = ['label' => 'Customer Collected', 'status' => 'completed', 'class' => 'btn-primary'];
                                                }
                                                break;
                                        }
                                        
                                        foreach ($available_actions as $action):
                                        ?>
                                            <button class="btn <?php echo $action['class']; ?>" 
                                                    onclick="updateOrderStatus(<?php echo $order_item['order_id']; ?>, '<?php echo $action['status']; ?>')">
                                                <?php echo $action['label']; ?>
                                            </button>
                                        <?php endforeach; ?>
                                        
                                        <button class="btn btn-outline" onclick="viewOrderDetails(<?php echo $order_item['order_id']; ?>)">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Status Update Modal -->
    <div id="statusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 8px; max-width: 400px; width: 90%;">
            <div style="padding: 1.5rem; border-bottom: 1px solid #ddd;">
                <h3 style="margin: 0;">Update Order Status</h3>
                <button onclick="closeStatusModal()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div style="padding: 1.5rem;">
                <form method="POST" id="statusForm">
                    <input type="hidden" name="order_id" id="statusOrderId">
                    <input type="hidden" name="new_status" id="statusNewStatus">
                    
                    <div class="form-group">
                        <label for="notes">Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" 
                                  placeholder="Add any notes about this status change..."></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <button type="button" class="btn btn-outline" onclick="closeStatusModal()">Cancel</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateOrderStatus(orderId, newStatus) {
            document.getElementById('statusOrderId').value = orderId;
            document.getElementById('statusNewStatus').value = newStatus;
            document.getElementById('statusModal').style.display = 'block';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        function viewOrderDetails(orderId) {
            window.open('../admin/get_order_details.php?order_id=' + orderId, '_blank', 'width=600,height=800');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const statusModal = document.getElementById('statusModal');
            if (event.target === statusModal) {
                closeStatusModal();
            }
        }

        // Auto-refresh every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>

    <style>
        @media (max-width: 768px) {
            .orders-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</body>
</html>

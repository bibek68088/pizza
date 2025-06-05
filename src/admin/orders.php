<?php
/**
 * Admin Orders Management
 * CRUD operations for orders
 * Crust Pizza Online Ordering System
 */

require_once '../config/database.php';
require_once '../classes/Order.php';
require_once '../includes/functions.php';

startSession();

// Check if user is admin
if (!isAdmin()) {
    setFlashMessage('Access denied. Admin privileges required.', 'error');
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

// Handle order deletion (soft delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_order'])) {
    $order_id = (int)$_POST['order_id'];
    
    if ($order->updateStatus($order_id, 'cancelled', $_SESSION['staff_id'], 'Order cancelled by admin')) {
        $message = 'Order cancelled successfully';
    } else {
        $error = 'Failed to cancel order';
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$date_filter = isset($_GET['date']) ? sanitizeInput($_GET['date']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query based on filters
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "o.status = :status";
    $params[':status'] = $status_filter;
}

if ($date_filter) {
    $where_conditions[] = "DATE(o.created_at) = :date";
    $params[':date'] = $date_filter;
}

if ($search) {
    $where_conditions[] = "(o.customer_name LIKE :search OR o.customer_phone LIKE :search OR o.order_id LIKE :search)";
    $params[':search'] = "%{$search}%";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$orders_query = "SELECT o.*, s.name as store_name 
                 FROM orders o
                 LEFT JOIN stores s ON o.store_id = s.store_id
                 {$where_clause}
                 ORDER BY o.created_at DESC
                 LIMIT 50";

$orders_stmt = $db->prepare($orders_query);
$orders_stmt->execute($params);
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get order statuses for filter
$statuses = ['pending', 'confirmed', 'preparing', 'prepared', 'out_for_delivery', 'ready_for_pickup', 'delivered', 'completed', 'cancelled'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Crust Pizza Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Admin Header -->
    <header class="header" style="background: #333;">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice" style="color: #ff6b35;"></i>
                <h1 style="color: white;"><a href="../index.php" style="color: white; text-decoration: none;">Crust Pizza Admin</a></h1>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-link" style="color: white;">Dashboard</a>
                <a href="orders.php" class="nav-link active" style="color: #ff6b35;">Orders</a>
                <a href="menu.php" class="nav-link" style="color: white;">Menu</a>
                <a href="users.php" class="nav-link" style="color: white;">Users</a>
                <a href="../logout.php" class="nav-link" style="color: white;">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container" style="padding: 2rem 20px;">
            <div class="page-header">
                <h1><i class="fas fa-shopping-bag"></i> Orders Management</h1>
                <p>Manage and track all customer orders</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3 style="margin: 0;">Filter Orders</h3>
                </div>
                <div class="card-body">
                    <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                        <div class="form-group" style="margin: 0;">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin: 0;">
                            <label for="date">Date</label>
                            <input type="date" name="date" id="date" class="form-control" value="<?php echo $date_filter; ?>">
                        </div>
                        
                        <div class="form-group" style="margin: 0;">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Order ID, customer name, phone..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="orders.php" class="btn btn-outline">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-header">
                    <h3 style="margin: 0;">Orders (<?php echo count($orders); ?> found)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <p style="text-align: center; color: #666; padding: 2rem;">No orders found</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order_item): ?>
                                        <tr>
                                            <td><strong>#<?php echo $order_item['order_id']; ?></strong></td>
                                            <td>
                                                <div><?php echo htmlspecialchars($order_item['customer_name']); ?></div>
                                                <div style="font-size: 0.9rem; color: #666;"><?php echo htmlspecialchars($order_item['customer_phone']); ?></div>
                                            </td>
                                            <td>
                                                <i class="fas fa-<?php echo $order_item['order_type'] === 'delivery' ? 'truck' : 'store'; ?>"></i>
                                                <?php echo ucfirst($order_item['order_type']); ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $order_item['status'] === 'completed' ? 'success' : ($order_item['status'] === 'cancelled' ? 'danger' : 'primary'); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $order_item['status'])); ?>
                                                </span>
                                            </td>
                                            <td><strong><?php echo formatCurrency($order_item['total']); ?></strong></td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($order_item['created_at'])); ?></td>
                                            <td>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;" 
                                                            onclick="viewOrder(<?php echo $order_item['order_id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($order_item['status'] !== 'completed' && $order_item['status'] !== 'cancelled'): ?>
                                                        <button class="btn btn-primary" style="padding: 0.25rem 0.5rem;" 
                                                                onclick="updateOrderStatus(<?php echo $order_item['order_id']; ?>, '<?php echo $order_item['status']; ?>')">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Order Details Modal -->
    <div id="orderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80%; overflow-y: auto;">
            <div style="padding: 1.5rem; border-bottom: 1px solid #ddd;">
                <h3 style="margin: 0;">Order Details</h3>
                <button onclick="closeModal()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div id="orderDetails" style="padding: 1.5rem;">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

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
                    
                    <div class="form-group">
                        <label for="new_status">New Status</label>
                        <select name="new_status" id="new_status" class="form-control" required>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo $status; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
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
        function viewOrder(orderId) {
            // Show loading
            document.getElementById('orderDetails').innerHTML = '<div style="text-align: center; padding: 2rem;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
            document.getElementById('orderModal').style.display = 'block';
            
            // Fetch order details via AJAX
            fetch(`get_order_details.php?order_id=${orderId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('orderDetails').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('orderDetails').innerHTML = '<div style="text-align: center; padding: 2rem; color: red;">Error loading order details</div>';
                });
        }

        function updateOrderStatus(orderId, currentStatus) {
            document.getElementById('statusOrderId').value = orderId;
            document.getElementById('new_status').value = currentStatus;
            document.getElementById('statusModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const orderModal = document.getElementById('orderModal');
            const statusModal = document.getElementById('statusModal');
            
            if (event.target === orderModal) {
                closeModal();
            }
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
        .table-responsive {
            overflow-x: auto;
        }
        
        @media (max-width: 768px) {
            .table {
                font-size: 0.9rem;
            }
            
            .table td, .table th {
                padding: 0.5rem;
            }
        }
    </style>
</body>
</html>

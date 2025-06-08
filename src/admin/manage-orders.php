<?php
require_once '../config/database.php';
require_once '../classes/Order.php';
require_once '../includes/functions.php';

startSession();

if (!hasPermission('admin_access')) {
    setFlashMessage('Access denied.', 'error');
    redirect(BASE_PATH . 'login.php');
}

$database = Database::getInstance();
$db = $database->getConnection();
$order = new Order($db);

$orders = $order->getAllOrders()['orders'];

// Handle edit order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $order_id = (int)$_POST['order_id'];
        $data = [
            'status' => sanitizeInput($_POST['status']),
            'delivery_address' => sanitizeInput($_POST['delivery_address']),
            'special_requests' => sanitizeInput($_POST['special_requests'])
        ];

        if ($order->update($order_id, $data)) {
            sendNotification($order_id, $data['status']);
            setFlashMessage('Order updated successfully.', 'success');
            logActivity('update_order', "Updated order ID: $order_id", getCurrentStaffId());
        } else {
            setFlashMessage('Failed to update order.', 'error');
        }
    }
    redirect('manage-orders.php');
}

// Handle delete order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_order'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $order_id = (int)$_POST['order_id'];
        if ($order->delete($order_id)) {
            setFlashMessage('Order deleted successfully.', 'success');
            logActivity('delete_order', "Deleted order ID: $order_id", getCurrentStaffId());
        } else {
            setFlashMessage('Failed to delete order.', 'error');
        }
    }
    redirect('manage-orders.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Crust Pizza</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .form-container {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
        }

        .table th {
            background: #f8f9fa;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #ff6b35;
            color: white;
            border: none;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
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
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Manage Orders</h1>
        <?php displayFlashMessages(); ?>

        <h2>Order List</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Placed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $ord): ?>
                    <tr>
                        <td><a href="view-order.php?id=<?php echo $ord['order_id']; ?>"><?php echo htmlspecialchars($ord['order_number']); ?></a></td>
                        <td><?php echo htmlspecialchars($ord['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($ord['status']); ?></td>
                        <td><?php echo formatCurrency($ord['total']); ?></td>
                        <td><?php echo timeAgo($ord['created_at']); ?></td>
                        <td>
                            <a href="view-order.php?id=<?php echo $ord['order_id']; ?>" class="btn btn-primary"><i class="fas fa-eye"></i> View</a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="order_id" value="<?php echo $ord['order_id']; ?>">
                                <button type="submit" name="delete_order" class="btn btn-danger" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
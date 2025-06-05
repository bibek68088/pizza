<?php
/**
 * Admin Dashboard
 * Main admin interface for managing the system
 * Crust Pizza Online Ordering System
 */

require_once '../config/database.php';
require_once '../classes/Order.php';
require_once '../classes/Pizza.php';
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
$pizza = new Pizza($db);

// Get dashboard statistics
$stats = $order->getOrderStats();
$recent_orders = $order->getRecentOrders(10);

// Get today's stats
$today_query = "SELECT 
                    COUNT(*) as today_orders,
                    COALESCE(SUM(total), 0) as today_revenue
                FROM orders 
                WHERE DATE(created_at) = CURDATE()";
$today_stmt = $db->prepare($today_query);
$today_stmt->execute();
$today_stats = $today_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Crust Pizza</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Admin Header -->
    <header class="header" style="background: #333;">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice" style="color: #ff6b35;"></i>
                <h1 style="color: white;">Crust Pizza Admin</h1>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-link active" style="color: #ff6b35;">Dashboard</a>
                <a href="orders.php" class="nav-link" style="color: white;">Orders</a>
                <a href="menu.php" class="nav-link" style="color: white;">Menu</a>
                <a href="users.php" class="nav-link" style="color: white;">Users</a>
                <a href="../logout.php" class="nav-link" style="color: white;">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container" style="padding: 2rem 20px;">
            <div class="page-header">
                <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
            </div>

            <?php displayFlashMessages(); ?>

            <!-- Statistics Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin: 2rem 0;">
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <i class="fas fa-shopping-bag" style="font-size: 3rem; color: #ff6b35; margin-bottom: 1rem;"></i>
                        <h3 style="margin: 0; font-size: 2rem;"><?php echo number_format($stats['total_orders']); ?></h3>
                        <p style="margin: 0; color: #666;">Total Orders</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <i class="fas fa-dollar-sign" style="font-size: 3rem; color: #28a745; margin-bottom: 1rem;"></i>
                        <h3 style="margin: 0; font-size: 2rem;"><?php echo formatCurrency($stats['total_revenue']); ?></h3>
                        <p style="margin: 0; color: #666;">Total Revenue</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <i class="fas fa-calendar-day" style="font-size: 3rem; color: #17a2b8; margin-bottom: 1rem;"></i>
                        <h3 style="margin: 0; font-size: 2rem;"><?php echo number_format($today_stats['today_orders']); ?></h3>
                        <p style="margin: 0; color: #666;">Today's Orders</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <i class="fas fa-clock" style="font-size: 3rem; color: #ffc107; margin-bottom: 1rem;"></i>
                        <h3 style="margin: 0; font-size: 2rem;"><?php echo number_format($stats['pending_orders']); ?></h3>
                        <p style="margin: 0; color: #666;">Pending Orders</p>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem;">
                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-header">
                        <h3 style="margin: 0;">Recent Orders</h3>
                        <a href="orders.php" class="btn btn-outline">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_orders)): ?>
                            <p style="text-align: center; color: #666;">No orders found</p>
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
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_orders as $recent_order): ?>
                                            <tr>
                                                <td>#<?php echo $recent_order['order_id']; ?></td>
                                                <td><?php echo htmlspecialchars($recent_order['customer_name']); ?></td>
                                                <td>
                                                    <i class="fas fa-<?php echo $recent_order['order_type'] === 'delivery' ? 'truck' : 'store'; ?>"></i>
                                                    <?php echo ucfirst($recent_order['order_type']); ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $recent_order['status'] === 'completed' ? 'success' : 'primary'; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $recent_order['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatCurrency($recent_order['total']); ?></td>
                                                <td><?php echo timeAgo($recent_order['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div>
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3 style="margin: 0;">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <a href="orders.php?status=pending" class="btn btn-primary">
                                    <i class="fas fa-clock"></i> View Pending Orders
                                </a>
                                <a href="menu.php" class="btn btn-secondary">
                                    <i class="fas fa-pizza-slice"></i> Manage Menu
                                </a>
                                <a href="users.php" class="btn btn-outline">
                                    <i class="fas fa-users"></i> Manage Users
                                </a>
                                <a href="../staff/" class="btn btn-outline">
                                    <i class="fas fa-user-tie"></i> Staff Dashboard
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- System Status -->
                    <div class="card">
                        <div class="card-header">
                            <h3 style="margin: 0;">System Status</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <span>Database:</span>
                                <span class="badge badge-success">Online</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <span>Orders System:</span>
                                <span class="badge badge-success">Active</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <span>Payment System:</span>
                                <span class="badge badge-success">Active</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Last Backup:</span>
                                <span style="color: #666; font-size: 0.9rem;">2 hours ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Auto-refresh dashboard every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>

    <style>
        .table-responsive {
            overflow-x: auto;
        }
        
        @media (max-width: 768px) {
            div[style*="grid-template-columns: 2fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</body>
</html>

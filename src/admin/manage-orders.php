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
        :root {
            --primary-color: #ff6b35;
            --hover-color: #f7931e;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: flex;
            gap: 2rem;
            padding-top: 80px;
        }

        .main-content {
            flex: 3;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
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

        .sidebar {
            flex: 1;
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar h3 {
            margin-top: 0;
            color: #333;
        }

        .sidebar a {
            display: block;
            margin: 0.5rem 0;
            color: #ff6b35;
            text-decoration: none;
            font-weight: 600;
        }

        .sidebar a:hover {
            text-decoration: underline;
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
            min-width: 160px;
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

        .dropdown .nav-link::after,
        .dropdown-toggle::after {
            display: none !important;
        }

        @media (max-width: 767px) {
            .dashboard-container {
                flex-direction: column;
                padding-top: 100px;
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
        }

        .page-header {
            text-align: center;
            margin: 2rem 0;
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

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .page-header p {
                font-size: 14px;
            }
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
            <button class="nav-toggle" onclick="toggleNavMenu()" aria-label="Toggle Navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="nav-menu" id="navMenu">
                <a href="../index.php" class="nav-link">Home</a>
                <a href="../menu.php" class="nav-link">Menu</a>
                <a href="../build-pizza.php" class="nav-link">Build Your Pizza</a>
                <a href="../track-order.php" class="nav-link">Track Order</a>
                <div class="dropdown">
                    <button class="dropdown-toggle" onclick="toggleDropdown()" aria-label="Admin Menu" aria-expanded="false" title="Admin Menu">
                        <span class="user-icon"><i class="fas fa-user"></i></span>
                        <span class="dropdown-arrow"></span>
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a class="dropdown-item" href="edit-profile.php">Profile</a>
                        <a class="dropdown-item" href="dashboard.php">Dashboard</a>
                        <a class="dropdown-item" href="../logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <h3>Admin Menu</h3>
            <a href="manage-ingredients.php"><i class="fas fa-carrot"></i> Manage Ingredients</a>
            <a href="manage-pizzas.php"><i class="fas fa-pizza-slice"></i> Manage Pizzas</a>
            <a href="manage-menu-items.php"><i class="fas fa-utensils"></i> Manage Menu Items</a>
            <a href="manage-users.php"><i class="fas fa-users"></i> Manage Users</a>
            <a href="manage-orders.php"><i class="fas fa-shopping-cart"></i> Manage Orders</a>
            <a href="edit-profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
            <a href="change-password.php"><i class="fas fa-key"></i> Change Password</a>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-shopping-cart"></i> Manage Orders</h1>
                <p>View and manage all customer orders</p>
            </div>

            <?php displayFlashMessages(); ?>

            <div class="recent-orders">
                <h2>Order List</h2>
                <?php if (empty($orders)): ?>
                    <p>No orders found.</p>
                <?php else: ?>
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
                <?php endif; ?>
            </div>
        </main>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Crust Pizza</h3>
                    <p>Australia's favorite gourmet pizza destination since 2001. From our family to yours, we're committed to delivering exceptional taste and quality in every bite.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="../menu.php"><i class="fas fa-pizza-slice"></i> Our Menu</a></li>
                        <li><a href="../build-pizza.php"><i class="fas fa-tools"></i> Build Your Pizza</a></li>
                        <li><a href="../track-order.php"><i class="fas fa-truck"></i> Track Your Order</a></li>
                        <li><a href="../locations.php"><i class="fas fa-map-marker-alt"></i> Find a Store</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Customer Care</h4>
                    <ul>
                        <li><a href="#"><i class="fas fa-phone"></i> Contact Us</a></li>
                        <li><a href="#"><i class="fas fa-question-circle"></i> FAQ</a></li>
                        <li><a href="#"><i class="fas fa-comment"></i> Feedback</a></li>
                        <li><a href="#"><i class="fas fa-file-contract"></i> Terms & Conditions</a></li>
                        <li><a href="#"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> <strong>1300 278 787</strong></li>
                        <li><i class="fas fa-envelope"></i> info@crustpizza.com.au</li>
                        <li><i class="fas fa-clock"></i> Mon-Sun: 11AM - 11PM</li>
                        <li><i class="fas fa-map-marker-alt"></i> 130+ locations across Australia</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>© <span id="currentYear"></span> Crust Pizza. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Set the current year in the copyright notice
        document.getElementById('currentYear').textContent = new Date().getFullYear();

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
            if (!dropdown.contains(event.target) && !navToggle.contains(event.target)) {
                dropdownMenu.classList.remove('show');
                navMenu.classList.remove('active');
                document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
            }
        });

        // Close dropdown and nav menu on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.getElementById('dropdownMenu').classList.remove('show');
                document.getElementById('navMenu').classList.remove('active');
                document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
            }
        });
    </script>
</body>

</html>
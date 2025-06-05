<?php

require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';
require_once 'includes/functions.php';

startSession();

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('Please log in to view your profile', 'warning');
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$order = new Order($db);

$message = '';
$error = '';

// Get user details
$user->getUserById($_SESSION['user_id']);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $user->full_name = sanitizeInput($_POST['full_name']);
    $user->phone = sanitizeInput($_POST['phone']);
    $user->address = sanitizeInput($_POST['address']);

    if ($user->update()) {
        $_SESSION['full_name'] = $user->full_name;
        $message = 'Profile updated successfully!';
    } else {
        $error = 'Failed to update profile';
    }
}

// Get user's order history
$user_orders = $order->getOrdersByUserId($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile - Crust Pizza</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice"></i>
                <p><a href="index.php" style="text-decoration: none; color: inherit;">Crust Pizza</a></p>
            </div>
            <button class="nav-toggle" onclick="toggleNavMenu()" aria-label="Toggle Navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="nav-menu" id="navMenu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="menu.php" class="nav-link">Menu</a>
                <a href="build-pizza.php" class="nav-link">Build Your Pizza</a>
                <a href="track-order.php" class="nav-link">Track Order</a>
                <div class="dropdown">
                    <button class="dropdown-toggle" onclick="toggleDropdown()" aria-label="User Menu" aria-expanded="false" title="User Menu">
                        <span class="user-icon"><i class="fas fa-user"></i></span>
                        <span class="dropdown-arrow"></span>
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <?php if (isLoggedIn()): ?>
                            <a class="dropdown-item" href="profile.php">Profile</a>
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        <?php else: ?>
                            <a class="dropdown-item" href="login.php">Login</a>
                            <a class="dropdown-item" href="signup.php">Sign Up</a>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="cart.php" class="nav-link cart-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-user"></i> Your Profile</h1>
                <p>Manage your account and view order history</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                <!-- Profile Information -->
                <div class="card">
                    <div class="card-header">
                        <h3 style="margin: 0;">Profile Information</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" class="form-control"
                                    value="<?php echo htmlspecialchars($user->username); ?>" disabled>
                                <small style="color: #666;">Username cannot be changed</small>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" class="form-control"
                                    value="<?php echo htmlspecialchars($user->email); ?>" disabled>
                                <small style="color: #666;">Email cannot be changed</small>
                            </div>

                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" name="full_name" id="full_name" class="form-control"
                                    value="<?php echo htmlspecialchars($user->full_name); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" name="phone" id="phone" class="form-control"
                                    value="<?php echo htmlspecialchars($user->phone); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea name="address" id="address" class="form-control" rows="3" required><?php echo htmlspecialchars($user->address); ?></textarea>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Account Summary -->
                <div>
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3 style="margin: 0;">Account Summary</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center;">
                                <div>
                                    <h3 style="margin: 0; color: #ff6b35;"><?php echo count($user_orders); ?></h3>
                                    <p style="margin: 0; color: #666;">Total Orders</p>
                                </div>
                                <div>
                                    <h3 style="margin: 0; color: #ff6b35;">
                                        <?php
                                        $total_spent = array_sum(array_column($user_orders, 'total'));
                                        echo formatCurrency($total_spent);
                                        ?>
                                    </h3>
                                    <p style="margin: 0; color: #666;">Total Spent</p>
                                </div>
                            </div>

                            <hr>

                            <div style="text-align: center;">
                                <p><strong>Member Since:</strong> <?php echo date('M j, Y', strtotime($user->created_at)); ?></p>
                                <p><strong>Last Updated:</strong> <?php echo timeAgo($user->updated_at); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h3 style="margin: 0;">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <a href="menu.php" class="btn btn-primary">
                                    <i class="fas fa-pizza-slice"></i> Order Now
                                </a>
                                <a href="build-pizza.php" class="btn btn-primary">
                                    <i class="fas fa-magic"></i> Build Custom Pizza
                                </a>
                                <a href="track-order.php" class="btn btn-outline">
                                    <i class="fas fa-search"></i> Track Order
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order History -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3 style="margin: 0;">Order History</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($user_orders)): ?>
                        <div style="text-align: center; padding: 3rem; color: #666;">
                            <i class="fas fa-shopping-bag" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <h3>No orders yet</h3>
                            <p>Start by ordering your first delicious pizza!</p>
                            <a href="menu.php" class="btn btn-primary">Browse Menu</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($user_orders as $user_order): ?>
                                        <tr>
                                            <td><strong>#<?php echo $user_order['order_id']; ?></strong></td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($user_order['created_at'])); ?></td>
                                            <td>
                                                <i class="fas fa-<?php echo $user_order['order_type'] === 'delivery' ? 'truck' : 'store'; ?>"></i>
                                                <?php echo ucfirst($user_order['order_type']); ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $user_order['status'] === 'completed' ? 'success' : ($user_order['status'] === 'cancelled' ? 'danger' : 'primary'); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $user_order['status'])); ?>
                                                </span>
                                            </td>
                                            <td><strong><?php echo formatCurrency($user_order['total']); ?></strong></td>
                                            <td>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <a href="track-order.php?order_id=<?php echo $user_order['order_id']; ?>"
                                                        class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($user_order['status'] === 'completed'): ?>
                                                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;"
                                                            onclick="reorderItems(<?php echo $user_order['order_id']; ?>)">
                                                            <i class="fas fa-redo"></i>
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

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Crust Pizza</h3>
                    <p>Gourmet gurus since 2001</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="menu.php"><i class="fas fa-pizza-slice"></i> Menu</a></li>
                        <li><a href="build-pizza.php"><i class="fas fa-magic"></i> Build Your Pizza</a></li>
                        <li><a href="track-order.php"><i class="fas fa-search"></i> Track Order</a></li>
                        <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-phone"></i> 1300 CRUST (780 987)</p>
                    <p><i class="fas fa-envelope"></i> info@crustpizza.com.au</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Pizza St, Food City</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 Crust Pizza. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
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

        function reorderItems(orderId) {
            if (confirm('Add all items from this order to your cart?')) {
                alert('Reorder functionality would be implemented here');
            }
        }
    </script>

    <style>
        :root {
            --primary-color: #ff6b35;
            --hover-color: #f7931e;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .page-header {
            text-align: center;
            margin: 2rem 0;
            padding-top: 80px;
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

        /* Override style.css nav-link */
        .dropdown .nav-link::after,
        .dropdown-toggle::after {
            display: none !important;
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
            min-width: 120px;
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

        @media (max-width: 767px) {
            div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: auto !important;
                display: block;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .page-header p {
                font-size: 14px;
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
    </style>
</body>

</html>
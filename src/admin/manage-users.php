<?php
require_once '../config/database.php';
require_once '../classes/User.php';
require_once '../includes/functions.php';

startSession();

if (!hasPermission('admin_access')) {
    setFlashMessage('Access denied.', 'error');
    redirect(BASE_PATH . 'login.php');
}

$database = Database::getInstance();
$db = $database->getConnection();
$user = new User($db);

// Get users (fixed to access 'users' key)
$usersResult = $user->getAllUsers();
$users = isset($usersResult['users']) && is_array($usersResult['users']) ? $usersResult['users'] : [];

// Handle add/edit user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_user'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $data = [
            'username' => sanitizeInput($_POST['username']),
            'full_name' => sanitizeInput($_POST['full_name']),
            'email' => sanitizeInput($_POST['email']),
            'phone' => sanitizeInput($_POST['phone']),
            'role' => sanitizeInput($_POST['role']),
            'password_hash' => !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null
        ];

        if (!validateEmail($data['email'])) {
            setFlashMessage('Invalid email address.', 'error');
        } elseif (!validatePhone($data['phone'])) {
            setFlashMessage('Invalid phone number.', 'error');
        } else {
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;

            if ($user_id) {
                $user->getUserById($user_id);
                $user->username = $data['username'];
                $user->full_name = $data['full_name'];
                $user->email = $data['email'];
                $user->phone = $data['phone'];
                $user->role = $data['role'];
                if ($data['password_hash']) {
                    $user->password_hash = $data['password_hash'];
                }
                if ($user->update()) {
                    setFlashMessage('User updated successfully.', 'success');
                    logActivity('update_user', "Updated user: {$data['username']}", getCurrentStaffId());
                } else {
                    setFlashMessage('Failed to update user.', 'error');
                }
            } else {
                $user->username = $data['username'];
                $user->full_name = $data['full_name'];
                $user->email = $data['email'];
                $user->phone = $data['phone'];
                $user->role = $data['role'];
                $user->password_hash = $data['password_hash'];
                if ($user->create()) {
                    setFlashMessage('User added successfully.', 'success');
                    logActivity('add_user', "Added user: {$data['username']}", getCurrentStaffId());
                } else {
                    setFlashMessage('Failed to add user.', 'error');
                }
            }
        }
    }
    redirect('manage-users.php');
}

// Handle delete user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $user_id = (int)$_POST['user_id'];
        if ($user_id !== $_SESSION['user_id']) {
            if ($user->delete($user_id)) {
                setFlashMessage('User deleted successfully.', 'success');
                logActivity('delete_user', "Deleted user ID: $user_id", getCurrentStaffId());
            } else {
                setFlashMessage('Failed to delete user.', 'error');
            }
        } else {
            setFlashMessage('Cannot delete your own account.', 'error');
        }
    }
    redirect('manage-users.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Crust Pizza</title>
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

        .form-container {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 4px;
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
                <h1><i class="fas fa-users"></i> Manage Users</h1>
                <p>Add, edit, or delete user accounts</p>
            </div>

            <?php displayFlashMessages(); ?>

            <div class="form-container">
                <h2><?php echo isset($_GET['edit_id']) ? 'Edit User' : 'Add New User'; ?></h2>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <?php if (isset($_GET['edit_id'])): ?>
                        <?php
                        $edit_id = (int)$_GET['edit_id'];
                        $editUser = [];
                        if ($user->getUserById($edit_id)) {
                            $editUser = [
                                'username' => $user->username,
                                'full_name' => $user->full_name,
                                'email' => $user->email,
                                'phone' => $user->phone,
                                'role' => $user->role
                            ];
                        }
                        ?>
                        <input type="hidden" name="user_id" value="<?php echo $edit_id; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($editUser['username'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($editUser['full_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($editUser['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" name="phone" class="form-control" required value="<?php echo htmlspecialchars($editUser['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select name="role" class="form-control" required>
                            <option value="customer" <?php echo (isset($editUser['role']) && $editUser['role'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                            <option value="kitchen" <?php echo (isset($editUser['role']) && $editUser['role'] == 'kitchen') ? 'selected' : ''; ?>>Kitchen Staff</option>
                            <option value="delivery" <?php echo (isset($editUser['role']) && $editUser['role'] == 'delivery') ? 'selected' : ''; ?>>Delivery Staff</option>
                            <option value="counter" <?php echo (isset($editUser['role']) && $editUser['role'] == 'counter') ? 'selected' : ''; ?>>Counter Staff</option>
                            <option value="admin" <?php echo (isset($editUser['role']) && $editUser['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="password"><?php echo isset($_GET['edit_id']) ? 'New Password (optional)' : 'Password'; ?></label>
                        <input type="password" name="password" class="form-control" <?php echo isset($_GET['edit_id']) ? '' : 'required'; ?>>
                    </div>
                    <button type="submit" name="save_user" class="btn btn-primary">Save User</button>
                </form>
            </div>

            <div class="recent-orders">
                <h2>User List</h2>
                <?php if (empty($users)): ?>
                    <p>No users found.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['role']); ?></td>
                                    <td>
                                        <a href="?edit_id=<?php echo $u['user_id']; ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-danger" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i> Delete</button>
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
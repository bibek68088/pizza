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

// Get current user data - fix the issue here
$currentUser = null;
if ($user->getUserById($_SESSION['user_id'])) {
    $currentUser = [
        'user_id' => $user->user_id,
        'username' => $user->username,
        'email' => $user->email,
        'full_name' => $user->full_name,
        'phone' => $user->phone,
        'address' => $user->address,
        'date_of_birth' => $user->date_of_birth,
        'role' => $user->role,
        'store_id' => $user->store_id,
        'hire_date' => $user->hire_date,
        'salary' => $user->salary,
        'is_active' => $user->is_active,
        'email_verified' => $user->email_verified,
        'created_at' => $user->created_at,
        'updated_at' => $user->updated_at
    ];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        // Validate email uniqueness (excluding current user)
        if ($user->emailExists($_POST['email'], $_SESSION['user_id'])) {
            setFlashMessage('Email address is already in use by another account.', 'error');
        } elseif (!validateEmail($_POST['email'])) {
            setFlashMessage('Invalid email address.', 'error');
        } elseif (!empty($_POST['phone']) && !validatePhone($_POST['phone'])) {
            setFlashMessage('Invalid phone number.', 'error');
        } else {
            // Set the user properties for update
            $user->user_id = $_SESSION['user_id'];
            $user->full_name = sanitizeInput($_POST['full_name']);
            $user->phone = sanitizeInput($_POST['phone']);
            $user->address = sanitizeInput($_POST['address'] ?? '');
            $user->date_of_birth = $_POST['date_of_birth'] ?? null;

            // Keep existing values for fields not being updated
            $user->role = $currentUser['role'];
            $user->store_id = $currentUser['store_id'];
            $user->hire_date = $currentUser['hire_date'];
            $user->salary = $currentUser['salary'];
            $user->is_active = $currentUser['is_active'];
            $user->email_verified = $currentUser['email_verified'];

            if ($user->update()) {
                // Update email separately if changed
                if ($_POST['email'] !== $currentUser['email']) {
                    $query = "UPDATE users SET email = :email, updated_at = NOW() WHERE user_id = :user_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(":email", $_POST['email']);
                    $stmt->bindParam(":user_id", $_SESSION['user_id'], PDO::PARAM_INT);
                    $stmt->execute();
                }

                $_SESSION['full_name'] = $user->full_name;
                setFlashMessage('Profile updated successfully.', 'success');
                logActivity('update_profile', "Updated profile for user ID: {$_SESSION['user_id']}", $_SESSION['user_id']);

                // Refresh current user data
                if ($user->getUserById($_SESSION['user_id'])) {
                    $currentUser = [
                        'user_id' => $user->user_id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'full_name' => $user->full_name,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'date_of_birth' => $user->date_of_birth,
                        'role' => $user->role,
                        'store_id' => $user->store_id,
                        'hire_date' => $user->hire_date,
                        'salary' => $user->salary,
                        'is_active' => $user->is_active,
                        'email_verified' => $user->email_verified,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ];
                }
            } else {
                setFlashMessage('Failed to update profile.', 'error');
            }
        }
    }
    redirect('edit-profile.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Crust Pizza</title>
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

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .card-header h3 {
            margin: 0;
            color: #333;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .form-control:disabled {
            background: #f8f9fa;
            color: #666;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: var(--hover-color);
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
            background: linear-gradient(45deg, var(--primary-color), var(--hover-color));
            color: #fff;
            outline: none;
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
            }

            .nav-link,
            .dropdown {
                padding: 0;
                width: 100%;
                text-align: left;
            }

            .dropdown-menu {
                position: static;
                width: 100%;
                min-width: 0;
                box-shadow: none;
                margin-top: 0;
                padding: 0 0 0 20px;
                background: #f8f9fa;
                border-radius: 0;
            }

            .dropdown-item {
                padding: 6px 12px;
            }

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
                <h1><i class="fas fa-user"></i> Edit Profile</h1>
                <p>Manage your account details</p>
            </div>

            <?php displayFlashMessages(); ?>

            <?php if ($currentUser): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Profile Information</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" class="form-control"
                                    value="<?php echo htmlspecialchars($currentUser['username'] ?? ''); ?>" disabled>
                                <small style="color: #666;">Username cannot be changed</small>
                            </div>
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" name="full_name" id="full_name" class="form-control" required
                                    value="<?php echo htmlspecialchars($currentUser['full_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control" required
                                    value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" name="phone" id="phone" class="form-control"
                                    value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea name="address" id="address" class="form-control" rows="3"><?php echo htmlspecialchars($currentUser['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control"
                                    value="<?php echo htmlspecialchars($currentUser['date_of_birth'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="role">Role</label>
                                <input type="text" id="role" class="form-control"
                                    value="<?php echo htmlspecialchars(ucfirst($currentUser['role'] ?? '')); ?>" disabled>
                                <small style="color: #666;">Role cannot be changed</small>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <p style="color: #dc3545; text-align: center;">Unable to load user profile. Please try again.</p>
                    </div>
                </div>
            <?php endif; ?>
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
                        <li><a href="../build-pizza.php"><i class="fas fa-tools"></i> Ricciardo Build Your Pizza</a></li>
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

    <script src="../assets/js/main.js"></script>
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

        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
            const cartCount = cart.reduce((total, item) => total + (item.quantity || 1), 0);
            const cartCountElement = document.getElementById('cartCount');
            if (cartCountElement) {
                cartCountElement.textContent = cartCount;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();

            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                    navbar.style.boxShadow = '0 4px 25px rgba(0, 0, 0, 0.15)';
                } else {
                    navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                    navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
                }
            });
        });
    </script>
</body>

</html>
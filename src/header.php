<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

startSession();

$database = new Database();
$db = $database->getConnection();


// Helper function to get dashboard URL based on user role
function getDashboardUrl($role)
{
    $role = strtolower($role);
    $validRoles = ['admin', 'kitchen', 'delivery', 'counter'];

    if ($role === 'admin') {
        return 'admin/dashboard.php';
    } elseif (in_array($role, $validRoles)) {
        $path = "staff/$role/dashboard.php";
        return file_exists($path) ? $path : 'index.php';
    }
    return 'dashboard.php'; // Default for regular users
}

// Helper function to get profile URL
function getProfileUrl($role)
{
    $role = strtolower($role);
    if ($role === 'admin') {
        return 'admin/edit-profile.php';
    }
    return 'profile.php';
}

// Determine the current page
$current_page = basename($_SERVER['PHP_SELF']);

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crust Pizza - Gourmet Pizza Delivered</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Enhanced Navigation -->
    <nav class="navbar">
        <?php if (isLoggedIn()): ?>
            <meta name="user-id" content="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
        <?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice"></i>
                <p><a href="index.php" style="text-decoration: none; color: inherit;">Crust Pizza</a></p>
            </div>
            <button class="nav-toggle" onclick="toggleNavMenu()" aria-label="Toggle Navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="nav-menu" id="navMenu">
                <a href="index.php" class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a>
                <a href="menu.php" class="nav-link <?php echo $current_page === 'menu.php' ? 'active' : ''; ?>">Menu</a>
                <a href="build-pizza.php" class="nav-link <?php echo $current_page === 'build-pizza.php' ? 'active' : ''; ?>">Build Your Pizza</a>
                <?php if (isLoggedIn() && $_SESSION['role'] !== 'admin' && !isStaff()): ?>
                    <a href="track-order.php" class="nav-link <?php echo $current_page === 'track-order.php' ? 'active' : ''; ?>">Track Order</a>
                <?php endif; ?>
                <div class="dropdown">
                    <button class="dropdown-toggle" onclick="toggleDropdown()" aria-label="User Menu" aria-expanded="false" title="User Menu">
                        <span class="user-icon"><i class="fas fa-user"></i></span>
                        <span class="dropdown-arrow"></span>
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <?php if (isLoggedIn()): ?>
                            <a class="dropdown-item" href="<?php echo htmlspecialchars(getProfileUrl($_SESSION['role'])); ?>">Profile</a>
                            <a class="dropdown-item" href="<?php echo htmlspecialchars(getDashboardUrl($_SESSION['role'])); ?>">Dashboard</a>
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        <?php else: ?>
                            <a class="dropdown-item" href="login.php">Login</a>
                            <a class="dropdown-item" href="register.php">Sign Up</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!isStaff()): ?>
                    <a href="cart.php" class="nav-link cart-link">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if (isLoggedIn() && !isStaff()): ?>
                            <span class="cart-count" id="cartCount">0</span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script src="assets/js/main.js"></script>
    <script>
        // Function to update cart count in the header
        function updateCartCount() {
            try {
                const cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
                if (!Array.isArray(cart)) {
                    console.error('Invalid cart data:', cart);
                    localStorage.setItem('crustPizzaCart', JSON.stringify([]));
                    const cartCountElement = document.getElementById('cartCount');
                    if (cartCountElement) {
                        cartCountElement.textContent = '0';
                    }
                    return;
                }
                const cartCount = cart.reduce((total, item) => {
                    const qty = Number(item.quantity) || 1;
                    return total + qty;
                }, 0);
                const cartCountElement = document.getElementById('cartCount');
                if (cartCountElement) {
                    cartCountElement.textContent = cartCount.toString();
                }
            } catch (error) {
                console.error('Error updating cart count:', error);
                localStorage.setItem('crustPizzaCart', JSON.stringify([]));
                const cartCountElement = document.getElementById('cartCount');
                if (cartCountElement) {
                    cartCountElement.textContent = '0';
                }
            }
        }

        // Load cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });

        // Functions for dropdown and navigation menu (unchanged)
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
    <style>
        /* Cart Link and Counter Styles (aligned with cart.php) */
        .cart-link {
            position: relative;
            display: flex;
            align-items: center;
            color: inherit;
            text-decoration: none;
            padding: 0.5rem 1rem;
            transition: color 0.2s ease;
        }

        .cart-link:hover {
            color: #ff6b35;
        }

        .cart-link i {
            font-size: 1.2rem;
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: 0;
            background: #ff6b35;
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .cart-link {
                padding: 0.5rem;
            }

            .cart-count {
                width: 16px;
                height: 16px;
                font-size: 0.65rem;
            }
        }
    </style>
</body>

</html>
<?php
require_once 'config/database.php';
require_once 'classes/Pizza.php';
require_once 'includes/functions.php';

startSession();

$database = new Database();
$db = $database->getConnection();
$pizza = new Pizza($db);

// Get featured pizzas for homepage
$featured_pizzas = $pizza->getFeaturedPizzas(6);

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
        <?php
        // Generate CSRF token if not already set
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        ?>
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
                <a href="track-order.php" class="nav-link <?php echo $current_page === 'track-order.php' ? 'active' : ''; ?>">Track Order</a>
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
                <a href="cart.php" class="nav-link cart-link">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (isLoggedIn()): ?>
                        <span class="cart-count" id="cartCount">0</span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>
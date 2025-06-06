<?php

require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'includes/functions.php';

startSession();

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$login_error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $login_error = 'Please fill in all fields';
    } else {
        if ($user->login($username, $password)) {
            $_SESSION['user_id'] = $user->user_id;
            $_SESSION['username'] = $user->username;
            $_SESSION['full_name'] = $user->full_name;
            setFlashMessage('Welcome back, ' . $user->full_name . '!', 'success');
            redirect('index.php');
        } else {
            $login_error = 'Invalid username or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Crust Pizza</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-container {
            min-height: calc(100vh - 140px);
            /* Account for header and footer */
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
        }

        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
            width: 100%;
            max-width: 450px;
            padding: 3rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }

        .login-header p {
            color: #666;
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff6b35;
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: #ff6b35;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-login:hover {
            background: #e55a2b;
        }

        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .auth-links a {
            color: #ff6b35;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice"></i>
                <p><a href="index.php" style="text-decoration: none; color: inherit;">Crust Pizza</a></p>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="menu.php" class="nav-link">Menu</a>
                <a href="build-pizza.php" class="nav-link">Build Your Pizza</a>
                <a href="track-order.php" class="nav-link">Track Order</a>
                <a href="login.php" class="nav-link active">Login</a>
                <a href="cart.php" class="nav-link cart-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Login Content -->
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your Crust Pizza account</p>
            </div>

            <?php if ($login_error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" name="login" class="btn-login">
                    Sign In
                </button>
            </form>

            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Crust Pizza</h3>
                    <p>Delivering gourmet pizza experiences since 2024</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="build-pizza.php">Build Your Pizza</a></li>
                        <li><a href="track-order.php">Track Order</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-phone"></i> 1300 CRUST (1300 278 787)</p>
                    <p><i class="fas fa-envelope"></i> info@crustpizza.com.au</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Crust Pizza. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>

</html>
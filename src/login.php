<?php
// Ensure errors are visible
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering
ob_start();

// Log file for debugging
define('LOG_FILE', 'C:/xampp/htdocs/pizza/src/debug.log');
function debug_log($message)
{
    file_put_contents(LOG_FILE, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

debug_log("--- New request to login.php ---");

require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'includes/functions.php';

startSession();
debug_log("Session ID: " . session_id());
debug_log("Session data before processing: " . print_r($_SESSION, true));

// Redirect if already logged in
if (isLoggedIn()) {
    debug_log("User already logged in, redirecting");
    ob_end_flush();
    redirectByRole($_SESSION['role']);
    exit;
}

// Simple redirect function
function redirectByRole($role)
{
    $role = strtolower($role);
    debug_log("Redirecting for role: $role");
    $validRoles = ['admin', 'kitchen', 'delivery', 'counter'];

    $url = 'index.php';
    if ($role === 'admin') {
        $url = 'admin/dashboard.php';
    } elseif (in_array($role, $validRoles)) {
        $path = "staff/$role/dashboard.php";
        $url = file_exists($path) ? $path : 'index.php';
    }

    debug_log("Redirecting to: $url");
    header("Location: $url");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log("POST request received: " . print_r($_POST, true));

    $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    debug_log("Username: $username");

    if (empty($username) || empty($password)) {
        $login_error = 'Please fill in all fields';
        debug_log("Empty username or password");
    } else {
        try {
            if ($user->login($username, $password)) {
                debug_log("Login successful for user ID: {$user->user_id}");
                $_SESSION['user_id'] = $user->user_id;
                $_SESSION['username'] = $user->username;
                $_SESSION['full_name'] = $user->full_name;
                $_SESSION['role'] = $user->role;
                debug_log("Session data set: " . print_r($_SESSION, true));

                setFlashMessage('Welcome back, ' . $user->full_name . '!', 'success');

                session_write_close();
                ob_end_flush();
                redirectByRole($user->role);
                exit;
            } else {
                $login_error = 'Invalid username or password';
                debug_log("Login failed: Invalid credentials");
            }
        } catch (Exception $e) {
            $login_error = 'Login error occurred';
            debug_log("Login exception: " . $e->getMessage());
        }
    }
}

ob_end_flush();
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
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
            margin-top: 50px;
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

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
    </style>
</head>

<body>

    <?php include 'header.php'; ?>

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
            <?php displayFlashMessages(); ?>

            <form method="POST" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="form-group">
                    <label for="username">Email</label>
                    <input type="text" id="username" name="username" placeholder="Email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="********" class="form-control" required>
                </div>
                <button type="submit" name="login" value="1" class="btn-login">Sign In</button>
            </form>

            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Register</a></p>
                <p>Forgot password? <a href="forgot-password.php">Reset Password</a></p>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('Form submitting');
                });
            }
        });
    </script>
</body>

</html>
<?php
/**
 * Staff Login Page
 * Authentication for staff members
 * Crust Pizza Online Ordering System
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

startSession();

// Redirect if already logged in as staff
if (isStaff()) {
    redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();

$error = '';

// Handle staff login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['staff_login'])) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $query = "SELECT staff_id, username, password_hash, full_name, role, store_id 
                  FROM staff 
                  WHERE (username = :username OR email = :username) AND is_active = 1 
                  LIMIT 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $staff = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $staff['password_hash'])) {
                $_SESSION['staff_id'] = $staff['staff_id'];
                $_SESSION['staff_username'] = $staff['username'];
                $_SESSION['full_name'] = $staff['full_name'];
                $_SESSION['staff_role'] = $staff['role'];
                $_SESSION['staff_store_id'] = $staff['store_id'];
                
                setFlashMessage('Welcome back, ' . $staff['full_name'] . '!', 'success');
                redirect('index.php');
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - Crust Pizza</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .staff-login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .staff-login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }
        .staff-login-header {
            text-align: center;
            padding: 2rem;
            background: #2c3e50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="staff-login-container">
        <div class="staff-login-card">
            <div class="staff-login-header">
                <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 1rem;">
                    <i class="fas fa-user-tie" style="font-size: 2rem; color: #ff6b35;"></i>
                    <h1 style="margin: 0;">Staff Login</h1>
                </div>
                <p style="margin: 0; opacity: 0.8;">Crust Pizza Staff Portal</p>
            </div>
            
            <div style="padding: 2rem;">
                <h2 style="margin-bottom: 1.5rem; text-align: center;">Welcome Back</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
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
                    
                    <button type="submit" name="staff_login" class="btn btn-primary" style="width: 100%; background: #2c3e50; border-color: #2c3e50;">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>
                
                <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #ddd;">
                    <a href="../index.php" style="color: #666; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Main Site
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

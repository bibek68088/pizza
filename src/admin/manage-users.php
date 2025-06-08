<?php
require_once '../config/database.php';
require_once '..//classes/User.php';
require_once '../includes/functions.php';

startSession();

if (!hasPermission('admin_access')) {
    setFlashMessage('Access denied.', 'error');
    redirect(BASE_PATH . 'login.php');
}

$database = Database::getInstance();
$db = $database->getConnection();
$user = new User($db);

$users = $user->getAllUsers();

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
                if ($user->getUserById($user_id)) { // Load user to set properties
                    if ($user->update($data)) {
                        setFlashMessage('User updated successfully.', 'success');
                        logActivity('update_user', "Updated user: {$data['username']}", getCurrentStaffId());
                    } else {
                        setFlashMessage('Failed to update user.', 'error');
                    }
                } else {
                    setFlashMessage('User not found.', 'error');
                }
            } else {
                if ($user->create($data)) {
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
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .form-container {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
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
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice"></i>
                <p><a href="../index.php" style="text-decoration: none; color: inherit;">Crust Pizza</a></p>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="../logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Manage Users</h1>
        <?php displayFlashMessages(); ?>

        <div class="form-container">
            <h2><?php echo isset($_GET['edit_id']) ? 'Edit User' : 'Add New User'; ?></h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php if (isset($_GET['edit_id'])): ?>
                    <?php
                    $edit_id = (int)$_GET['edit_id'];
                    $editUser = $user->getUserById($edit_id);
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

        <h2>User List</h2>
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
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>© 2024 Crust Pizza. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>
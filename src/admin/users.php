<?php
require_once '../config/database.php';
require_once '../classes/User.php';
require_once '../includes/functions.php';

startSession();

// Check if user is admin
if (!isAdmin()) {
    setFlashMessage('Access denied. Admin privileges required.', 'error');
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle user operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_staff'])) {
        // Create staff member
        $query = "INSERT INTO staff (username, email, password_hash, full_name, role, store_id) 
                  VALUES (:username, :email, :password_hash, :full_name, :role, :store_id)";
        $stmt = $db->prepare($query);

        $password_hash = hashPassword($_POST['password']);

        $stmt->bindParam(':username', $_POST['username']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':full_name', $_POST['full_name']);
        $stmt->bindParam(':role', $_POST['role']);
        $stmt->bindParam(':store_id', $_POST['store_id']);

        if ($stmt->execute()) {
            $message = 'Staff member created successfully!';
        } else {
            $error = 'Failed to create staff member';
        }
    }

    if (isset($_POST['update_user_status'])) {
        // Update user status (activate/deactivate)
        $user_id = (int)$_POST['user_id'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $query = "UPDATE users SET updated_at = NOW() WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            $message = 'User status updated successfully!';
        } else {
            $error = 'Failed to update user status';
        }
    }
}

// Get all users
$users_query = "SELECT u.*, COUNT(o.order_id) as total_orders, COALESCE(SUM(o.total), 0) as total_spent
                FROM users u
                LEFT JOIN orders o ON u.user_id = o.user_id
                GROUP BY u.user_id
                ORDER BY u.created_at DESC";
$users_stmt = $db->prepare($users_query);
$users_stmt->execute();
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all staff
$staff_query = "SELECT s.*, st.name as store_name
                FROM staff s
                LEFT JOIN stores st ON s.store_id = st.store_id
                WHERE s.is_active = 1
                ORDER BY s.created_at DESC";
$staff_stmt = $db->prepare($staff_query);
$staff_stmt->execute();
$staff = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get stores for staff assignment
$stores_query = "SELECT * FROM stores WHERE is_active = 1 ORDER BY name";
$stores_stmt = $db->prepare($stores_query);
$stores_stmt->execute();
$stores = $stores_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Crust Pizza Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Admin Header -->
    <header class="header" style="background: #333;">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice" style="color: #ff6b35;"></i>
                <h1 style="color: white;"><a href="../index.php" style="color: white; text-decoration: none;">Crust Pizza Admin</a></h1>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-link" style="color: white;">Dashboard</a>
                <a href="orders.php" class="nav-link" style="color: white;">Orders</a>
                <a href="menu.php" class="nav-link" style="color: white;">Menu</a>
                <a href="users.php" class="nav-link active" style="color: #ff6b35;">Users</a>
                <a href="../logout.php" class="nav-link" style="color: white;">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container" style="padding: 2rem 20px;">
            <div class="page-header">
                <h1><i class="fas fa-users"></i> Users Management</h1>
                <p>Manage customers and staff members</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- User Tabs -->
            <div class="user-tabs" style="margin: 2rem 0;">
                <div class="tab-buttons" style="display: flex; gap: 1rem; border-bottom: 2px solid #ddd; margin-bottom: 2rem;">
                    <button class="tab-btn active" onclick="showTab('customers')" style="padding: 1rem 2rem; border: none; background: none; font-weight: 600; border-bottom: 3px solid #ff6b35; color: #ff6b35;">
                        Customers (<?php echo count($users); ?>)
                    </button>
                    <button class="tab-btn" onclick="showTab('staff')" style="padding: 1rem 2rem; border: none; background: none; font-weight: 600; color: #666;">
                        Staff (<?php echo count($staff); ?>)
                    </button>
                </div>

                <!-- Customers Tab -->
                <div id="customers-tab" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3 style="margin: 0;">Customer Accounts</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($users)): ?>
                                <p style="text-align: center; color: #666;">No customers found</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Contact</th>
                                                <th>Orders</th>
                                                <th>Total Spent</th>
                                                <th>Joined</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                                        <br><small style="color: #666;">@<?php echo htmlspecialchars($user['username']); ?></small>
                                                    </td>
                                                    <td>
                                                        <div><?php echo htmlspecialchars($user['email']); ?></div>
                                                        <div style="font-size: 0.9rem; color: #666;"><?php echo htmlspecialchars($user['phone']); ?></div>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-primary"><?php echo $user['total_orders']; ?> orders</span>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo formatCurrency($user['total_spent']); ?></strong>
                                                    </td>
                                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                                    <td>
                                                        <div style="display: flex; gap: 0.5rem;">
                                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;"
                                                                onclick="viewUserDetails(<?php echo $user['user_id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;"
                                                                onclick="viewUserOrders(<?php echo $user['user_id']; ?>)">
                                                                <i class="fas fa-shopping-bag"></i>
                                                            </button>
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

                <!-- Staff Tab -->
                <div id="staff-tab" class="tab-content" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem;">
                        <!-- Staff List -->
                        <div class="card">
                            <div class="card-header">
                                <h3 style="margin: 0;">Staff Members</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($staff)): ?>
                                    <p style="text-align: center; color: #666;">No staff members found</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Staff Member</th>
                                                    <th>Role</th>
                                                    <th>Store</th>
                                                    <th>Contact</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($staff as $staff_member): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($staff_member['full_name']); ?></strong>
                                                            <br><small style="color: #666;">@<?php echo htmlspecialchars($staff_member['username']); ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-<?php echo $staff_member['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                                                <?php echo ucfirst($staff_member['role']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($staff_member['store_name'] ?: 'All Stores'); ?></td>
                                                        <td>
                                                            <div style="font-size: 0.9rem;"><?php echo htmlspecialchars($staff_member['email']); ?></div>
                                                        </td>
                                                        <td>
                                                            <div style="display: flex; gap: 0.5rem;">
                                                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <?php if ($staff_member['staff_id'] != $_SESSION['staff_id']): ?>
                                                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; color: #dc3545; border-color: #dc3545;">
                                                                        <i class="fas fa-user-slash"></i>
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

                        <!-- Add Staff Form -->
                        <div class="card">
                            <div class="card-header">
                                <h3 style="margin: 0;">Add New Staff Member</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="form-group">
                                        <label for="staffUsername">Username</label>
                                        <input type="text" name="username" id="staffUsername" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="staffEmail">Email</label>
                                        <input type="email" name="email" id="staffEmail" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="staffFullName">Full Name</label>
                                        <input type="text" name="full_name" id="staffFullName" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="staffPassword">Password</label>
                                        <input type="password" name="password" id="staffPassword" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="staffRole">Role</label>
                                        <select name="role" id="staffRole" class="form-control" required>
                                            <option value="">Select Role</option>
                                            <option value="kitchen">Kitchen Staff</option>
                                            <option value="delivery">Delivery Staff</option>
                                            <option value="counter">Counter Staff</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="staffStore">Store</label>
                                        <select name="store_id" id="staffStore" class="form-control">
                                            <option value="">All Stores</option>
                                            <?php foreach ($stores as $store): ?>
                                                <option value="<?php echo $store['store_id']; ?>">
                                                    <?php echo htmlspecialchars($store['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <button type="submit" name="create_staff" class="btn btn-primary" style="width: 100%;">
                                        <i class="fas fa-user-plus"></i> Add Staff Member
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Tab functionality
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });

            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.style.borderBottom = 'none';
                btn.style.color = '#666';
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').style.display = 'block';

            // Add active class to clicked button
            event.target.classList.add('active');
            event.target.style.borderBottom = '3px solid #ff6b35';
            event.target.style.color = '#ff6b35';
        }

        function viewUserDetails(userId) {
            // Implement user details modal
            alert('View user details for ID: ' + userId);
        }

        function viewUserOrders(userId) {
            // Redirect to orders page with user filter
            window.location.href = 'orders.php?user_id=' + userId;
        }
    </script>

    <style>
        .table-responsive {
            overflow-x: auto;
        }

        @media (max-width: 768px) {
            div[style*="grid-template-columns: 1fr 400px"] {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</body>

</html>
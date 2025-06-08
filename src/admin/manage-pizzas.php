<?php
require_once '../config/database.php';
require_once '../classes/Pizza.php';
require_once '../classes/Ingredient.php';
require_once '../includes/functions.php';

startSession();

if (!hasPermission('admin_access')) {
    setFlashMessage('Access denied.', 'error');
    redirect(BASE_PATH . 'login.php');
}

$database = Database::getInstance();
$db = $database->getConnection();
$pizza = new Pizza($db);
$ingredient = new Ingredient($db);

$pizzas = $pizza->getAllPizzas()['pizzas'];
$ingredients = $ingredient->getAllIngredients();

// Handle add/edit pizza
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_pizza'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $data = [
            'name' => sanitizeInput($_POST['name']),
            'description' => sanitizeInput($_POST['description']),
            'base_price' => floatval($_POST['base_price']),
            'image' => '',
            'ingredients' => $_POST['ingredients'] ?? [],
            'category_id' => (int)($_POST['category_id'] ?? 1),
            'is_available' => isset($_POST['is_available']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_vegan' => isset($_POST['is_vegan']) ? 1 : 0,
            'is_gluten_free_available' => isset($_POST['is_gluten_free_available']) ? 1 : 0,
            'allergens' => sanitizeInput($_POST['allergens'] ?? ''),
            'prep_time_minutes' => (int)($_POST['prep_time_minutes'] ?? 15),
            'calories_small' => (int)($_POST['calories_small'] ?? 0),
            'calories_medium' => (int)($_POST['calories_medium'] ?? 0),
            'calories_large' => (int)($_POST['calories_large'] ?? 0),
            'popularity_score' => (int)($_POST['popularity_score'] ?? 0)
        ];

        // Handle image upload
        $pizza_id = isset($_POST['pizza_id']) ? (int)$_POST['pizza_id'] : null;
        $editPizza = $pizza_id ? $pizza->getPizzaById($pizza_id) : [];
        $staff_id = getCurrentStaffId();
        if (!empty($_FILES['image']['name']) && isValidUpload($_FILES['image'], ['image/jpeg', 'image/png'], 5242880)) {
            $uploadDir = BASE_PATH . 'assets/public/uploads/';
            error_log("BASE_PATH: " . BASE_PATH);
            error_log("Attempting to save image to: $uploadDir");

            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    error_log("Failed to create directory: $uploadDir");
                    setFlashMessage('Failed to create upload directory.', 'error');
                    logActivity('upload_dir_failure', "Failed to create directory: $uploadDir", null, $staff_id);
                    redirect('manage-pizzas.php');
                }
            }

            // Check if directory is writable
            if (!is_writable($uploadDir)) {
                error_log("Directory is not writable: $uploadDir");
                setFlashMessage('Upload directory is not writable.', 'error');
                logActivity('upload_dir_not_writable', "Directory not writable: $uploadDir", null, $staff_id);
                redirect('manage-pizzas.php');
            }

            // Check temporary file
            if (!file_exists($_FILES['image']['tmp_name']) || !is_readable($_FILES['image']['tmp_name'])) {
                error_log("Temporary file issue: {$_FILES['image']['tmp_name']}");
                setFlashMessage('Invalid or inaccessible temporary file.', 'error');
                logActivity('upload_temp_file_issue', "Temporary file issue: {$_FILES['image']['tmp_name']}", null, $staff_id);
                $data['image'] = $editPizza['image_url'] ?? '';
            } else {
                $data['image'] = $uploadDir . generateUniqueFilename($_FILES['image']['name']);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $data['image'])) {
                    $data['image'] = str_replace(BASE_PATH, '/', $data['image']);
                    error_log("Image successfully uploaded to: {$data['image']}");
                    logActivity('image_upload_success', "Uploaded image for pizza: {$data['name']} to {$data['image']}", null, $staff_id);
                    // Delete old image if updating
                    if ($pizza_id && !empty($editPizza['image_url']) && file_exists(BASE_PATH . $editPizza['image_url'])) {
                        if (unlink(BASE_PATH . $editPizza['image_url'])) {
                            error_log("Deleted old image: " . BASE_PATH . $editPizza['image_url']);
                            logActivity('image_delete_success', "Deleted old image: {$editPizza['image_url']}", null, $staff_id);
                        } else {
                            error_log("Failed to delete old image: " . BASE_PATH . $editPizza['image_url']);
                            logActivity('image_delete_failure', "Failed to delete old image: {$editPizza['image_url']}", null, $staff_id);
                        }
                    }
                } else {
                    error_log("Failed to move uploaded file to: {$data['image']}, Error: {$_FILES['image']['error']}");
                    setFlashMessage('Failed to upload image.', 'error');
                    logActivity('image_upload_failure', "Failed to upload image for pizza: {$data['name']}, Error: {$_FILES['image']['error']}", null, $staff_id);
                    $data['image'] = $editPizza['image_url'] ?? '';
                }
            }
        } else {
            if (!empty($_FILES['image']['name'])) {
                error_log("Invalid upload: Error code {$_FILES['image']['error']}, Type: {$_FILES['image']['type']}, Size: {$_FILES['image']['size']}");
                setFlashMessage('Invalid image file or upload error.', 'error');
                logActivity('invalid_upload', "Invalid image upload for pizza: {$data['name']}, Error: {$_FILES['image']['error']}", null, $staff_id);
            }
            $data['image'] = $editPizza['image_url'] ?? ''; // Retain existing image
        }

        if ($pizza_id) {
            if ($pizza->update($pizza_id, $data, $staff_id)) {
                setFlashMessage('Pizza updated successfully.', 'success');
                logActivity('update_pizza', "Updated pizza: {$data['name']}", null, $staff_id);
            } else {
                setFlashMessage('Failed to update pizza.', 'error');
                logActivity('update_pizza_failure', "Failed to update pizza: {$data['name']}", null, $staff_id);
            }
        } else {
            if ($pizza->create($data, $staff_id)) {
                setFlashMessage('Pizza added successfully.', 'success');
                logActivity('add_pizza', "Added pizza: {$data['name']}", null, $staff_id);
            } else {
                setFlashMessage('Failed to add pizza.', 'error');
                logActivity('add_pizza_failure', "Failed to add pizza: {$data['name']}", null, $staff_id);
            }
        }
    }
    redirect('manage-pizzas.php');
}

// Handle delete pizza
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_pizza'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $pizza_id = (int)$_POST['pizza_id'];
        $staff_id = getCurrentStaffId();
        $pizzaData = $pizza->getPizzaById($pizza_id);
        if ($pizza->delete($pizza_id, $staff_id)) {
            // Delete associated image
            if (!empty($pizzaData['image_url']) && file_exists(BASE_PATH . $pizzaData['image_url'])) {
                if (unlink(BASE_PATH . $pizzaData['image_url'])) {
                    error_log("Deleted image for pizza ID $pizza_id: " . BASE_PATH . $pizzaData['image_url']);
                    logActivity('image_delete_success', "Deleted image for pizza ID: $pizza_id", null, $staff_id);
                } else {
                    error_log("Failed to delete image for pizza ID $pizza_id: " . BASE_PATH . $pizzaData['image_url']);
                    logActivity('image_delete_failure', "Failed to delete image for pizza ID: $pizza_id", null, $staff_id);
                }
            }
            setFlashMessage('Pizza deleted successfully.', 'success');
            logActivity('delete_pizza', "Deleted pizza ID: $pizza_id", null, $staff_id);
        } else {
            setFlashMessage('Failed to delete pizza.', 'error');
            logActivity('delete_pizza_failure', "Failed to delete pizza ID: $pizza_id", null, $staff_id);
        }
    }
    redirect('manage-pizzas.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pizzas - Crust Pizza</title>
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
        }

        .form-control[type="checkbox"] {
            width: auto;
            margin-right: 0.5rem;
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
            border: none;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn:hover {
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
            transition: transform 0.3s ease;
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
                <h1><i class="fas fa-pizza-slice"></i> Manage Pizzas</h1>
                <p>Manage your pizza menu</p>
            </div>

            <?php displayFlashMessages(); ?>

            <div class="form-container">
                <h2><?php echo isset($_GET['edit_id']) ? 'Edit Pizza' : 'Add New Pizza'; ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <?php if (isset($_GET['edit_id'])): ?>
                        <?php
                        $edit_id = (int)$_GET['edit_id'];
                        $editPizza = $pizza->getPizzaById($edit_id);
                        ?>
                        <input type="hidden" name="pizza_id" value="<?php echo $edit_id; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($editPizza['name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" class="form-control"><?php echo htmlspecialchars($editPizza['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="base_price">Base Price (Small)</label>
                        <input type="number" name="base_price" step="0.01" class="form-control" required value="<?php echo htmlspecialchars($editPizza['base_price_small'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select name="category_id" class="form-control">
                            <?php
                            $categories = $db->query("SELECT category_id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($categories as $category) {
                                $selected = ($category['category_id'] == ($editPizza['category_id'] ?? '')) ? 'selected' : '';
                                echo "<option value=\"{$category['category_id']}\" $selected>" . htmlspecialchars($category['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png">
                        <?php if (!empty($editPizza['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($editPizza['image_url']); ?>" alt="Pizza Image" style="max-width: 100px; margin-top: 10px;">
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="allergens">Allergens</label>
                        <input type="text" name="allergens" class="form-control" value="<?php echo htmlspecialchars($editPizza['allergens'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="prep_time_minutes">Prep Time (Minutes)</label>
                        <input type="number" name="prep_time_minutes" class="form-control" value="<?php echo htmlspecialchars($editPizza['prep_time_minutes'] ?? 15); ?>">
                    </div>
                    <div class="form-group">
                        <label>Ingredients</label>
                        <?php foreach ($ingredients as $ing): ?>
                            <div>
                                <input type="checkbox" name="ingredients[]" value="<?php echo $ing['ingredient_id']; ?>"
                                    <?php echo (isset($editPizza['ingredients']) && in_array($ing['ingredient_id'], $editPizza['ingredients'])) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($ing['name']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="is_available" <?php echo (isset($editPizza['is_available']) && $editPizza['is_available']) ? 'checked' : ''; ?>> Available</label>
                        <label><input type="checkbox" name="is_featured" <?php echo (isset($editPizza['is_featured']) && $editPizza['is_featured']) ? 'checked' : ''; ?>> Featured</label>
                        <label><input type="checkbox" name="is_vegan" <?php echo (isset($editPizza['is_vegan']) && $editPizza['is_vegan']) ? 'checked' : ''; ?>> Vegan</label>
                        <label><input type="checkbox" name="is_gluten_free_available" <?php echo (isset($editPizza['is_gluten_free_available']) && $editPizza['is_gluten_free_available']) ? 'checked' : ''; ?>> Gluten-Free Available</label>
                    </div>
                    <button type="submit" name="save_pizza" class="btn btn-primary">Save Pizza</button>
                </form>
            </div>

            <h2>Pizza List</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Ingredients</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pizzas as $pz): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pz['name']); ?></td>
                            <td><?php echo formatCurrency($pz['base_price_small']); ?></td>
                            <td><?php echo htmlspecialchars($pz['ingredient_list'] ?? 'None'); ?></td>
                            <td>
                                <a href="?edit_id=<?php echo $pz['pizza_id']; ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="pizza_id" value="<?php echo $pz['pizza_id']; ?>">
                                    <button type="submit" name="delete_pizza" class="btn btn-danger" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
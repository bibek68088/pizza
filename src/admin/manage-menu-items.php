<?php
require_once '../config/database.php';
require_once '../classes/MenuItem.php';
require_once '../includes/functions.php';

startSession();

if (!hasPermission('admin_access')) {
    setFlashMessage('Access denied.', 'error');
    redirect(BASE_PATH . 'login.php');
}

$database = Database::getInstance();
$db = $database->getConnection();
$menuItem = new MenuItem($db);

$menuItems = $menuItem->getAllMenuItems();

// Handle add/edit menu item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_menu_item'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $data = [
            'name' => sanitizeInput($_POST['name']),
            'description' => sanitizeInput($_POST['description']),
            'price' => floatval($_POST['price']),
            'cost' => floatval($_POST['cost'] ?? 0.00),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'image_url' => '',
            'prep_time_minutes' => (int)($_POST['prep_time_minutes'] ?? 5),
            'calories' => (int)($_POST['calories'] ?? 0),
            'is_available' => isset($_POST['is_available']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_vegan' => isset($_POST['is_vegan']) ? 1 : 0,
            'is_gluten_free' => isset($_POST['is_gluten_free']) ? 1 : 0,
            'allergens' => sanitizeInput($_POST['allergens'] ?? ''),
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'popularity_score' => (int)($_POST['popularity_score'] ?? 0)
        ];

        // Handle image upload
        if (!empty($_FILES['image']['name']) && isValidUpload($_FILES['image'], ['image/jpeg', 'image/png'], 5242880)) {
            $uploadDir = BASE_PATH . 'assets/images/menu_items/';
            $data['image_url'] = $uploadDir . generateUniqueFilename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $data['image_url']);
            $data['image_url'] = str_replace(BASE_PATH, '/', $data['image_url']); // Store relative path
        }

        $menu_item_id = isset($_POST['menu_item_id']) ? (int)$_POST['menu_item_id'] : null;
        $staff_id = getCurrentStaffId();

        if ($menu_item_id) {
            if ($menuItem->update($menu_item_id, $data)) {
                setFlashMessage('Menu item updated successfully.', 'success');
                logActivity('update_menu_item', "Updated menu item: {$data['name']}", $staff_id);
            } else {
                setFlashMessage('Failed to update menu item.', 'error');
            }
        } else {
            if ($menuItem->create($data)) {
                setFlashMessage('Menu item added successfully.', 'success');
                logActivity('add_menu_item', "Added menu item: {$data['name']}", $staff_id);
            } else {
                setFlashMessage('Failed to add menu item.', 'error');
            }
        }
    }
    redirect('manage-menu-items.php');
}

// Handle delete menu item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_menu_item'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $menu_item_id = (int)$_POST['menu_item_id'];
        $staff_id = getCurrentStaffId();
        if ($menuItem->delete($menu_item_id)) {
            setFlashMessage('Menu item deleted successfully.', 'success');
            logActivity('delete_menu_item', "Deleted menu item ID: $menu_item_id", $staff_id);
        } else {
            setFlashMessage('Failed to delete menu item.', 'error');
        }
    }
    redirect('manage-menu-items.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu Items - Crust Pizza</title>
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
        <h1>Manage Menu Items</h1>
        <?php displayFlashMessages(); ?>

        <div class="form-container">
            <h2><?php echo isset($_GET['edit_id']) ? 'Edit Menu Item' : 'Add New Menu Item'; ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php if (isset($_GET['edit_id'])): ?>
                    <?php
                    $edit_id = (int)$_GET['edit_id'];
                    $editMenuItem = $menuItem->getMenuItemById($edit_id);
                    ?>
                    <input type="hidden" name="menu_item_id" value="<?php echo $edit_id; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($editMenuItem['name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" class="form-control"><?php echo htmlspecialchars($editMenuItem['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" name="price" step="0.01" class="form-control" required value="<?php echo htmlspecialchars($editMenuItem['price'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="cost">Cost</label>
                    <input type="number" name="cost" step="0.01" class="form-control" required value="<?php echo htmlspecialchars($editMenuItem['cost'] ?? '0.00'); ?>">
                </div>
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select name="category_id" class="form-control">
                        <?php
                        $categories = $db->query("SELECT category_id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($categories as $category) {
                            $selected = ($category['category_id'] == ($editMenuItem['category_id'] ?? '')) ? 'selected' : '';
                            echo "<option value=\"{$category['category_id']}\" $selected>" . htmlspecialchars($category['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" name="image" class="form-control" accept="image/jpeg,image/png">
                    <?php if (!empty($editMenuItem['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($editMenuItem['image_url']); ?>" alt="Menu Item Image" style="max-width: 100px; margin-top: 10px;">
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="prep_time_minutes">Prep Time (Minutes)</label>
                    <input type="number" name="prep_time_minutes" class="form-control" value="<?php echo htmlspecialchars($editMenuItem['prep_time_minutes'] ?? 5); ?>">
                </div>
                <div class="form-group">
                    <label for="calories">Calories</label>
                    <input type="number" name="calories" class="form-control" value="<?php echo htmlspecialchars($editMenuItem['calories'] ?? 0); ?>">
                </div>
                <div class="form-group">
                    <label for="allergens">Allergens</label>
                    <input type="text" name="allergens" class="form-control" value="<?php echo htmlspecialchars($editMenuItem['allergens'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="stock_quantity">Stock Quantity</label>
                    <input type="number" name="stock_quantity" class="form-control" value="<?php echo htmlspecialchars($editMenuItem['stock_quantity'] ?? 0); ?>">
                </div>
                <div class="form-group">
                    <label for="popularity_score">Popularity Score</label>
                    <input type="number" name="popularity_score" class="form-control" value="<?php echo htmlspecialchars($editMenuItem['popularity_score'] ?? 0); ?>">
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_available" <?php echo (isset($editMenuItem['is_available']) && $editMenuItem['is_available']) ? 'checked' : ''; ?>> Available</label>
                    <label><input type="checkbox" name="is_featured" <?php echo (isset($editMenuItem['is_featured']) && $editMenuItem['is_featured']) ? 'checked' : ''; ?>> Featured</label>
                    <label><input type="checkbox" name="is_vegan" <?php echo (isset($editMenuItem['is_vegan']) && $editMenuItem['is_vegan']) ? 'checked' : ''; ?>> Vegan</label>
                    <label><input type="checkbox" name="is_gluten_free" <?php echo (isset($editMenuItem['is_gluten_free']) && $editMenuItem['is_gluten_free']) ? 'checked' : ''; ?>> Gluten-Free</label>
                </div>
                <button type="submit" name="save_menu_item" class="btn btn-primary">Save Menu Item</button>
            </form>
        </div>

        <h2>Menu Item List</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menuItems as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo formatCurrency($item['price']); ?></td>
                        <td>
                            <?php
                            $category = $db->query("SELECT name FROM categories WHERE category_id = " . (int)$item['category_id'])->fetch(PDO::FETCH_ASSOC);
                            echo htmlspecialchars($category['name'] ?? 'Unknown');
                            ?>
                        </td>
                        <td>
                            <a href="?edit_id=<?php echo $item['menu_item_id']; ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="menu_item_id" value="<?php echo $item['menu_item_id']; ?>">
                                <button type="submit" name="delete_menu_item" class="btn btn-danger" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i> Delete</button>
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
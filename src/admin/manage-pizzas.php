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
        if (!empty($_FILES['image']['name']) && isValidUpload($_FILES['image'], ['image/jpeg', 'image/png'], 5242880)) {
            $uploadDir = BASE_PATH . 'public/';
            $data['image'] = $uploadDir . generateUniqueFilename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $data['image']);
            $data['image'] = str_replace(BASE_PATH, '/', $data['image']); // Store relative path
        }

        $pizza_id = isset($_POST['pizza_id']) ? (int)$_POST['pizza_id'] : null;
        $staff_id = getCurrentStaffId();

        if ($pizza_id) {
            if ($pizza->update($pizza_id, $data, $staff_id)) {
                setFlashMessage('Pizza updated successfully.', 'success');
                logActivity('update_pizza', "Updated pizza: {$data['name']}", $staff_id);
            } else {
                setFlashMessage('Failed to update pizza.', 'error');
            }
        } else {
            if ($pizza->create($data, $staff_id)) {
                setFlashMessage('Pizza added successfully.', 'success');
                logActivity('add_pizza', "Added pizza: {$data['name']}", $staff_id);
            } else {
                setFlashMessage('Failed to add pizza.', 'error');
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
        if ($pizza->delete($pizza_id, $staff_id)) {
            setFlashMessage('Pizza deleted successfully.', 'success');
            logActivity('delete_pizza', "Deleted pizza ID: $pizza_id", $staff_id);
        } else {
            setFlashMessage('Failed to delete pizza.', 'error');
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
        <h1>Manage Pizzas</h1>
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
                        // Assuming a categories table exists
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
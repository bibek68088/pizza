<?php
require_once '../config/database.php';
require_once '../classes/Ingredient.php';
require_once '../includes/functions.php';

startSession();

if (!hasPermission('admin_access')) {
    setFlashMessage('Access denied.', 'error');
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();
$ingredient = new Ingredient($db);

$ingredients = $ingredient->getAllIngredients();

// Handle add/edit ingredient
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_ingredient'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $data = [
            'name' => sanitizeInput($_POST['name']),
            'price' => floatval($_POST['price'])
        ];
        $ingredient_id = isset($_POST['ingredient_id']) ? (int)$_POST['ingredient_id'] : null;

        if ($ingredient_id) {
            if ($ingredient->update($ingredient_id, $data)) {
                setFlashMessage('Ingredient updated successfully.', 'success');
                logActivity('update_ingredient', "Updated ingredient: {$data['name']}", getCurrentStaffId());
            } else {
                setFlashMessage('Failed to update ingredient.', 'error');
            }
        } else {
            if ($ingredient->create($data)) {
                setFlashMessage('Ingredient added successfully.', 'success');
                logActivity('add_ingredient', "Added ingredient: {$data['name']}", getCurrentStaffId());
            } else {
                setFlashMessage('Failed to add ingredient.', 'error');
            }
        }
    }
    redirect('manage-ingredients.php');
}

// Handle delete ingredient
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_ingredient'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid CSRF token.', 'error');
    } else {
        $ingredient_id = (int)$_POST['ingredient_id'];
        if ($ingredient->delete($ingredient_id)) {
            setFlashMessage('Ingredient deleted successfully.', 'success');
            logActivity('delete_ingredient', "Deleted ingredient ID: $ingredient_id", getCurrentStaffId());
        } else {
            setFlashMessage('Failed to delete ingredient.', 'error');
        }
    }
    redirect('manage-ingredients.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ingredients - Crust Pizza</title>
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
        <h1>Manage Ingredients</h1>
        <?php displayFlashMessages(); ?>
        <div class="form-container">
            <h2><?php echo isset($_GET['edit_id']) ? 'Edit Ingredient' : 'Add New Ingredient'; ?></h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <?php if (isset($_GET['edit_id'])): ?>
                    <?php
                    $edit_id = (int)$_GET['edit_id'];
                    $editIngredient = $ingredient->getById($edit_id);
                    ?>
                    <input type="hidden" name="ingredient_id" value="<?php echo $edit_id; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($editIngredient['name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" name="price" step救助
                        step="0.01" class="form-control" required value="<?php echo htmlspecialchars($editIngredient['price'] ?? ''); ?>">
                </div>
                <button type="submit" name="save_ingredient" class="btn btn-primary">Save Ingredient</button>
            </form>
        </div>
        <h2>Ingredient List</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ingredients as $ing): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ing['name']); ?></td>
                        <td><?php echo formatCurrency($ing['price']); ?></td>
                        <td>
                            <a href="?edit_id=<?php echo $ing['ingredient_id']; ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="ingredient_id" value="<?php echo $ing['ingredient_id']; ?>">
                                <button type="submit" name="delete_ingredient" class="btn btn-danger" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i> Delete</button>
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
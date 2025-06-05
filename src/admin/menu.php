<?php
/**
 * Admin Menu Management
 * CRUD operations for pizzas and menu items
 * Crust Pizza Online Ordering System
 */

require_once '../config/database.php';
require_once '../classes/Pizza.php';
require_once '../includes/functions.php';

startSession();

// Check if user is admin
if (!isAdmin()) {
    setFlashMessage('Access denied. Admin privileges required.', 'error');
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();
$pizza = new Pizza($db);

$message = '';
$error = '';

// Handle pizza creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_pizza'])) {
    $pizza->name = sanitizeInput($_POST['name']);
    $pizza->description = sanitizeInput($_POST['description']);
    $pizza->category_id = (int)$_POST['category_id'];
    $pizza->base_price_small = (float)$_POST['base_price_small'];
    $pizza->base_price_medium = (float)$_POST['base_price_medium'];
    $pizza->base_price_large = (float)$_POST['base_price_large'];
    $pizza->image_url = sanitizeInput($_POST['image_url']);
    
    if ($pizza->create()) {
        $message = 'Pizza created successfully!';
    } else {
        $error = 'Failed to create pizza';
    }
}

// Handle pizza update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_pizza'])) {
    $pizza->pizza_id = (int)$_POST['pizza_id'];
    $pizza->name = sanitizeInput($_POST['name']);
    $pizza->description = sanitizeInput($_POST['description']);
    $pizza->category_id = (int)$_POST['category_id'];
    $pizza->base_price_small = (float)$_POST['base_price_small'];
    $pizza->base_price_medium = (float)$_POST['base_price_medium'];
    $pizza->base_price_large = (float)$_POST['base_price_large'];
    $pizza->image_url = sanitizeInput($_POST['image_url']);
    
    if ($pizza->update()) {
        $message = 'Pizza updated successfully!';
    } else {
        $error = 'Failed to update pizza';
    }
}

// Handle pizza deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_pizza'])) {
    $pizza_id = (int)$_POST['pizza_id'];
    
    if ($pizza->delete($pizza_id)) {
        $message = 'Pizza deleted successfully!';
    } else {
        $error = 'Failed to delete pizza';
    }
}

// Handle menu item operations
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_menu_item'])) {
    $query = "INSERT INTO menu_items (name, description, category_id, price, image_url, is_vegan, is_gluten_free) 
              VALUES (:name, :description, :category_id, :price, :image_url, :is_vegan, :is_gluten_free)";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':name', $_POST['name']);
    $stmt->bindParam(':description', $_POST['description']);
    $stmt->bindParam(':category_id', $_POST['category_id']);
    $stmt->bindParam(':price', $_POST['price']);
    $stmt->bindParam(':image_url', $_POST['image_url']);
    $stmt->bindParam(':is_vegan', isset($_POST['is_vegan']) ? 1 : 0);
    $stmt->bindParam(':is_gluten_free', isset($_POST['is_gluten_free']) ? 1 : 0);
    
    if ($stmt->execute()) {
        $message = 'Menu item created successfully!';
    } else {
        $error = 'Failed to create menu item';
    }
}

// Get all pizzas
$pizzas = $pizza->getAllPizzas();

// Get all menu items
$menu_items_query = "SELECT mi.*, c.name as category_name 
                     FROM menu_items mi
                     LEFT JOIN categories c ON mi.category_id = c.category_id
                     WHERE mi.is_available = 1
                     ORDER BY c.name, mi.name";
$menu_items_stmt = $db->prepare($menu_items_query);
$menu_items_stmt->execute();
$menu_items = $menu_items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$categories_query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Crust Pizza Admin</title>
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
                <a href="menu.php" class="nav-link active" style="color: #ff6b35;">Menu</a>
                <a href="users.php" class="nav-link" style="color: white;">Users</a>
                <a href="../logout.php" class="nav-link" style="color: white;">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container" style="padding: 2rem 20px;">
            <div class="page-header">
                <h1><i class="fas fa-pizza-slice"></i> Menu Management</h1>
                <p>Manage pizzas and menu items</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Menu Tabs -->
            <div class="menu-tabs" style="margin: 2rem 0;">
                <div class="tab-buttons" style="display: flex; gap: 1rem; border-bottom: 2px solid #ddd; margin-bottom: 2rem;">
                    <button class="tab-btn active" onclick="showTab('pizzas')" style="padding: 1rem 2rem; border: none; background: none; font-weight: 600; border-bottom: 3px solid #ff6b35; color: #ff6b35;">
                        Pizzas
                    </button>
                    <button class="tab-btn" onclick="showTab('menu-items')" style="padding: 1rem 2rem; border: none; background: none; font-weight: 600; color: #666;">
                        Menu Items
                    </button>
                    <button class="tab-btn" onclick="showTab('categories')" style="padding: 1rem 2rem; border: none; background: none; font-weight: 600; color: #666;">
                        Categories
                    </button>
                </div>

                <!-- Pizzas Tab -->
                <div id="pizzas-tab" class="tab-content">
                    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem;">
                        <!-- Pizza List -->
                        <div class="card">
                            <div class="card-header">
                                <h3 style="margin: 0;">Current Pizzas</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($pizzas)): ?>
                                    <p style="text-align: center; color: #666;">No pizzas found</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Category</th>
                                                    <th>Prices</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pizzas as $pizza_item): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($pizza_item['name']); ?></strong>
                                                            <br><small style="color: #666;"><?php echo htmlspecialchars(substr($pizza_item['description'], 0, 50)); ?>...</small>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($pizza_item['category_name']); ?></td>
                                                        <td>
                                                            <small>
                                                                S: <?php echo formatCurrency($pizza_item['base_price_small']); ?><br>
                                                                M: <?php echo formatCurrency($pizza_item['base_price_medium']); ?><br>
                                                                L: <?php echo formatCurrency($pizza_item['base_price_large']); ?>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <div style="display: flex; gap: 0.5rem;">
                                                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;" 
                                                                        onclick="editPizza(<?php echo htmlspecialchars(json_encode($pizza_item)); ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <form method="POST" style="display: inline;" 
                                                                      onsubmit="return confirm('Are you sure you want to delete this pizza?')">
                                                                    <input type="hidden" name="pizza_id" value="<?php echo $pizza_item['pizza_id']; ?>">
                                                                    <button type="submit" name="delete_pizza" class="btn btn-outline" 
                                                                            style="padding: 0.25rem 0.5rem; color: #dc3545; border-color: #dc3545;">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
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

                        <!-- Add/Edit Pizza Form -->
                        <div class="card">
                            <div class="card-header">
                                <h3 style="margin: 0;" id="pizzaFormTitle">Add New Pizza</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="pizzaForm">
                                    <input type="hidden" name="pizza_id" id="pizzaId">
                                    
                                    <div class="form-group">
                                        <label for="pizzaName">Pizza Name</label>
                                        <input type="text" name="name" id="pizzaName" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="pizzaDescription">Description</label>
                                        <textarea name="description" id="pizzaDescription" class="form-control" rows="3" required></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="pizzaCategory">Category</label>
                                        <select name="category_id" id="pizzaCategory" class="form-control" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['category_id']; ?>">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="pizzaImageUrl">Image URL</label>
                                        <input type="url" name="image_url" id="pizzaImageUrl" class="form-control">
                                    </div>
                                    
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                                        <div class="form-group">
                                            <label for="priceSmall">Small Price</label>
                                            <input type="number" step="0.01" name="base_price_small" id="priceSmall" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="priceMedium">Medium Price</label>
                                            <input type="number" step="0.01" name="base_price_medium" id="priceMedium" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="priceLarge">Large Price</label>
                                            <input type="number" step="0.01" name="base_price_large" id="priceLarge" class="form-control" required>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; gap: 1rem;">
                                        <button type="submit" name="create_pizza" id="createPizzaBtn" class="btn btn-primary" style="flex: 1;">
                                            <i class="fas fa-plus"></i> Add Pizza
                                        </button>
                                        <button type="submit" name="update_pizza" id="updatePizzaBtn" class="btn btn-primary" style="flex: 1; display: none;">
                                            <i class="fas fa-save"></i> Update Pizza
                                        </button>
                                        <button type="button" class="btn btn-outline" onclick="resetPizzaForm()">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menu Items Tab -->
                <div id="menu-items-tab" class="tab-content" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem;">
                        <!-- Menu Items List -->
                        <div class="card">
                            <div class="card-header">
                                <h3 style="margin: 0;">Current Menu Items</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($menu_items)): ?>
                                    <p style="text-align: center; color: #666;">No menu items found</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Category</th>
                                                    <th>Price</th>
                                                    <th>Dietary</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($menu_items as $item): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                                            <br><small style="color: #666;"><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</small>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                                        <td><?php echo formatCurrency($item['price']); ?></td>
                                                        <td>
                                                            <?php if ($item['is_vegan']): ?>
                                                                <span class="badge badge-success">Vegan</span>
                                                            <?php endif; ?>
                                                            <?php if ($item['is_gluten_free']): ?>
                                                                <span class="badge badge-info">GF</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div style="display: flex; gap: 0.5rem;">
                                                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; color: #dc3545; border-color: #dc3545;">
                                                                    <i class="fas fa-trash"></i>
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

                        <!-- Add Menu Item Form -->
                        <div class="card">
                            <div class="card-header">
                                <h3 style="margin: 0;">Add New Menu Item</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="form-group">
                                        <label for="itemName">Item Name</label>
                                        <input type="text" name="name" id="itemName" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="itemDescription">Description</label>
                                        <textarea name="description" id="itemDescription" class="form-control" rows="3" required></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="itemCategory">Category</label>
                                        <select name="category_id" id="itemCategory" class="form-control" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['category_id']; ?>">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="itemPrice">Price</label>
                                        <input type="number" step="0.01" name="price" id="itemPrice" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="itemImageUrl">Image URL</label>
                                        <input type="url" name="image_url" id="itemImageUrl" class="form-control">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="is_vegan" style="margin-right: 0.5rem;">
                                            Vegan
                                        </label>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="is_gluten_free" style="margin-right: 0.5rem;">
                                            Gluten Free
                                        </label>
                                    </div>
                                    
                                    <button type="submit" name="create_menu_item" class="btn btn-primary" style="width: 100%;">
                                        <i class="fas fa-plus"></i> Add Menu Item
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories Tab -->
                <div id="categories-tab" class="tab-content" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h3 style="margin: 0;">Categories</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $category['is_active'] ? 'success' : 'secondary'; ?>">
                                                        <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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

        // Pizza form functions
        function editPizza(pizza) {
            document.getElementById('pizzaFormTitle').textContent = 'Edit Pizza';
            document.getElementById('pizzaId').value = pizza.pizza_id;
            document.getElementById('pizzaName').value = pizza.name;
            document.getElementById('pizzaDescription').value = pizza.description;
            document.getElementById('pizzaCategory').value = pizza.category_id;
            document.getElementById('pizzaImageUrl').value = pizza.image_url || '';
            document.getElementById('priceSmall').value = pizza.base_price_small;
            document.getElementById('priceMedium').value = pizza.base_price_medium;
            document.getElementById('priceLarge').value = pizza.base_price_large;
            
            document.getElementById('createPizzaBtn').style.display = 'none';
            document.getElementById('updatePizzaBtn').style.display = 'block';
        }

        function resetPizzaForm() {
            document.getElementById('pizzaFormTitle').textContent = 'Add New Pizza';
            document.getElementById('pizzaForm').reset();
            document.getElementById('pizzaId').value = '';
            document.getElementById('createPizzaBtn').style.display = 'block';
            document.getElementById('updatePizzaBtn').style.display = 'none';
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

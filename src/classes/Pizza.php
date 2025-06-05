<?php
require_once 'config/database.php';

class Pizza
{
    private $conn;
    private $table_name = "pizzas";
    public $pizza_id;
    public $name;
    public $description;
    public $category_id;
    public $image_url;
    public $base_price_small;
    public $base_price_medium;
    public $base_price_large;
    public $cost_small;
    public $cost_medium;
    public $cost_large;
    public $prep_time_minutes;
    public $calories_small;
    public $calories_medium;
    public $calories_large;
    public $is_available;
    public $is_featured;
    public $is_vegan;
    public $is_gluten_free_available;
    public $allergens;
    public $popularity_score;

    public function __construct($db = null)
    {
        $this->conn = $db ?: Database::getInstance()->getConnection();
    }

    public function getAllPizzas($page = 1, $limit = 20, $search = '', $category = '', $featured_only = false)
    {
        $offset = ($page - 1) * $limit;

        $whereConditions = ['p.is_available = 1'];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(p.name LIKE :search OR p.description LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if (!empty($category)) {
            $whereConditions[] = "p.category_id = :category";
            $params[':category'] = $category;
        }

        if ($featured_only) {
            $whereConditions[] = "p.is_featured = 1";
        }

        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM " . $this->table_name . " p " . $whereClause;
        $countStmt = $this->conn->prepare($countQuery);
        $countStmt->execute($params);
        $totalPizzas = $countStmt->fetch()['total'];

        // Get pizzas
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  " . $whereClause . "
                  ORDER BY p.is_featured DESC, p.popularity_score DESC, p.name ASC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'pizzas' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $totalPizzas,
            'pages' => ceil($totalPizzas / $limit),
            'current_page' => $page
        ];
    }

    public function getPizzaById($pizza_id)
    {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  WHERE p.pizza_id = :pizza_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pizza_id", $pizza_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPizzaIngredients($pizza_id)
    {
        $query = "SELECT i.*, pi.is_default, pi.quantity
                  FROM ingredients i
                  JOIN pizza_ingredients pi ON i.ingredient_id = pi.ingredient_id
                  WHERE pi.pizza_id = :pizza_id AND i.is_available = 1
                  ORDER BY i.category, i.name";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pizza_id", $pizza_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPizzasByCategory($category_id)
    {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  WHERE p.category_id = :category_id AND p.is_available = 1
                  ORDER BY p.is_featured DESC, p.popularity_score DESC, p.name";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchPizzas($search_term)
    {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  WHERE (p.name LIKE :search OR p.description LIKE :search) 
                  AND p.is_available = 1
                  ORDER BY p.popularity_score DESC, p.name";

        $stmt = $this->conn->prepare($query);
        $search_param = "%{$search_term}%";
        $stmt->bindParam(":search", $search_param);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFeaturedPizzas($limit = 6)
    {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  WHERE p.is_featured = 1 AND p.is_available = 1
                  ORDER BY p.popularity_score DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function calculateCustomPrice($pizza_id, $size, $custom_ingredients = [])
    {
        // Get base price
        $pizza = $this->getPizzaById($pizza_id);
        if (!$pizza) return 0;

        $base_price = $pizza["base_price_{$size}"];
        $total_price = $base_price;

        // Add custom ingredient prices
        if (!empty($custom_ingredients)) {
            $placeholders = str_repeat('?,', count($custom_ingredients) - 1) . '?';
            $query = "SELECT SUM(price) as total_ingredient_price 
                      FROM ingredients 
                      WHERE ingredient_id IN ($placeholders) AND is_available = 1";

            $stmt = $this->conn->prepare($query);
            $stmt->execute($custom_ingredients);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['total_ingredient_price']) {
                $total_price += $result['total_ingredient_price'];
            }
        }

        return $total_price;
    }

    // Updated methods in Pizza class

    public function create($staff_id = null)
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, description=:description, category_id=:category_id,
                      image_url=:image_url, base_price_small=:base_price_small,
                      base_price_medium=:base_price_medium, base_price_large=:base_price_large,
                      cost_small=:cost_small, cost_medium=:cost_medium, cost_large=:cost_large,
                      prep_time_minutes=:prep_time_minutes, calories_small=:calories_small,
                      calories_medium=:calories_medium, calories_large=:calories_large,
                      is_featured=:is_featured, is_vegan=:is_vegan, 
                      is_gluten_free_available=:is_gluten_free_available, allergens=:allergens";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->allergens = htmlspecialchars(strip_tags($this->allergens));

        // Bind parameters
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":base_price_small", $this->base_price_small);
        $stmt->bindParam(":base_price_medium", $this->base_price_medium);
        $stmt->bindParam(":base_price_large", $this->base_price_large);
        $stmt->bindParam(":cost_small", $this->cost_small);
        $stmt->bindParam(":cost_medium", $this->cost_medium);
        $stmt->bindParam(":cost_large", $this->cost_large);
        $stmt->bindParam(":prep_time_minutes", $this->prep_time_minutes);
        $stmt->bindParam(":calories_small", $this->calories_small);
        $stmt->bindParam(":calories_medium", $this->calories_medium);
        $stmt->bindParam(":calories_large", $this->calories_large);
        $stmt->bindParam(":is_featured", $this->is_featured);
        $stmt->bindParam(":is_vegan", $this->is_vegan);
        $stmt->bindParam(":is_gluten_free_available", $this->is_gluten_free_available);
        $stmt->bindParam(":allergens", $this->allergens);

        if ($stmt->execute()) {
            $this->pizza_id = $this->conn->lastInsertId();

            // Get staff ID from session if not provided
            if ($staff_id === null && isset($_SESSION['staff_id'])) {
                $staff_id = $_SESSION['staff_id'];
            }

            // Only log if we have a staff ID
            if ($staff_id && function_exists('logActivity')) {
                logActivity('pizza_created', "Pizza '{$this->name}' created", null, $staff_id);
            }

            return true;
        }

        return false;
    }

    public function update($staff_id = null)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name, description=:description, category_id=:category_id,
                      image_url=:image_url, base_price_small=:base_price_small,
                      base_price_medium=:base_price_medium, base_price_large=:base_price_large,
                      cost_small=:cost_small, cost_medium=:cost_medium, cost_large=:cost_large,
                      prep_time_minutes=:prep_time_minutes, calories_small=:calories_small,
                      calories_medium=:calories_medium, calories_large=:calories_large,
                      is_featured=:is_featured, is_vegan=:is_vegan, 
                      is_gluten_free_available=:is_gluten_free_available, allergens=:allergens,
                      updated_at=NOW()
                  WHERE pizza_id=:pizza_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->allergens = htmlspecialchars(strip_tags($this->allergens));

        // Bind parameters
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":base_price_small", $this->base_price_small);
        $stmt->bindParam(":base_price_medium", $this->base_price_medium);
        $stmt->bindParam(":base_price_large", $this->base_price_large);
        $stmt->bindParam(":cost_small", $this->cost_small);
        $stmt->bindParam(":cost_medium", $this->cost_medium);
        $stmt->bindParam(":cost_large", $this->cost_large);
        $stmt->bindParam(":prep_time_minutes", $this->prep_time_minutes);
        $stmt->bindParam(":calories_small", $this->calories_small);
        $stmt->bindParam(":calories_medium", $this->calories_medium);
        $stmt->bindParam(":calories_large", $this->calories_large);
        $stmt->bindParam(":is_featured", $this->is_featured);
        $stmt->bindParam(":is_vegan", $this->is_vegan);
        $stmt->bindParam(":is_gluten_free_available", $this->is_gluten_free_available);
        $stmt->bindParam(":allergens", $this->allergens);
        $stmt->bindParam(":pizza_id", $this->pizza_id);

        if ($stmt->execute()) {
            // Get staff ID from session if not provided
            if ($staff_id === null && isset($_SESSION['staff_id'])) {
                $staff_id = $_SESSION['staff_id'];
            }

            // Only log if we have a staff ID
            if ($staff_id && function_exists('logActivity')) {
                logActivity('pizza_updated', "Pizza '{$this->name}' updated", null, $staff_id);
            }

            return true;
        }

        return false;
    }

    public function delete($pizza_id, $staff_id = null)
    {
        $query = "UPDATE " . $this->table_name . " SET is_available = 0, updated_at = NOW() WHERE pizza_id = :pizza_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pizza_id", $pizza_id);

        if ($stmt->execute()) {
            // Get staff ID from session if not provided
            if ($staff_id === null && isset($_SESSION['staff_id'])) {
                $staff_id = $_SESSION['staff_id'];
            }

            // Only log if we have a staff ID
            if ($staff_id && function_exists('logActivity')) {
                logActivity('pizza_deleted', "Pizza deleted", null, $staff_id);
            }

            return true;
        }
        return false;
    }

    public function updatePopularityScore($pizza_id, $increment = 1)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET popularity_score = popularity_score + :increment 
                  WHERE pizza_id = :pizza_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":increment", $increment);
        $stmt->bindParam(":pizza_id", $pizza_id);

        return $stmt->execute();
    }

    public function getPizzaStats($pizza_id = null, $days = 30)
    {
        $whereClause = $pizza_id ? "AND oi.pizza_id = :pizza_id" : "";

        $query = "SELECT 
                    p.pizza_id,
                    p.name,
                    COUNT(oi.order_item_id) as total_sold,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.total_price) as total_revenue,
                    AVG(oi.unit_price) as average_price
                  FROM pizzas p
                  LEFT JOIN order_items oi ON p.pizza_id = oi.pizza_id
                  LEFT JOIN orders o ON oi.order_id = o.order_id
                  WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  AND o.status != 'cancelled'
                  $whereClause
                  GROUP BY p.pizza_id, p.name
                  ORDER BY total_sold DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":days", $days);
        if ($pizza_id) {
            $stmt->bindParam(":pizza_id", $pizza_id);
        }
        $stmt->execute();

        return $pizza_id ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addIngredients($pizza_id, $ingredients)
    {
        try {
            $this->conn->beginTransaction();

            // Remove existing ingredients
            $deleteQuery = "DELETE FROM pizza_ingredients WHERE pizza_id = :pizza_id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(":pizza_id", $pizza_id);
            $deleteStmt->execute();

            // Add new ingredients
            $insertQuery = "INSERT INTO pizza_ingredients (pizza_id, ingredient_id, is_default, quantity) 
                           VALUES (:pizza_id, :ingredient_id, :is_default, :quantity)";
            $insertStmt = $this->conn->prepare($insertQuery);

            foreach ($ingredients as $ingredient) {
                $insertStmt->bindParam(":pizza_id", $pizza_id);
                $insertStmt->bindParam(":ingredient_id", $ingredient['ingredient_id']);
                $insertStmt->bindParam(":is_default", $ingredient['is_default']);
                $insertStmt->bindParam(":quantity", $ingredient['quantity']);
                $insertStmt->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}

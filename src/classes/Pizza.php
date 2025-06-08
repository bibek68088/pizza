<?php
require_once BASE_PATH . 'config/database.php';

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

        // Get pizzas with ingredients
        $query = "SELECT p.*, c.name as category_name,
                         GROUP_CONCAT(i.name) as ingredient_list
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN pizza_ingredients pi ON p.pizza_id = pi.pizza_id
                  LEFT JOIN ingredients i ON pi.ingredient_id = i.ingredient_id
                  " . $whereClause . "
                  GROUP BY p.pizza_id
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
        $query = "SELECT p.*, c.name as category_name,
                         GROUP_CONCAT(pi.ingredient_id) as ingredients
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN pizza_ingredients pi ON p.pizza_id = pi.pizza_id
                  WHERE p.pizza_id = :pizza_id
                  GROUP BY p.pizza_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pizza_id", $pizza_id, PDO::PARAM_INT);
        $stmt->execute();

        $pizza = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pizza && $pizza['ingredients']) {
            $pizza['ingredients'] = explode(',', $pizza['ingredients']);
        } else {
            $pizza['ingredients'] = [];
        }
        return $pizza;
    }

    public function getPizzaIngredients($pizza_id)
    {
        $query = "SELECT i.*, pi.is_default, pi.quantity
                  FROM ingredients i
                  JOIN pizza_ingredients pi ON i.ingredient_id = pi.ingredient_id
                  WHERE pi.pizza_id = :pizza_id AND i.is_available = 1
                  ORDER BY i.category, i.name";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pizza_id", $pizza_id, PDO::PARAM_INT);
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
        $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
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
        $stmt->bindValue(':search', "%{$search_term}%");
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

    public function create($data, $staff_id = null)
    {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO " . $this->table_name . " 
                      SET name=:name, description=:description, category_id=:category_id,
                          image_url=:image_url, base_price_small=:base_price_small,
                          base_price_medium=:base_price_medium, base_price_large=:base_price_large,
                          cost_small=:cost_small, cost_medium=:cost_medium, cost_large=:cost_large,
                          prep_time_minutes=:prep_time_minutes, calories_small=:calories_small,
                          calories_medium=:calories_medium, calories_large=:calories_large,
                          is_available=:is_available, is_featured=:is_featured,
                          is_vegan=:is_vegan, is_gluten_free_available=:is_gluten_free_available,
                          allergens=:allergens, popularity_score=:popularity_score,
                          created_at=NOW()";

            $stmt = $this->conn->prepare($query);

            // Sanitize and set defaults
            $name = htmlspecialchars(strip_tags($data['name']));
            $description = htmlspecialchars(strip_tags($data['description']));
            $image_url = htmlspecialchars(strip_tags($data['image'] ?? ''));
            $allergens = htmlspecialchars(strip_tags($data['allergens'] ?? ''));
            $base_price_small = $data['base_price'] ?? 0;
            $base_price_medium = $data['base_price'] * 1.2 ?? 0;
            $base_price_large = $data['base_price'] * 1.5 ?? 0;
            $cost_small = $base_price_small * 0.6;
            $cost_medium = $base_price_medium * 0.6;
            $cost_large = $base_price_large * 0.6;
            $prep_time_minutes = $data['prep_time_minutes'] ?? 15;
            $calories_small = $data['calories_small'] ?? 0;
            $calories_medium = $data['calories_medium'] ?? 0;
            $calories_large = $data['calories_large'] ?? 0;
            $is_available = $data['is_available'] ?? 1;
            $is_featured = $data['is_featured'] ?? 0;
            $is_vegan = $data['is_vegan'] ?? 0;
            $is_gluten_free_available = $data['is_gluten_free_available'] ?? 0;
            $popularity_score = $data['popularity_score'] ?? 0;
            $category_id = $data['category_id'] ?? 1; // Default category

            // Bind parameters
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
            $stmt->bindParam(":image_url", $image_url);
            $stmt->bindParam(":base_price_small", $base_price_small);
            $stmt->bindParam(":base_price_medium", $base_price_medium);
            $stmt->bindParam(":base_price_large", $base_price_large);
            $stmt->bindParam(":cost_small", $cost_small);
            $stmt->bindParam(":cost_medium", $cost_medium);
            $stmt->bindParam(":cost_large", $cost_large);
            $stmt->bindParam(":prep_time_minutes", $prep_time_minutes, PDO::PARAM_INT);
            $stmt->bindParam(":calories_small", $calories_small);
            $stmt->bindParam(":calories_medium", $calories_medium);
            $stmt->bindParam(":calories_large", $calories_large);
            $stmt->bindParam(":is_available", $is_available, PDO::PARAM_BOOL);
            $stmt->bindParam(":is_featured", $is_featured, PDO::PARAM_BOOL);
            $stmt->bindParam(":is_vegan", $is_vegan, PDO::PARAM_BOOL);
            $stmt->bindParam(":is_gluten_free_available", $is_gluten_free_available, PDO::PARAM_BOOL);
            $stmt->bindParam(":allergens", $allergens);
            $stmt->bindParam(":popularity_score", $popularity_score);

            if ($stmt->execute()) {
                $pizza_id = $this->conn->lastInsertId();
                // Add ingredients
                if (!empty($data['ingredients'])) {
                    $this->addPizzaIngredients($pizza_id, $data['ingredients']);
                }
                $this->conn->commit();
                logActivity('pizza_created', "Pizza {$name} created", null, $staff_id);
                return true;
            }

            $this->conn->rollback();
            return false;
        } catch (\Exception $e) {
            $this->conn->rollback();
            error_log("Pizza creation failed: " . $e->getMessage());
            return false;
        }
    }

    public function update($pizza_id, $data, $user_id)
    {
        try {
            $this->conn->beginTransaction();

            $query = "UPDATE " . $this->table_name . " 
                      SET name=:name, description=:description, category_id=:category_id,
                          image_url=:image_url, base_price_small=:base_price_small,
                          base_price_medium=:base_price_medium, base_price_large=:base_price_large,
                          cost_small=:cost_small, cost_medium=:cost_medium, cost_large=:cost_large,
                          prep_time_minutes=:prep_time_minutes, calories_small=:calories_small,
                          calories_medium=:calories_medium, calories_large=:calories_large,
                          is_available=:is_available, is_featured=:is_featured,
                          is_vegan=:is_vegan, is_gluten_free_available=:is_gluten_free_available,
                          allergens=:allergens, popularity_score=:popularity_score,
                          updated_at=NOW()
                      WHERE pizza_id=:pizza_id";

            $stmt = $this->conn->prepare($query);

            // Sanitize and set defaults
            $name = htmlspecialchars(strip_tags($data['name']));
            $description = htmlspecialchars(strip_tags($data['description']));
            $image_url = htmlspecialchars(strip_tags($data['image'] ?? ''));
            $allergens = htmlspecialchars(strip_tags($data['allergens'] ?? ''));
            $base_price_small = $data['base_price'] ?? 0;
            $base_price_medium = $data['base_price'] * 1.2 ?? 0;
            $base_price_large = $data['base_price'] * 1.5 ?? 0;
            $cost_small = $base_price_small * 0.6;
            $cost_medium = $base_price_medium * 0.6;
            $cost_large = $base_price_large * 0.6;
            $prep_time_minutes = $data['prep_time_minutes'] ?? 15;
            $calories_small = $data['calories_small'] ?? 0;
            $calories_medium = $data['calories_medium'] ?? 0;
            $calories_large = $data['calories_large'] ?? 0;
            $is_available = $data['is_available'] ?? 1;
            $is_featured = $data['is_featured'] ?? 0;
            $is_vegan = $data['is_vegan'] ?? 0;
            $is_gluten_free_available = $data['is_gluten_free_available'] ?? 0;
            $popularity_score = $data['popularity_score'] ?? 0;
            $category_id = $data['category_id'] ?? 1; // Default category

            // Bind parameters
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
            $stmt->bindParam(":image_url", $image_url);
            $stmt->bindParam(":base_price_small", $base_price_small);
            $stmt->bindParam(":base_price_medium", $base_price_medium);
            $stmt->bindParam(":base_price_large", $base_price_large);
            $stmt->bindParam(":cost_small", $cost_small);
            $stmt->bindParam(":cost_medium", $cost_medium);
            $stmt->bindParam(":cost_large", $cost_large);
            $stmt->bindParam(":prep_time_minutes", $prep_time_minutes, PDO::PARAM_INT);
            $stmt->bindParam(":calories_small", $calories_small);
            $stmt->bindParam(":calories_medium", $calories_medium);
            $stmt->bindParam(":calories_large", $calories_large);
            $stmt->bindParam(":is_available", $is_available, PDO::PARAM_BOOL);
            $stmt->bindParam(":is_featured", $is_featured, PDO::PARAM_BOOL);
            $stmt->bindParam(":is_vegan", $is_vegan, PDO::PARAM_BOOL);
            $stmt->bindParam(":is_gluten_free_available", $is_gluten_free_available, PDO::PARAM_BOOL);
            $stmt->bindParam(":allergens", $allergens);
            $stmt->bindParam(":popularity_score", $popularity_score);
            $stmt->bindParam(":pizza_id", $pizza_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Update ingredients
                if (isset($data['ingredients'])) {
                    // Clear existing ingredients
                    $this->conn->prepare("DELETE FROM pizza_ingredients WHERE pizza_id = :pizza_id")
                        ->execute([':pizza_id' => $pizza_id]);
                    $this->addPizzaIngredients($pizza_id, $data['ingredients']);
                }
                $this->conn->commit();
                logActivity('pizza_updated', "Pizza ID {$pizza_id} updated", null, $user_id);
                return true;
            }
            $this->conn->rollback();
            return false;
        } catch (\Exception $e) {
            $this->conn->rollback();
            error_log("Pizza update failed: " . $e->getMessage());
            return false;
        }
    }

    private function addPizzaIngredients($pizza_id, $ingredient_ids)
    {
        $query = "INSERT INTO pizza_ingredients (pizza_id, ingredient_id, is_default, quantity) 
                VALUES (:pizza_id, :ingredient_id, 1, 1)";
        $stmt = $this->conn->prepare($query);

        foreach ($ingredient_ids as $id) {
            $stmt->bindValue(':pizza_id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':pizza_id', $pizza_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    public function delete($pizza_id, $user_id)
    {
        try {
            $this->conn->beginTransaction();

            // Delete associated ingredients
            $this->conn->prepare("DELETE FROM pizza_ingredients WHERE pizza_id = :pizza_id")
                ->execute(['pizza_id' => $pizza_id]);

            $query = "DELETE FROM " . $this->table_name . " WHERE pizza_id = :pizza_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':pizza_id', $pizza_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $this->conn->commit();
                logActivity('pizza_deleted', "Pizza ID {$pizza_id} deleted", null, $user_id);
                return true;
            }
            $this->conn->rollback();
            return false;
        } catch (\Exception $e) {
            $this->conn->rollback();
            error_log("Pizza deletion failed: " . $e->getMessage());
            return false;
        }
    }
}

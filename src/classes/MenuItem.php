<?php
require_once BASE_PATH . 'config/database.php';

class MenuItem
{
    private $conn;
    private $table_name = "menu_items";

    public $menu_item_id;
    public $name;
    public $description;
    public $category_id; 
    public $price;
    public $cost;
    public $image_url;
    public $prep_time_minutes;
    public $calories;
    public $is_available;
    public $is_featured;
    public $is_vegan;
    public $is_gluten_free;
    public $allergens;
    public $stock_quantity;
    public $popularity_score;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllMenuItems()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_available = 1 ORDER BY category_id, name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMenuItemById($menu_item_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE menu_item_id = :menu_item_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':menu_item_id', $menu_item_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      SET name=:name, description=:description, price=:price, cost=:cost, 
                          category_id=:category_id, image_url=:image_url, 
                          prep_time_minutes=:prep_time_minutes, calories=:calories, 
                          is_available=:is_available, is_featured=:is_featured, 
                          is_vegan=:is_vegan, is_gluten_free=:is_gluten_free, 
                          allergens=:allergens, stock_quantity=:stock_quantity, 
                          popularity_score=:popularity_score, created_at=NOW()";
            $stmt = $this->conn->prepare($query);

            // Sanitize and bind parameters
            $name = htmlspecialchars(strip_tags($data['name']));
            $description = htmlspecialchars(strip_tags($data['description'] ?? ''));
            $price = floatval($data['price']);
            $cost = floatval($data['cost'] ?? 0.00);
            $category_id = (int)($data['category_id'] ?? 0);
            $image_url = htmlspecialchars(strip_tags($data['image_url'] ?? ''));
            $prep_time_minutes = (int)($data['prep_time_minutes'] ?? 5);
            $calories = (int)($data['calories'] ?? 0);
            $is_available = isset($data['is_available']) ? 1 : 0;
            $is_featured = isset($data['is_featured']) ? 1 : 0;
            $is_vegan = isset($data['is_vegan']) ? 1 : 0;
            $is_gluten_free = isset($data['is_gluten_free']) ? 1 : 0;
            $allergens = htmlspecialchars(strip_tags($data['allergens'] ?? ''));
            $stock_quantity = (int)($data['stock_quantity'] ?? 0);
            $popularity_score = (int)($data['popularity_score'] ?? 0);

            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':cost', $cost);
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->bindParam(':image_url', $image_url);
            $stmt->bindParam(':prep_time_minutes', $prep_time_minutes, PDO::PARAM_INT);
            $stmt->bindParam(':calories', $calories, PDO::PARAM_INT);
            $stmt->bindParam(':is_available', $is_available, PDO::PARAM_INT);
            $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_INT);
            $stmt->bindParam(':is_vegan', $is_vegan, PDO::PARAM_INT);
            $stmt->bindParam(':is_gluten_free', $is_gluten_free, PDO::PARAM_INT);
            $stmt->bindParam(':allergens', $allergens);
            $stmt->bindParam(':stock_quantity', $stock_quantity, PDO::PARAM_INT);
            $stmt->bindParam(':popularity_score', $popularity_score, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("MenuItem creation failed: " . $e->getMessage());
            return false;
        }
    }

    public function update($menu_item_id, $data)
    {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET name=:name, description=:description, price=:price, cost=:cost, 
                          category_id=:category_id, image_url=:image_url, 
                          prep_time_minutes=:prep_time_minutes, calories=:calories, 
                          is_available=:is_available, is_featured=:is_featured, 
                          is_vegan=:is_vegan, is_gluten_free=:is_gluten_free, 
                          allergens=:allergens, stock_quantity=:stock_quantity, 
                          popularity_score=:popularity_score, updated_at=NOW() 
                      WHERE menu_item_id=:menu_item_id";
            $stmt = $this->conn->prepare($query);

            // Sanitize and bind parameters
            $name = htmlspecialchars(strip_tags($data['name']));
            $description = htmlspecialchars(strip_tags($data['description'] ?? ''));
            $price = floatval($data['price']);
            $cost = floatval($data['cost'] ?? 0.00);
            $category_id = (int)($data['category_id'] ?? 0);
            $image_url = htmlspecialchars(strip_tags($data['image_url'] ?? ''));
            $prep_time_minutes = (int)($data['prep_time_minutes'] ?? 5);
            $calories = (int)($data['calories'] ?? 0);
            $is_available = isset($data['is_available']) ? 1 : 0;
            $is_featured = isset($data['is_featured']) ? 1 : 0;
            $is_vegan = isset($data['is_vegan']) ? 1 : 0;
            $is_gluten_free = isset($data['is_gluten_free']) ? 1 : 0;
            $allergens = htmlspecialchars(strip_tags($data['allergens'] ?? ''));
            $stock_quantity = (int)($data['stock_quantity'] ?? 0);
            $popularity_score = (int)($data['popularity_score'] ?? 0);

            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':cost', $cost);
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->bindParam(':image_url', $image_url);
            $stmt->bindParam(':prep_time_minutes', $prep_time_minutes, PDO::PARAM_INT);
            $stmt->bindParam(':calories', $calories, PDO::PARAM_INT);
            $stmt->bindParam(':is_available', $is_available, PDO::PARAM_INT);
            $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_INT);
            $stmt->bindParam(':is_vegan', $is_vegan, PDO::PARAM_INT);
            $stmt->bindParam(':is_gluten_free', $is_gluten_free, PDO::PARAM_INT);
            $stmt->bindParam(':allergens', $allergens);
            $stmt->bindParam(':stock_quantity', $stock_quantity, PDO::PARAM_INT);
            $stmt->bindParam(':popularity_score', $popularity_score, PDO::PARAM_INT);
            $stmt->bindParam(':menu_item_id', $menu_item_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("MenuItem update failed: " . $e->getMessage());
            return false;
        }
    }

    public function delete($menu_item_id)
    {
        try {
            $query = "UPDATE " . $this->table_name . " SET is_available = 0 WHERE menu_item_id = :menu_item_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':menu_item_id', $menu_item_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("MenuItem deletion failed: " . $e->getMessage());
            return false;
        }
    }
}

<?php
require_once BASE_PATH . 'config/database.php';

class Ingredient {
    private $conn;
    private $table_name = "ingredients";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all ingredients
     * @return array Array of ingredients
     */
    public function getAllIngredients() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get ingredient by ID
     * @param int $id Ingredient ID
     * @return array|null Ingredient data or null if not found
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE ingredient_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new ingredient
     * @param array $data Ingredient data (name, price, stock, unit)
     * @return bool True on success, false on failure
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, price, stock, unit, created_at) 
                  VALUES (:name, :price, :stock, :unit, NOW())";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindParam(':stock', $data['stock'], PDO::PARAM_INT);
        $stmt->bindParam(':unit', $data['unit']);

        return $stmt->execute();
    }

    /**
     * Update an existing ingredient
     * @param int $id Ingredient ID
     * @param array $data Ingredient data (name, price, stock, unit)
     * @return bool True on success, false on failure
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, price = :price, stock = :stock, unit = :unit 
                  WHERE ingredient_id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindParam(':stock', $data['stock'], PDO::PARAM_INT);
        $stmt->bindParam(':unit', $data['unit']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete an ingredient
     * @param int $id Ingredient ID
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE ingredient_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
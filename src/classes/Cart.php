<?php

/**
 * Cart Class
 * Handle user-specific cart operations
 * Crust Pizza Online Ordering System
 */

class Cart
{
    private $conn;
    private $table_name = "cart";

    public $cart_id;
    public $user_id;
    public $item_type;
    public $pizza_id;
    public $menu_item_id;
    public $item_name;
    public $size;
    public $quantity;
    public $unit_price;
    public $total_price;
    public $custom_ingredients;
    public $special_instructions;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Add item to cart
     */
    public function addItem()
    {
        // Check if similar item already exists
        $existing_item = $this->findSimilarItem();

        if ($existing_item) {
            // Update quantity of existing item
            return $this->updateQuantity(
                $existing_item['cart_id'],
                $existing_item['quantity'] + $this->quantity
            );
        } else {
            // Add new item
            $query = "INSERT INTO " . $this->table_name . "
                    SET user_id = :user_id,
                        item_type = :item_type,
                        pizza_id = :pizza_id,
                        menu_item_id = :menu_item_id,
                        item_name = :item_name,
                        size = :size,
                        quantity = :quantity,
                        unit_price = :unit_price,
                        total_price = :total_price,
                        custom_ingredients = :custom_ingredients,
                        special_instructions = :special_instructions";

            $stmt = $this->conn->prepare($query);

            // Sanitize
            $this->user_id = htmlspecialchars(strip_tags($this->user_id));
            $this->item_type = htmlspecialchars(strip_tags($this->item_type));
            $this->item_name = htmlspecialchars(strip_tags($this->item_name));
            $this->size = htmlspecialchars(strip_tags($this->size));
            $this->quantity = htmlspecialchars(strip_tags($this->quantity));
            $this->unit_price = htmlspecialchars(strip_tags($this->unit_price));
            $this->total_price = $this->unit_price * $this->quantity;

            // Bind values
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":item_type", $this->item_type);
            $stmt->bindParam(":pizza_id", $this->pizza_id);
            $stmt->bindParam(":menu_item_id", $this->menu_item_id);
            $stmt->bindParam(":item_name", $this->item_name);
            $stmt->bindParam(":size", $this->size);
            $stmt->bindParam(":quantity", $this->quantity);
            $stmt->bindParam(":unit_price", $this->unit_price);
            $stmt->bindParam(":total_price", $this->total_price);
            $stmt->bindParam(":custom_ingredients", $this->custom_ingredients);
            $stmt->bindParam(":special_instructions", $this->special_instructions);

            if ($stmt->execute()) {
                $this->cart_id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        }
    }

    /**
     * Find similar item in cart (same pizza, size, ingredients)
     */
    private function findSimilarItem()
    {
        $query = "SELECT cart_id, quantity FROM " . $this->table_name . "
                WHERE user_id = :user_id 
                AND item_type = :item_type 
                AND item_name = :item_name 
                AND size = :size 
                AND COALESCE(custom_ingredients, '') = COALESCE(:custom_ingredients, '')
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":item_type", $this->item_type);
        $stmt->bindParam(":item_name", $this->item_name);
        $stmt->bindParam(":size", $this->size);
        $stmt->bindParam(":custom_ingredients", $this->custom_ingredients);

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all cart items for a user
     */
    public function getUserCart($user_id)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update item quantity
     */
    public function updateQuantity($cart_id, $quantity)
    {
        if ($quantity <= 0) {
            return $this->removeItem($cart_id);
        }

        $query = "UPDATE " . $this->table_name . "
                SET quantity = :quantity,
                    total_price = unit_price * :quantity,
                    updated_at = CURRENT_TIMESTAMP
                WHERE cart_id = :cart_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->bindParam(":cart_id", $cart_id);

        return $stmt->execute();
    }

    /**
     * Remove item from cart
     */
    public function removeItem($cart_id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE cart_id = :cart_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cart_id", $cart_id);
        return $stmt->execute();
    }

    /**
     * Clear entire cart for user
     */
    public function clearUserCart($user_id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }

    /**
     * Get cart count for user
     */
    public function getCartCount($user_id)
    {
        $query = "SELECT COALESCE(SUM(quantity), 0) as total_items 
                FROM " . $this->table_name . " 
                WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_items'];
    }

    /**
     * Get cart total for user
     */
    public function getCartTotal($user_id)
    {
        $query = "SELECT 
                    COALESCE(SUM(total_price), 0) as subtotal,
                    COALESCE(SUM(quantity), 0) as total_items
                FROM " . $this->table_name . " 
                WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Sync localStorage cart with database (for login)
     */
    public function syncLocalStorageCart($user_id, $localStorage_cart)
    {
        if (empty($localStorage_cart)) {
            return true;
        }

        $success = true;
        foreach ($localStorage_cart as $item) {
            $this->user_id = $user_id;
            $this->item_type = $item['item_type'] ?? 'pizza';
            $this->pizza_id = $item['pizza_id'] ?? null;
            $this->menu_item_id = $item['menu_item_id'] ?? null;
            $this->item_name = $item['name'];
            $this->size = $item['size'] ?? null;
            $this->quantity = $item['quantity'] ?? 1;
            $this->unit_price = $item['price'];
            $this->custom_ingredients = isset($item['customIngredients']) ?
                json_encode($item['customIngredients']) : null;
            $this->special_instructions = $item['specialInstructions'] ?? null;

            if (!$this->addItem()) {
                $success = false;
            }
        }

        return $success;
    }
}

<?php
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
        error_log("Cart class instantiated");
    }

    /**
     * Add item to cart
     */
    public function addItem()
    {
        try {
            // Validate required fields
            if (empty($this->user_id) || empty($this->item_name) || empty($this->quantity) || empty($this->unit_price)) {
                error_log("Invalid cart item data: user_id=" . ($this->user_id ?? 'null') .
                    ", item_name=" . ($this->item_name ?? 'null') .
                    ", quantity=" . ($this->quantity ?? 'null') .
                    ", unit_price=" . ($this->unit_price ?? 'null'));
                return false;
            }

            // Check if similar item exists
            $existing_item = $this->findSimilarItem();
            if ($existing_item) {
                error_log("Updating existing item: cart_id={$existing_item['cart_id']}, new_quantity=" . ($existing_item['quantity'] + $this->quantity));
                return $this->updateQuantity(
                    $existing_item['cart_id'],
                    $existing_item['quantity'] + $this->quantity
                );
            }

            // Add new item
            $query = "INSERT INTO $this->table_name
                (user_id, item_type, pizza_id, menu_item_id, item_name, size, quantity, unit_price, total_price, custom_ingredients, special_instructions)
                VALUES (:user_id, :item_type, :pizza_id, :menu_item_id, :item_name, :size, :quantity, :unit_price, :total_price, :custom_ingredients, :special_instructions)";

            $stmt = $this->conn->prepare($query);

            // Sanitize inputs
            $this->user_id = filter_var($this->user_id, FILTER_VALIDATE_INT);
            $this->item_type = htmlspecialchars(strip_tags($this->item_type ?? 'pizza'));
            $this->pizza_id = $this->pizza_id ? filter_var($this->pizza_id, FILTER_VALIDATE_INT) : null;
            $this->menu_item_id = $this->menu_item_id ? filter_var($this->menu_item_id, FILTER_VALIDATE_INT) : null;
            $this->item_name = htmlspecialchars(strip_tags($this->item_name ?? 'Custom Pizza'));
            $this->size = htmlspecialchars(strip_tags($this->size ?? 'medium'));
            $this->quantity = (int)($this->quantity ?? 1);
            $this->unit_price = (float)($this->unit_price ?? 0.0);
            $this->total_price = $this->unit_price * $this->quantity;
            $this->custom_ingredients = $this->custom_ingredients ? json_encode(json_decode($this->custom_ingredients, true)) : null;
            $this->special_instructions = $this->special_instructions ? htmlspecialchars(strip_tags($this->special_instructions)) : null;

            // Validate numeric fields
            if (
                $this->user_id === false || ($this->pizza_id !== null && $this->pizza_id === false) ||
                ($this->menu_item_id !== null && $this->menu_item_id === false)
            ) {
                error_log("Invalid numeric input: user_id=" . ($this->user_id ?? 'null') .
                    ", pizza_id=" . ($this->pizza_id ?? 'null') .
                    ", menu_item_id=" . ($this->menu_item_id ?? 'null'));
                return false;
            }

            // Bind values
            $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
            $stmt->bindParam(":item_type", $this->item_type);
            $stmt->bindParam(":pizza_id", $this->pizza_id, PDO::PARAM_INT);
            $stmt->bindParam(":menu_item_id", $this->menu_item_id, PDO::PARAM_INT);
            $stmt->bindParam(":item_name", $this->item_name);
            $stmt->bindParam(":size", $this->size);
            $stmt->bindParam(":quantity", $this->quantity, PDO::PARAM_INT);
            $stmt->bindParam(":unit_price", $this->unit_price);
            $stmt->bindParam(":total_price", $this->total_price);
            $stmt->bindParam(":custom_ingredients", $this->custom_ingredients);
            $stmt->bindParam(":special_instructions", $this->special_instructions);

            if ($stmt->execute()) {
                $this->cart_id = $this->conn->lastInsertId();
                error_log("Added new item: cart_id=$this->cart_id, user_id=$this->user_id, item_name=$this->item_name");
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Failed to execute INSERT query: SQLSTATE={$errorInfo[0]}, Error={$errorInfo[2]}");
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error adding item: SQLSTATE={$e->getCode()}, Message={$e->getMessage()}");
            return false;
        } catch (Exception $e) {
            error_log("Unexpected error in addItem: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find similar item in cart
     */
    private function findSimilarItem()
    {
        try {
            $query = "SELECT cart_id, quantity FROM $this->table_name
                WHERE user_id = :user_id
                AND item_type = :item_type
                AND item_name = :item_name
                AND size = :size
                AND COALESCE(custom_ingredients, '') = COALESCE(:custom_ingredients, '')
                LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
            $stmt->bindParam(":item_type", $this->item_type);
            $stmt->bindParam(":item_name", $this->item_name);
            $stmt->bindParam(":size", $this->size);
            $stmt->bindParam(":custom_ingredients", $this->custom_ingredients);

            if ($stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("findSimilarItem result: " . ($result ? print_r($result, true) : "No matching item"));
                return $result;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Failed to execute findSimilarItem query: SQLSTATE={$errorInfo[0]}, Error={$errorInfo[2]}");
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error finding similar item: SQLSTATE={$e->getCode()}, Message={$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get all cart items for a user
     */
    public function getUserCart($user_id)
    {
        try {
            $query = "SELECT cart_id, user_id, item_type, pizza_id, menu_item_id, item_name,
                       size, quantity, unit_price, total_price, custom_ingredients,
                       special_instructions, created_at, updated_at
                FROM $this->table_name
                WHERE user_id = :user_id
                ORDER BY created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("Fetched " . count($items) . " cart items for user_id=$user_id");
                return $items;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Failed to execute getUserCart query: SQLSTATE={$errorInfo[0]}, Error={$errorInfo[2]}");
                return [];
            }
        } catch (PDOException $e) {
            error_log("Error fetching cart items: SQLSTATE={$e->getCode()}, Message={$e->getMessage()}");
            return [];
        }
    }

    /**
     * Update item quantity
     */
    public function updateQuantity($cart_id, $quantity)
    {
        try {
            if ($quantity <= 0) {
                return $this->removeItem($cart_id);
            }

            $query = "UPDATE $this->table_name
                SET quantity = :quantity,
                    total_price = unit_price * :quantity,
                    updated_at = CURRENT_TIMESTAMP
                WHERE cart_id = :cart_id AND user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
            $stmt->bindParam(":cart_id", $cart_id, PDO::PARAM_INT);
            $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                error_log("Updated quantity: cart_id=$cart_id, quantity=$quantity");
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Failed to execute updateQuantity query: SQLSTATE={$errorInfo[0]}, Error={$errorInfo[2]}");
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error updating quantity: SQLSTATE={$e->getCode()}, Message={$e->getMessage()}");
            return false;
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem($cart_id)
    {
        try {
            $query = "DELETE FROM $this->table_name WHERE cart_id = :cart_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cart_id", $cart_id, PDO::PARAM_INT);
            $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                error_log("Removed item: cart_id=$cart_id");
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Failed to execute removeItem query: SQLSTATE={$errorInfo[0]}, Error={$errorInfo[2]}");
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error removing item: SQLSTATE={$e->getCode()}, Message={$e->getMessage()}");
            return false;
        }
    }

    /**
     * Clear entire cart for user
     */
    public function clearUserCart($user_id)
    {
        try {
            $query = "DELETE FROM $this->table_name WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                error_log("Cleared cart: user_id=$user_id");
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Failed to execute clearUserCart query: SQLSTATE={$errorInfo[0]}, Error={$errorInfo[2]}");
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error clearing cart: SQLSTATE={$e->getCode()}, Message={$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get cart count for user
     */
    public function getCartCount($user_id)
    {
        try {
            $query = "SELECT COALESCE(SUM(quantity), 0) as total_items
                FROM $this->table_name
                WHERE user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Cart count for user_id=$user_id: {$result['total_items']}");
                return (int)$result['total_items'];
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Failed to execute getCartCount query: SQLSTATE={$errorInfo[0]}, Error={$errorInfo[2]}");
                return 0;
            }
        } catch (PDOException $e) {
            error_log("Error getting cart count: SQLSTATE={$e->getCode()}, Message={$e->getMessage()}");
            return 0;
        }
    }

    /**
     * Get cart total for user
     */
    public function getCartTotal($user_id)
    {
        try {
            $query = "SELECT
                    COALESCE(SUM(total_price), 0) as subtotal,
                    COALESCE(SUM(quantity), 0) as total_items
                FROM $this->table_name
                WHERE user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Cart total for user_id=$user_id: subtotal={$result['subtotal']}, items={$result['total_items']}");
                return $result;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Failed to execute getCartTotal query: SQLSTATE={$errorInfo[0]}, Error={$errorInfo[2]}");
                return ['subtotal' => 0, 'total_items' => 0];
            }
        } catch (PDOException $e) {
            error_log("Error getting cart total: SQLSTATE={$e->getCode()}, Message={$e->getMessage()}");
            return ['subtotal' => 0, 'total_items' => 0];
        }
    }

    /**
     * Sync localStorage cart with database
     */
    public function syncLocalStorageCart($user_id, $localStorage_cart)
    {
        try {
            if (empty($localStorage_cart)) {
                error_log("No local cart items to sync for user_id=$user_id");
                return true;
            }

            $success = true;
            foreach ($localStorage_cart as $index => $item) {
                // Validate item
                if (!isset($item['name']) || !isset($item['quantity']) || !isset($item['price'])) {
                    error_log("Invalid local cart item at index $index for user_id=$user_id: " . print_r($item, true));
                    $success = false;
                    continue;
                }

                $this->user_id = $user_id;
                $this->item_type = $item['item_type'] ?? 'pizza';
                $this->pizza_id = isset($item['pizza_id']) ? filter_var($item['pizza_id'], FILTER_VALIDATE_INT) : null;
                $this->menu_item_id = isset($item['menu_item_id']) ? filter_var($item['menu_item_id'], FILTER_VALIDATE_INT) : null;
                $this->item_name = htmlspecialchars(strip_tags($item['name'] ?? 'Custom Pizza'));
                $this->size = htmlspecialchars(strip_tags($item['size'] ?? 'medium'));
                $this->quantity = (int)($item['quantity'] ?? 1);
                $this->unit_price = (float)($item['price'] ?? 10.0);
                $this->custom_ingredients = isset($item['custom_ingredients']) ? json_encode($item['custom_ingredients']) : null;
                $this->special_instructions = isset($item['special_instructions']) ? htmlspecialchars(strip_tags($item['special_instructions'])) : null;

                error_log("Syncing item for user_id=$user_id: " . print_r(get_object_vars($this), true));

                if (!$this->addItem()) {
                    $success = false;
                    error_log("Failed to sync item at index $index for user_id=$user_id: " . print_r($item, true));
                }
            }

            error_log("Cart sync completed for user_id=$user_id, success=" . ($success ? 'true' : 'false'));
            return $success;
        } catch (Exception $e) {
            error_log("Error syncing cart for user_id=$user_id: SQLSTATE={$e->getCode()}, Message={$e->getMessage()}");
            return false;
        }
    }
}

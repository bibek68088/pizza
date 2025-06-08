<?php
require_once BASE_PATH . 'config/database.php';

class Order
{
    private $conn;
    private $table_name = "orders";

    public $order_id;
    public $order_number;
    public $user_id;
    public $store_id;
    public $order_type;
    public $status;
    public $priority;
    public $subtotal;
    public $tax;
    public $delivery_fee;
    public $discount_amount;
    public $total;
    public $payment_method;
    public $payment_status;
    public $payment_reference;
    public $customer_name;
    public $customer_phone;
    public $customer_email;
    public $delivery_address;
    public $delivery_instructions;
    public $estimated_prep_time;
    public $estimated_delivery_time;
    public $actual_delivery_time;
    public $assigned_staff_id;
    public $special_requests;
    public $rating;
    public $review;

    public function __construct($db = null)
    {
        $this->conn = $db ?: Database::getInstance()->getConnection();
    }

    // Create new order
    public function create()
    {
        try {
            $this->conn->beginTransaction();
            $this->order_number = $this->generateOrderNumber();

            $query = "INSERT INTO " . $this->table_name . " 
                  SET order_number=:order_number, user_id=:user_id, store_id=:store_id, 
                      order_type=:order_type, priority=:priority, subtotal=:subtotal, 
                      tax=:tax, delivery_fee=:delivery_fee, discount_amount=:discount_amount, 
                      total=:total, payment_method=:payment_method, customer_name=:customer_name,
                      customer_phone=:customer_phone, customer_email=:customer_email,
                      delivery_address=:delivery_address, delivery_instructions=:delivery_instructions,
                      estimated_prep_time=:estimated_prep_time, special_requests=:special_requests,
                      payment_reference=:payment_reference";

            $stmt = $this->conn->prepare($query);

            // Sanitize inputs
            $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
            $this->customer_phone = htmlspecialchars(strip_tags($this->customer_phone));
            $this->customer_email = htmlspecialchars(strip_tags($this->customer_email ?? ''));
            $this->delivery_address = htmlspecialchars(strip_tags($this->delivery_address ?? ''));
            $this->delivery_instructions = htmlspecialchars(strip_tags($this->delivery_instructions ?? ''));
            $this->special_requests = htmlspecialchars(strip_tags($this->special_requests ?? ''));
            $this->payment_reference = htmlspecialchars(strip_tags($this->payment_reference ?? ''));

            // Bind parameters
            $stmt->bindParam(":order_number", $this->order_number);
            $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
            $stmt->bindParam(":store_id", $this->store_id, PDO::PARAM_INT);
            $stmt->bindParam(":order_type", $this->order_type);
            $stmt->bindParam(":priority", $this->priority);
            $stmt->bindParam(":subtotal", $this->subtotal);
            $stmt->bindParam(":tax", $this->tax);
            $stmt->bindParam(":delivery_fee", $this->delivery_fee);
            $stmt->bindParam(":discount_amount", $this->discount_amount);
            $stmt->bindParam(":total", $this->total);
            $stmt->bindParam(":payment_method", $this->payment_method);
            $stmt->bindParam(":customer_name", $this->customer_name);
            $stmt->bindParam(":customer_phone", $this->customer_phone);
            $stmt->bindParam(":customer_email", $this->customer_email);
            $stmt->bindParam(":delivery_address", $this->delivery_address);
            $stmt->bindParam(":delivery_instructions", $this->delivery_instructions);
            $stmt->bindParam(":estimated_prep_time", $this->estimated_prep_time, PDO::PARAM_INT);
            $stmt->bindParam(":special_requests", $this->special_requests);
            $stmt->bindParam(":payment_reference", $this->payment_reference);

            if ($stmt->execute()) {
                $this->order_id = $this->conn->lastInsertId();

                // Add initial status to history
                $this->addStatusHistory('pending', null, 'Order created');

                $this->conn->commit();
                logActivity('order_created', "Order {$this->order_number} created", $this->user_id);
                return true;
            }

            $this->conn->rollback();
            return false;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Order creation failed: " . $e->getMessage());
            return false;
        }
    }
    // Generate unique order number (placeholder implementation)
    private function generateOrderNumber()
    {
        // This should be implemented based on your requirements
        // Example: Combine timestamp and random string
        return 'ORD' . date('YmdHis') . substr(uniqid(), -4);
    }

    // Get order by ID
    public function getOrderById($order_id)
    {
        $query = "SELECT o.*, s.name as store_name, s.address as store_address, s.phone as store_phone,
                         u.full_name as assigned_staff_name
                  FROM " . $this->table_name . " o
                  LEFT JOIN stores s ON o.store_id = s.store_id
                  LEFT JOIN users u ON o.assigned_staff_id = u.user_id
                  WHERE o.order_id = :order_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get order by order number
    public function getOrderByNumber($order_number)
    {
        $query = "SELECT o.*, s.name as store_name, s.address as store_address, s.phone as store_phone
                  FROM " . $this->table_name . " o
                  LEFT JOIN stores s ON o.store_id = s.store_id
                  WHERE o.order_number = :order_number
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_number", $order_number);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get orders by user ID
    public function getOrdersByUserId($user_id, $limit = 20)
    {
        $query = "SELECT o.*, s.name as store_name
                  FROM " . $this->table_name . " o
                  LEFT JOIN stores s ON o.store_id = s.store_id
                  WHERE o.user_id = :user_id
                  ORDER BY o.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get orders by status with filtering
    public function getOrdersByStatus($status, $store_id = null, $conditions = [], $limit = 50)
    {
        try {
            $whereConditions = ['o.status = :status'];
            $params = [':status' => $status];

            if ($store_id !== null) {
                $whereConditions[] = 'o.store_id = :store_id';
                $params[':store_id'] = $store_id;
            }

            // Add additional conditions
            if (!empty($conditions)) {
                foreach ($conditions as $key => $value) {
                    $whereConditions[] = "o.$key = :$key";
                    $params[":$key"] = $value;
                }
            }

            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

            $query = "SELECT o.*, s.name as store_name, u.full_name as assigned_staff_name
                  FROM orders o
                  LEFT JOIN stores s ON o.store_id = s.store_id
                  LEFT JOIN users u ON o.assigned_staff_id = u.user_id
                  $whereClause
                  ORDER BY o.priority DESC, o.created_at ASC
                  LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug: Log query and results
            error_log("Query: $query");
            error_log("Params: " . print_r($params, true));
            error_log("Results: " . print_r($results, true));

            return $results;
        } catch (PDOException $e) {
            error_log("Database Error in getOrdersByStatus: " . $e->getMessage());
            return [];
        }
    }


    // Get all orders with advanced filtering
    public function getAllOrders($page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;

        $query = "SELECT o.*, u.full_name as customer_name
                  FROM " . $this->table_name . " o
                  LEFT JOIN users u ON o.user_id = u.user_id
                  ORDER BY o.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $countStmt = $this->conn->prepare($countQuery);
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'orders' => $orders,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }

    // Update order status
    public function updateStatus($order_id, $new_status, $staff_id = null, $notes = '')
    {
        try {
            $this->conn->beginTransaction();

            // Validate status against allowed ENUM values
            $validStatuses = [
                'pending',
                'confirmed',
                'preparing',
                'prepared',
                'out_for_delivery',
                'ready_for_pickup',
                'delivered',
                'delivery_failure',
                'received_by_customer',
                'cancelled'
            ];
            if (!in_array($new_status, $validStatuses)) {
                throw new Exception("Invalid status: $new_status");
            }

            // Update order status
            $query = "UPDATE " . $this->table_name . " 
                      SET status = :status, updated_at = NOW()";

            // Update assigned staff if provided
            if ($staff_id) {
                $query .= ", assigned_staff_id = :staff_id";
            }

            // Update delivery time for delivered or received_by_customer statuses
            if (in_array($new_status, ['delivered', 'received_by_customer'])) {
                $query .= ", actual_delivery_time = NOW()";
            }

            $query .= " WHERE order_id = :order_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":status", $new_status);
            $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
            if ($staff_id) {
                $stmt->bindParam(":staff_id", $staff_id, PDO::PARAM_INT);
            }
            $stmt->execute();

            // Add to status history
            $this->addStatusHistory($new_status, $staff_id, $notes, $order_id);

            $this->conn->commit();
            logActivity('order_status_updated', "Order status updated to $new_status", null, $staff_id);
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Order status update failed: " . $e->getMessage());
            return false;
        }
    }

    // Add status history entry
    private function addStatusHistory($status, $staff_id, $notes, $order_id = null)
    {
        $order_id = $order_id ?: $this->order_id;

        $query = "INSERT INTO order_status_history 
                  SET order_id = :order_id, status = :status, 
                      changed_by = :changed_by, notes = :notes";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":changed_by", $staff_id, PDO::PARAM_INT);
        $stmt->bindParam(":notes", $notes);
        $stmt->execute();
    }

    // Get order status history
    public function getOrderStatusHistory($order_id)
    {
        $query = "SELECT osh.*, u.full_name as staff_name
                  FROM order_status_history osh
                  LEFT JOIN users u ON osh.changed_by = u.user_id
                  WHERE osh.order_id = :order_id
                  ORDER BY osh.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get order items
    public function getOrderItems($order_id)
    {
        $query = "SELECT oi.*, p.name as pizza_name, p.base_price_small, p.base_price_medium, p.base_price_large, 
                         mi.name as menu_item_name, mi.price as menu_item_price
                  FROM order_items oi
                  LEFT JOIN pizzas p ON oi.pizza_id = p.pizza_id
                  LEFT JOIN menu_items mi ON oi.menu_item_id = mi.menu_item_id
                  WHERE oi.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($order_id, $data)
    {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET status=:status, delivery_address=:delivery_address, 
                          special_requests=:special_requests, updated_at=NOW() 
                      WHERE order_id=:order_id";
            $stmt = $this->conn->prepare($query);

            $status = htmlspecialchars(strip_tags($data['status']));
            $delivery_address = htmlspecialchars(strip_tags($data['delivery_address']));
            $special_requests = htmlspecialchars(strip_tags($data['special_requests']));

            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':delivery_address', $delivery_address);
            $stmt->bindParam(':special_requests', $special_requests);
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Order update failed: " . $e->getMessage());
            return false;
        }
    }

    public function delete($order_id)
    {
        try {
            $this->conn->beginTransaction();

            // Delete order items
            $this->conn->prepare("DELETE FROM order_items WHERE order_id = :order_id")
                ->execute(['order_id' => $order_id]);

            // Delete order
            $query = "DELETE FROM " . $this->table_name . " WHERE order_id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':order_id', $order_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $this->conn->commit();
                return true;
            }
            $this->conn->rollback();
            return false;
        } catch (\Exception $e) {
            $this->conn->rollback();
            error_log("Order deletion failed: " . $e->getMessage());
            return false;
        }
    }

    // Add item to order
    public function addOrderItem($item_data)
    {
        $query = "INSERT INTO order_items 
                  SET order_id=:order_id, item_type=:item_type, pizza_id=:pizza_id,
                      menu_item_id=:menu_item_id, size=:size, quantity=:quantity,
                      unit_price=:unit_price, total_price=:total_price,
                      special_instructions=:special_instructions";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":order_id", $item_data['order_id'], PDO::PARAM_INT);
        $stmt->bindParam(":item_type", $item_data['item_type']);
        $stmt->bindParam(":pizza_id", $item_data['pizza_id'], PDO::PARAM_INT);
        $stmt->bindParam(":menu_item_id", $item_data['menu_item_id'], PDO::PARAM_INT);
        $stmt->bindParam(":size", $item_data['size']);
        $stmt->bindParam(":quantity", $item_data['quantity'], PDO::PARAM_INT);
        $stmt->bindParam(":unit_price", $item_data['unit_price']);
        $stmt->bindParam(":total_price", $item_data['total_price']);
        $stmt->bindParam(":special_instructions", $item_data['special_instructions']);

        if ($stmt->execute()) {
            $order_item_id = $this->conn->lastInsertId();

            // Add custom ingredients if any
            if (!empty($item_data['custom_ingredients'])) {
                $this->addOrderItemIngredients($order_item_id, $item_data['custom_ingredients']);
            }

            return $order_item_id;
        }

        return false;
    }

    // Add custom ingredients to order item
    private function addOrderItemIngredients($order_item_id, $ingredients)
    {
        $query = "INSERT INTO order_item_ingredients 
                  SET order_item_id=:order_item_id, ingredient_id=:ingredient_id, 
                      quantity=:quantity, price=:price";

        $stmt = $this->conn->prepare($query);

        foreach ($ingredients as $ingredient) {
            $stmt->bindParam(":order_item_id", $order_item_id, PDO::PARAM_INT);
            $stmt->bindParam(":ingredient_id", $ingredient['ingredient_id'], PDO::PARAM_INT);
            $stmt->bindParam(":quantity", $ingredient['quantity']);
            $stmt->bindParam(":price", $ingredient['price']);
            $stmt->execute();
        }
    }

    // Get order statistics
    public function getOrderStats($store_id = null, $days = 30)
    {
        $whereConditions = ["o.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)"];
        $params = [':days' => $days];

        if ($store_id !== null) {
            $whereConditions[] = "o.store_id = :store_id";
            $params[':store_id'] = $store_id;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

        $query = "SELECT 
                    COUNT(*) as total_orders,
                    COUNT(CASE WHEN o.status != 'cancelled' THEN 1 END) as completed_orders,
                    COUNT(CASE WHEN o.status = 'cancelled' THEN 1 END) as cancelled_orders,
                    COALESCE(SUM(CASE WHEN o.status != 'cancelled' THEN o.total ELSE 0 END), 0) as total_revenue,
                    COALESCE(AVG(CASE WHEN o.status != 'cancelled' THEN o.total ELSE NULL END), 0) as average_order_value,
                    COUNT(CASE WHEN o.status = 'pending' THEN 1 END) as pending_orders,
                    COUNT(CASE WHEN o.status = 'preparing' THEN 1 END) as preparing_orders,
                    COUNT(CASE WHEN o.status = 'prepared' THEN 1 END) as prepared_orders,
                    COUNT(CASE WHEN o.status = 'out_for_delivery' THEN 1 END) as out_for_delivery_orders,
                    COUNT(CASE WHEN o.status = 'ready_for_pickup' THEN 1 END) as ready_for_pickup_orders
                  FROM " . $this->table_name . " o " . $whereClause;

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get recent orders
    public function getRecentOrders($limit = 10, $store_id = null)
    {
        $whereConditions = [];
        $params = [];

        if ($store_id !== null) {
            $whereConditions[] = "o.store_id = :store_id";
            $params[':store_id'] = $store_id;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $query = "SELECT o.*, s.name as store_name
                  FROM " . $this->table_name . " o
                  LEFT JOIN stores s ON o.store_id = s.store_id
                  $whereClause
                  ORDER BY o.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update order priority
    public function updatePriority($order_id, $priority)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET priority = :priority, updated_at = NOW() 
                  WHERE order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":priority", $priority);
        $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            logActivity('order_priority_updated', "Order priority updated to $priority", null, $this->getCurrentStaffId());
            return true;
        }
        return false;
    }

    // Assign staff to order
    public function assignStaff($order_id, $staff_id)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET assigned_staff_id = :staff_id, updated_at = NOW() 
                  WHERE order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":staff_id", $staff_id, PDO::PARAM_INT);
        $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            logActivity('order_staff_assigned', "Staff assigned to order", null, $staff_id);
            return true;
        }
        return false;
    }

    // Add order review and rating
    public function addReview($order_id, $rating, $review)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET rating = :rating, review = :review, updated_at = NOW() 
                  WHERE order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rating", $rating, PDO::PARAM_INT);
        $stmt->bindParam(":review", htmlspecialchars(strip_tags($review)));
        $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            logActivity('order_reviewed', "Order reviewed with rating $rating");
            return true;
        }
        return false;
    }

    // Get daily sales report
    public function getDailySalesReport($date, $store_id = null)
    {
        $whereConditions = ["DATE(o.created_at) = :date", "o.status != 'cancelled'"];
        $params = [':date' => $date];

        if ($store_id !== null) {
            $whereConditions[] = "o.store_id = :store_id";
            $params[':store_id'] = $store_id;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

        $query = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(o.total) as total_revenue,
                    AVG(o.total) as average_order_value,
                    COUNT(CASE WHEN o.order_type = 'delivery' THEN 1 END) as delivery_orders,
                    COUNT(CASE WHEN o.order_type = 'pickup' THEN 1 END) as pickup_orders,
                    SUM(CASE WHEN o.order_type = 'delivery' THEN o.delivery_fee ELSE 0 END) as delivery_fees
                  FROM " . $this->table_name . " o " . $whereClause;

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cancel order
    public function cancelOrder($order_id, $reason = '', $staff_id = null)
    {
        try {
            $this->conn->beginTransaction();

            // Update order status
            $query = "UPDATE " . $this->table_name . " 
                      SET status = 'cancelled', updated_at = NOW() 
                      WHERE order_id = :order_id AND status IN ('pending', 'confirmed')";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                // Add to status history
                $this->addStatusHistory('cancelled', $staff_id, $reason, $order_id);

                $this->conn->commit();
                logActivity('order_cancelled', "Order cancelled: $reason", null, $staff_id);
                return true;
            }

            $this->conn->rollback();
            return false;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Order cancellation failed: " . $e->getMessage());
            return false;
        }
    }

    private function getCurrentStaffId()
    {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
}

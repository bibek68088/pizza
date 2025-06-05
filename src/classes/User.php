<?php
require_once 'config/database.php';

class User
{
    private $conn;
    private $table_name = "users";

    public $user_id;
    public $username;
    public $email;
    public $password_hash;
    public $full_name;
    public $phone;
    public $address;
    public $date_of_birth;
    public $is_active;
    public $email_verified;
    public $created_at;
    public $updated_at;

    public function __construct($db = null)
    {
        $this->conn = $db ?: Database::getInstance()->getConnection();
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, email=:email, password_hash=:password_hash, 
                      full_name=:full_name, phone=:phone, address=:address, 
                      date_of_birth=:date_of_birth";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));

        // Bind parameters
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":date_of_birth", $this->date_of_birth);

        if ($stmt->execute()) {
            $this->user_id = $this->conn->lastInsertId();
            logActivity('user_created', "User {$this->username} created", $this->user_id);
            return true;
        }
        return false;
    }

    public function login($username, $password)
    {
        $query = "SELECT user_id, username, email, password_hash, full_name, phone, address, 
                     date_of_birth, is_active, email_verified
              FROM " . $this->table_name . " 
              WHERE (username = ? OR email = ?) AND is_active = 1
              LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username, $username]);

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $row['password_hash'])) {
                $this->user_id = $row['user_id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->full_name = $row['full_name'];
                $this->phone = $row['phone'];
                $this->address = $row['address'];
                $this->date_of_birth = $row['date_of_birth'];
                $this->is_active = $row['is_active'];
                $this->email_verified = $row['email_verified'];

                logActivity('user_login', "User {$this->username} logged in", $this->user_id);
                return true;
            }
        }
        return false;
    }

    public function usernameExists($username, $excludeUserId = null)
    {
        $query = "SELECT user_id FROM " . $this->table_name . " WHERE username = :username";
        if ($excludeUserId) {
            $query .= " AND user_id != :exclude_id";
        }
        $query .= " LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        if ($excludeUserId) {
            $stmt->bindParam(":exclude_id", $excludeUserId);
        }
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function emailExists($email, $excludeUserId = null)
    {
        $query = "SELECT user_id FROM " . $this->table_name . " WHERE email = :email";
        if ($excludeUserId) {
            $query .= " AND user_id != :exclude_id";
        }
        $query .= " LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        if ($excludeUserId) {
            $stmt->bindParam(":exclude_id", $excludeUserId);
        }
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getUserById($user_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->user_id = $row['user_id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->full_name = $row['full_name'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->date_of_birth = $row['date_of_birth'];
            $this->is_active = $row['is_active'];
            $this->email_verified = $row['email_verified'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET full_name=:full_name, phone=:phone, address=:address, 
                      date_of_birth=:date_of_birth, updated_at=NOW()
                  WHERE user_id=:user_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));

        // Bind parameters
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":date_of_birth", $this->date_of_birth);
        $stmt->bindParam(":user_id", $this->user_id);

        if ($stmt->execute()) {
            logActivity('user_updated', "User profile updated", $this->user_id);
            return true;
        }
        return false;
    }

    public function changePassword($newPassword)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET password_hash=:password_hash, updated_at=NOW()
                  WHERE user_id=:user_id";

        $stmt = $this->conn->prepare($query);
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt->bindParam(":password_hash", $passwordHash);
        $stmt->bindParam(":user_id", $this->user_id);

        if ($stmt->execute()) {
            logActivity('password_changed', "Password changed", $this->user_id);
            return true;
        }
        return false;
    }

    public function getAllUsers($page = 1, $limit = 20, $search = '', $status = 'all')
    {
        $offset = ($page - 1) * $limit;

        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(username LIKE :search OR email LIKE :search OR full_name LIKE :search OR phone LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if ($status !== 'all') {
            $whereConditions[] = "is_active = :status";
            $params[':status'] = $status === 'active' ? 1 : 0;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM " . $this->table_name . " " . $whereClause;
        $countStmt = $this->conn->prepare($countQuery);
        $countStmt->execute($params);
        $totalUsers = $countStmt->fetch()['total'];

        // Get users
        $query = "SELECT u.*, 
                         COUNT(o.order_id) as total_orders,
                         COALESCE(SUM(o.total), 0) as total_spent,
                         MAX(o.created_at) as last_order_date
                  FROM " . $this->table_name . " u
                  LEFT JOIN orders o ON u.user_id = o.user_id
                  " . $whereClause . "
                  GROUP BY u.user_id
                  ORDER BY u.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'users' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $totalUsers,
            'pages' => ceil($totalUsers / $limit),
            'current_page' => $page
        ];
    }

    public function deactivate($user_id)
    {
        $query = "UPDATE " . $this->table_name . " SET is_active = 0, updated_at = NOW() WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);

        if ($stmt->execute()) {
            logActivity('user_deactivated', "User deactivated", $user_id);
            return true;
        }
        return false;
    }

    public function reactivate($user_id)
    {
        $query = "UPDATE " . $this->table_name . " SET is_active = 1, updated_at = NOW() WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);

        if ($stmt->execute()) {
            logActivity('user_reactivated', "User reactivated", $user_id);
            return true;
        }
        return false;
    }

    public function getUserAddresses($user_id)
    {
        $query = "SELECT * FROM user_addresses WHERE user_id = :user_id ORDER BY is_default DESC, created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addAddress($addressData)
    {
        $query = "INSERT INTO user_addresses 
                  SET user_id=:user_id, address_type=:address_type, address_line_1=:address_line_1,
                      address_line_2=:address_line_2, suburb=:suburb, state=:state, postcode=:postcode,
                      country=:country, is_default=:is_default, delivery_instructions=:delivery_instructions";

        $stmt = $this->conn->prepare($query);

        // If this is set as default, unset other defaults
        if ($addressData['is_default']) {
            $this->conn->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = :user_id")->execute([':user_id' => $addressData['user_id']]);
        }

        $stmt->bindParam(":user_id", $addressData['user_id']);
        $stmt->bindParam(":address_type", $addressData['address_type']);
        $stmt->bindParam(":address_line_1", $addressData['address_line_1']);
        $stmt->bindParam(":address_line_2", $addressData['address_line_2']);
        $stmt->bindParam(":suburb", $addressData['suburb']);
        $stmt->bindParam(":state", $addressData['state']);
        $stmt->bindParam(":postcode", $addressData['postcode']);
        $stmt->bindParam(":country", $addressData['country']);
        $stmt->bindParam(":is_default", $addressData['is_default']);
        $stmt->bindParam(":delivery_instructions", $addressData['delivery_instructions']);

        return $stmt->execute();
    }

    public function getUserFavorites($user_id)
    {
        $query = "SELECT uf.*, 
                         p.name as pizza_name, p.description as pizza_description,
                         mi.name as menu_item_name, mi.description as menu_item_description
                  FROM user_favorites uf
                  LEFT JOIN pizzas p ON uf.pizza_id = p.pizza_id
                  LEFT JOIN menu_items mi ON uf.menu_item_id = mi.menu_item_id
                  WHERE uf.user_id = :user_id
                  ORDER BY uf.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addToFavorites($user_id, $item_type, $item_id, $size = null, $custom_ingredients = null)
    {
        $query = "INSERT INTO user_favorites 
                  SET user_id=:user_id, item_type=:item_type, " .
            ($item_type === 'pizza' ? 'pizza_id' : 'menu_item_id') . "=:item_id, 
                      size=:size, custom_ingredients=:custom_ingredients";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":item_type", $item_type);
        $stmt->bindParam(":item_id", $item_id);
        $stmt->bindParam(":size", $size);
        $stmt->bindParam(":custom_ingredients", json_encode($custom_ingredients));

        return $stmt->execute();
    }

    public function getLoyaltyPoints($user_id)
    {
        $query = "SELECT 
                    COALESCE(SUM(CASE WHEN transaction_type = 'earned' THEN points_earned ELSE 0 END), 0) as total_earned,
                    COALESCE(SUM(CASE WHEN transaction_type = 'redeemed' THEN points_redeemed ELSE 0 END), 0) as total_redeemed,
                    COALESCE(SUM(CASE WHEN transaction_type = 'earned' THEN points_earned ELSE 0 END), 0) - 
                    COALESCE(SUM(CASE WHEN transaction_type = 'redeemed' THEN points_redeemed ELSE 0 END), 0) as current_balance
                  FROM loyalty_points 
                  WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addLoyaltyPoints($user_id, $points, $order_id = null, $description = '')
    {
        $query = "INSERT INTO loyalty_points 
                  SET user_id=:user_id, order_id=:order_id, points_earned=:points, 
                      transaction_type='earned', description=:description";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":order_id", $order_id);
        $stmt->bindParam(":points", $points);
        $stmt->bindParam(":description", $description);

        return $stmt->execute();
    }

    public function getUserStats($user_id)
    {
        $query = "SELECT 
                    COUNT(o.order_id) as total_orders,
                    COALESCE(SUM(o.total), 0) as total_spent,
                    COALESCE(AVG(o.total), 0) as average_order_value,
                    MAX(o.created_at) as last_order_date,
                    COUNT(CASE WHEN o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as orders_last_30_days
                  FROM orders o
                  WHERE o.user_id = :user_id AND o.status != 'cancelled'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

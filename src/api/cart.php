<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../classes/Cart.php';

header('Content-Type: application/json');

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    startSession();
}

$db = Database::getInstance()->getConnection();
$cart = new Cart($db);

$user_id = $_SESSION['user_id'] ?? null;
$action = $_GET['action'] ?? '';

if (!$user_id && !in_array($action, ['get', 'count'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

switch ($action) {
    case 'get':
        try {
            $cart_items = $cart->getUserCart($user_id);
            if (empty($cart_items)) {
                echo json_encode(['success' => true, 'cart' => [], 'message' => 'Cart is empty']);
            } else {
                $formatted_items = array_map(function($item) {
                    return [
                        'cart_id' => (int)$item['cart_id'],
                        'pizza_id' => $item['pizza_id'] ? (int)$item['pizza_id'] : null,
                        'menu_item_id' => $item['menu_item_id'] ? (int)$item['menu_item_id'] : null,
                        'item_name' => $item['item_name'],
                        'unit_price' => (float)$item['unit_price'],
                        'size' => $item['size'],
                        'quantity' => (int)$item['quantity'],
                        'item_type' => $item['item_type'],
                        'custom_ingredients' => $item['custom_ingredients'] ? json_decode($item['custom_ingredients'], true) : [],
                        'special_instructions' => $item['special_instructions'],
                        'created_at' => $item['created_at']
                    ];
                }, $cart_items);
                echo json_encode(['success' => true, 'cart' => $formatted_items]);
            }
        } catch (Exception $e) {
            error_log("Cart API get error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch cart: ' . $e->getMessage()]);
        }
        break;

    case 'add':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['item_name']) || !isset($data['unit_price']) || !is_numeric($data['unit_price']) || $data['unit_price'] < 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid input data']);
                exit;
            }
            $cart->user_id = $user_id;
            $cart->item_type = $data['item_type'] ?? 'pizza';
            $cart->pizza_id = $data['pizza_id'] ?? null;
            $cart->menu_item_id = $data['menu_item_id'] ?? null;
            $cart->item_name = sanitizeInput($data['item_name']);
            $cart->size = sanitizeInput($data['size'] ?? '');
            $cart->quantity = (int)($data['quantity'] ?? 1);
            $cart->unit_price = (float)$data['unit_price'];
            $cart->custom_ingredients = isset($data['custom_ingredients']) ? json_encode($data['custom_ingredients']) : null;
            $cart->special_instructions = sanitizeInput($data['special_instructions'] ?? '');

            if ($cart->addItem()) {
                echo json_encode(['success' => true, 'cart_id' => $cart->cart_id]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to add item to cart']);
            }
        } catch (Exception $e) {
            error_log("Cart API add error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Add item failed: ' . $e->getMessage()]);
        }
        break;

    case 'remove':
        try {
            $cart_id = (int)($_GET['cart_id'] ?? 0);
            if ($cart_id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid cart ID']);
                exit;
            }
            if ($cart->removeItem($cart_id)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to remove item']);
            }
        } catch (Exception $e) {
            error_log("Cart API remove error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Remove item failed: ' . $e->getMessage()]);
        }
        break;

    case 'update':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $cart_id = (int)($data['cart_id'] ?? 0);
            $quantity = (int)($data['quantity'] ?? 1);
            if ($cart_id <= 0 || $quantity < 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid cart ID or quantity']);
                exit;
            }
            if ($cart->updateQuantity($cart_id, $quantity)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update quantity']);
            }
        } catch (Exception $e) {
            error_log("Cart API update error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Update quantity failed: ' . $e->getMessage()]);
        }
        break;

    case 'clear':
        try {
            if ($cart->clearUserCart($user_id)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to clear cart']);
            }
        } catch (Exception $e) {
            error_log("Cart API clear error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Clear cart failed: ' . $e->getMessage()]);
        }
        break;

    case 'count':
        try {
            $count = $cart->getCartCount($user_id);
            echo json_encode(['success' => true, 'count' => (int)$count]);
        } catch (Exception $e) {
            error_log("Cart API count error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to get cart count: ' . $e->getMessage()]);
        }
        break;

    case 'sync':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $local_cart = $data['cart'] ?? [];
            if ($cart->syncLocalStorageCart($user_id, $local_cart)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to sync cart']);
            }
        } catch (Exception $e) {
            error_log("Cart API sync error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Sync cart failed: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
?>
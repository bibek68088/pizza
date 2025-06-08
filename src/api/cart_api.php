<?php
// Enable error logging, disable display
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/htdocs/pizza/src/php_errors.log');
error_reporting(E_ALL);

// Start output buffering
ob_start();

// Define BASE_PATH if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

// Include dependencies
$required_files = [
    BASE_PATH . 'config/database.php' => 'Database file not found',
    BASE_PATH . 'classes/Cart.php' => 'Cart file not found',
    BASE_PATH . 'includes/functions.php' => 'Functions file not found'
];

foreach ($required_files as $file => $error_message) {
    if (!file_exists($file)) {
        error_log("[$error_message]: $file");
        $response = ['success' => false, 'message' => 'Server configuration error'];
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    require_once $file;
}

// Start session
startSession();

header('Content-Type: application/json');

// Initialize response
$response = ['success' => false, 'message' => 'Invalid request'];

try {
    $database = new Database();
    $db = $database->getConnection();
    $cart = new Cart($db);

    // Parse JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON parse error: " . json_last_error_msg());
        $response['message'] = 'Invalid JSON data';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    error_log("Received input: " . print_r($input, true));

    // Validate CSRF token
    if (!isset($input['csrf_token'])) {
        error_log("CSRF token not provided in request");
        $response['message'] = 'CSRF token missing';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }

    if (!isset($_SESSION['csrf_token'])) {
        error_log("Session CSRF token not set. Session data: " . print_r($_SESSION, true));
        $response['message'] = 'Session CSRF token not found';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }

    if (!validateCSRFToken($input['csrf_token'])) {
        error_log("CSRF token mismatch. Received: {$input['csrf_token']}, Expected: {$_SESSION['csrf_token']}");
        $response['message'] = 'Invalid CSRF token';
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    error_log("CSRF token validated successfully");

    $action = isset($_GET['action']) ? $_GET['action'] : '';

    switch ($action) {
        case 'add':
            if (!isLoggedIn()) {
                $response['message'] = 'Please log in to add items to cart';
                ob_end_clean();
                echo json_encode($response);
                exit;
            }

            if (!$input) {
                $response['message'] = 'Invalid data provided';
                ob_end_clean();
                echo json_encode($response);
                exit;
            }

            $cart->user_id = $_SESSION['user_id'];
            $cart->item_type = $input['item_type'] ?? 'pizza';
            $cart->pizza_id = $input['pizza_id'] ?? null;
            $cart->menu_item_id = $input['menu_item_id'] ?? null;
            $cart->item_name = $input['name'] ?? 'Custom Pizza';
            $cart->size = $input['size'] ?? 'medium';
            $cart->quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
            $cart->unit_price = isset($input['price']) ? (float)$input['price'] : 10.0;
            $cart->custom_ingredients = $input['custom_ingredients'] ?? null;
            $cart->special_instructions = $input['special_instructions'] ?? null;

            try {
                if ($cart->addItem()) {
                    $response = [
                        'success' => true,
                        'message' => 'Item added to cart',
                        'data' => ['items' => $cart->getUserCart($_SESSION['user_id'])]
                    ];
                } else {
                    $response['message'] = 'Failed to add item to cart. Check server logs for details.';
                    error_log("addItem failed for user_id={$_SESSION['user_id']}, pizza_id={$cart->pizza_id}, item_name={$cart->item_name}");
                }
            } catch (Exception $e) {
                $response['message'] = 'Server error: ' . $e->getMessage();
                error_log("Exception in addItem: SQLSTATE={$e->getCode()}, Message={$e->getMessage()}");
            }
            break;

        case 'get':
            if (!isLoggedIn()) {
                $response['message'] = 'Please log in to view cart';
                ob_end_clean();
                echo json_encode($response);
                exit;
            }

            $items = $cart->getUserCart($_SESSION['user_id']);
            $response = [
                'success' => true,
                'data' => ['items' => $items]
            ];
            break;

        case 'remove':
            if (!isLoggedIn()) {
                $response['message'] = 'Please log in to modify cart';
                ob_end_clean();
                echo json_encode($response);
                exit;
            }

            $cart_id = $input['cart_id'] ?? null;
            $cart->user_id = $_SESSION['user_id'];

            if ($cart_id && $cart->removeItem($cart_id)) {
                $response = [
                    'success' => true,
                    'message' => 'Item removed from cart',
                    'data' => ['items' => $cart->getUserCart($_SESSION['user_id'])]
                ];
            } else {
                $response['message'] = 'Failed to remove item';
            }
            break;

        case 'update':
            if (!isLoggedIn()) {
                $response['message'] = 'Please log in to modify cart';
                ob_end_clean();
                echo json_encode($response);
                exit;
            }

            $cart_id = $input['cart_id'] ?? null;
            $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
            $cart->user_id = $_SESSION['user_id'];

            if ($cart_id && $cart->updateQuantity($cart_id, $quantity)) {
                $response = [
                    'success' => true,
                    'message' => 'Quantity updated',
                    'data' => ['items' => $cart->getUserCart($_SESSION['user_id'])]
                ];
            } else {
                $response['message'] = 'Failed to update quantity';
            }
            break;

        case 'clear':
            if (!isLoggedIn()) {
                $response['message'] = 'Please log in to clear cart';
                ob_end_clean();
                echo json_encode($response);
                exit;
            }

            if ($cart->clearUserCart($_SESSION['user_id'])) {
                $response = [
                    'success' => true,
                    'message' => 'Cart cleared',
                    'data' => ['items' => []]
                ];
            } else {
                $response['message'] = 'Failed to clear cart';
            }
            break;

        case 'sync':
            if (!isLoggedIn()) {
                $response['message'] = 'Please log in to sync cart';
                ob_end_clean();
                echo json_encode($response);
                exit;
            }

            $local_cart = $input['items'] ?? [];

            if ($cart->syncLocalStorageCart($_SESSION['user_id'], $local_cart)) {
                $response = [
                    'success' => true,
                    'message' => 'Cart synced successfully',
                    'data' => ['items' => $cart->getUserCart($_SESSION['user_id'])]
                ];
            } else {
                $response['message'] = 'Failed to sync cart';
            }
            break;

        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    error_log("Error in cart_api.php: " . $e->getMessage());
    $response['message'] = 'Server error occurred';
}

ob_end_clean();
echo json_encode($response);
exit;

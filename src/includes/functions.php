<?php

/**
 * Common Functions
 * Crust Pizza Online Ordering System
 * Created for DWIN309 Final Assessment at Kent Institute Australia
 */

/**
 * Sanitize input data
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email address
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (Australian format)
 * @param string $phone Phone number to validate
 * @return bool True if valid, false otherwise
 */
function validatePhone($phone)
{
    $pattern = '/^(\+61|0)[2-9]\d{8}$/';
    return preg_match($pattern, $phone);
}

/**
 * Hash password securely
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if password matches, false otherwise
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Start session if not already started
 */
function startSession()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn()
{
    startSession();
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool True if admin, false otherwise
 */
function isAdmin()
{
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is staff
 * @return bool True if staff, false otherwise
 */
function isStaff()
{
    startSession();
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['kitchen', 'delivery', 'counter', 'admin']);
}

/**
 * Redirect to specified page
 * @param string $page Page to redirect to
 */
function redirect($page)
{
    header("Location: $page");
    exit();
}

/**
 * Format currency
 * @param float $amount Amount to format
 * @return string Formatted currency string
 */
function formatCurrency($amount)
{
    return '$' . number_format($amount, 2);
}

/**
 * Calculate tax amount
 * @param float $subtotal Subtotal amount
 * @param float $taxRate Tax rate (default 10% GST)
 * @return float Tax amount
 */
function calculateTax($subtotal, $taxRate = 0.10)
{
    return $subtotal * $taxRate;
}

/**
 * Calculate delivery fee based on distance/location
 * @param string $address Delivery address
 * @return float Delivery fee
 */
function calculateDeliveryFee($address)
{
    // Simple delivery fee calculation
    // In real implementation, this would use distance calculation
    return 5.50;
}

/**
 * Generate order number
 * @return string Unique order number
 */
function generateOrderNumber()
{
    return 'ORD' . date('Ymd') . rand(1000, 9999);
}

/**
 * Get time ago string
 * @param string $datetime Datetime string
 * @return string Time ago string
 */
function timeAgo($datetime)
{
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time / 60) . ' minutes ago';
    if ($time < 86400) return floor($time / 3600) . ' hours ago';
    if ($time < 2592000) return floor($time / 86400) . ' days ago';

    return date('M j, Y', strtotime($datetime));
}

/**
 * Display flash messages
 */
function displayFlashMessages()
{
    startSession();
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        echo "<div class='alert alert-{$type}'>{$_SESSION['flash_message']}</div>";
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

/**
 * Set flash message
 * @param string $message Message to display
 * @param string $type Message type (success, error, warning, info)
 */
function setFlashMessage($message, $type = 'info')
{
    startSession();
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message_time'] = time();
}

/**
 * Check user permissions for specific actions
 * @param string $action Action to check
 * @return bool True if allowed, false otherwise
 */
function hasPermission($action)
{
    startSession();

    switch ($action) {
        case 'admin_access':
            return isAdmin();
        case 'staff_access':
            return isStaff();
        case 'user_access':
            return isLoggedIn();
        case 'kitchen_access':
            return isset($_SESSION['role']) && $_SESSION['role'] === 'kitchen';
        case 'delivery_access':
            return isset($_SESSION['role']) && $_SESSION['role'] === 'delivery';
        case 'counter_access':
            return isset($_SESSION['role']) && $_SESSION['role'] === 'counter';
        default:
            return false;
    }
}

/**
 * Log user activity
 * @param string $action Action performed
 * @param string $details Additional details
 * @param int|null $user_id User ID (optional)
 * @param int|null $staff_id Staff ID (optional)
 */
function logActivity($action, $details = '', $user_id = null, $staff_id = null)
{
    startSession();

    $username = $_SESSION['username'] ?? 'Anonymous';
    $user_id = $user_id ?? $_SESSION['user_id'] ?? null;

    // Log to database
    $db = Database::getInstance()->getConnection();
    $query = "INSERT INTO activity_logs (user_id, action, details, created_at) 
              VALUES (:user_id, :action, :details, NOW())";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':action', $action);
    $stmt->bindParam(':details', $details);
    $stmt->execute();
}

/**
 * Validate CSRF token
 * @param string $token Token to validate
 * @return bool True if valid, false otherwise
 */
function validateCSRFToken($token)
{
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken()
{
    startSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Cleanup old session data
 */
function cleanupSessions()
{
    startSession();

    if (
        isset($_SESSION['flash_message_time']) &&
        time() - $_SESSION['flash_message_time'] > 300
    ) {
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message_time']);
    }
}

/**
 * Get user's IP address
 * @return string IP address
 */
function getUserIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Check rate limit for an action
 * @param string $action Action to rate limit
 * @param int $limit Maximum attempts
 * @param int $window Time window in seconds
 * @return bool True if within limit, false otherwise
 */
function checkRateLimit($action, $limit = 5, $window = 300)
{
    startSession();

    $key = 'rate_limit_' . $action . '_' . getUserIP();
    $now = time();

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [];
    }

    $_SESSION[$key] = array_filter($_SESSION[$key], function ($timestamp) use ($now, $window) {
        return ($now - $timestamp) < $window;
    });

    if (count($_SESSION[$key]) >= $limit) {
        return false;
    }

    $_SESSION[$key][] = $now;
    return true;
}

/**
 * Send email notification
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email message
 * @return bool True if sent successfully, false otherwise
 */
function sendEmail($to, $subject, $message)
{
    // In a real application, use a proper email library (e.g., PHPMailer)
    $headers = "From: no-reply@crustpizza.com.au\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    if (mail($to, $subject, $message, $headers)) {
        error_log("EMAIL SENT: To: $to, Subject: $subject");
        return true;
    } else {
        error_log("EMAIL FAILED: To: $to, Subject: $subject");
        return false;
    }
}

/**
 * Send notification to customer
 * @param int $order_id Order ID
 * @param string $status New order status
 * @return bool True if notification sent, false otherwise
 */
function sendNotification($order_id, $status)
{
    $db = Database::getInstance()->getConnection();
    $query = "SELECT o.order_number, o.customer_email, o.customer_name 
              FROM orders o 
              WHERE o.order_id = :order_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order || empty($order['customer_email'])) {
        return false;
    }

    $statusMessages = [
        'prepared' => 'Your order is prepared and will soon be delivered.',
        'out_for_delivery' => 'Your order is out for delivery.',
        'ready_for_pickup' => 'Your order is ready for pickup at the store.',
        'delivered' => 'Your order has been delivered. Enjoy your meal!',
        'delivery_failure' => 'We encountered an issue delivering your order. Please contact support.'
    ];

    $subject = "Crust Pizza Order #{$order['order_number']} Update";
    $message = "Dear {$order['customer_name']},\n\n";
    $message .= "Your order #{$order['order_number']} status has been updated to: $status.\n";
    $message .= $statusMessages[$status] ?? "Status updated.";
    $message .= "\n\nThank you for choosing Crust Pizza!\nCrust Pizza Team";

    return sendEmail($order['customer_email'], $subject, $message);
}

/**
 * Format phone number
 * @param string $phone Phone number
 * @return string Formatted phone number
 */
function formatPhone($phone)
{
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
        return substr($phone, 0, 4) . ' ' . substr($phone, 4, 3) . ' ' . substr($phone, 7);
    }
    return $phone;
}

/**
 * Generate unique filename
 * @param string $originalName Original filename
 * @return string Unique filename
 */
function generateUniqueFilename($originalName)
{
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

/**
 * Validate file upload
 * @param array $file Uploaded file
 * @param array $allowedTypes Allowed MIME types
 * @param int $maxSize Maximum file size in bytes
 * @return bool True if valid, false otherwise
 */
function isValidUpload($file, $allowedTypes = [], $maxSize = 5242880)
{ // 5MB default
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if ($file['size'] > $maxSize) {
        return false;
    }

    if (!empty($allowedTypes) && !in_array($file['type'], $allowedTypes)) {
        return false;
    }

    return true;
}

/**
 * Get current staff ID
 * @return int|null Staff user ID or null if not logged in
 */
function getCurrentStaffId()
{
    startSession();
    if (isset($_SESSION['user_id']) && isStaff()) {
        return $_SESSION['user_id'];
    }
    return null;
}

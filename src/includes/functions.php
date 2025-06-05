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
function sanitizeInput($data) {
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
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (Australian format)
 * @param string $phone Phone number to validate
 * @return bool True if valid, false otherwise
 */
function validatePhone($phone) {
    $pattern = '/^(\+61|0)[2-9]\d{8}$/';
    return preg_match($pattern, $phone);
}

/**
 * Hash password securely
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if password matches, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Start session if not already started
 */
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    startSession();
    return isset($_SESSION['staff_role']) && $_SESSION['staff_role'] === 'admin';
}

/**
 * Check if user is staff
 * @return bool True if staff, false otherwise
 */
function isStaff() {
    startSession();
    return isset($_SESSION['staff_id']);
}

/**
 * Redirect to specified page
 * @param string $page Page to redirect to
 */
function redirect($page) {
    header("Location: $page");
    exit();
}

/**
 * Format currency
 * @param float $amount Amount to format
 * @return string Formatted currency string
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Calculate tax amount
 * @param float $subtotal Subtotal amount
 * @param float $taxRate Tax rate (default 10% GST)
 * @return float Tax amount
 */
function calculateTax($subtotal, $taxRate = 0.10) {
    return $subtotal * $taxRate;
}

/**
 * Calculate delivery fee based on distance/location
 * @param string $address Delivery address
 * @return float Delivery fee
 */
function calculateDeliveryFee($address) {
    // Simple delivery fee calculation
    // In real implementation, this would use distance calculation
    return 5.50;
}

/**
 * Generate order number
 * @return string Unique order number
 */
function generateOrderNumber() {
    return 'ORD' . date('Ymd') . rand(1000, 9999);
}

/**
 * Get time ago string
 * @param string $datetime Datetime string
 * @return string Time ago string
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}

/**
 * Display flash messages
 */
function displayFlashMessages() {
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
function setFlashMessage($message, $type = 'info') {
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
function hasPermission($action) {
    startSession();
    
    switch ($action) {
        case 'admin_access':
            return isAdmin();
        case 'staff_access':
            return isStaff();
        case 'user_access':
            return isLoggedIn();
        case 'kitchen_access':
            return isStaff() && $_SESSION['staff_role'] === 'kitchen';
        case 'delivery_access':
            return isStaff() && $_SESSION['staff_role'] === 'delivery';
        case 'counter_access':
            return isStaff() && $_SESSION['staff_role'] === 'counter';
        default:
            return false;
    }
}

/**
 * Log user activity
 * @param string $action Action performed
 * @param string $details Additional details
 */
function logActivity($action, $details = '') {
    startSession();
    
    // In a real application, this would log to database
    error_log(sprintf(
        "[%s] User: %s, Action: %s, Details: %s",
        date('Y-m-d H:i:s'),
        $_SESSION['username'] ?? $_SESSION['staff_username'] ?? 'Anonymous',
        $action,
        $details
    ));
}

function validateCSRFToken($token) {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generateCSRFToken() {
    startSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function cleanupSessions() {
    startSession();
    
    if (isset($_SESSION['flash_message_time']) && 
        time() - $_SESSION['flash_message_time'] > 300) { 
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message_time']);
    }
}

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function checkRateLimit($action, $limit = 5, $window = 300) {
    startSession();
    
    $key = 'rate_limit_' . $action . '_' . getUserIP();
    $now = time();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [];
    }
    
    $_SESSION[$key] = array_filter($_SESSION[$key], function($timestamp) use ($now, $window) {
        return ($now - $timestamp) < $window;
    });
    
    if (count($_SESSION[$key]) >= $limit) {
        return false;
    }
    
    $_SESSION[$key][] = $now;
    return true;
}

function sendEmail($to, $subject, $message) {
    error_log("EMAIL: To: $to, Subject: $subject, Message: $message");
    return true;
}

function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
        return substr($phone, 0, 4) . ' ' . substr($phone, 4, 3) . ' ' . substr($phone, 7);
    }
    
    return $phone;
}

function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

function isValidUpload($file, $allowedTypes = [], $maxSize = 5242880) { // 5MB default
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

function getCurrentStaffId() {
    // Check if staff is logged in and return their ID
    if (isset($_SESSION['staff_id']) && !empty($_SESSION['staff_id'])) {
        return $_SESSION['staff_id'];
    }
    
    // Return null if no staff is logged in
    return null;
}
?>

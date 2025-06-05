<?php
/**
 * Logout Script
 * Destroys user session and redirects to home
 */

require_once 'includes/functions.php';

startSession();

// Destroy session
session_destroy();

// Redirect to home page
redirect('index.php');
?>

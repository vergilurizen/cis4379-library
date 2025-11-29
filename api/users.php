<?php
// Enable error reporting at the very top
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include files
include '../config.php';
include 'helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle GET requests
if ($method === 'GET') {
    // Get current logged-in user
    if (!isset($_SESSION['user'])) {
        sendJSON(false, null, "Not logged in");
    }
    
    $user = $_SESSION['user'];
    
    // Don't send password back
    unset($user['password']);
    
    sendJSON(true, $user, "User retrieved successfully");
}

else {
    sendJSON(false, null, "Method not allowed. Use GET to retrieve current user info.");
}
?>


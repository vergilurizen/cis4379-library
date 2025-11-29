<?php
// Enable error reporting at the very top
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include files
include '../config.php';
include 'helpers.php';
include '../lib/database_functions.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle POST requests
if ($method === 'POST') {
    $data = getPostData();
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // LOGIN
    if ($action === 'login') {
        if (!isset($data['username']) || !isset($data['password'])) {
            sendJSON(false, null, "Username and password required");
        }
        
        $result = db_login($data['username'], $data['password'], $conn);
        
        if ($result['success']) {
            unset($result['data']['password']); // Don't send password back
        }
        
        sendJSON($result['success'], $result['data'], $result['message']);
    }
    
    // REGISTER
    else if ($action === 'register') {
        if (!isset($data['username']) || !isset($data['password'])) {
            sendJSON(false, null, "Username and password required");
        }
        
        $result = db_register($data['username'], $data['password'], $conn);
        sendJSON($result['success'], $result['data'], $result['message']);
    }
    
    // LOGOUT
    else if ($action === 'logout') {
        session_destroy();
        sendJSON(true, null, "Logged out successfully");
    }
    
    else {
        sendJSON(false, null, "Invalid action. Use ?action=login, ?action=register, or ?action=logout");
    }
}

// Handle GET requests
else if ($method === 'GET') {
    sendJSON(false, null, "Use POST with ?action=login or ?action=register");
}

else {
    sendJSON(false, null, "Method not allowed");
}
?>


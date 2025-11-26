<?php
// Enable error reporting at the very top
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include files
include '../config.php';
include 'helpers.php';
include '../lib/database_functions.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle GET requests - List rentals
if ($method === 'GET') {
    requireLogin();
    
    // Get all rentals (admin only)
    if (isset($_GET['all']) && $_GET['all'] == '1') {
        requireAdmin();
        $result = db_get_all_rentals($conn);
        sendJSON($result['success'], $result['data'], $result['message']);
    }
    // Get single rental by ID
    else if (isset($_GET['id'])) {
        $rentalId = intval($_GET['id']);
        $userId = isset($_SESSION['user']) ? intval($_SESSION['user']['id']) : 0;
        $isAdmin = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
        
        // Get rental with material details
        $sql = "
            SELECT 
                bm.id AS rental_id,
                bm.borrowed_date,
                bm.status,
                bm.user_id,
                m.id AS material_id,
                m.title,
                m.author,
                m.category
            FROM borrowed_materials bm
            JOIN materials m ON bm.material_id = m.id
            WHERE bm.id = $rentalId
        ";
        
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) === 1) {
            $rental = mysqli_fetch_assoc($result);
            
            // Check access: admin or owner
            if (!$isAdmin && intval($rental['user_id']) !== $userId) {
                sendJSON(false, null, "Access denied");
            }
            
            // If admin, also include username
            if ($isAdmin) {
                $userSql = "SELECT username FROM users WHERE id = " . intval($rental['user_id']);
                $userResult = mysqli_query($conn, $userSql);
                if ($userResult && mysqli_num_rows($userResult) === 1) {
                    $user = mysqli_fetch_assoc($userResult);
                    $rental['username'] = $user['username'];
                }
            }
            
            // Remove user_id from response for security
            unset($rental['user_id']);
            
            sendJSON(true, $rental, "Rental retrieved successfully");
        } else {
            sendJSON(false, null, "Rental not found");
        }
    }
    // Get current user's rentals
    else {
        $userId = intval($_SESSION['user']['id']);
        $result = db_get_user_rentals($userId, $conn);
        sendJSON($result['success'], $result['data'], $result['message']);
    }
}

// Handle POST requests - Rent materials (checkout)
else if ($method === 'POST') {
    requireLogin();
    
    $data = getPostData();
    
    if (!isset($data['material_ids']) || !is_array($data['material_ids']) || empty($data['material_ids'])) {
        sendJSON(false, null, "Material IDs array required");
    }
    
    $userId = intval($_SESSION['user']['id']);
    $result = db_rent_materials($userId, $data['material_ids'], $conn);
    sendJSON($result['success'], $result['data'], $result['message']);
}

// Handle PUT requests - Update rental status (admin only)
else if ($method === 'PUT') {
    requireAdmin();
    
    $data = getPostData();
    
    if (!isset($data['rental_id']) || !isset($data['status'])) {
        sendJSON(false, null, "Rental ID and status required");
    }
    
    $rentalId = intval($data['rental_id']);
    $newStatus = trim($data['status']);
    
    $result = db_update_rental_status($rentalId, $newStatus, $conn);
    sendJSON($result['success'], $result['data'], $result['message']);
}

else {
    sendJSON(false, null, "Method not allowed");
}
?>


<?php
// Helper functions for API endpoints

/**
 * Send JSON response and exit
 */
function sendJSON($success, $data = null, $message = "") {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

/**
 * Check if user is logged in
 */
function requireAuth() {
    if (!isset($_SESSION['user'])) {
        sendJSON(false, null, "Authentication required");
    }
}

/**
 * Check if user is admin
 */
function requireAdmin() {
    requireAuth();
    if ($_SESSION['user']['role'] !== 'admin') {
        sendJSON(false, null, "Admin access required");
    }
}

/**
 * Get POST data as array
 */
function getPostData() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // If JSON decode fails, fall back to $_POST
    if ($data === null) {
        return $_POST;
    }
    
    return $data;
}

/**
 * Basic input sanitization
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
?>


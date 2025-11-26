<?php
// Helper functions for API endpoints and frontend

/**
 * Send JSON response and exit (used by API files like auth.php, materials.php, users.php)
 */
function sendJSON($success, $data = null, $message = "") {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data'    => $data,
        'message' => $message
    ]);
    exit;
}

/**
 * Read POST data as JSON if available, otherwise use $_POST
 */
function getPostData() {
    $raw = file_get_contents("php://input");
    if (!empty($raw)) {
        $data = json_decode($raw, true);
        if (is_array($data)) {
            return $data;
        }
    }
    return $_POST;
}

/**
 * Basic input sanitization (for simple text fields like username, titles, etc.)
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate username as required, 3 to 30 character, and valid characters only
 */
function validate_username($username, &$error) {
    $username = trim($username);

    if ($username === '') {
        $error = "Username is required.";
        return false;
    }
    if (strlen($username) < 3 || strlen($username) > 30) {
        $error = "Username must be between 3 and 30 characters.";
        return false;
    }
    if (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
        $error = "Username may only contain letters, numbers, and underscores.";
        return false;
    }

    return true;
}

/**
 * Validate password as required and at least 6 characters
 */
function validate_password($password, &$error) {
    if ($password === '') {
        $error = "Password is required.";
        return false;
    }
    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
        return false;
    }
    return true;
}

/**
 * Require admin access - sends JSON error and exits if not admin
 */
function requireAdmin() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        sendJSON(false, null, "Admin access required");
    }
}

/**
 * Require login - sends JSON error and exits if not logged in
 */
function requireLogin() {
    if (!isset($_SESSION['user'])) {
        sendJSON(false, null, "Authentication required");
    }
}

/**
 * Make API call to backend endpoint
 * Since we're on the same server, we directly call the database functions
 * This matches the API response format but avoids HTTP overhead
 */
function callAPI($endpoint, $method = 'GET', $data = null) {
    // Ensure database functions are available
    if (!function_exists('db_get_materials')) {
        // Include database functions if not already included
        $dbPath = dirname(__DIR__) . '/lib/database_functions.php';
        if (file_exists($dbPath)) {
            include_once $dbPath;
        }
    }
    
    // Get database connection
    global $conn;
    if (!isset($conn)) {
        // Try to get connection from config
        $configPath = dirname(__DIR__) . '/config.php';
        if (file_exists($configPath)) {
            include_once $configPath;
        }
    }
    
    // Parse endpoint
    $parts = parse_url($endpoint);
    $apiFile = isset($parts['path']) ? basename($parts['path'], '.php') : basename($endpoint, '.php');
    $queryParams = [];
    if (isset($parts['query'])) {
        parse_str($parts['query'], $queryParams);
    }
    
    // Handle different API endpoints
    switch ($apiFile) {
        case 'materials':
            if ($method === 'GET') {
                if (isset($queryParams['id']) || (isset($data['id']))) {
                    $id = isset($queryParams['id']) ? $queryParams['id'] : $data['id'];
                    $result = db_get_material($id, $conn);
                } else {
                    $availableOnly = isset($queryParams['available']) && $queryParams['available'] == '1';
                    $result = db_get_materials($conn, $availableOnly);
                }
                return $result;
            } else if ($method === 'POST') {
                if (!isset($data['title']) || !isset($data['author']) || !isset($data['category'])) {
                    return ['success' => false, 'data' => null, 'message' => 'Title, author, and category required'];
                }
                return db_add_material($data['title'], $data['author'], $data['category'], $conn);
            }
            break;
            
        case 'rentals':
            if ($method === 'GET') {
                if (isset($queryParams['all']) && $queryParams['all'] == '1') {
                    // Admin: get all rentals
                    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                        return ['success' => false, 'data' => null, 'message' => 'Admin access required'];
                    }
                    return db_get_all_rentals($conn);
                } else if (isset($queryParams['id']) || (isset($data['id']))) {
                    // Get single rental - simplified for now
                    $id = isset($queryParams['id']) ? $queryParams['id'] : $data['id'];
                    $userId = isset($_SESSION['user']) ? intval($_SESSION['user']['id']) : 0;
                    $result = db_get_user_rentals($userId, $conn);
                    // Find the specific rental
                    if ($result['success'] && !empty($result['data'])) {
                        foreach ($result['data'] as $rental) {
                            if (intval($rental['rental_id']) == intval($id)) {
                                return ['success' => true, 'data' => $rental, 'message' => 'Rental found'];
                            }
                        }
                    }
                    return ['success' => false, 'data' => null, 'message' => 'Rental not found'];
                } else {
                    // Get current user's rentals
                    if (!isset($_SESSION['user'])) {
                        return ['success' => false, 'data' => null, 'message' => 'Authentication required'];
                    }
                    $userId = intval($_SESSION['user']['id']);
                    return db_get_user_rentals($userId, $conn);
                }
            } else if ($method === 'POST') {
                if (!isset($_SESSION['user'])) {
                    return ['success' => false, 'data' => null, 'message' => 'Authentication required'];
                }
                if (!isset($data['material_ids']) || !is_array($data['material_ids']) || empty($data['material_ids'])) {
                    return ['success' => false, 'data' => null, 'message' => 'Material IDs array required'];
                }
                $userId = intval($_SESSION['user']['id']);
                return db_rent_materials($userId, $data['material_ids'], $conn);
            } else if ($method === 'PUT') {
                if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                    return ['success' => false, 'data' => null, 'message' => 'Admin access required'];
                }
                if (!isset($data['rental_id']) || !isset($data['status'])) {
                    return ['success' => false, 'data' => null, 'message' => 'Rental ID and status required'];
                }
                return db_update_rental_status($data['rental_id'], $data['status'], $conn);
            }
            break;
            
        case 'users':
            if ($method === 'GET') {
                if (!isset($_SESSION['user'])) {
                    return ['success' => false, 'data' => null, 'message' => 'Not logged in'];
                }
                $user = $_SESSION['user'];
                unset($user['password']);
                return ['success' => true, 'data' => $user, 'message' => 'User retrieved successfully'];
            }
            break;
    }
    
    return [
        'success' => false,
        'data' => null,
        'message' => 'API endpoint not implemented: ' . $apiFile . ' with method ' . $method
    ];
}
?>

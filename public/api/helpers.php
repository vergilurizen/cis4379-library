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
?>

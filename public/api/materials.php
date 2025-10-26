<?php
// Enable error reporting at the very top
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include files
include '../config.php';
include 'helpers.php';
include '../lib/database_functions.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle GET requests - List materials
if ($method === 'GET') {
    // Get single material by ID
    if (isset($_GET['id'])) {
        $result = db_get_material($_GET['id'], $conn);
        sendJSON($result['success'], $result['data'], $result['message']);
    }
    // List materials (all or available only)
    else {
        $availableOnly = isset($_GET['available']) && $_GET['available'] == '1';
        $result = db_get_materials($conn, $availableOnly);
        sendJSON($result['success'], $result['data'], $result['message']);
    }
}

// Handle POST requests - Add material (admin only)
else if ($method === 'POST') {
    requireAdmin();
    
    $data = getPostData();
    
    if (!isset($data['title']) || !isset($data['author']) || !isset($data['category'])) {
        sendJSON(false, null, "Title, author, and category required");
    }
    
    $result = db_add_material($data['title'], $data['author'], $data['category'], $conn);
    sendJSON($result['success'], $result['data'], $result['message']);
}

// Handle PUT requests - Update material (admin only)
else if ($method === 'PUT') {
    requireAdmin();
    
    $data = getPostData();
    
    if (!isset($data['id'])) {
        sendJSON(false, null, "Material ID required");
    }
    
    $id = intval($data['id']);
    $updates = [];
    
    if (isset($data['title'])) {
        $title = sanitize($data['title']);
        $updates[] = "title='$title'";
    }
    if (isset($data['author'])) {
        $author = sanitize($data['author']);
        $updates[] = "author='$author'";
    }
    if (isset($data['category'])) {
        $category = sanitize($data['category']);
        $updates[] = "category='$category'";
    }
    if (isset($data['available'])) {
        $available = intval($data['available']);
        $updates[] = "available=$available";
    }
    
    if (empty($updates)) {
        sendJSON(false, null, "No fields to update");
    }
    
    $sql = "UPDATE materials SET " . implode(', ', $updates) . " WHERE id=$id";
    
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            sendJSON(true, null, "Material updated successfully");
        } else {
            sendJSON(false, null, "Material not found or no changes made");
        }
    } else {
        sendJSON(false, null, "Error updating material: " . mysqli_error($conn));
    }
}

// Handle DELETE requests - Delete material (admin only)
else if ($method === 'DELETE') {
    requireAdmin();
    
    if (!isset($_GET['id'])) {
        sendJSON(false, null, "Material ID required");
    }
    
    $id = intval($_GET['id']);
    $sql = "DELETE FROM materials WHERE id=$id";
    
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            sendJSON(true, null, "Material deleted successfully");
        } else {
            sendJSON(false, null, "Material not found");
        }
    } else {
        sendJSON(false, null, "Error deleting material: " . mysqli_error($conn));
    }
}

else {
    sendJSON(false, null, "Method not allowed");
}
?>


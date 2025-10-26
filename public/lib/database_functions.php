<?php
/**
 * Shared Database Functions
 * Used by both frontend pages and API endpoints
 * No HTTP/cURL needed - just include and call!
 */

/**
 * User Authentication - Login
 */
function db_login($username, $password, $conn) {
    $username = htmlspecialchars(strip_tags(trim($username)));
    
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user'] = $user;
        return [
            'success' => true,
            'data' => $user,
            'message' => 'Login successful'
        ];
    } else {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Invalid username or password'
        ];
    }
}

/**
 * User Registration
 */
function db_register($username, $password, $conn) {
    $username = htmlspecialchars(strip_tags(trim($username)));
    
    // Check if username exists
    $check = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $check);
    
    if (mysqli_num_rows($result) > 0) {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Username already exists'
        ];
    }
    
    // Insert new user
    $insert = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'user')";
    if (mysqli_query($conn, $insert)) {
        return [
            'success' => true,
            'data' => ['username' => $username],
            'message' => 'Account created successfully'
        ];
    } else {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Error creating account: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Get all materials
 */
function db_get_materials($conn, $availableOnly = false) {
    if ($availableOnly) {
        $sql = "SELECT * FROM materials WHERE available=1";
    } else {
        $sql = "SELECT * FROM materials";
    }
    
    $result = mysqli_query($conn, $sql);
    $materials = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $materials[] = $row;
    }
    
    return [
        'success' => true,
        'data' => $materials,
        'message' => 'Materials retrieved successfully'
    ];
}

/**
 * Get single material by ID
 */
function db_get_material($id, $conn) {
    $id = intval($id);
    $sql = "SELECT * FROM materials WHERE id=$id";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $material = mysqli_fetch_assoc($result);
        return [
            'success' => true,
            'data' => $material,
            'message' => 'Material found'
        ];
    } else {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Material not found'
        ];
    }
}

/**
 * Add new material (admin only)
 */
function db_add_material($title, $author, $category, $conn) {
    // Check if user is admin
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Admin access required'
        ];
    }
    
    // Sanitize inputs
    $title = htmlspecialchars(strip_tags(trim($title)));
    $author = htmlspecialchars(strip_tags(trim($author)));
    $category = htmlspecialchars(strip_tags(trim($category)));
    
    // Build SQL query
    $sql = "INSERT INTO materials (title, author, category, available) VALUES ('$title', '$author', '$category', 1)";
    
    // Execute query
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $newId = mysqli_insert_id($conn);
        
        // Verify the insert actually worked
        $affected = mysqli_affected_rows($conn);
        
        return [
            'success' => true,
            'data' => ['id' => $newId, 'title' => $title, 'author' => $author, 'category' => $category, 'affected_rows' => $affected],
            'message' => "Material added successfully! (ID: $newId, Rows affected: $affected)"
        ];
    } else {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Error adding material: ' . mysqli_error($conn) . ' | SQL: ' . $sql
        ];
    }
}

/**
 * Update material (admin only)
 */
function db_update_material($id, $updates, $conn) {
    // Check if user is admin
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Admin access required'
        ];
    }
    
    $id = intval($id);
    $updateParts = [];
    
    if (isset($updates['title'])) {
        $title = htmlspecialchars(strip_tags(trim($updates['title'])));
        $updateParts[] = "title='$title'";
    }
    if (isset($updates['author'])) {
        $author = htmlspecialchars(strip_tags(trim($updates['author'])));
        $updateParts[] = "author='$author'";
    }
    if (isset($updates['category'])) {
        $category = htmlspecialchars(strip_tags(trim($updates['category'])));
        $updateParts[] = "category='$category'";
    }
    if (isset($updates['available'])) {
        $available = intval($updates['available']);
        $updateParts[] = "available=$available";
    }
    
    if (empty($updateParts)) {
        return [
            'success' => false,
            'data' => null,
            'message' => 'No fields to update'
        ];
    }
    
    $sql = "UPDATE materials SET " . implode(', ', $updateParts) . " WHERE id=$id";
    
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            return [
                'success' => true,
                'data' => null,
                'message' => 'Material updated successfully'
            ];
        } else {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Material not found or no changes made'
            ];
        }
    } else {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Error updating material: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Delete material (admin only)
 */
function db_delete_material($id, $conn) {
    // Check if user is admin
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Admin access required'
        ];
    }
    
    $id = intval($id);
    $sql = "DELETE FROM materials WHERE id=$id";
    
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            return [
                'success' => true,
                'data' => null,
                'message' => 'Material deleted successfully'
            ];
        } else {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Material not found'
            ];
        }
    } else {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Error deleting material: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Get current user info
 */
function db_get_current_user() {
    if (!isset($_SESSION['user'])) {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Not logged in'
        ];
    }
    
    $user = $_SESSION['user'];
    unset($user['password']); // Don't send password
    
    return [
        'success' => true,
        'data' => $user,
        'message' => 'User retrieved successfully'
    ];
}
?>


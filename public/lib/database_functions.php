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
    
    // *** NEW: only look up by username ***
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // *** NEW: verify the entered password against the stored hash ***
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            return [
                'success' => true,
                'data' => $user,
                'message' => 'Login successful'
            ];
        }
    }

    // If no user or password_verify fails:
    return [
        'success' => false,
        'data' => null,
        'message' => 'Invalid username or password'
    ];
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

    // Hash the password for storing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $insert = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashedPassword', 'user')";
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
 * Get a single material by ID
 */
function db_get_material($id, $conn) {
    $id = intval($id);
    $sql = "SELECT * FROM materials WHERE id=$id";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) === 1) {
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
            'data' => [
                'id' => $newId,
                'title' => $title,
                'author' => $author,
                'category' => $category,
                'affected_rows' => $affected
            ],
            'message' => "Material added successfully! (ID: $newId, Rows affected: $affected)"
        ];
    } else {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Error adding material: ' . mysqli_error($conn)
        ];
    }
}

/**
 * Update existing material (admin only)
 */
function db_update_material($id, $title, $author, $category, $available, $conn) {
    // Check if user is admin
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Admin access required'
        ];
    }
    
    $id = intval($id);
    $available = intval($available) ? 1 : 0;
    
    // Sanitize inputs
    $title = htmlspecialchars(strip_tags(trim($title)));
    $author = htmlspecialchars(strip_tags(trim($author)));
    $category = htmlspecialchars(strip_tags(trim($category)));
    
    // Build SQL query
    $sql = "UPDATE materials SET 
                title='$title',
                author='$author',
                category='$category',
                available=$available
            WHERE id=$id";
    
    // Execute query
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_affected_rows($conn) > 0) {
        return [
            'success' => true,
            'data' => null,
            'message' => 'Material updated successfully'
        ];
    } else {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Error updating material or no changes made'
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
                'message' => 'Material not found or no changes made'
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
 * Get the current logged-in user info (for UI display)
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

/**
 * Rent a list of materials for a user (shopping cart checkout)
 * - Inserts into borrowed_materials with status='Pending'
 * - Sets materials.available = 0
 */
function db_rent_materials($userId, $materialIds, $conn) {
    $userId = intval($userId);
    if (!is_array($materialIds) || empty($materialIds)) {
        return [
            'success' => false,
            'data' => null,
            'message' => 'No items to rent'
        ];
    }

    // Normalize IDs to integers
    $ids = [];
    foreach ($materialIds as $mid) {
        $mid = intval($mid);
        if ($mid > 0) {
            $ids[] = $mid;
        }
    }

    if (empty($ids)) {
        return [
            'success' => false,
            'data' => null,
            'message' => 'No valid material IDs to rent'
        ];
    }

    $errors = [];

    foreach ($ids as $mid) {
        // Check that the material exists and is available
        $checkSql = "SELECT available FROM materials WHERE id=$mid LIMIT 1";
        $checkRes = mysqli_query($conn, $checkSql);

        if ($checkRes === false || mysqli_num_rows($checkRes) !== 1) {
            $errors[] = "Material $mid not found.";
            continue;
        }

        $row = mysqli_fetch_assoc($checkRes);
        if (intval($row['available']) !== 1) {
            $errors[] = "Material $mid is no longer available.";
            continue;
        }

        // Insert into borrowed_materials with status Pending
        $insertSql = "INSERT INTO borrowed_materials (user_id, material_id, borrowed_date, status)
                      VALUES ($userId, $mid, NOW(), 'Pending')";
        $insertRes = mysqli_query($conn, $insertSql);
        if ($insertRes === false) {
            $errors[] = "Error renting material $mid: " . mysqli_error($conn);
            continue;
        }

        // Mark material unavailable
        $updateSql = "UPDATE materials SET available = 0 WHERE id = $mid";
        $updateRes = mysqli_query($conn, $updateSql);
        if ($updateRes === false) {
            $errors[] = "Error updating availability for material $mid: " . mysqli_error($conn);
        }
    }

    if (empty($errors)) {
        return [
            'success' => true,
            'data' => null,
            'message' => 'All items rented successfully. Please pick up your materials at the TAMUCT Library, located at 1001 Leadership Pl, WH-101, Killeen, TX 76549, United States.'
        ];
    }

    return [
        'success' => false,
        'data' => $errors,
        'message' => 'Some items could not be rented'
    ];
}

/**
 * Get all rentals for a specific user (for "My Rentals" page)
 */
function db_get_user_rentals($userId, $conn) {
    $userId = intval($userId);

    $sql = "
        SELECT 
            bm.id AS rental_id,
            bm.borrowed_date,
            bm.status,
            m.id AS material_id,
            m.title,
            m.author,
            m.category
        FROM borrowed_materials bm
        JOIN materials m ON bm.material_id = m.id
        WHERE bm.user_id = $userId
        ORDER BY bm.borrowed_date DESC
    ";

    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Error fetching rentals: ' . mysqli_error($conn)
        ];
    }

    $rentals = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rentals[] = $row;
    }

    return [
        'success' => true,
        'data' => $rentals,
        'message' => 'User rentals retrieved successfully'
    ];
}

/**
 * Get all rentals (for admin orders view)
 */
function db_get_all_rentals($conn) {
    $sql = "
        SELECT 
            bm.id AS rental_id,
            bm.borrowed_date,
            bm.status,
            m.id AS material_id,
            m.title,
            m.author,
            m.category,
            u.id AS user_id,
            u.username
        FROM borrowed_materials bm
        JOIN materials m ON bm.material_id = m.id
        JOIN users u ON bm.user_id = u.id
        ORDER BY bm.borrowed_date DESC
    ";

    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Error fetching all rentals: ' . mysqli_error($conn)
        ];
    }

    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }

    return [
        'success' => true,
        'data' => $orders,
        'message' => 'All rentals retrieved successfully'
    ];
}

/**
 * Admin-only: update rental status
 * Allowed transitions:
 *   Pending   -> Delivered
 *   Delivered -> Returned
 *   Pending   -> Returned (admin override)
 * When a rental is marked Returned, the associated material is made available again.
 */
function db_update_rental_status($rentalId, $newStatus, $conn) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Admin access required'
        ];
    }

    $rentalId = intval($rentalId);
    $newStatus = trim($newStatus);

    $allowedStatuses = ['Pending', 'Delivered', 'Returned'];
    if (!in_array($newStatus, $allowedStatuses, true)) {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Invalid status value'
        ];
    }

    // Load current rental
    $selectSql = "SELECT status, material_id FROM borrowed_materials WHERE id = $rentalId";
    $selectRes = mysqli_query($conn, $selectSql);
    if ($selectRes === false || mysqli_num_rows($selectRes) !== 1) {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Rental not found'
        ];
    }

    $row = mysqli_fetch_assoc($selectRes);
    $currentStatus = $row['status'];
    $materialId = intval($row['material_id']);

    // Check allowed transitions
    $valid = false;
    if ($currentStatus === 'Pending' && $newStatus === 'Delivered') {
        $valid = true;
    } elseif ($currentStatus === 'Delivered' && $newStatus === 'Returned') {
        $valid = true;
    } elseif ($currentStatus === 'Pending' && $newStatus === 'Returned') {
        $valid = true;
    }

    if (!$valid) {
        return [
            'success' => false,
            'data' => null,
            'message' => "Invalid status transition ($currentStatus → $newStatus)"
        ];
    }

    // Update rental status
    $updateSql = "UPDATE borrowed_materials SET status = '$newStatus' WHERE id = $rentalId";
    $updateRes = mysqli_query($conn, $updateSql);
    if ($updateRes === false) {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Error updating status: ' . mysqli_error($conn)
        ];
    }

    // If returned, make the material available again
    if ($newStatus === 'Returned' && $materialId > 0) {
        $matSql = "UPDATE materials SET available = 1 WHERE id = $materialId";
        $matRes = mysqli_query($conn, $matSql);
        if ($matRes === false) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Status updated, but failed to mark material available: ' . mysqli_error($conn)
            ];
        }
    }

    return [
        'success' => true,
        'data' => null,
        'message' => "Status updated to $newStatus"
    ];
}

?>

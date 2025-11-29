<?php
/**
 * Shared Database Functions
 * Used by both frontend pages and API endpoints
 * No HTTP/cURL needed - just include and call!
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require './vendor/phpmailer/phpmailer/src/Exception.php';
require './vendor/phpmailer/phpmailer/src/PHPMailer.php';
require './vendor/phpmailer/phpmailer/src/SMTP.php';

/**
 * Email User with order status
 */
function send_status($userId, $orderId, $subject, $message, $conn) {
    $mail = new PHPMailer(true);

    // Load user info
    $userSql = "SELECT * FROM users WHERE id=$userId";
    $user = mysqli_query($conn, $userSql);
    if (mysqli_num_rows($user) == 1) {
        $user = mysqli_fetch_assoc($user);
    } else {
        return;
    }

    try {
        //Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'libraryinventorysystem@gmail.com';     // SMTP username
        $mail->Password   = 'qrtf cxmo lace edsz';                  // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom('libraryinventorysystem@gmail.com', 'Library Inventory System');
        $mail->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);     // Add a recipient

        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
    } catch (Exception $e) {
    }
}

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
function db_register($username, $password, $firstName, $lastName, $email, $conn) {
    $username = htmlspecialchars(strip_tags(trim($username)));
    $firstName = htmlspecialchars(strip_tags(trim($firstName)));
    $lastName = htmlspecialchars(strip_tags(trim($lastName)));
    
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
    $insert = "INSERT INTO users (username, password, first_name, last_name, email, role) VALUES ('$username', '$hashedPassword', '$firstName', '$lastName', '$email', 'user')";
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
 * Update User Account
 */
function db_update_account($id, $username, $firstName, $lastName, $email, $conn) {
    $username = htmlspecialchars(strip_tags(trim($username)));
    $firstName = htmlspecialchars(strip_tags(trim($firstName)));
    $lastName = htmlspecialchars(strip_tags(trim($lastName)));
    
    // Check if user exists
    $check = "SELECT * FROM users WHERE id='$id'";
    $result = mysqli_query($conn, $check);
    
    if (mysqli_num_rows($result) == 0) {
        return [
            'success' => false,
            'data' => null,
            'message' => 'User not found'
        ];
    }

    // Update account info
    $update = "UPDATE users SET username='$username', first_name='$firstName', last_name='$lastName', email='$email' WHERE id='$id'";
    if (mysqli_query($conn, $update)) {
        // Reload session info
        $sql = "SELECT * FROM users WHERE id='$id'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user'] = $user;
        }

        return [
            'success' => true,
            'data' => null,
            'message' => 'Account updated successfully'
        ];
    } else {
        return [
            'success' => false,
            'data' => null,
            'message' => 'Error updating account: ' . mysqli_error($conn)
        ];
    }
}

function db_change_password($id, $oldPassword, $newPassword, $conn) {    
    // Check for user's existence 
    $sql = "SELECT * FROM users WHERE id='$id'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Verify the entered password against the stored hash ***
        if (password_verify($oldPassword, $user['password'])) {
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $update = "UPDATE users SET password='$hashedPassword' WHERE id='$id'";
            if (mysqli_query($conn, $update)) {
                return [
                    'success' => true,
                    'data' => null,
                    'message' => 'Password updated!'
                ];
            }

            return [
                'success' => false,
                'data' => null,
                'message' => 'Could not update password'
            ];
        } else {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Incorrect password, please try again.'
            ];
        }
    }

    // If no user or password_verify fails:
    return [
        'success' => false,
        'data' => null,
        'message' => 'User not found'
    ];
}

/**
 * Get all materials
 */
function db_get_materials($conn, $search = "", $availableOnly = false) {
    $sql = "SELECT * FROM materials WHERE CONCAT(title,author,category) LIKE '%$search%' and available <> 2" . ($availableOnly ? " and available=1" : "");
    
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
    $sql = "SELECT * FROM materials WHERE id=$id AND available <> 2";
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
            'message' => "Material added successfully!"
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
    $sql = "UPDATE materials SET available=2 WHERE id=$id";
    
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

    // Ensure user won't have more than 3 items rented
    $rentCountSql = "SELECT count(*) from borrowed_materials where user_id = $userId and status <> 'Returned'";
    $rentCountResult = mysqli_query($conn, $rentCountSql);

    while($row = mysqli_fetch_assoc($rentCountResult)) {
        if(intval($row['count(*)']) + count($ids) > 3) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Only 3 items can be checked out at a time.'
            ];
        }
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

    // Load user info
    $userSql = "SELECT * FROM users WHERE id=$userId";
    $user = mysqli_query($conn, $userSql);
    if (mysqli_num_rows($user) == 1) {
        $user = mysqli_fetch_assoc($user);
    } else {
        return;
    }

    // Send email to user with order details
    $msg = 'Dear ' . $user['first_name'] . ',</p>' .
    '<p>Your material rental has been confirmed. Please pick it up at:</p>' .
    '<p>TAMUCT Library, 1001 Leadership Pl, WH-101, Killeen, TX 76549</p>';
    send_status($userId, $conn->insert_id, 'Rental Confirmation', $msg, $conn);

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
            DATE(bm.borrowed_date) as borrowed_date,
            bm.status,
            DATE(bm.due_date) as due_date,
            DATE(bm.returned_date) as returned_date,
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
    $selectSql = "SELECT status, material_id, user_id FROM borrowed_materials WHERE id = $rentalId";
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

    // Load user info
    $userSql = "SELECT * FROM users WHERE id=" . $row['user_id'];
    $user = mysqli_query($conn, $userSql);
    if (mysqli_num_rows($user) == 1) {
        $user = mysqli_fetch_assoc($user);
    } else {
        return;
    }

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

    // Update rental status to delivered
    if($newStatus === 'Delivered') {
        $updateSql = "UPDATE borrowed_materials SET status = '$newStatus', delivered_date = NOW(), due_date = DATE_ADD(NOW(), INTERVAL 14 DAY) WHERE id = $rentalId";
        $updateRes = mysqli_query($conn, $updateSql);
        if ($updateRes === false) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Error updating status: ' . mysqli_error($conn)
            ];
        }

        // Send email to user that order was delivered
        $date = new DateTime('now'); 
        $date->modify('+2 weeks');

        $msg = 'Dear ' . $user['first_name'] . ',</p>' .
        '<p>Your material rental has been delivered. It is due on ' . $date->format('Y-m-d') . '. Please return to:</p>' .
        '<p>TAMUCT Library, 1001 Leadership Pl, WH-101, Killeen, TX 76549</p>';
        send_status($user['id'], $conn->insert_id, 'Rental Delivery', $msg, $conn);
    }
    // Update rental status to returned
    if($newStatus === 'Returned') {
        $updateSql = "UPDATE borrowed_materials SET status = '$newStatus', returned_date = NOW() WHERE id = $rentalId";
        $updateRes = mysqli_query($conn, $updateSql);
        if ($updateRes === false) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Error updating status: ' . mysqli_error($conn)
            ];
        }

        $msg = 'Dear ' . $user['first_name'] . ',</p>' .
        '<p>Your material rental has been returned. We hope to see you again!</p>';
        send_status($user['id'], $conn->insert_id, 'Rental Return', $msg, $conn);
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

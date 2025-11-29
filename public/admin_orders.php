<?php
include 'config.php';
include 'api/helpers.php';
include 'lib/database_functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
    echo "Access denied. Admins only.";
    exit;
}

$error   = "";
$message = "";

// handle status change requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setDelivered'])) {

    $rentalId  = (int)$_POST['rental_id'];
    $newStatus = $_POST['new_status'];

    // Use API endpoint to update rental status
    $resp = callAPI('rentals.php', 'PUT', [
        'rental_id' => $rentalId,
        'status' => $newStatus
    ]);

    if (!empty($resp['success'])) {
        $message = $resp['message'] ?? "Status updated.";
    } else {
        $error = $resp['message'] ?? "Error updating status.";
    }
}

// handle status change requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setReturned'])) {

    $rentalId  = (int)$_POST['rental_id'];
    $newStatus = $_POST['new_status'];

    // Use API endpoint to update rental status
    $resp = callAPI('rentals.php', 'PUT', [
        'rental_id' => $rentalId,
        'status' => $newStatus
    ]);

    if (!empty($resp['success'])) {
        $message = $resp['message'] ?? "Status updated.";
    } else {
        $error = $resp['message'] ?? "Error updating status.";
    }
}

// Fetch all orders using API
$response = callAPI('rentals.php?all=1', 'GET');
$orders   = [];

if (!empty($response['success'])) {
    $orders = $response['data'] ?? [];
} else {
    $error = $response['message'] ?? "Error loading orders.";
}
?>
<!doctype html>
<html>
<head>
  <title>Rental Orders</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header><h1>Rental Orders</h1></header>

<div class="container" id="orders-container">
  <p>
    Logged in as <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong> (Admin)
  </p>
  <p>
    <a href="catalog.php">← Back to Catalog</a> |
    <a href="admin.php">Admin Panel</a> |
    <a href="logout.php">Logout</a>
  </p>

  <?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <?php if (!empty($message)): ?>
    <p style="color:green;"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <?php if (!empty($orders)): ?>
    <table class="table" id="all-orders-table">
      <tr>
        <th>Order #</th>
        <th>User</th>
        <th>Title</th>
        <th>Author</th>
        <th>Category</th>
        <th>Borrowed Date</th>
        <th>Due Date</th>
        <th>Returned Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
      <?php foreach ($orders as $order): ?>
        <tr>
          <td><?= (int)$order['rental_id'] ?></td>
          <td><?= htmlspecialchars($order['username']) ?></td>
          <td><?= htmlspecialchars($order['title']) ?></td>
          <td><?= htmlspecialchars($order['author']) ?></td>
          <td><?= htmlspecialchars($order['category']) ?></td>
          <td><?= $order['borrowed_date'] ?></td>
          <td><?= $order['due_date'] ?></td>
          <td><?= $order['returned_date'] ?></td>
          <td><?= htmlspecialchars($order['status']) ?></td>
          <td>
            <?php if ($order['status'] === 'Pending'): ?>
              <form method="post" style="display:inline;">
                <input type="hidden" name="rental_id" value="<?= (int)$order['rental_id'] ?>">
                <input type="hidden" name="new_status" value="Delivered">
                <button type="submit" name="setDelivered">Mark Delivered</button>
              </form>
            <?php elseif ($order['status'] === 'Delivered'): ?>
              <form method="post" style="display:inline;">
                <input type="hidden" name="rental_id" value="<?= (int)$order['rental_id'] ?>">
                <input type="hidden" name="new_status" value="Returned">
                <button type="submit" name="setReturned">Mark Returned</button>
              </form>
            <?php else: ?>
              Returned
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>No rental orders have been submitted yet.</p>
  <?php endif; ?>
</div>
</body>
</html>

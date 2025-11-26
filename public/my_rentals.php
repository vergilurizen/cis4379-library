<?php
include 'config.php';
include 'api/helpers.php';
include 'lib/database_functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Use API endpoint to get user rentals
$response = callAPI('rentals.php', 'GET');
$rentals  = [];
$error    = "";
$message  = "";

if (!empty($response['success'])) {
    $rentals = $response['data'] ?? [];
} else {
    $error = $response['message'] ?? "Error loading your rentals.";
}
?>
<!doctype html>
<html>
<head>
  <title>My Rentals</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header><h1>My Rented Materials</h1></header>

<div class="container">
  <p>
    Welcome, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong>!
  </p>
  <p>
    <a href="catalog.php">← Back to Catalog</a> |
    <a href="cart.php">View Cart</a> |
    <a href="logout.php">Logout</a>
  </p>

  <?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <?php if (!empty($message)): ?>
    <p style="color:green;"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <?php if (!empty($rentals)): ?>
    <table class="table">
      <tr>
        <th>Title</th>
        <th>Author</th>
        <th>Category</th>
        <th>Borrowed Date</th>
		<th>Status</th>
      </tr>
      <?php foreach ($rentals as $rental): ?>
        <tr>
          <td><?= htmlspecialchars($rental['title']) ?></td>
          <td><?= htmlspecialchars($rental['author']) ?></td>
          <td><?= htmlspecialchars($rental['category']) ?></td>
          <td><?= htmlspecialchars($rental['borrowed_date']) ?></td>
		  <td><?= htmlspecialchars($rental['status']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>You have not rented any materials yet.</p>
  <?php endif; ?>
</div>
</body>
</html>

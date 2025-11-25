<?php
include 'config.php';
include 'api/helpers.php';
include 'lib/database_functions.php';

if (!isset($_SESSION['user'])) {
  header("Location: index.php");
  exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
<!doctype html>
<html>
<head>
  <title>Library Catalog</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header><h1>Library Catalog</h1></header>

<div class="container">
  <p>Welcome, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong>!</p>
<p>
  <a href="logout.php">Logout</a> |
  <a href="cart.php">View Cart (<?= count($_SESSION['cart']) ?>)</a> |
  <a href="my_rentals.php">My Rentals</a>
  <?php if ($_SESSION['user']['role'] == 'admin'): ?>
    | <a href="admin.php">Admin Panel</a>
    | <a href="admin_orders.php">View Orders</a>
  <?php endif; ?>
</p>


  <h2>Available Materials</h2>
  <table class="table">
    <tr>
      <th>Title</th>
      <th>Author</th>
      <th>Category</th>
      <th>Action</th>
    </tr>
    <?php
      $response = db_get_materials($conn, true); // only available
      if ($response['success'] && !empty($response['data'])) {
        foreach ($response['data'] as $material) {
          echo "<tr>";
          echo "<td>" . htmlspecialchars($material['title']) . "</td>";
          echo "<td>" . htmlspecialchars($material['author']) . "</td>";
          echo "<td>" . htmlspecialchars($material['category']) . "</td>";
          echo "<td><a href='cart.php?action=add&id=" . intval($material['id']) . "'>Add to Cart</a></td>";
          echo "</tr>";
        }
      } else {
        echo "<tr><td colspan='4'>No materials available</td></tr>";
      }
    ?>
  </table>
</div>
</body>
</html>

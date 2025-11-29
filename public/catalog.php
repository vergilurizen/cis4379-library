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
  <a href="my_rentals.php">My Rentals</a> |
  <a href="manage_account.php?id=<?= $_SESSION['user']['id'] ?>">Manage Account</a>
  <?php if ($_SESSION['user']['role'] == 'admin'): ?>
    | <a href="admin.php">Admin Panel</a>
    | <a href="admin_orders.php">View Orders</a>
  <?php endif; ?>
</p>


  <div class="table-header">
    <div style="width:250px;"><h2>Available Materials</h2></div>
    <div style="width:185px;"></div>
    <form action="" method="GET" class="searchbar">
      <input type="text" name="search" value="<?php if(isset($_GET['search'])) {echo $_GET['search'];} ?>" placeholder="Enter Keyword"/>
      <button type='submit'>Search</button>
    </form>
  </div>
  <table class="table">
    <tr>
      <th>Title</th>
      <th>Author</th>
      <th>Category</th>
      <th>Action</th>
    </tr>
    <?php
      // Use API endpoint to get available materials
      $response = callAPI('materials.php', 'GET', [
        'available' => 1,
        'search' => (isset($_GET['search']) ? $_GET['search'] : '')
      ]);
      
      // Debug: Show error if API call failed
      if (!$response['success']) {
        echo "<tr><td colspan='4' style='color:red;'>Error: " . htmlspecialchars($response['message'] ?? 'Unknown error') . "</td></tr>";
      } else if (!empty($response['data'])) {
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

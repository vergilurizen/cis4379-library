<?php
include 'config.php';
include 'lib/database_functions.php';

if (!isset($_SESSION['user'])) {
  header("Location: index.php");
  exit;
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
  <p><a href="logout.php">Logout</a></p>

  <h2>Available Materials</h2>
  <table class="table">
    <tr><th>Title</th><th>Author</th><th>Category</th></tr>
    <?php
    // Call shared database function directly - no cURL!
    $response = db_get_materials($conn, true); // true = available only
    
    if ($response['success'] && !empty($response['data'])) {
      foreach ($response['data'] as $material) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($material['title']) . "</td>";
        echo "<td>" . htmlspecialchars($material['author']) . "</td>";
        echo "<td>" . htmlspecialchars($material['category']) . "</td>";
        echo "</tr>";
      }
    } else {
      echo "<tr><td colspan='3'>No materials available</td></tr>";
    }
    ?>
  </table>

  <?php if ($_SESSION['user']['role'] == 'admin'): ?>
    <p><a href="admin.php">Go to Admin Panel</a></p>
  <?php endif; ?>
</div>
</body>
</html>

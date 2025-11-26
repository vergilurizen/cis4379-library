<?php 
include 'config.php';
include 'api/helpers.php';
include 'lib/database_functions.php';
?>
<!doctype html>
<html>
<head>
  <title>Admin Panel</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header><h1>Admin Dashboard</h1></header>
<div class="container">
  <?php
  if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    die("<p style='color:red;'>Access denied.</p>");
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];

    // Use API endpoint to add material
    $response = callAPI('materials.php', 'POST', [
      'title' => $title,
      'author' => $author,
      'category' => $category
    ]);

    if ($response['success']) {
      echo "<p style='color:green;'>" . htmlspecialchars($response['message']) . "</p>";
    } else {
      echo "<p style='color:red;'>" . htmlspecialchars($response['message']) . "</p>";
    }
  }
  ?>

  <form method="post">
    <h3>Add New Material</h3>
    <label>Title</label>
    <input type="text" name="title" required>
    <label>Author</label>
    <input type="text" name="author" required>
    <label>Category</label>
    <input type="text" name="category" required>
    <button type="submit">Add Material</button>
  </form>

  <h3>All Materials</h3>
  <table class="table">
    <tr><th>ID</th><th>Title</th><th>Author</th><th>Category</th></tr>
    <?php
    // Use API endpoint to get all materials
    $response = callAPI('materials.php', 'GET');
    
    if ($response['success'] && !empty($response['data'])) {
      foreach ($response['data'] as $material) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($material['id']) . "</td>";
        echo "<td>" . htmlspecialchars($material['title']) . "</td>";
        echo "<td>" . htmlspecialchars($material['author']) . "</td>";
        echo "<td>" . htmlspecialchars($material['category']) . "</td>";
        echo "</tr>";
      }
    } else {
      echo "<tr><td colspan='4'>No materials found</td></tr>";
    }
    ?>
  </table>

  <form method="get" action="export.php">
    <button type="submit">Export to CSV</button>
  </form>
  
  <p><a href="catalog.php">← Back to Catalog</a></p>
</div>
</body>
</html>

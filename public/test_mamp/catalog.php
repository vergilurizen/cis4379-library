<?php
include 'config.php';
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
    $result = mysqli_query($conn, "SELECT * FROM materials WHERE available=1");
    while ($row = mysqli_fetch_assoc($result)) {
      echo "<tr><td>{$row['title']}</td><td>{$row['author']}</td><td>{$row['category']}</td></tr>";
    }
    ?>
  </table>

  <?php if ($_SESSION['user']['role'] == 'admin'): ?>
    <p><a href="admin.php">Go to Admin Panel</a></p>
  <?php endif; ?>
</div>
</body>
</html>

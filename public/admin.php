<?php include 'config.php'; ?>
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

    $sql = "INSERT INTO materials (title, author, category) VALUES ('$title', '$author', '$category')";
    if (mysqli_query($conn, $sql)) {
      echo "<p style='color:green;'>Material added successfully!</p>";
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
    $result = mysqli_query($conn, "SELECT * FROM materials");
    while ($row = mysqli_fetch_assoc($result)) {
      echo "<tr><td>{$row['id']}</td><td>{$row['title']}</td><td>{$row['author']}</td><td>{$row['category']}</td></tr>";
    }
    ?>
  </table>

  <form method="get" action="export_csv.php">
    <button type="submit">Export to CSV</button>
  </form>
</div>
</body>
</html>

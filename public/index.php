<?php 
include 'config.php';
include 'lib/database_functions.php';
?>
<!doctype html>
<html>
<head>
  <title>Library Login</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header><h1>Library Portal</h1></header>

<div class="container">
  <?php
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Call shared database function directly - no cURL!
    $response = db_login($username, $password, $conn);

    if ($response['success']) {
      header("Location: catalog.php");
      exit;
    } else {
      echo "<p style='color:red;'>" . htmlspecialchars($response['message']) . "</p>";
    }
  }
  ?>

  <h2>Login</h2>
  <form method="post">
    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
  </form>

  <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>

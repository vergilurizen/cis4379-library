<?php 
include 'config.php';
include 'lib/database_functions.php';
?>
<!doctype html>
<html>
<head>
  <title>Register Account</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header><h1>Create a Library Account</h1></header>

<div class="container">
  <?php
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Call shared database function directly - no cURL!
    $response = db_register($username, $password, $conn);

    if ($response['success']) {
      echo "<p style='color:green;'>" . htmlspecialchars($response['message']) . " <a href='index.php'>Login here</a>.</p>";
    } else {
      echo "<p style='color:red;'>" . htmlspecialchars($response['message']) . "</p>";
    }
  }
  ?>

  <h2>Register</h2>
  <form method="post">
    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Create Account</button>
  </form>

  <p>Already registered? <a href="index.php">Go back to Login</a></p>
</div>
</body>
</html>

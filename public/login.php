<?php include 'config.php'; ?>
<!doctype html>
<html>
<head>
  <title>Login</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header><h1>Library Login</h1></header>
<div class="container">
  <?php
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
      $row = mysqli_fetch_assoc($result);
      $_SESSION['user'] = $row;
      header("Location: index.php");
      exit;
    } else {
      echo "<p style='color:red;'>Invalid username or password</p>";
    }
  }
  ?>
  <form method="post">
    <label>Username</label>
    <input type="text" name="username" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
  </form>
  <p>Don’t have an account? <a href="register.php">Register</a></p>
</div>
</body>
</html>

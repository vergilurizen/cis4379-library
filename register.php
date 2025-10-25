<?php include 'config.php'; ?>
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

    $check = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) > 0) {
      echo "<p style='color:red;'>Username already exists. Please choose another.</p>";
    } else {
      $insert = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'user')";
      if (mysqli_query($conn, $insert)) {
        echo "<p style='color:green;'>Account created successfully! <a href='index.php'>Login here</a>.</p>";
      } else {
        echo "<p style='color:red;'>Error creating account: " . mysqli_error($conn) . "</p>";
      }
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

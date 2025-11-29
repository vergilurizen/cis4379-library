<?php 
include 'config.php';
include 'api/helpers.php';
include 'lib/database_functions.php';

$errors  = [];
$message = "";

// handle form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rawUsername = $_POST['username'] ?? '';
    $rawPassword = $_POST['password'] ?? '';

    $username = $rawUsername;         // validate without mutating
    $password = $rawPassword;

    // validate username & password
    if (!validate_username($username, $err)) {
        $errors[] = $err;
    }
    if (!validate_password($password, $err)) {
        $errors[] = $err;
    }

    if (empty($errors)) {
        $response = db_login($username, $password, $conn);
        if ($response['success']) {
            header("Location: catalog.php");
            exit;
        } else {
            $message = $response['message'] ?? "Login failed.";
        }
    }
}
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
  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <?php foreach ($errors as $e): ?>
        <p><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($message) && empty($errors)): ?>
    <div class="alert alert-error">
      <p><?= htmlspecialchars($message) ?></p>
    </div>
  <?php endif; ?>

  <form method="post">
    <label>Username</label>
    <input 
      type="text" 
      name="username" 
      required
      value="<?= isset($rawUsername) ? htmlspecialchars($rawUsername) : '' ?>"
    >

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
  </form>

  <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>

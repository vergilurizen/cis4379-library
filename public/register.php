<?php 
include 'config.php';
include 'api/helpers.php';
include 'lib/database_functions.php';

$errors  = [];
$message = "";

// handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rawUsername = $_POST['username'] ?? '';
    $rawPassword = $_POST['password'] ?? '';

    $username = $rawUsername;
    $password = $rawPassword;

    // validate username and password
    if (!validate_username($username, $err)) {
        $errors[] = $err;
    }
    if (!validate_password($password, $err)) {
        $errors[] = $err;
    }

    if (empty($errors)) {
        $response = db_register($username, $password, $conn);

        if ($response['success']) {
            $message = $response['message'] ?? "Account created successfully. You can now log in.";
        } else {
            $errors[] = $response['message'] ?? "Error creating account.";
        }
    }
}
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
  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <?php foreach ($errors as $e): ?>
        <p><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($message) && empty($errors)): ?>
    <div class="alert alert-success">
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

    <button type="submit">Create Account</button>
  </form>

  <p>Already registered? <a href="index.php">Go back to Login</a></p>
</div>
</body>
</html>

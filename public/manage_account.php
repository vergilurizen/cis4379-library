<?php
include 'config.php';
include 'api/helpers.php';
include 'lib/database_functions.php';

if (!isset($_SESSION['user'])) {
  header("Location: manage_account.php");
  exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if(isset($_POST['updateInfo'])) {
    $response = db_update_account($_SESSION['user']['id'], $_POST['username'], $_POST['firstName'], $_POST['lastName'], $_POST['email'], $conn);

    if($response['success']) {
        $updateMessage = $response['message'] ?? "Account updated!";
    } else {
        $updateErrors[] = $response['message'] ?? "Error updating account.";
    }
}

if(isset($_POST['changePassword'])) {
    echo '<p>running</p>';
    $response = db_change_password($_SESSION['user']['id'], $_POST['oldPassword'], $_POST['newPassword'], $conn);

    if($response['success']) {
        $passMessage = $response['message'] ?? "Account updated!";
    } else {
        $passErrors[] = $response['message'] ?? "Error updating account.";
    }
}
?>
<!doctype html>
<html>
<head>
  <title>Manage Account</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header><h1>Manage Account</h1></header>

<div class="container">
  <p>Welcome, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong>!</p>
<p>
  <a href="logout.php">Logout</a> |
  <a href="cart.php">View Cart (<?= count($_SESSION['cart']) ?>)</a> |
  <a href="my_rentals.php">My Rentals</a> |
  <a href="manage_account.php?id=<?= $_SESSION['user']['id'] ?>">Manage Account</a>
  <?php if ($_SESSION['user']['role'] == 'admin'): ?>
    | <a href="admin.php">Admin Panel</a>
    | <a href="admin_orders.php">View Orders</a>
  <?php endif; ?>
</p>


    <h2>Update Account</h2>

    <?php if (!empty($updateErrors)): ?>
        <div class="alert alert-error">
        <?php foreach ($updateErrors as $e): ?>
            <p style='color:red;'><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($updateMessage) && empty($updateErrors)): ?>
        <div>
        <p style='color:green;'><?= htmlspecialchars($updateMessage) ?></p>
        </div>
    <?php endif; ?>

    <form method="post">
        <label>Username</label>
        <input type="text" name="username" value="<?php if(isset($_SESSION['user']['username'])) { echo $_SESSION['user']['username'];} ?>" />

        <label>First Name</label>
        <input type="text" name="firstName" value="<?php if(isset($_SESSION['user']['first_name'])) {echo $_SESSION['user']['first_name'];} ?>" />

        <label>Last Name</label>
        <input type="text" name="lastName" value="<?php if(isset($_SESSION['user']['last_name'])) {echo $_SESSION['user']['last_name'];} ?>"/>

        <label>Email</label>
        <input type="text" name="email" value="<?php if(isset($_SESSION['user']['email'])) {echo $_SESSION['user']['email'];} ?>"/>

        <input name="id" type="hidden" value="<?php $_SESSION['user']['id'] ?>"/>

        <button type="submit" name="updateInfo">Save</button>
    </form>

    <h2>Change Password</h2>

    <?php if (!empty($passErrors)): ?>
        <div class="alert alert-error">
        <?php foreach ($passErrors as $e): ?>
            <p style='color:red;'><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($passMessage) && empty($passErrors)): ?>
        <div>
        <p style='color:green;'><?= htmlspecialchars($passMessage) ?></p>
        </div>
    <?php endif; ?>

    <form method="post">
        <label>Old Password</label>
        <input type="password" name="oldPassword"/>

        <label>New Password</label>
        <input type="password" name="newPassword"/>

        <button type="submit" name="changePassword">Save</button>
    </form>
  
</div>
</body>
</html>

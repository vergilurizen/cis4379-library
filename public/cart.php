<?php
include 'config.php';
include 'api/helpers.php';
include 'lib/database_functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$userId  = intval($_SESSION['user']['id']);
$message = "";
$error   = "";

if (isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $idRaw  = $_GET['id'];

    if (!in_array($action, ['add', 'remove'], true)) {
        $error = "Invalid action.";
    } elseif (!ctype_digit($idRaw) || intval($idRaw) <= 0) {
        $error = "Invalid item ID.";
    } else {
        $materialId = intval($idRaw);

        if ($action === 'add') {
            if (!in_array($materialId, $_SESSION['cart'])) {
                $_SESSION['cart'][] = $materialId;
                $message = "Item added to cart.";
            } else {
                $message = "Item is already in your cart.";
            }
        } elseif ($action === 'remove') {
            $_SESSION['cart'] = array_values(array_filter(
                $_SESSION['cart'],
                function($id) use ($materialId) {
                    return $id != $materialId;
                }
            ));
            $message = "Item removed from cart.";
        }
    }
}

// handle checkout (rent items)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (empty($_SESSION['cart'])) {
        $error = "Your cart is empty.";
    } else {
        $response = db_rent_materials($userId, $_SESSION['cart'], $conn);

        if (!empty($response['success'])) {
    $_SESSION['cart'] = [];
    $message = ($response['message'] ?? "All items rented successfully. Please pick up your materials at the TAMUCT Library, located at 1001 Leadership Pl, WH-101, Killeen, TX 76549, United States.");
        } else {
            $error = $response['message'] ?? "Some items could not be rented.";
        }
    }
}

$cartItems = [];
foreach ($_SESSION['cart'] as $materialId) {
    $resp = db_get_material($materialId, $conn);
    if (!empty($resp['success']) && !empty($resp['data'])) {
        $cartItems[] = $resp['data'];
    }
}
?>
<!doctype html>
<html>
<head>
  <title>Your Cart</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header><h1>Your Cart</h1></header>
<div class="container">
  <p>Welcome, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong>!</p>
  <p>
    <a href="catalog.php">← Back to Catalog</a> |
    <a href="logout.php">Logout</a>
  </p>

  <?php if (!empty($message)): ?>
    <p style="color:green;"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <?php if (!empty($cartItems)): ?>
    <h2>Items in Your Cart</h2>
    <form method="post">
      <table class="table">
        <tr>
          <th>Title</th>
          <th>Author</th>
          <th>Category</th>
          <th>Remove</th>
        </tr>
        <?php foreach ($cartItems as $item): ?>
          <tr>
            <td><?= htmlspecialchars($item['title']) ?></td>
            <td><?= htmlspecialchars($item['author']) ?></td>
            <td><?= htmlspecialchars($item['category']) ?></td>
            <td>
              <a href="cart.php?action=remove&id=<?= intval($item['id']) ?>">Remove</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>

      <button type="submit" name="checkout">Rent Items</button>
    </form>
  <?php else: ?>
    <p>Your cart is currently empty.</p>
  <?php endif; ?>
</div>
</body>
</html>

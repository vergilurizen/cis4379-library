<?php 
include 'config.php';
include 'api/helpers.php';
include 'lib/database_functions.php';
?>
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

  if (isset($_POST["add_material"])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];

    // Use API endpoint to add material
    $response = callAPI('materials.php', 'POST', [
      'title' => $title,
      'author' => $author,
      'category' => $category
    ]);

    if ($response['success']) {
      echo "<p style='color:green;'>" . htmlspecialchars($response['message']) . "</p>";
    } else {
      echo "<p style='color:red;'>" . htmlspecialchars($response['message']) . "</p>";
    }
  }

  if (isset($_POST['action']) && $_POST['id']) {
    if ($_POST['action'] == 'Edit') {
      $response = callAPI('materials.php', 'GET', ['id' => $_POST['id']]);

      if($response['success']) {
        echo '<div class="materialEditor" style="">' . 
          '<form method="post" action="" name="submitEdit" id="editForm">' .
            '<h3>Edit Material</h3>' .
            '<label>Title</label>' .
            '<input type="text" name="editTitle" required value="' . $response['data']['title'] . '">' .
            '<label>Author</label>' .
            '<input type="text" name="editAuthor" required value="' . $response['data']['author'] . '">' .
            '<label>Category</label>' .
            '<input type="text" name="editCategory" required value="' . $response['data']['category'] . '">' .
            '<div class="form-buttons">'.
              '<button type="submit" name="save_edit" id="saveEditBtn">Save</button>' .
              '<button type="submit" name="cancel" id="cancelEditBtn">Cancel</button>'.
            '</div>' .
            '<input type="hidden" name="id" value=' . $response['data']['id'] . "/>" .
            '<input type="hidden" name="available" value=' . $response['data']['available'] . "/>" .
          '</form>' .
        '</div>';
      } else {
        echo "<p style='color:red;'>" . htmlspecialchars($response['message']) . "</p>";
      }
    }

    if($_POST['action'] == 'Delete') {
      $response = callAPI('materials.php', 'DELETE', ['id' => $_POST['id']]);

      if ($response['success']) {
        echo "<p style='color:green;'>" . htmlspecialchars($response['message']) . "</p>";
      } else {
        echo "<p style='color:red;'>" . htmlspecialchars($response['message']) . "</p>";
      }
    }
  }

  if(isset($_POST['save_edit'])) {
    $response = callAPI('materials.php', 'PUT', [
      'id' => $_POST['id'],
      'title' => $_POST['editTitle'],
      'author' => $_POST['editAuthor'],
      'category' => $_POST['editCategory'],
      'available' => $_POST['available']
    ]);

    if ($response['success']) {
      echo "<p style='color:green;'>" . htmlspecialchars($response['message']) . "</p>";
    } else {
      echo "<p style='color:red;'>" . htmlspecialchars($response['message']) . "</p>";
    }
  }
  ?>

  <form method="post" id="addMaterialForm">
    <h3>Add New Material</h3>
    <label>Title</label>
    <input type="text" name="title" required>
    <label>Author</label>
    <input type="text" name="author" required>
    <label>Category</label>
    <input type="text" name="category" required>
    <button type="submit" name="add_material">Add Material</button>
  </form>

  <div class="table-header">
    <div style="width:250px;"><h3>All Materials</h3></div>
    <div style="width:185px;"></div>
    <form action="" method="GET" class="searchbar">
      <input type="text" name="search" value="<?php if(isset($_GET['search'])) {echo $_GET['search'];} ?>" placeholder="Enter Keyword"/>
      <button type='submit'>Search</button>
    </form>
  </div>
  <table class="table">
    <tr>
      <!--<th>ID</th>-->
      <th>Title</th>
      <th>Author</th>
      <th>Category</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
    <?php
    // Use API endpoint to get all materials
    $response = callAPI('materials.php', 'GET', [
      'search' => (isset($_GET['search']) ? $_GET['search'] : '')
    ]);
    
    if ($response['success'] && !empty($response['data'])) {
      foreach ($response['data'] as $material) {
        echo "<tr>";
        // echo "<td>" . htmlspecialchars($material['id']) . "</td>";
        echo "<td>" . htmlspecialchars($material['title']) . "</td>";
        echo "<td>" . htmlspecialchars($material['author']) . "</td>";
        echo "<td>" . htmlspecialchars($material['category']) . "</td>";
        echo "<td>" . ($material['available'] == 1 ? 'Available' : 'Unavailable') . "</td>";
        echo "<td>" .
          '<form method="post" action="" style="padding-right:-100px;">' .
            '<input type="submit" name="action" value="Edit" id="materialEditBtn"/>' .
            '<input type="submit" name="action" value="Delete" id="materialDeleteBtn"/>' .
            '<input type="hidden" name="id" value=' . htmlspecialchars($material['id']) . "/>" .
          "</form>" .
        "</td>";
        echo "</tr>";
      }
    } else {
      echo "<tr><td colspan='4'>No materials found</td></tr>";
    }
    ?>
  </table>

  <form method="get" action="export.php">
    <button type="submit">Export to CSV</button>
  </form>
  
  <p><a href="catalog.php">← Back to Catalog</a></p>
</div>
</body>
</html>

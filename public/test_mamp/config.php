<?php
$host = "localhost";
$user = "root";
$pass = "root"; //change root/root if your creds for MAMP are different
$db   = "db_lms";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
  die("Database connection failed: " . mysqli_connect_error());
}

session_start();
?>

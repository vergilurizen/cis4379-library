<?php
include 'config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
  die("Access denied.");
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=materials_report.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Title', 'Author', 'Category', 'Available']);

$result = mysqli_query($conn, 
  "SELECT 
	  id,
    title,
    author,
    category,
    (
        CASE
        	WHEN available THEN 'Available'
        	ELSE 'Unavailable'
       	END
    )
  FROM materials 
  WHERE available <> 2; "
);
while ($row = mysqli_fetch_assoc($result)) {
  fputcsv($output, $row);
}
fclose($output);
exit;

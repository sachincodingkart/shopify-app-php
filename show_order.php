<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
use phpish\shopify;
// require __DIR__ . '/conf.php';
$sql_query = "SELECT * FROM order_data";
$style ="<link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css\">";
$data_table="<div class='container'><h1 class='h1'>Order Data</h1><table class='.table-hover .table-dark'><tr><th>Order Id</th><th>Order Email</th><th>Created At</th><th>Updated At</th><th>Total Price</th><th>User Id</th><th>Item Ids</th><th>Item Titles</th><th>Shop</th></tr>";
$rows = "";
if ($result = mysqli_query($conn, $sql_query)) {
    // Fetch one and one row
    while ($row = mysqli_fetch_row($result)) {
        $rows = $rows."<tr><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td><td>".$row[5]."</td><td>".$row[6]."</td><td>".$row[7]."</td><td>".$row[8]."</td><td>".$row[9]."</td></tr>";
    }
    mysqli_free_result($result);
}
$data_table = $style."".$data_table."".$rows."</table></div>";
print_r($data_table);
?>
<?php
http_response_code(200);
header('HTTP/1.0 200 OK');
header("Status: 200 OK");
session_start();
require __DIR__ . '/vendor/autoload.php';
use phpish\shopify;

if (!empty($_SESSION['shop'])) {
  $shop = $_SESSION['shop'];
} else {
  $shop = $_SESSION['shop'] = $_GET['shop'];
}


$update_query = "UPDATE config SET app_status = '0' WHERE shop = '" . $shop . "'";
$result = mysqli_query($conn, $update_query);

$update_query = "UPDATE donationIds SET donation_id='',status='0',donation_sendbox_secret='',donation_production_secret='',donation_environmnet='' WHERE shop='" . $shop . "'";
$result = mysqli_query($conn, $update_query);
?>
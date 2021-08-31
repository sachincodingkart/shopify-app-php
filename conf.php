<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
define('SHOPIFY_APP_API_KEY', '414a26c18bef3b6fae73f2f6becedaff');
define('SHOPIFY_APP_SHARED_SECRET', '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');

// SHOPIFY_SITE_URL = app main directory URL
define('SHOPIFY_SITE_URL', 'https://5b1e-103-21-55-66.ngrok.io/count-down/');

// DATABASE CONNECTION STRING
define('SHOPIFY_DB_HOST', 'localhost');
define('SHOPIFY_DB_USER', 'debian-sys-maint');
define('SHOPIFY_DB_PASS', 'F9jAObw2qvCHKZfV');
define('SHOPIFY_DB_NAME', 'countDonwTimer');

// CONNECTING DATABASE
$conn = mysqli_connect(SHOPIFY_DB_HOST, SHOPIFY_DB_USER, SHOPIFY_DB_PASS, SHOPIFY_DB_NAME);

// DATABASE CONNECTION ERROR HANDLING
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
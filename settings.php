<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

use phpish\shopify;

// require __DIR__ . '/conf.php';

if (!empty($_GET['shop']) && !empty($_GET['code'])) {
  //Get app directory name
  $dir = explode("/", getcwd());

  $shop = $_GET['shop']; //shop name

  //get permanent access token
  $access_token = shopify\access_token($_GET['shop'], SHOPIFY_APP_API_KEY, SHOPIFY_APP_SHARED_SECRET, $_GET['code']);
  //save the signature and shop name to the current session
  if(isset($_GET['signature'])) {
    $shopify_signature       = $_SESSION['shopify_signature'] = $_GET['signature'];
  }
  $_SESSION['shop']        = $shop;
  $_SESSION['oauth_token'] = $access_token;

  //Create config table
  // $config_table_name = "shopify_App_" . end($dir);
$config_table_name = "config";

  $create_table_sql = "CREATE TABLE IF NOT EXISTS config (
        id INT NOT NULL AUTO_INCREMENT,
        PRIMARY KEY(id),
        shop varchar(255),
        oauth_token varchar(255),
        app_status varchar(122) 
    )";

  if (mysqli_query($conn, $create_table_sql)) {
    // echo "Table MyGuests created successfully";
  } else {
    echo "Error creating table: " . mysqli_error($conn);
  }

  $sql = "SELECT * FROM config WHERE shop ='$shop'";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) <= 0) {
    $sql = "INSERT INTO config (shop,oauth_token,app_status) VALUES ('$shop','$access_token','0')";
  } else {
    $sql = "UPDATE config SET oauth_token = '$access_token' WHERE shop ='$shop' ";
  }
  mysqli_query($conn, $sql);

  //app path 
  $current_script_name = explode("/", $_SERVER['SCRIPT_NAME']);
  if (($key = array_search(end($current_script_name), $current_script_name)) !== false) {
    unset($current_script_name[$key]);
  }
  $app_path = implode("/", $current_script_name);

  $confirmation_url = "https://$shop/admin/apps/" . SHOPIFY_APP_API_KEY . $app_path . "/admin_dashboard.php?oauth_token=" . $access_token . "&shop=" . $shop . "&table=" . $config_table_name;

  die("<script> top.location.href='$confirmation_url'</script>");
}

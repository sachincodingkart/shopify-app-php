<?php
require __DIR__.'/conf.php';


$config_table_name = "shopify_App_".end($dir);

$id=$_GET['id'];
echo $id;

// sql to delete a record
$sql = "DELETE FROM $config_table_name WHERE id ='".$id."'";

if (mysqli_query($conn, $sql)) {
    // echo "Record deleted successfully";
} else {
    echo "Error deleting record: " . mysqli_error($conn);
}

header("location: admin_dashboard.php");
$shop = $_GET['shop'];

$sql = "SELECT * FROM $config_table_name WHERE shop ='$shop'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while($row = mysqli_fetch_assoc($result)) {
      $oauth_token = $row['oauth_token'];
    }
} else {
    // echo "0 results";
}




$shopify = shopify\client($shop, SHOPIFY_APP_API_KEY, $oauth_token);
$get_slider_images = $shopify('POST /admin/redirects.json', array() );
?>

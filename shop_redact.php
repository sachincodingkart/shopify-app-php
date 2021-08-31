<?php
header('Access-Control-Allow-Origin: *');
http_response_code(200);
header('HTTP/1.0 200 OK');
header("Status: 200 OK");
require 'conf.php';

if(empty($_REQUEST['shop']))
	die();
$shop        = $_REQUEST['shop'];
$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$productData = file_get_contents('php://input');
$verified    = verify_webhook($productData, $hmac_header);

function verify_webhook($data, $hmac_header)
{
    $calculated_hmac = base64_encode(hash_hmac('sha256', $data, SHOPIFY_APP_SHARED_SECRET, true));
    return ($hmac_header == $calculated_hmac);
}
$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$data        = file_get_contents('php://input'); // get webhook data
$data        = str_replace("'","||",$data);
$get_shop_detail = json_decode($data,true);
$shop_id 			= $get_shop_detail['id'];
$shop_domain 		= $get_shop_detail['domain'];


$delete_seller_details 		= "DELETE FROM `Tpop_shopify_app_customization_sellerAddressDetails` WHERE `shop` = '$shop_domain'";
$delete_seller_details_rs 	= mysqli_query($conn,$delete_seller_details);

$delete_card_details 		= "DELETE FROM `Tpop_shopify_app_customization_Card_Details` WHERE `shop` = '$shop_domain'";
$delete_card_details_rs 	= mysqli_query($conn,$delete_seller_details);

$delete_productDetails 		= "DELETE FROM `Tpop_shopify_app_customization_manufacture_product` WHERE `shop` = '$shop_domain'";
$delete_productDetails_rs 	= mysqli_query($conn,$delete_seller_details);

$delete_app_language 		= "DELETE FROM `Tpop_shopify_app_Language` WHERE `shop` = '$shop_domain'";
$delete_app_language_rs 	= mysqli_query($conn,$delete_seller_details);

$delete_allorders 			= "DELETE FROM `Tpop_shopify_app_customization_allorders` WHERE `shop` = '$shop_domain'";
$delete_allorders_rs 		= mysqli_query($conn,$delete_seller_details);
?>



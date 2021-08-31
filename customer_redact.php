<?php
header('Access-Control-Allow-Origin: *');
http_response_code(200);
header('HTTP/1.0 200 OK');
header("Status: 200 OK");
require 'conf.php';

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
$orders_to_redact  	= $get_shop_detail['orders_to_redact'];
// $orders_to_redact = explode(',', $orders_to_redact);
foreach ($orders_to_redact as $key => $order_id) {
	$delete_order_details 		= "DELETE FROM `Tpop_shopify_app_customization_allorders` WHERE `shop` = '$shop_domain' AND `order_id` = '$order_id'";
	$delete_order_details_rs 	= mysqli_query($conn,$delete_seller_details);
}
?>



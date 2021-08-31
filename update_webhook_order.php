<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
use phpish\shopify;
// require __DIR__ . '/conf.php';

$shop = $_REQUEST['shop'];
// get the data from curl
$data = file_get_contents('php://input');
$json = json_decode($data, true);

$item_id = "";
$item_title = "";
$check = "false";
$current_date = date("Y-m-d");

$sql_query = "SELECT * FROM order_data Where order_id = '".$json["id"]."'";

if ($result = mysqli_query($conn, $sql_query)) {
    $item_count = count($json["line_items"]);
    for($j = 0 ; $j < $item_count ; $j++){
        $item_id = $json["line_items"][$j]["id"].",".$item_id;
        $item_title = $json["line_items"][$j]["title"].",".$item_title;
        if( $json["line_items"][$j]["title"] == "Ind-Donation" ){
            $check = "true";
        }
        if($j == $item_count - 1 && $check == "true"){
            $sql_query = "UPDATE order_data SET order_email = '".$json["email"]."', order_created_date = '".$json["created_at"]."' , order_updated_date = '".$current_date."' , order_total_price = '".$json["total_price"]."' , order_user_id = '".$json["user_id"]."' , order_item_ids = '".$item_id."' , order_item_titles = '".$item_title."'";
            $result = mysqli_query($conn, $sql_query);
            $set_data = $sql_query."   _____   ".$result;
            $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
            fwrite($myfile, $set_data);
            fclose($myfile);
        }
    }
}else{

// retrive the order data one by and store in the DB one by one
    $item_count = count($json["line_items"]);
    for($j = 0 ; $j < $item_count ; $j++){
        $item_id = $json["line_items"][$j]["id"].",".$item_id;
        $item_title = $json["line_items"][$j]["title"].",".$item_title;
        if( $json["line_items"][$j]["title"] == "Ind-Donation" ){
            $check = "true";
        }
        if($j == $item_count - 1 && $check == "true"){
            $sql_query = "INSERT INTO order_data (order_id,order_email,order_created_date,order_updated_date,order_total_price,order_user_id,order_item_ids,order_item_titles) VALUES ('".$json["id"]."','".$json["email"]."','".$json["created_at"]."','".$json["updated_at"]."','".$json["total_price"]."','".$json["user_id"]."','".$item_id."','".$item_title."')";
            $result = mysqli_query($conn, $sql_query);
            $set_data = $sql_query."   _____   ".$result;
            $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
            fwrite($myfile, $set_data);
            fclose($myfile);
        }
    }
}
?>

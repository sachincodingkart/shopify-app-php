<?php
http_response_code(200);
header('HTTP/1.0 200 OK');
header("Status: 200 OK");
header("Access-Control-Allow-Origin: *");
// session_start();
require __DIR__ . '/vendor/autoload.php';

use phpish\shopify;

// require __DIR__ . '/conf.php';


// header('Access-Control-Allow-Origin: *');
// http_response_code(200);
// header('HTTP/1.0 200 OK');
// header("Status: 200 OK");
// require __DIR__ . '/conf.php';

$data = file_get_contents('php://input'); // get webhook data
// $data = str_replace("'", "", $data);
if(!empty($data)) {
$data = str_replace("'", " ", $data);
$json = json_decode($data, true);

$item_id = "";
$item_title = "";
$check = false;
$created_date;
$updated_date;
$donation_price;
$donation_id;
$fullname = $json["billing_address"]["first_name"] . " " . $json["billing_address"]["last_name"];
$item_count = count($json["line_items"]);
$json_daata = json_encode($json, true);
// file_put_contents("checkIt.txt", $json_daata);

$shop = $_REQUEST['shop'];
$get_environment = '';
$sendbox_secret;
$production_secret;
$current_secret;

$check_id_query = "SELECT * FROM donationIds WHERE shop='" . $shop . "';";
if ($result = mysqli_query($conn, $check_id_query)) {
  while ($row = mysqli_fetch_row($result)) {
    $sendbox_secret = $row[4];
    $production_secret = $row[5];
    $get_environment = $row[6];
    $donation_id = $row[2];
  }
  mysqli_free_result($result);
}
if ($get_environment == 'sandbox') {
  $filedata = array("status" => "success", "secret" => $sendbox_secret);
  $current_secret = $sendbox_secret;
} else {
  $filedata = array("status" => "success", "secret" => $production_secret);
  $current_secret = $production_secret;
}


$data_file = json_encode($filedata);
// $curl = "http://portal.aidonline.net/api/donation/" . $donation_id;

// $ch = curl_init($curl);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
// curl_setopt($ch, CURLOPT_POSTFIELDS, $data_file);
// $response = curl_exec($ch);
// echo 'Status-Code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE);
for ($j = 0; $j < $item_count; $j++) {
  $item_id = $json["line_items"][$j]["id"] . "," . $item_id;
  $item_title = $json["line_items"][$j]["title"] . "," . $item_title;
  if ($json["line_items"][$j]["title"] == "Thank you for your generosity !!") {
    $donation_price = $json["line_items"][$j]["price"];
    if(isset($json["line_items"][$j]["properties"][0]["value"])) {
      $donation_id = $json["line_items"][$j]["properties"][0]["value"];
      if(isset($json['financial_status']) && $json['financial_status'] == 'paid') {
	      $curl = "https://portal.aidonline.net/api/donation/" . $donation_id.'?status=success&secret='.$current_secret;
	      file_put_contents('logsWebhook.txt', "Request URL " . $curl . PHP_EOL, FILE_APPEND | LOCK_EX);
	      $ch = curl_init($curl);
	      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	      // curl_setopt($ch, CURLOPT_POSTFIELDS, $data_file);
	      $response = curl_exec($ch);
	      file_put_contents('logsWebhook.txt', "Order Webhook Called " . print_r($response, true) . PHP_EOL, FILE_APPEND | LOCK_EX);
	      curl_close($ch);
	      $check = true;
	  }
	  else {
	  	file_put_contents('logsWebhook.txt', "Order Webhook Called. Payment status " . $json['financial_status'] . " donaion id " . $donation_id . PHP_EOL, FILE_APPEND | LOCK_EX);
	  }
    }
  }
  if ($j == $item_count - 1 && $check) {
    // mail 
    $to = trim($json["email"]);
    $subject = "Thank you for your contribution !";
    $txt = "Dear " . $json["billing_address"]["first_name"] . ",
        
        We are grateful for your donation on the website of " . $shop . ".
        Aid On provides innovative ways for you to donate and connect with social causes.
        We provide real-time information and the ability to track your donations. 
        Kindly visit our projects page to know more about the social projects, https://www.aidonline.net.

        Thank you again !
		
	";
    $headers = "From: aidonindia@gmail.com";
    $curl = 'https://portal.aidonline.net/api/donation/';
    mail($to, $subject, $txt);
    $temp = explode("T", $json["created_at"]);
    $created_date = $temp[0];
    $temp = explode("T", $json["updated_at"]);
    $updated_date = $temp[0];
    $sql_query = "INSERT INTO order_data (order_id,order_email,order_created_date,order_updated_date,order_total_price,order_user_id,order_item_ids,order_item_titles,shop,donation,donation_id,full_name) VALUES ('" . $json["id"] . "','" . $json["email"] . "','" . $created_date . "','" . $updated_date . "','" . $json["total_price"] . "','" . $json["user_id"] . "','" . $item_id . "','" . $item_title . "','" . $shop . "','" . $donation_price . "','" . $donation_id . "','" . $fullname . "')";
    $result = mysqli_query($conn, $sql_query);
  }
}
}
return;

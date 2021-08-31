<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
use phpish\shopify;
// require __DIR__ . '/conf.php';
//  Note : This is mendatory code to make the $shopify object work currectly
if (!empty($_SESSION['shop']))
{
    $shop = $_SESSION['shop'];
}
else
{
    $shop = $_SESSION['shop'] = $_GET['shop'];
}

if (!empty($_SESSION['oauth_token']))
{
    $oauth_token = $_SESSION['oauth_token'];
}
else
{
    $dir = explode("/", getcwd());
    $config_table_name = "shopify_App_" . end($dir);
    $sql = "SELECT * FROM $config_table_name WHERE shop ='$shop' ";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0)
    {
        // output data of each row
        while ($row = mysqli_fetch_assoc($result))
        {
            $oauth_token = $row['oauth_token'];
        }
    }
    else
    {
    }
}

$shopify = shopify\client($shop, SHOPIFY_APP_API_KEY, $oauth_token);

// $json = file_get_contents('php://input');
// Converts it into a PHP object
file_put_contents("order-creation.txt", $_REQUEST['req_data']);
$order_arr = json_decode($_REQUEST['req_data'], true);

// $order_arr=json_decode($data,true);
$querry = "SELECT * FROM `shopify_App_goalminus_authenticate` WHERE shop ='$shop'";
$querry_result = mysqli_query($conn, $querry);
$result_array = mysqli_fetch_assoc($querry_result);

$order_status = 0;
$line_items_arr = [];
foreach ($order_arr['line_items'] as $key)
{
    if ($key['title'] == "Seeisu")
    {
        $order_status = 1;
        $goalminus_charge = $key['price'];
    }

    $tempArr = array(
        'product_id' => $key['id'],
        'product_title' => $key['title'],
        'product_price' => $key['price']
    );
    array_push($line_items_arr, $tempArr);
}

if ($order_status == 1)
{
    $product_arr = array(
        'key' => $result_array['key_value'],
        'shop' => $shop,
        'date_of_order' => $order_arr['created_at'],
        'order_number' => $order_arr['order_number'],
        'cost' => $order_arr['total_line_items_price'],
        'retailer_currency' => $order_arr['currency'],
        'customer_name' => $order_arr['customer']['first_name'],
        'customer_email' => $order_arr['email'],
        'product_list' => $line_items_arr,
        'shipping_Address' => array(
            'first_name' => $order_arr['customer']['first_name'],
            'last_name' => $order_arr['customer']['last_name'],
            'country_name' => $order_arr['shipping_address']['country'],
            'city_name' => $order_arr['shipping_address']['city'],
            'zip_Code' => $order_arr['shipping_address']['zip'],
            'address_1' => $order_arr['shipping_address']['address1'],
        ) ,
        'charges' => array(
            'shipping_charge' => $order_arr['shipping_lines'][0]['price'],
            'goalminus_charge' => $goalminus_charge
        )
    );
    $curl = 'http://buildmycode.com/i/goalminus/admin/wp-json/gminus/v1/order';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $curl);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($product_arr));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type:application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1); //timeout in seconds
    $result = curl_exec($ch);
    curl_close($ch);
    $resp = json_decode($result, true);
    if ($resp['message'] == "success")
    {
        $update_order = array(
            'order' => array(
                'id' => $order_arr['id'],
                'tags' => 'seeisu',
            ) ,
        );
        try
        {
            $product_created = $shopify('PUT /admin/api/2020-07/orders/' . $order_arr['id'] . '.json', array() , $update_order);
            // print_r($product_created);
            
        }
        catch(Exception $e)
        {
            echo $e;

        }
    }
}


<?php
header("Access-Control-Allow-Origin: *");
session_start();
require __DIR__ . '/vendor/autoload.php';

use phpish\shopify;

// require __DIR__ . '/conf.php';

$oauth_token = $_GET['token'];
$id = $_GET['id'];
$shop = $_GET['shop'];
$price = $_GET['price'];

$query = "SELECT * FROM `config` WHERE shop ='$shop'";
$querry_rs = mysqli_query($conn, $query);
$querry_arr = mysqli_fetch_assoc($querry_rs);
$oauth_token = $querry_arr['oauth_token'];
$productDbId = '';
$shopify = shopify\client($shop, SHOPIFY_APP_API_KEY, $oauth_token);
$check_product = false;
$get_all_product = $shopify('GET /admin/api/2020-07/products.json?title=Thank%20you%20for%20your%20generosity%20!!');
$check_count_product = count($get_all_product);
if ($check_count_product > 0) {
    $check_product = true;
    $productDbId = $get_all_product[0]['id'];
    $variant_id = $get_all_product[0]['variants'][0]['id'];
    $variant_name = $get_all_product[0]['title'];
    $json_data = array("product_variant_id" => $variant_id, "product_variant_name" => $variant_name);
    echo json_encode($json_data);
} else {
    $check_product = false;
}
if ($check_product != true) {


    $variantArray = array(
        array(
            'title' => 'Default Title',
            "price" => $price,
            "inventory_management" => "shopify",
            "inventory_policy" => "continue",
            "taxable" => false,
        )
    );

    $create_product = array(
        'product' => array(
            "title" => "Thank you for your generosity !!",
            "body_html" => $id,
            "tags" => "Thank you for your generosity !!",
            "vendor" => 'Thank you for your generosity !!',
            "variants" => $variantArray,
            "product_type" => "shipping",
            "images" => array(
                0 => array(
                    "src" => "https://appsworld.website/sapp/t1/aidOn-donation/images/aid-on-donation.png"
                )
            )
        )
    );
    try {
        $product_created = $shopify('POST /admin/api/2020-07/products.json', array(), $create_product);
        $product_variant_id = $product_created['variants'][0]['id'];
        $product_variant_name = $product_created['title'];
        $json_data = array("product_variant_id" => $product_variant_id, "product_variant_name" => $product_variant_name);
        echo json_encode($json_data);
    } catch (Exception $e) {
        echo "<pre>";
        print_r($e);
        die();
    }
}

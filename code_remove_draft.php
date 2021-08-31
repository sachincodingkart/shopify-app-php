<?php

http_response_code(200);
header('HTTP/1.0 200 OK');
header("Status: 200 OK");
session_start();
require __DIR__ . '/vendor/autoload.php';

use phpish\shopify;

// require __DIR__ . '/conf.php';
// Note : This is mendatory code to make the $shopify object work currectly
$shop = $_GET['shop'];
$api_key = SHOPIFY_APP_API_KEY;

$query = "SELECT * FROM `config` WHERE shop ='$shop'";
$querry_rs = mysqli_query($conn, $query);
$querry_arr = mysqli_fetch_assoc($querry_rs);
$oauth_token = $querry_arr['oauth_token'];

$shopify = shopify\client($shop, SHOPIFY_APP_API_KEY, $oauth_token);
// script tag

$ScriptCount = $shopify('GET /admin/script_tags/count.json');

//  Note : This is js file which will load the proxy file to your store front end
$app_load = SHOPIFY_SITE_URL . "js/app_load_goalminus.js?shop=" . $shop . "";

$ScriptDetails = $shopify('GET /admin/script_tags.json');




if ($ScriptCount >= 1) {
    foreach ($ScriptDetails as $key => $value) {
        foreach ($value as $key1 => $value1) {
            if ($key1 == 'id') {
                $ScriptDelete = $shopify("DELETE /admin/script_tags/" . $ScriptDetails[$key]['id'] . ".json");
            }
        }
    }
}
$product_id = '';
$get_all_product = $shopify('GET /admin/api/2020-07/products.json');
$check_product = false;
$count = count($get_all_product);
for ($i = 0; $i < $count; $i++) {
    if ($get_all_product[$i]['title'] == 'Thank you for your generosity !!') {
        $product_id = $get_all_product[$i]['id'];
    }
}

// ------------------------------------------
try {
    if ($product_id != '') {
        $product_delete = $shopify("DELETE /admin/api/2020-10/products/" . $product_id . ".json");
    }
} catch (Exception $e) {
}


$get_script_tag = $shopify('GET /admin/api/2020-07/themes.json');
foreach ($get_script_tag as $key) {
    if ($key['role'] == "main") {
        $theme_id = $key['id'];
    }
}

$assets_data = $shopify('GET /admin/api/2020-07/themes/' . $theme_id . '/assets.json?asset[key]=templates/cart.liquid');


$custom_html = '<div><div id="aid-on-widget" style="display: flex; width: 100%; margin-right: -1%; padding: 6px;"></div><p class="cart-attribute__field" ><input id="donation_id" type="hidden" name="attributes[donation_id]" value="{{ cart.attributes["donation_id"] }}"></p><div class="loading">Loading&#8230;</div><div class="content"><h3></h3></div><script type="text/javascript" src="http://cdn.aidonline.net/index.min.js"></script></div>';
$check_code = strpos($assets_data['value'], '<div><div id="aid-on-widget" style="display: flex; width:');
if ($check_code == false) {
    $check_code = false;
} else {
    $check_code = true;
}
if (strpos($assets_data['value'], "</form>") == false) {
    $check_cart = "false";
} else {
    $check_cart = "true";
}
if (strpos($assets_data['value'], '<input type="submit" name="checkout"') !== false) {
    $check_checkout = "true";
} else {
    $check_checkout = "false";
}
if ($check_cart == "true" && $check_code == true) {


    if ($cart_flag == 0) {

        if ($check_checkout == "true") {
            $pieces = explode($custom_html, $assets_data['value']);

            $html_string = $pieces[0] . "" . $pieces[1];

            $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
            $final_string = $js_string[0];
            $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                'asset' => array(
                    // "key" => "sections/cart-template.liquid",
                    "key" => "templates/cart.liquid",
                    "value" => $final_string,
                )
            ));
            echo "Success";
        } else {
            $pieces = explode($custom_html, $assets_data['value']);

            $html_string = $pieces[0] . "" . $pieces[1];
            $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
            $final_string = $js_string[0];
            $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                'asset' => array(
                    // "key" => "sections/cart-template.liquid",
                    "key" => "templates/cart.liquid",
                    "value" => $final_string,
                )
            ));
            echo "Success";
        }
    } elseif ($cart_flag == 1 && $shopify_theme_id != $theme_id) {
        // check checkbox is already or not
        if (strpos($assets_data['value'], "<div id='aid-on-widget' style='display: flex; width: 100%; justify-content:") == false) {
            $code_check = false;
        } else {
            $code_check = true;
        }
        if ($code_check) {
            // $check_checkout = "true";
            if ($check_checkout == "true") {
                $pieces = explode($custom_html, $assets_data['value']);

                $html_string = $pieces[0] . "" . $pieces[1];
                $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
                $final_string = $js_string[0];
                $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                    'asset' => array(
                        // "key" => "sections/cart-template.liquid",
                        "key" => "templates/cart.liquid",
                        "value" => $final_string,
                    )
                ));
                echo "Success";
            } else {
                $pieces = explode($custom_html, $assets_data['value']);

                $html_string = $pieces[0] . "" . $pieces[1];
                $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
                $final_string = $js_string[0];
                $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                    'asset' => array(
                        // "key" => "sections/cart-template.liquid",
                        "key" => "templates/cart.liquid",
                        "value" => $final_string,
                    )
                ));
                echo "Success";
            }
        } else {

            if ($check_checkout == "true") {
                $pieces = explode($custom_html, $assets_data['value']);

                $html_string = $pieces[0] . "" . $pieces[1];
                $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
                $final_string = $js_string[0];
                $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                    'asset' => array(
                        // "key" => "sections/cart-template.liquid",
                        "key" => "templates/cart.liquid",
                        "value" => $final_string,
                    )
                ));
                echo "Success";
            } else {
                $pieces = explode($custom_html, $assets_data['value']);

                $html_string = $pieces[0] . "" . $pieces[1];
                $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
                $final_string = $js_string[0];
                $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                    'asset' => array(
                        // "key" => "sections/cart-template.liquid",
                        "key" => "templates/cart.liquid",
                        "value" => $final_string,
                    )
                ));
                echo "Success";
            }
        }
    } else {
        if ($check_checkout == "true") {
            $pieces = explode($custom_html, $assets_data['value']);

            $html_string = $pieces[0] . "" . $pieces[1];
            $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
            $final_string = $js_string[0];
            $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                'asset' => array(
                    // "key" => "sections/cart-template.liquid",
                    "key" => "templates/cart.liquid",
                    "value" => $final_string,
                )
            ));
            echo "Success";
        } else {
            $pieces = explode($custom_html, $assets_data['value']);

            $html_string = $pieces[0] . "" . $pieces[1];
            $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
            $final_string = $js_string[0];
            $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                'asset' => array(
                    // "key" => "sections/cart-template.liquid",
                    "key" => "templates/cart.liquid",
                    "value" => $final_string,
                )
            ));
            echo "Success";
        }
    }
} elseif ($check_cart == "false") {


    $assets_data_2 = $shopify('GET /admin/api/2020-07/themes/' . $theme_id . '/assets.json?asset[key]=sections/cart-template.liquid');
    $check_code_2 = strpos($assets_data_2['value'], '<div><div id="aid-on-widget" style="display: flex; width:');
    if ($check_code_2 == true) {

        if (strpos($assets_data_2['value'], '<input type="submit" name="checkout"') !== false) {
            $check_checkout = "true";
        } else {
            $check_checkout = "false";
        }
        if ($cart_flag == 0) {
            if ($check_checkout == "true") {
                $pieces = explode($custom_html, $assets_data_2['value']);

                $html_string = $pieces[0] . "" . $pieces[1];
                $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
                $final_string = $js_string[0];
                $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                    'asset' => array(
                        "key" => "sections/cart-template.liquid",
                        "value" => $final_string,
                    )
                ));
                echo "Success";
            } else {
                $pieces = explode($custom_html, $assets_data_2['value']);

                $html_string = $pieces[0] . "" . $pieces[1];
                $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
                $final_string = $js_string[0];
                $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                    'asset' => array(
                        "key" => "sections/cart-template.liquid",
                        "value" => $final_string,
                    )
                ));
                echo "Success";
            }
        } elseif ($cart_flag == 1 && $shopify_theme_id != $theme_id) {
            // check if checkbox is already or not
            if (strpos($assets_data_2['value'], '<div><div id="aid-on-widget" style="display: flex; width:') == true) {
                // $check_checkout = "true";
                if ($check_checkout == "true") {
                    $pieces = explode($custom_html, $assets_data_2['value']);

                    $html_string = $pieces[0] . "" . $pieces[1];
                    $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
                    $final_string = $js_string[0];
                    $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                        'asset' => array(
                            "key" => "sections/cart-template.liquid",
                            "value" => $final_string,
                        )
                    ));
                    echo "Success";
                } else {
                    $pieces = explode($custom_html, $assets_data_2['value']);

                    $html_string = $pieces[0] . "" . $pieces[1];
                    $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
                    $final_string = $js_string[0];
                    $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                        'asset' => array(
                            "key" => "sections/cart-template.liquid",
                            "value" => $final_string,
                        )
                    ));
                    echo "Success";
                }
            } else {

                if ($check_checkout == "true") {
                    $pieces = explode($custom_html, $assets_data_2['value']);

                    $html_string = $pieces[0] . "" . $pieces[1];
                    $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
                    $final_string = $js_string[0];
                    $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                        'asset' => array(
                            "key" => "sections/cart-template.liquid",
                            "value" => $final_string,
                        )
                    ));
                    echo "Success";
                } else {
                    $pieces = explode($custom_html, $assets_data_2['value']);

                    $html_string = $pieces[0] . "" . $pieces[1];
                    $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
                    $final_string = $js_string[0];
                    $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                        'asset' => array(
                            "key" => "sections/cart-template.liquid",
                            "value" => $final_string,
                        )
                    ));
                    echo "Success";
                }
            }
        } else {
            if ($check_checkout == "true") {
                $pieces = explode($custom_html, $assets_data_2['value']);

                $html_string = $pieces[0] . "" . $pieces[1];
                $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
                $final_string = $js_string[0];
                $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                    'asset' => array(
                        "key" => "sections/cart-template.liquid",
                        "value" => $final_string,
                    )
                ));
                echo "Success";
            } else {
                $pieces = explode($custom_html, $assets_data_2['value']);

                $html_string = $pieces[0] . "" . $pieces[1];
                $js_string = explode("<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>", $html_string);
                $final_string = $js_string[0];
                $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
                    'asset' => array(
                        "key" => "sections/cart-template.liquid",
                        "value" => $final_string,
                    )
                ));
                echo "Success";
            }
        }
    }
}


$update_query = "UPDATE config SET app_status = '0' WHERE shop = '" . $shop . "'";
$result = mysqli_query($conn, $update_query);

$update_query = "UPDATE donationIds SET donation_id='',status='0',donation_sendbox_secret='',donation_production_secret='',donation_environmnet='' WHERE shop='" . $shop . "'";
$result = mysqli_query($conn, $update_query);

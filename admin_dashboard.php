<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

use phpish\shopify;
?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.shopify.com/s/assets/external/app.js"></script>

<?php
//  Note : This is mendatory code to make the $shopify object work currectly

$shop = $_GET['shop'];
$oauth_token = $_GET['oauth_token'];
$api_key = SHOPIFY_APP_API_KEY;


$shopify = shopify\client($shop, SHOPIFY_APP_API_KEY, $oauth_token);
$ngurl = SHOPIFY_SITE_URL;

$check_query = "SELECT * FROM config WHERE shop='" . $shop . "'";
$status;
if ($result = mysqli_query($conn, $check_query)) {
  while ($row = mysqli_fetch_row($result)) {
    $status = $row[3];
  }
  mysqli_free_result($result);
}
$check_id = false;
$get_donation_id;
$get_donation_environmnet = '';
$get_sendbox_secret = '';
$get_prodcution_secret = '';
$check_id_query = "SELECT * FROM donationIds WHERE shop='" . $shop . "' AND status='1'";
if ($result = mysqli_query($conn, $check_id_query)) {
  while ($row = mysqli_fetch_row($result)) {
    $get_donation_id = $row[2];
    $get_donation_environmnet = $row[6];
    $get_sendbox_secret = $row[4];
    $get_prodcution_secret = $row[5];
    $check_id = true;
  }
  mysqli_free_result($result);
}
// $webHResp = $shopify('GET /admin/api/2020-10/webhooks.json');
// echo "<pre>";
// print_r($webHResp);
// echo "</pre>";
if ($check_id) {
  if ($status == 0) {


    try {
      // webhook registration
      $webhook_register = array(
        'webhook' => array(
          'topic' => 'orders/create',
          'address' => $ngurl . 'order_wehbook.php?shop=' . $shop,
          'format' => 'json',
        ),
      );
      $webhooks_resp = $shopify('POST /admin/api/2020-07/webhooks.json', array(), $webhook_register);
      // uninstall webhook 
      $webhook_uninstall = array(
        'webhook' => array(
          'topic' => 'app/uninstalled',
          'address' => $ngurl . 'uninstall_wehbook.php?shop=' . $shop,
          'format' => 'json',
        ),
      );
      $webhooks_uninstall_resp = $shopify('POST /admin/api/2020-07/webhooks.json', array(), $webhook_uninstall);
    } catch (Exception $e) {
      // echo "<pre>";
      // print_r("Please Reinstall the App");
      // echo json_encode(["error"]);
      // echo $e->getMessage();
      // echo "</pre>";
      // die();
    }
    $update_query = "UPDATE config SET app_status = '1' WHERE shop = '" . $shop . "'";

    $result = mysqli_query($conn, $update_query);
  }

  // -----------------------------------------
  // script tag
  $ScriptCount = $shopify('GET /admin/script_tags/count.json');

  //  Note : This is js file which will load the proxy file to your store front end
  $app_load = SHOPIFY_SITE_URL . "js/app_load_goalminus.js?shop=" . $shop . "";


  $ScriptDetails = $shopify('GET /admin/script_tags.json');

  if ($ScriptCount == 0) {
    $js_scripts = $shopify('POST /admin/script_tags.json', array(), array(
      'script_tag' => array(
        "event" => "onload",
        "src" => $app_load,
      )
    ));
  }
  if ($ScriptCount > 1) {
    foreach ($ScriptDetails as $key => $value) {
      foreach ($value as $key1 => $value1) {
        if ($key1 == 'id') {
          $ScriptDelete = $shopify("DELETE /admin/script_tags/" . $ScriptDetails[$key]['id'] . ".json");
        }
      }
    }

    $js_scripts = $shopify('POST /admin/script_tags.json', array(), array(
      'script_tag' => array(
        "event" => "onload",
        "src" => $app_load,
      )
    ));
  }
  // ------------------------------------------


  $get_script_tag = $shopify('GET /admin/api/2020-07/themes.json');
  foreach ($get_script_tag as $key) {
    if ($key['role'] == "main") {
      $theme_id = $key['id'];
    }
  }

  $assets_data = $shopify('GET /admin/api/2020-07/themes/' . $theme_id . '/assets.json?asset[key]=templates/cart.liquid');

  $custom_html = '<div><div id="aid-on-widget" style="display: flex; width: 100%; margin-right: -1%; padding: 6px;"></div><p class="cart-attribute__field" ><input id="donation_id" type="hidden" name="attributes[donation_id]" value="{{ cart.attributes["donation_id"] }}"></p><div class="loading">Loading&#8230;</div><div class="content"><h3></h3></div><script type="text/javascript" src="http://cdn.aidonline.net/index.min.js"></script></div>';
  // $custom_html_none = "<div id='aid-on-widget' style='display: flex; width: 100%; margin-right: -1%; padding: 6px;'></div><p class='cart-attribute__field' style='display:none'><label for='donation_id'>Your name</label><input id='donation_id' type='text' name='attributes[donation_id]' value='{{ cart.attributes['donation_id'] }}'></p><div class='loading'>Loading&#8230;</div><div class='content'><h3></h3></div><script type='text/javascript' src='http://cdn.aidonline.net/index.min.js'></script>";
  $custom_js = " <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
    <script type='text/javascript'>
        var id_data;
        var api_auth = '" . $oauth_token . "';
        var shop = '" . $shop . "';
        var varient_id;
        var cart_len;
        var cart_data;
        var check_donation = false;

        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const check = urlParams.get('check');
            const val_id = urlParams.get('id');
              var test = false;	
          $('body *').each(function() {
                 if ($(this).text().trim() === 'Thank you for your generosity !!') { 
                   test = true; return; 
                 } });
                  if(test == true){
                             setTimeout(function() {
                    $('#aid-on-widget-checkbox').prop('checked', true);
                }, 1800);
                  }else{
                  }
        });
        var Aidon = {
            vendor: '" . $get_donation_id . "',
        }
        aidOnCallback = function(response) {
            var price = response['response']['donation']['amount'];
            var id = response['response']['donation']['id'];
            var url = '" . $ngurl . "create_product.php';
            $('#donation_id').val(id);
            adjust_cart = [];
            if ($('#aid-on-widget-checkbox').is(':checked')) {
                $.ajax({
                    url: '" . $ngurl . "create_product.php?token=' + api_auth + '&price=' + price + '&shop=' + shop + '&id=' + id + '',
                    method: 'GET',
                    success: function(data) {
                        data = JSON.parse(data);
                        varient_id = data['product_variant_id'];
                        if ($('#aid-on-widget-checkbox').is(':checked')) {
                          $.ajax({
                            type: 'GET',
                            url: '/cart.js',
                            cache: false,
                            dataType: 'json',
                            success: function(cart) {
                              cart_data = cart['items'];
                              cart_len = cart_data.length-1;
                              for (var i = cart_len; i >= 0 ; i--) {
                                adjust_cart.push({
                                  quantity: cart_data[i]['quantity'],
                                  id: cart_data[i]['id']
                                });
                              }
                              $.ajax({
                                type: 'POST',
                                url: '/cart/clear.js',
                                cache: false,
                                dataType: 'json',
                                success: function(cart) {
                                  adjust_cart.splice(0, 0, {
                                    quantity: 1,
                                    id: varient_id,
                                    properties: {
                                      'donation_id': id
                                    }
                                  });
                                  var getresp = jQuery.post('/cart/add.js', {
                                    items: adjust_cart
                                  });
                                  setTimeout(function() {
                                    window.location.reload();
                                  }, 500);
                                }
                              });
                            }
                          });
                        }
                    }
                });
            } else {
                var varient = parseInt(varient_id);
                var get_res = jQuery.post('/cart/change.js', 'line=' + cart_len + '&quantity=0');
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            }
        }
        aidOnRemovedCallback = function(response) {}
        $(document).on('click', '#aid-on-widget-checkbox', function() {
            if ($('#aid-on-widget-checkbox').is(':checked')) {} else {
                   $.ajax({
                     type: 'GET',
                     url: '/cart.js',
                     cache: false,
                     dataType: 'json',
                     success: function(data) {
                       for(var i = 0; i < data['items'].length ; i++){
                         if(data['items'][i]['title'] == 'Thank you for your generosity !!'){
                           var get_res = jQuery.post('/cart/change.js', 'id=' + data['items'][i]['id'] + '&quantity=0');
                           setTimeout(function() {
                             window.location.reload();
                           }, 1000);
                         }
                       }
                     }
                   });
            }
        });
        $('.cart__remove').on('click',function(){
          var n = $(this).children('a').attr('aria-label');
         if(n.includes('Thank you for your generosity !!')){
           setTimeout(function() {
              window.location.reload();
            }, 1000);
         }else{
         }
        });
    </script>
            <style>
            .button{
                float: right;
            }
            .cart .btn {
                    float: right !important;
                } 
                /* Absolute Center Spinner */
                .loading {
                display:none;
                position: fixed;
                z-index: 999;
                height: 2em;
                width: 2em;
                overflow: show;
                margin: auto;
                top: 0;
                left: 0;
                bottom: 0;
                right: 0;
                }

                /* Transparent Overlay */
                .loading:before {
                content: '';
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                    background: radial-gradient(rgba(20, 20, 20,.8), rgba(0, 0, 0, .8));

                background: -webkit-radial-gradient(rgba(20, 20, 20,.8), rgba(0, 0, 0,.8));
                }

                /* :not(:required) hides these rules from IE9 and below */
                .loading:not(:required) {
                /* hide 'loading...' text */
                font: 0/0 a;
                color: transparent;
                text-shadow: none;
                background-color: transparent;
                border: 0;
                }
                li.product-details__item.product-details__item--property{
                  display: none !important;
                }
                .loading:not(:required):after {
                content: '';
                display: block;
                font-size: 10px;
                width: 1em;
                height: 1em;
                margin-top: -0.5em;
                -webkit-animation: spinner 150ms infinite linear;
                -moz-animation: spinner 150ms infinite linear;
                -ms-animation: spinner 150ms infinite linear;
                -o-animation: spinner 150ms infinite linear;
                animation: spinner 150ms infinite linear;
                border-radius: 0.5em;
                -webkit-box-shadow: rgba(255,255,255, 0.75) 1.5em 0 0 0, rgba(255,255,255, 0.75) 1.1em 1.1em 0 0, rgba(255,255,255, 0.75) 0 1.5em 0 0, rgba(255,255,255, 0.75) -1.1em 1.1em 0 0, rgba(255,255,255, 0.75) -1.5em 0 0 0, rgba(255,255,255, 0.75) -1.1em -1.1em 0 0, rgba(255,255,255, 0.75) 0 -1.5em 0 0, rgba(255,255,255, 0.75) 1.1em -1.1em 0 0;
                box-shadow: rgba(255,255,255, 0.75) 1.5em 0 0 0, rgba(255,255,255, 0.75) 1.1em 1.1em 0 0, rgba(255,255,255, 0.75) 0 1.5em 0 0, rgba(255,255,255, 0.75) -1.1em 1.1em 0 0, rgba(255,255,255, 0.75) -1.5em 0 0 0, rgba(255,255,255, 0.75) -1.1em -1.1em 0 0, rgba(255,255,255, 0.75) 0 -1.5em 0 0, rgba(255,255,255, 0.75) 1.1em -1.1em 0 0;
                }

                /* Animation */

                @-webkit-keyframes spinner {
                0% {
                    -webkit-transform: rotate(0deg);
                    -moz-transform: rotate(0deg);
                    -ms-transform: rotate(0deg);
                    -o-transform: rotate(0deg);
                    transform: rotate(0deg);
                }
                100% {
                    -webkit-transform: rotate(360deg);
                    -moz-transform: rotate(360deg);
                    -ms-transform: rotate(360deg);
                    -o-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
                }
                @-moz-keyframes spinner {
                0% {
                    -webkit-transform: rotate(0deg);
                    -moz-transform: rotate(0deg);
                    -ms-transform: rotate(0deg);
                    -o-transform: rotate(0deg);
                    transform: rotate(0deg);
                }
                100% {
                    -webkit-transform: rotate(360deg);
                    -moz-transform: rotate(360deg);
                    -ms-transform: rotate(360deg);
                    -o-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
                }
                @-o-keyframes spinner {
                0% {
                    -webkit-transform: rotate(0deg);
                    -moz-transform: rotate(0deg);
                    -ms-transform: rotate(0deg);
                    -o-transform: rotate(0deg);
                    transform: rotate(0deg);
                }
                100% {
                    -webkit-transform: rotate(360deg);
                    -moz-transform: rotate(360deg);
                    -ms-transform: rotate(360deg);
                    -o-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
                }
                @keyframes spinner {
                0% {
                    -webkit-transform: rotate(0deg);
                    -moz-transform: rotate(0deg);
                    -ms-transform: rotate(0deg);
                    -o-transform: rotate(0deg);
                    transform: rotate(0deg);
                }
                100% {
                    -webkit-transform: rotate(360deg);
                    -moz-transform: rotate(360deg);
                    -ms-transform: rotate(360deg);
                    -o-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
                }
            </style>
  
  ";

  $check_code = strpos($assets_data['value'], '<div><div id="aid-on-widget" style="display: flex; width: 100%; margin-right: -1%; padding: 6px;">');

  if (strpos($assets_data['value'], "</form>") !== false) {
    $check_cart = "true";
  } else {
    $check_cart = "false";
  }
  if (strpos($assets_data['value'], '<input type="submit" name="checkout"') !== false) {
    $check_checkout = "true";
  } else {
    $check_checkout = "false";
  }
  if ($check_cart == "true" && $check_code == false) {


    if ($cart_flag == 0) {

      if ($check_checkout == "true") {
        $pieces = explode('<div class="cart__footer">', $assets_data['value']);

        $final_string = $pieces[0] . $custom_html . '<div class="cart__footer">' . $pieces[1];
        $final_string = $final_string . " " . $custom_js;
        $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
          'asset' => array(
            // "key" => "sections/cart-template.liquid",
            "key" => "templates/cart.liquid",
            "value" => $final_string,
          )
        ));
      } else {
        $x = explode('<div class="cart__footer">', $assets_data['value']);

        $final_string = $x[0] . $custom_html . '<div class="cart__footer">' . $x[1];
        $final_string = $final_string . " " . $custom_js;

        $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
          'asset' => array(
            // "key" => "sections/cart-template.liquid",
            "key" => "templates/cart.liquid",
            "value" => $final_string,
          )
        ));
      }
    } elseif ($cart_flag == 1 && $shopify_theme_id != $theme_id) {
      // check checkbox is already or not
      if (strpos($assets_data['value'], '<div class="custom_html"') !== false) {
        // $check_checkout = "true";
        $pieces = explode('<div class="cart__footer">', $assets_data['value']);
        if ($check_checkout == "true") {
          $second_part = explode('<div class="cart__footer">', $pieces[1]);
          $final_string = $pieces[0] . $custom_html . '<div class="cart__footer">' . $second_part[1];
          $final_string = $final_string . " " . $custom_js;

          $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
            'asset' => array(
              // "key" => "sections/cart-template.liquid",
              "key" => "templates/cart.liquid",
              "value" => $final_string,
            )
          ));
        } else {
          $second_part = explode('<div class="cart__footer">', $pieces[1]);
          $final_string = $pieces[0] . $custom_html . '<div class="cart__footer">' . $second_part[1];
          $final_string = $final_string . " " . $custom_js;

          $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
            'asset' => array(
              // "key" => "sections/cart-template.liquid",
              "key" => "templates/cart.liquid",
              "value" => $final_string,
            )
          ));
        }
      } else {

        if ($check_checkout == "true") {
          $pieces = explode('<div class="cart__footer">', $assets_data['value']);

          $final_string = $pieces[0] . $custom_html . '<div class="cart__footer">' . $pieces[1];
          $final_string = $final_string . " " . $custom_js;
          $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
            'asset' => array(
              "key" => "templates/cart.liquid",
              "value" => $final_string,
            )
          ));
        } else {
          $x = explode('<div class="cart__footer">', $assets_data['value']);

          $final_string = $x[0] . $custom_html . '<div class="cart__footer">' . $x[1];
          $final_string = $final_string . " " . $custom_js;

          $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
            'asset' => array(
              // "key" => "sections/cart-template.liquid",
              "key" => "templates/cart.liquid",
              "value" => $final_string,
            )
          ));
        }
      }
    } else {
      $pieces = explode('<div class="cart__footer">', $assets_data['value']);
      if ($check_checkout == "true") {
        $second_part = explode('<div class="cart__footer">', $pieces[1]);
        $final_string = $pieces[0] . $custom_html . '<div class="cart__footer">' . $second_part[1];
        $final_string = $final_string . " " . $custom_js;

        $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
          'asset' => array(
            // "key" => "sections/cart-template.liquid",
            "key" => "templates/cart.liquid",
            "value" => $final_string,
          )
        ));
      } else {
        $second_part = explode('<div class="cart__footer">', $pieces[1]);
        $final_string = $pieces[0] . $custom_html . '<div class="cart__footer">' . $second_part[1];
        $final_string = $final_string . " " . $custom_js;

        $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
          'asset' => array(
            // "key" => "sections/cart-template.liquid",
            "key" => "templates/cart.liquid",
            "value" => $final_string,
          )
        ));
      }
    }
  } elseif ($check_cart == "false") {


    $assets_data_2 = $shopify('GET /admin/api/2020-07/themes/' . $theme_id . '/assets.json?asset[key]=sections/cart-template.liquid');
    $check_code_2 = strpos($assets_data_2['value'], '<div><div id="aid-on-widget" style="display: flex; width: 100%; margin-right: -1%; padding: 6px;">');
    if ($check_code_2 == false) {

      if (strpos($assets_data_2['value'], '<div class="cart__footer">') !== false) {
        $check_checkout = "true";
      } else {
        $check_checkout = "false";
      }
      if ($cart_flag == 0) {
        if ($check_checkout == "true") {
          $pieces = explode('<div class="cart__footer">', $assets_data_2['value']);


          $final_string = $pieces[0] . $custom_html . '<div class="cart__footer">' . $pieces[1];
          $final_string = $final_string . " " . $custom_js;
          // sssss
          $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
            'asset' => array(
              "key" => "sections/cart-template.liquid",
              "value" => $final_string,
            )
          ));
          //update flag value

        } else {
          $x = explode('<div class="cart__footer">', $assets_data_2['value']);

          $final_string = $x[0] . $custom_html . '<div class="cart__footer">' . $x[1];
          $final_string = $final_string . " " . $custom_js;

          $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
            'asset' => array(
              "key" => "sections/cart-template.liquid",
              "value" => $final_string,
            )
          ));
          //update flag value


        }
      } elseif ($cart_flag == 1 && $shopify_theme_id != $theme_id) {
        // check if checkbox is already or not
        if (strpos($assets_data_2['value'], '<div class="custom_html"') !== false) {
          // $check_checkout = "true";
          $pieces = explode('<div class="cart__footer">', $assets_data_2['value']);
          if ($check_checkout == "true") {
            $second_part = explode('<div class="cart__footer">', $pieces[1]);
            $final_string = $pieces[0] . $custom_html . '<div class="cart__footer">' . $second_part[1];
            $final_string = $final_string . " " . $custom_js;
            $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
              'asset' => array(
                "key" => "sections/cart-template.liquid",
                "value" => $final_string,
              )
            ));
          } else {
            $second_part = explode('<div class="cart__footer">', $pieces[1]);
            $final_string = $pieces[0] . $custom_html .  '<div class="cart__footer">' . $second_part[1];
            $final_string = $final_string . " " . $custom_js;
            $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
              'asset' => array(
                "key" => "sections/cart-template.liquid",
                "value" => $final_string,
              )
            ));
          }
        } else {

          if ($check_checkout == "true") {
            $pieces = explode('<div class="cart__footer">', $assets_data_2['value']);
            $final_string = $pieces[0] . $custom_html . '<div class="cart__footer">' . $pieces[1];
            $final_string = $final_string . " " . $custom_js;
            $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
              'asset' => array(
                "key" => "sections/cart-template.liquid",
                "value" => $final_string,
              )
            ));
          } else {
            $x = explode('<div class="cart__footer">', $assets_data_2['value']);
            $final_string = $x[0] . $custom_html . '<div class="cart__footer">' . $x[1];
            $final_string = $final_string . " " . $custom_js;
            $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
              'asset' => array(
                "key" => "sections/cart-template.liquid",
                "value" => $final_string,
              )
            ));
          }
        }
      } else {
        $pieces = explode('<div class="cart__footer">', $assets_data_2['value']);
        if ($check_checkout == "true") {
          $second_part = explode('<div class="cart__footer">', $pieces[1]);
          $final_string = $pieces[0] . $custom_html . '<div class="cart__footer">' . $second_part[1];
          $final_string = $final_string . " " . $custom_js;
          $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
            'asset' => array(
              "key" => "sections/cart-template.liquid",
              "value" => $final_string,
            )
          ));
        } else {
          $second_part = explode('<div class="cart__footer">', $pieces[1]);
          $final_string = $pieces[0] . $custom_html .  '<div class="cart__footer">' . $second_part[1];
          $final_string = $final_string . " " . $custom_js;
          echo $final_string;
          $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array(), array(
            'asset' => array(
              "key" => "sections/cart-template.liquid",
              "value" => $final_string,
            )
          ));
        }
      }
    }
  }
}

?>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<link rel="stylesheet" href="http://cdn.datatables.net/1.10.2/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type="text/javascript" src="http://cdn.datatables.net/1.10.2/js/jquery.dataTables.min.js"></script>

<?php
if ($check_id) {
  $sql_query = "SELECT * FROM order_data WHERE shop='" . $shop . "'";
}
if (!$check_id) {
?>

  <div class="container" style="margin-top: 20px; width: 65%;">
    <h3 style="text-align: center;">Aid On Portal Settings</h3>
    <div class="form-group">
      <div style="margin-bottom: 20px">
        <label for="exampleInputEmail1">Aid On - Vendor Id</label>
        <input type="text" class="form-control" onkeyup="onchangeFields()" onchange="onchangeFields()" placeholder="Enter Aid On - Vendor Id" id="donationIdText" required>
        <small id="emailHelp" class="form-text text-muted"><i>Please double check the ID before activate.</i></small>
        <p class="text-danger" id="error_text_donation" class="faild_donation"></p>
      </div>
      <div>
        <label for="exampleInputEmail1">Aid On - Environment Secret Keys</label>
      </div>
      <div style="margin-bottom: 20px">
        <div>
          <input type="text" class="form-control" onkeyup="onchangeFields()" onchange="onchangeFields()" placeholder="Enter Aid On - Sandbox Secret Key" id="donationSecretSendboxText" required>
          <p class="text-danger" id="error_text_sendbox_donation_secret" class="faild_donation"></p>
        </div>
        <div style="margin-top:18px">
          <input type="text" class="form-control" onkeyup="onchangeFields()" onchange="onchangeFields()" placeholder="Enter Aid On - Production Secret Key" id="donationSecretProductionText" required>
          <p class="text-danger" id="error_text_production_donation_secret" class="faild_donation"></p>
        </div>
      </div>
      <div>
        <label for="exampleInputEmail1">Aid On - Environment</label>
      </div>
      <div style="display:flex">
        <div class="custom-control custom-radio">
          <input type="radio" class="custom-control-input" onclick="onchangeEnvironment(event)" value="sandbox" id="sendbox_environment" name="environment" checked>
          <label class="custom-control-label" for="defaultUnchecked">Sandbox</label>
        </div>
        <div class="custom-control custom-radio" style="margin-left:20px;">
          <input type="radio" class="custom-control-input" onclick="onchangeEnvironment(event)" value="production" id="production_environment" name="environment">
          <label class="custom-control-label" for="defaultUnchecked">Production</label>
        </div>
      </div>
      <div style="float: right;">
        <button type="submit" id="submit_donation_id" data-toggle="modal" data-target="#exampleModalCenter" class="btn btn-primary" style="margin-top: 20px;">Save</button>
      </div>
      <div style="text-align:center; width:100%; margin-top:40px">
        don't have details yet?<button type="button" class="btn btn-link" data-toggle="modal" data-target="#contactModal">contact us</button>
      </div>
    </div>
  </div>
  <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Confirmation</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Please double check the vendor id. It can't be changed after save.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" onclick="onSubmitId()" class="btn btn-primary">Confirm</button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="contactModal" style="display:none" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle"></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div>
            <h4 style="margin-bottom: 15px;">You can directly contact us using the following channels</h4>
            <p>Email: <strong><a href="mailto:aidonindia@gmail.com">aidonindia@gmail.com</a></strong></p>
            <p>Mob no: <strong><a href="tel:+91 9930265684">+91 9930265684</a></strong></p>
            <p>Website: <strong><a href="https://www.aidonline.net" title="www.aidonline.net">www.aidonline.net</a></strong></p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
<?php } ?>
<?php if ($check_id) { ?>
  <div class='container'>
    <div class='row'>
      <div class='col-md-8'>
        <h1 class='h1'>Donation and Charity Widget</h1>
        <p class='h5'>Aid On - Vendor Id: <strong><?php echo $get_donation_id; ?></strong></p>
        <p class='h5'>Aid On - Environment: <strong><?php echo $get_donation_environmnet; ?></strong></p>
        <p style="margin-bottom: 30px;"><button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#editsecret"><span class="glyphicon glyphicon-pencil"></span> Change</button></p>
      </div>
      <div class='col-md-4 d-flex align-items-center' style="text-align: center;padding-top: 15px;">
        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#uninstallDonationConfirmation" id="remove_donation">Unistall Donation</button>
      </div>
    </div>
    <table class='table table-bordered table-hover' style='margin-top: 38px;' id="myTable">
      <thead class='black white-text'>
        <tr>
          <th style='text-align:center; padding: 11px;'>Order Id</th>
          <th style='text-align:center; padding: 11px;'>Donation Id</th>
          <th style='text-align:center'>Full Name</th>
          <th style='text-align:center; padding: 11px;'>Email</th>
          <th style='text-align:center; padding: 11px;'>Order Total</th>
          <th style='text-align:center;font-weight: 600; padding: 11px;'>Donation Total</th>
        </tr>
      </thead>
      <tbody>
        <?
    if ($result = mysqli_query($conn, $sql_query)) {
        // Fetch one and one row
        while ($row = mysqli_fetch_row($result)) {
     
            ?>
        <tr>
          <td style='padding:8px;text-align: center;'><?php echo $row[1]; ?></td>
          <td style='padding:8px;text-align: center;'><?php echo $row[11]; ?></td>
          <td style='padding:8px;text-align: center;'><?php echo $row[12]; ?></td>
          <td style='padding:8px;text-align: center;'><?php echo $row[2]; ?></td>
          <td style='padding:8px;text-align: center;'><?php echo $row[5]; ?></td>
          <td style='padding:8px;font-weight: 600;text-align: center;'><?php echo $row[10]; ?></td>
        </tr>
        <?}
        mysqli_free_result($result);
    }
    ?>
      </tbody>
    </table>
    <div class="modal fade" id="uninstallDonationConfirmation" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Are you sure?</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Do you really want to remove the Aid On - Donation widget?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" onclick="removeCode()" class="btn btn-danger">Confirm</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="editsecret" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle"><strong>Switch Environment</strong></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div style="display:flex">
              <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" onclick="onchangeEnvironment(event)" value="sandbox" id="donation_sendbox" name="environment">
                <label class="custom-control-label" for="defaultUnchecked">Sandbox</label>
              </div>
              <div class="custom-control custom-radio" style="margin-left:20px;">
                <input type="radio" class="custom-control-input" onclick="onchangeEnvironment(event)" value="production" id="donation_production" name="environment">
                <label class="custom-control-label" for="defaultUnchecked">Production</label>
              </div>
            </div>
            <div>
              <div>
                <input type="text" class="form-control" placeholder="Enter Aid On - Sandbox Secret Key" value="<?php echo $get_sendbox_secret; ?>" id="changeSendboxsecret" required>
                <p class="text-danger" id="error_text_sendbox_donation_secret" class="faild_donation"></p>
              </div>
              <div style="margin-top:18px">
                <input type="text" class="form-control" placeholder="Enter Aid On - Production Secret Key" value="<?php echo $get_prodcution_secret; ?>" id="changeproductionboxsecret" required>
                <p class="text-danger" id="error_text_production_donation_secret" class="faild_donation"></p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" onclick="changeSecret()" class="btn btn-primary">Confirm</button>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php } ?>
<script>
  var donation_environment = '';

  $(document).ready(function() {

    <?php if ($get_donation_environmnet == '' || $get_donation_environmnet == 'sandbox') { ?>
      donation_environment = "sandbox";
      $('#donation_sendbox').prop('checked', true);
      $('#changeSendboxsecret').prop('disabled', false);
      $('#changeproductionboxsecret').prop('disabled', true);

    <?php
    } else { ?>
      donation_environment = "<?php echo $get_donation_environmnet; ?>";
      $('#donation_production').prop('checked', true);
      $('#changeproductionboxsecret').prop('disabled', false);
      $('#changeSendboxsecret').prop('disabled', true);

    <?php  } ?>
    console.log(donation_environment);
    $('#submit_donation_id').prop('disabled', true);

    $('#myTable').dataTable({
      "pageLength": 30
    });
    var chil = $("#myTable_length").children('label').children('select').empty()
      .append('<option selected="selected" value="30">30</option><option value="50">50</option><option  value="100">100</option>');


  });

  function removeCode() {
    var url = "<?php echo $ngurl; ?>code_remove_draft.php?shop=<?php echo $shop; ?>";
    $.ajax({
      type: 'GET',
      url: url,
      success: function(data) {
        location.reload();

      }
    })
  }

  function onchangeFields() {
    var donationid = $('#donationIdText').val();
    var send_secret = $('#donationSecretSendboxText').val();
    var prod_secret = $('#donationSecretProductionText').val();
    if (donationid == '') {
      $('#error_text_donation').html("Please Enter Donation Id");
    } else {
      $('#error_text_donation').html("");
    }
    if (send_secret == '') {
      $('#error_text_sendbox_donation_secret').html("Please Enter Sandbox secret");
    } else {
      $('#error_text_sendbox_donation_secret').html("");
    }
    if (prod_secret == '') {
      $('#error_text_production_donation_secret').html("Please Enter Production secret");
    } else {
      $('#error_text_production_donation_secret').html("");
    }
    if (donationid == '' || send_secret == '' || prod_secret == '') {
      $('#submit_donation_id').prop('disabled', true);
    } else {
      $('#submit_donation_id').prop('disabled', false);
      $('#donation_id_text').html(donationid);
    }
  }

  function onchangeEnvironment(e) {
    donation_environment = e.target.value;
    if (donation_environment == 'sandbox') {
      $('#changeproductionboxsecret').prop('disabled', true);
      $('#changeSendboxsecret').prop('disabled', false);
    } else {
      $('#changeproductionboxsecret').prop('disabled', false);
      $('#changeSendboxsecret').prop('disabled', true);
    }
  }

  function changeSecret() {
    var sendbox_secret = $('#changeSendboxsecret').val();
    var production_secret = $('#changeproductionboxsecret').val();
    var url = "<?php echo $ngurl; ?>changeSecret.php"
    var send_data = {
      shop: "<?php echo $shop; ?>",
      environment_select: donation_environment,
      send_sec: sendbox_secret,
      prod_sec: production_secret
    }
    $.ajax({
      type: 'POST',
      url: url,
      data: send_data,
      success: function(data) {
        var get_data = JSON.parse(data)
        if (get_data['res'] == 'inserted' || get_data['res'] == 'updated') {
          alert("Environment Switched Successfully");
          location.reload();

        } else {
          alert("Environment Unable to Change plaese try again");
        }
      }
    })
  }


  function onSubmitId() {
    var donationid = $('#donationIdText').val();
    var send_secret = $('#donationSecretSendboxText').val();
    var prod_secret = $('#donationSecretProductionText').val();
    var url = "<?php echo $ngurl; ?>setDonationId.php"
    var send_data = {
      id: donationid,
      shop: "<?php echo $shop; ?>",
      sendbox_secret: send_secret,
      production_secret: prod_secret,
      environment_select: donation_environment
    }
    $.ajax({
      type: 'POST',
      url: url,
      data: send_data,
      success: function(data) {
        var get_data = JSON.parse(data)
        if (get_data['res'] == 'inserted' || get_data['res'] == 'updated') {
          location.reload();
          $('#submit_donation_id').prop('disabled', false);
        } else {
          $('.faild_donation').html("Someting Went Wrong! Please Try Again");
          $('#submit_donation_id').prop('disabled', false);
        }
      }
    })
  }
</script>
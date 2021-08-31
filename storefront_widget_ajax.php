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

  // // script tag
  //           $ScriptCount = $shopify('GET /admin/script_tags/count.json');
            
  //           //  Note : This is js file which will load the proxy file to your store front end
  //           $app_load = SHOPIFY_SITE_URL . "js/app_load_goalminus.js?shop=" . $shop . "";

  //           $ScriptDetails = $shopify('GET /admin/script_tags.json');
  //           // $ScriptDetailsaaa = $shopify('GET /admin/storefront_access_tokens.json');
  //           // print_r($ScriptDetailsaaa);
  //           // echo "aaaaaaaaaaaaaaaaaa";
  //           if ($ScriptCount == 0)
  //           {

  //               $js_scripts = $shopify('POST /admin/script_tags.json', array() , array(
  //                   'script_tag' => array(
  //                       "event" => "onload",
  //                       "src" => $app_load,
  //                   )
  //               ));

  //           } 

  //          if ($ScriptCount > 1)
  //           {
  //               foreach ($ScriptDetails as $key => $value)
  //               {
  //                   foreach ($value as $key1 => $value1)
  //                   {
  //                       if ($key1 == 'id')
  //                       {
  //                           $ScriptDelete = $shopify('DELETE /admin/script_tags/' . $ScriptDetails[$key]["id"] . '.json');
  //                       }
  //                   }
  //               }

  //               $js_scripts = $shopify('POST /admin/script_tags.json', array() , array(
  //                   'script_tag' => array(
  //                       "event" => "onload",
  //                       "src" => $app_load,
  //                   )
  //               ));
  //           }
  //           //
 //get check box contain
            $query = "SELECT * FROM `shopify_App_goalminus_authenticate` WHERE shop ='$shop'";
            $querry_rs = mysqli_query($conn, $query);
            $querry_arr = mysqli_fetch_assoc($querry_rs);
            $html = $querry_arr['checkbox_value'];
            $price = $querry_arr['price'];
            $img = $querry_arr['checkbox_picture'];
            $cart_flag = $querry_arr['cart_flag'];
            $shopify_product_id = $querry_arr['shopify_product_id'];
            $shopify_theme_id = $querry_arr['theme_id'];
            $shopify_theme_name = $querry_arr['shopify_product_name'];
            $storefront_widget = $querry_arr['storefront_widget'];




                $get_script_tag = $shopify('GET /admin/api/2020-07/themes.json');
            foreach ($get_script_tag as $key)
            {
                if ($key['role'] == "main")
                {
                    $theme_id = $key['id'];
                }
            }

 $assets_data = $shopify('GET /admin/api/2020-07/themes/' . $theme_id . '/assets.json?asset[key]=templates/cart.liquid');

 
            if (strpos($assets_data['value'], "</form>") !== false)
            {
                $check_cart = "true";
            }
            else
            {
                $check_cart = "false";
            }
            if (strpos($assets_data['value'], '<input type="submit" name="checkout"') !== false)
            {
                $check_checkout = "true";
            }
            else
            {
                $check_checkout = "false";
            }


// check widget 

if($storefront_widget == 1){

            if ($check_cart == "true")
            {

                
            
                // else
                // {
                    $pieces = explode('<div class="custom_html"', $assets_data['value']);
                    if ($check_checkout == "true")
                    {
                        $second_part = explode('<input type="submit" name="checkout"', $pieces[1]);
                        $final_string = $pieces[0] . '<input type="submit" name="checkout"'.$second_part[1];

                        $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array() , array(
                            'asset' => array(
                                // "key" => "sections/cart-template.liquid",
                                "key" => "templates/cart.liquid",
                                "value" => $final_string,
                            )
                        ));

                    }
                    else
                    {
                        $second_part = explode('<button type="submit" name="checkout"', $pieces[1]);
                        $final_string = $pieces[0] . '<button type="submit" name="checkout"' .$second_part[1];

                        $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array() , array(
                            'asset' => array(
                                // "key" => "sections/cart-template.liquid",
                                "key" => "templates/cart.liquid",
                                "value" => $final_string,
                            )
                        ));
                    }
                // }
            }
            elseif ($check_cart == "false")
            {

                $assets_data_2 = $shopify('GET /admin/api/2020-07/themes/' . $theme_id . '/assets.json?asset[key]=sections/cart-template.liquid');

                if (strpos($assets_data_2['value'], '<input type="submit" name="checkout"') !== false)
                {
                    $check_checkout = "true";
                }
                else
                {
                    $check_checkout = "false";
                }
                
             
                // else
                // {
                    $pieces = explode('<div class="custom_html"', $assets_data_2['value']);
                    if ($check_checkout == "true")
                    {
                        $second_part = explode('<input type="submit" name="checkout"', $pieces[1]);
                        $final_string = $pieces[0] .'<input type="submit" name="checkout"' .$second_part[1];

                        $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array() , array(
                            'asset' => array(
                                "key" => "sections/cart-template.liquid",
                                "value" => $final_string,
                            )
                        ));
                    }
                    else
                    {
                        $second_part = explode('<button type="submit" name="checkout"', $pieces[1]);
                        $final_string = $pieces[0] . '<button type="submit" name="checkout"' .$second_part[1];

                        $js_scripts = $shopify('PUT /admin/api/2020-07/themes/' . $theme_id . '/assets.json', array() , array(
                            'asset' => array(
                                "key" => "sections/cart-template.liquid",
                                "value" => $final_string,
                            )
                        ));
                    }

                // }
            }
            if($js_scripts){
            	$sql = "UPDATE `shopify_App_goalminus_authenticate` SET  storefront_widget = 0 WHERE  shop ='$shop'";
        		mysqli_query($conn, $sql) or die(mysqli_error());
        		echo "true";
            }
    }

<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

use phpish\shopify;

// require __DIR__ . '/conf.php';
if (isset($_REQUEST['shop'])) {
  $shop = $_REQUEST['shop'];
}
//get check box contain
$query = "SELECT * FROM `config` WHERE shop ='$shop'";
$querry_rs = mysqli_query($conn, $query);
$querry_arr = mysqli_fetch_assoc($querry_rs);
$oauth_token = $querry_arr['oauth_token'];

$get_donation_id = 0;
$check_id_query = "SELECT * FROM donationIds WHERE shop='" . $shop . "'";
if ($result = mysqli_query($conn, $check_id_query)) {
  while ($row = mysqli_fetch_row($result)) {
    $get_donation_id = $row[2];
  }
  mysqli_free_result($result);
}
$base_url = SHOPIFY_SITE_URL;
?>
<div id='aid-on-widget' style='display: flex; width: 100%; justify-content: flex-end; margin-right: -1%; padding: 6px;'></div>
<p class='cart-attribute__field' style='display:none'><label for='donation_id'>Your name</label><input id='donation_id' type='text' name='attributes[donation_id]' value="{{ cart.attributes['donation_id'] }}"></p>

<div class='loading'>Loading&#8230;</div>
<div class='content'>
  <h3></h3>
</div>
<script type='text/javascript' src='https://cdn.aidonline.net/index.min.js'></script>
<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
<script type='text/javascript'>
  var id_data;
  var api_auth = '<?php echo $oauth_token; ?>';
  var shop = '<?php echo $shop; ?>';
  var varient_id;
  var cart_len;
  var cart_data;
  var check_donation = false;

  $(document).ready(function() {

    $('.loading').css('display', 'block');
    const urlParams = new URLSearchParams(window.location.search);
    const check = urlParams.get('check');
    const val_id = urlParams.get('id');
    var test = false;
    $("body *").each(function() {
      if ($(this).text().trim() === "Thank you for your generosity !!") {
        test = true;
        return;
      }
    });
    if (test == true) {
      $('.loading').css('display', 'none');
      setTimeout(function() {
        $('#aid-on-widget-checkbox').prop('checked', true);
      }, 1800);
    } else {
      $('.loading').css('display', 'none');
    }
  });
  var Aidon = {

    vendor: <?php echo $get_donation_id ?>,

  }

  aidOnCallback = function(response) {
    $('.loading').css('display', 'block');
    var price = response['response']['donation']['amount'];
    var id = response['response']['donation']['id'];
    var url = '<?php echo $base_url ?>create_product.php';
    $('#donation_id').val(id);
    adjust_cart = [];
    if ($('#aid-on-widget-checkbox').is(':checked')) {
      $.ajax({
        url: '<?php echo $base_url ?>create_product.php?token=' + api_auth + '&price=' + price + '&shop=' + shop + '&id=' + id + '',
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
                cart_len = cart_data.length;
                for (var i = 0; i < cart_len; i++) {
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
                      id: varient_id
                    });
                    var getresp = jQuery.post('/cart/add.js', {
                      items: adjust_cart
                    });
                    setTimeout(function() {
                      $('.loading').css('display', 'none');
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
        $('.loading').css('display', 'none');
        window.location.reload();
      }, 1000);
    }

  }
  $(document).on('click', '#aid-on-widget-checkbox', function() {
    $('.loading').css('display', 'block');
    if ($('#aid-on-widget-checkbox').is(':checked')) {} else {
      $.ajax({
        type: 'GET',
        url: '/cart.js',
        cache: false,
        dataType: 'json',
        success: function(data) {
          for (var i = 0; i < data['items'].length; i++) {
            if (data['items'][i]['title'] == 'Thank you for your generosity !!') {
              var get_res = jQuery.post('/cart/change.js', 'id=' + data['items'][i]['id'] + '&quantity=0');
              setTimeout(function() {
                $('.loading').css('display', 'none');
                window.location.reload();
              }, 1000);
            }
          }
        }
      });
    }
  });
  $('.cart__remove').on('click', function() {
    var n = $(this).children('a').attr('aria-label');
    if (n.includes('Thank you for your generosity !!')) {
      setTimeout(function() {
        window.location.reload();
      }, 1000);
    } else {}
  })
</script>
<style>
  /* style for checkout button*/
  .cart .btn {
    float: right !important;
  }

  /* Absolute Center Spinner */
  .loading {
    display: none;
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
    background: radial-gradient(rgba(20, 20, 20, .8), rgba(0, 0, 0, .8));

    background: -webkit-radial-gradient(rgba(20, 20, 20, .8), rgba(0, 0, 0, .8));
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
    -webkit-box-shadow: rgba(255, 255, 255, 0.75) 1.5em 0 0 0, rgba(255, 255, 255, 0.75) 1.1em 1.1em 0 0, rgba(255, 255, 255, 0.75) 0 1.5em 0 0, rgba(255, 255, 255, 0.75) -1.1em 1.1em 0 0, rgba(255, 255, 255, 0.75) -1.5em 0 0 0, rgba(255, 255, 255, 0.75) -1.1em -1.1em 0 0, rgba(255, 255, 255, 0.75) 0 -1.5em 0 0, rgba(255, 255, 255, 0.75) 1.1em -1.1em 0 0;
    box-shadow: rgba(255, 255, 255, 0.75) 1.5em 0 0 0, rgba(255, 255, 255, 0.75) 1.1em 1.1em 0 0, rgba(255, 255, 255, 0.75) 0 1.5em 0 0, rgba(255, 255, 255, 0.75) -1.1em 1.1em 0 0, rgba(255, 255, 255, 0.75) -1.5em 0 0 0, rgba(255, 255, 255, 0.75) -1.1em -1.1em 0 0, rgba(255, 255, 255, 0.75) 0 -1.5em 0 0, rgba(255, 255, 255, 0.75) 1.1em -1.1em 0 0;
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
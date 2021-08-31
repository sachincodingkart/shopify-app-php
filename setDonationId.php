<?php
// error_reporting(0);
session_start();
error_reporting(1);
require __DIR__ . '/vendor/autoload.php';

use phpish\shopify;

require __DIR__ . '/conf.php';
?>

<?php
//  Note : This is mendatory code to make the $shopify object work currectly

$shop = $_POST['shop'];
$id = $_POST['id'];
$sendbox_secret = $_POST['sendbox_secret'];
$production_secret = $_POST['production_secret'];
$environment_select = $_POST['environment_select'];



$check_id = false;
$get_donation_id;
$check_id_query = "SELECT * FROM donationIds WHERE shop='" . $shop . "'";
if ($result = mysqli_query($conn, $check_id_query)) {
    $count = mysqli_num_rows($result);
    if ($count > 0) {
        $update_query = "UPDATE donationIds SET donation_id='" . $id . "', status='1' , donation_sendbox_secret='" . $sendbox_secret . "' , donation_production_secret='" . $production_secret . "' , donation_environmnet='" . $environment_select . "' WHERE shop='" . $shop . "';";
        $result = mysqli_query($conn, $update_query);
        $res = array(
            "res" => "updated"
        );
        echo json_encode($res, true);
    } else {
        $insert_query = "INSERT INTO donationIds (`shop`, `donation_id`, `status`,`donation_sendbox_secret`,`donation_production_secret`,`donation_environmnet`) VALUES ('" . $shop . "','" . $id . "','1','" . $sendbox_secret . "','" . $production_secret . "','" . $environment_select . "');";
        $result = mysqli_query($conn, $insert_query);
        $res = array(
            "res" => "inserted"
        );
        echo json_encode($res, true);
    }
} else {
    $res = array(
        "res" => "failed"
    );
    echo json_encode($res, true);
}

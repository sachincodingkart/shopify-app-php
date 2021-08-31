<?php
require __DIR__ . '/vendor/autoload.php';
use phpish\shopify;
$shop = $_POST['shop'];
$donation_environment = $_POST['environment_select'];
$sendbox = $_POST['send_sec'];
$production = $_POST['prod_sec'];
$check_id = false;
$get_donation_id;
$check_id_query = "SELECT * FROM donationIds WHERE shop='" . $shop . "'";
if ($result = mysqli_query($conn, $check_id_query)) {
    $count = mysqli_num_rows($result);
    if ($count > 0) {
        $update_query = "UPDATE donationIds SET  donation_environmnet='" . $donation_environment . "', donation_sendbox_secret='" . $sendbox . "' , donation_production_secret='" . $production . "' WHERE shop='" . $shop . "';";
        $result = mysqli_query($conn, $update_query);
        $res = array(
            "res" => "updated"
        );
        echo json_encode($res, true);
    }
} else {
    $res = array(
        "res" => "failed"
    );
    echo json_encode($res, true);
}
?>
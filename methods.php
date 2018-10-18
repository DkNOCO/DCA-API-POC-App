<?php
/**
 * Created by PhpStorm.
 * User: krugerd
 * Date: 9/17/2018
 * Time: 10:32 PM
 */

include_once "config.php";

function callAPI($method,  $APIurl, $payload_JSON=null){
global $dcaFQDN;
    if (isset($_SESSION['LAST_ACTIVITY']) && isset($_SESSION['DCATOKEN'])){
        if ((time() - $_SESSION['LAST_ACTIVITY'] > 600)) {
            session_unset();
            session_destroy();
            $_SESSION['LAST_ACTIVITY'] = time();
            $_SESSION['DCATOKEN'] = file_get_contents('http://'.$_SERVER['SERVER_ADDR'].'/gettoken.php');
        }
    }else{
        $_SESSION['LAST_ACTIVITY'] = time();
        $_SESSION['DCATOKEN'] = file_get_contents('http://'.$_SERVER['SERVER_ADDR'].'/gettoken.php');
    }


    $tdDecoded = json_decode($_SESSION['DCATOKEN'], 1);
    $token = $tdDecoded['token']['id'];
    $refresh_token = $tdDecoded['refreshToken'];
    $baseURL = "https://". $dcaFQDN;
    $url = $baseURL . $APIurl;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    switch ($method){
        case "GET":
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Auth-Token:'.$token));
            break;
        case "POST":
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Auth-Token:'.$token,'Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_JSON);

    }
    $result = curl_exec($ch);
    $data = json_decode($result, true);
    return $data;
}

function getResourceGroups($uuid=null)
{
    $data = callAPI('GET',  "/urest/v1/resource_group", null);
    if ($uuid !== null) {
        foreach ($data['members'] as $group) {
            if ($group['uuid'] == $uuid) {
                break;
            }
        }
        return $data['group'];
    } else {
        return $data['members'];
    }
}
function getResourceGroupDetails($uuid){
    $data = callAPI( 'GET',"/urest/v1/resource_group/".$uuid."?fields=PolicySubscription,ChildResource,MaintenanceWindow", null);
    return $data;
}



?>


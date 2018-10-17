<?php
/**
 * Created by PhpStorm.
 * User: krugerd
 * Date: 9/17/2018
 * Time: 10:32 PM
 */



//API Url

function callAPI($RTG, $method, $payload_JSON=null, $APIurl){

    if (isset($_SESSION['LAST_ACTIVITY']) && isset($_SESSION['DCATOKEN'])){
        if ((time() - $_SESSION['LAST_ACTIVITY'] > 600)) {
            session_unset();     // unset $_SESSION variable for the run-time
            session_destroy();   // destroy session data in storage
            $_SESSION['CREATED'] = time();
            $_SESSION['DCATOKEN'] = file_get_contents('http://172.16.239.79/api.php');
        }


    }else{
        $_SESSION['CREATED'] = time();
        $_SESSION['DCATOKEN'] = file_get_contents('http://172.16.239.79/api.php');
    }


    //$tokenData = file_get_contents('http://172.16.239.79/api.php');
    $tokenData = $_SESSION['DCATOKEN'];

    $tdDecoded = json_decode($tokenData, 1);
    $token = $tdDecoded['token']['id'];
    $refresh_token = $tdDecoded['refreshToken'];
    $baseURL = "https://demo-mast.dca.demo.local";
    $url = $baseURL . $APIurl;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    switch ($method){
        case "GET":
            //curl_setopt($ch, CURLOPT_GET, 1);
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
    $data = callAPI('RGP', 'GET', null, "/urest/v1/resource_group");
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
    $data = callAPI('RGP','GET', null,"/urest/v1/resource_group/".$uuid."?fields=PolicySubscription,ChildResource,MaintenanceWindow");
    return $data;
}



?>


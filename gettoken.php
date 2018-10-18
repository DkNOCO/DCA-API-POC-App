<?php
/**
 * Created by PhpStorm.
 * User: krugerd
 * Date: 9/17/2018
 * Time: 4:25 PM
 */
    include "config.php";

    $url = "https://".$dcaFQDN.':5443/idm-service/v2.0/tokens';
    $ch = curl_init($url);
    $jsonData = array(
        'passwordCredentials' => array('username' => $dcauser, 'password' => $dcapw),
        'tenantName' => 'PROVIDER'
    );
    $str = base64_encode($transportUser.':'.$transportPw);
    $jsonDataEncoded = json_encode($jsonData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic ' . $str));
    $result = curl_exec($ch);
?>



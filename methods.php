<?php
/**
 * Created by PhpStorm.
 * User: krugerd
 * Date: 9/17/2018
 * Time: 10:32 PM
 */

include_once "config.php";

function callAPI($method, $APIurl, $payload_JSON=null){

    //ob_start(); // For debug
    //$out = fopen('php://output', 'w'); // For debug

    if($method=="POST_RESOURCE"){

        $contentType = 'multipart/form-data';
        $payload_JSON = array('file'=> new \CurlFile($payload_JSON, 'application/octet-stream', 'file'));
        $method="POST";
    }
    else{
        $contentType = 'application/json';
    }

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
    $refresh_token = $tdDecoded['refreshToken']; // not used
    $baseURL = "https://". $dcaFQDN;
    $url = $baseURL . $APIurl;
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_VERBOSE, true); // for debug
    //curl_setopt($ch, CURLOPT_STDERR, $out); // for Debug
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    switch ($method){
        case "GET":
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Auth-Token:'.$token));
            break;
        case "POST":
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Auth-Token:'.$token,'Content-Type: '.$contentType));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_JSON);
            break;
    }

    $result = curl_exec($ch);
    // fclose($out); // For debug
    // $debug = ob_get_clean(); // For debug
    //var_dump($debug); // For debug
    //die(); // For debug
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

function getResourceGrpChildren($uuid){
    $data = callAPI( 'GET',"/urest/v1/resource_group/".$uuid."/child_resource", null);
    return $data;
}

function getResourceGroupDetails($uuid){
    $data = callAPI( 'GET',"/urest/v1/resource_group/".$uuid."?fields=PolicySubscription,ChildResource,MaintenanceWindow", null);
    return $data;
}

function getResource($uuid=null, $payload=null, $fields=null){
    $apiURL = "/urest/v1/resource";
    if($uuid!=null && $fields==null){
        $apiURL = $apiURL . "/" . $uuid;
    }
    elseif($uuid!=null && $fields!=null){
        $apiURL = $apiURL . "/" . $uuid."?".$fields;
    }

    $data = callAPI('GET', $apiURL, $payload);

    if ($uuid !== null) {
        return $data;
    } else {
        return $data['members'];
    }

}

function adhocScan($uuid, $policyId, $jobType){
    $uuidDecoded = json_decode($uuid);
    if (json_last_error() === 0) {
       $targets = $uuidDecoded;

    }else
    {
        $targets = array(array(
            "type" => "RESOURCE",
            "uuid" => $uuid));
    }

    $payload = array(
        'jobType' => array(
            "name" => $jobType),
        "targets" => $targets,
        "properties" => array(array(
            "property" => "policyId",
            "value" => $policyId)),
        "schedule" => array(
            "type" => "NOW")
    );

    $payload = json_encode($payload);
    $data = callAPI("POST", "/urest/v1/scheduler/job_request",$payload);
    return $data;
}

function viewAllJobs(){

    $data = callAPI("GET", "/urest/v1/scheduler/job",null);
    return $data;
}
function addResources($resources=array()){

    $uuid = uniqid();
    $csv_file = 'csv/'. $uuid .'csv';
    $handle = fopen($csv_file, 'w') or die('Cannot open file:  '.$csv_file);
    $data = 'type,name,host_servertype,os_family,extended_os_family,display_label,os_description,credential_id
';
    foreach ($resources as $resource){
        $data = $data. $resource['type'].','.$resource['name'].','.$resource['host_servertype'].','.$resource['os_family'].','.$resource['extended_os_family'].','.$resource['display_label'].','.$resource['os_description'].','.$resource['credential_id'].'
    ';
    }
    fwrite($handle, $data);
    fclose($handle);
    $data = callAPI("POST_RESOURCE","/urest/v1/resource",$csv_file);
    return $data;
}

?>


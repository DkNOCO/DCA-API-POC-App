<?php
/**
 * Created by PhpStorm.
 * User: krugerd
 * Date: 10/25/2018
 * Time: 2:42 PM
 */
include 'methods.php';
if (isset($_GET['action'])){
    $action = $_GET['action'];
}
else{
    echo "Michael Bettan says, I Sucks -- You didnt set the action, I have no idea what you want me to do.";
    die();
}

switch ($action){
    case "AddResourceToGroup":
        if (isset($_GET['groupuuid'])&& isset($_GET['resourceuuid'])){
            $groupUuid = $_GET['groupuuid'];
            $resourceUuid = $_GET['resourceuuid'];
            addResourceToGroup($groupUuid,$resourceUuid);
            echo "Job sent, I hope it worked... Are you feeling lucky?";
            die();
        }
        else{
            echo "failed to set groupuuid or resourceuuid, try again";
            die();
        }
        break;

    case "ImportLinuxResource":
        if (isset($_GET['FQDNorIP'])&&isset($_GET['credentialid'])){
            $resourceFQDN = $_GET['FQDNorIP'];
            $credentialId = $_GET['credentialid'];

            $resources = array(array(
                'type' => 'host_node',
                'name' => $resourceFQDN,
                'host_servertype' => 'MANAGED',
                'os_family' => 'unix',
                'extended_os_family' => 'LINUX',
                'display_label' => $resourceFQDN,
                'os_description' => 'Red Hat Enterprise Linux Server 7 X86_64',
                'credential_id' => $credentialId
            ));

          addResources($resources);
          sleep(5);
            foreach (getResource() as $resource){
                if ($resource['name']==$resourceFQDN){
                    $uuid = $resource['uuid'];
                    echo $uuid;
                    break;
                }

            }
            if (!isset($uuid)){
                echo "Job was sent but we couldnt find the uuid for the resource you added... could be a timing thing";
                die();
            }

        }
        else{
            echo "failed to set FQDNorIP or credentialid, try again";
            die();
        }
        break;
    case "AdHocJob":
        if (isset($_GET['resourceuuid'])&& isset($_GET['policyuuid'])&&isset($_GET['jobtype'])){
            $targetUuid = $_GET['resourceuuid'];
            $policyUuid = $_GET['policyuuid'];
            $jobType = $_GET['jobtype'];
            adhocScan($targetUuid,$policyUuid,$jobType);
            echo "Job sent, I hope it worked... Are you feeling lucky?";
            die();
        }
        else{
            echo "failed to set resourceuuid, policyuuid, or jobtype, try again";
            die();
        }
        break;
    case "DeleteResource":
        if (isset($_GET['resourceuuid'])){
            $targetUuid = $_GET['targetuuid'];
            deleteResource($targetUuid);
            echo "Job sent, but this endpoint doesnt work.";
            die();
        }
        else{
            echo "failed to set resourceuuid";
            die();
        }
        break;

    default:
        echo "you set an action but the only problem is I couldnt find a matching action. Check for a typo in your action";
        die();

}

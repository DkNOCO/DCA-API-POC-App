<?php
/**
 * Created by PhpStorm.
 * User: krugerd
 * Date: 9/17/2018
 * Time: 10:32 PM
 */
include 'methods.php';

if (isset($_GET['view'])){
    $showContent = $_GET['view'];
}
if (isset($_POST['view'])){
    $showContent = $_POST['view'];
}
if (isset($_GET['uuid'])){
    $uuid = $_GET['uuid'];
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Company Application</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="base.css">
</head>
<body>


<header>
    <h2>My Company Application</h2>
    <h4>Data Center Automation REST API POC Demo</h4>
</header>

<section>
    <nav>
        <ul>
            <li><a href="index.php">Home</a><br></li>
            <li><b>Resources:</b></li>
            <li><a href="index.php?view=AllResourceGroups">List Resource Groups</a><br></li>
            <li><a href="index.php?view=AllResources">List Resources</a><br></li>
            <li><a href="index.php?view=AddResourcesForm">Add Resources</a><br></li>
            <li><b>Jobs:</b></li>
            <li><a href="index.php?view=ViewAllJobs">View All</a></li>
            <li>View Running</li>
            <li>View Scheduled</li>

        </ul>
    </nav>

    <article>
        <?php
        if (isset($showContent)){

            switch ($showContent){
                case "AllResourceGroups":
                    echo "
                        <table border=1 width=90%>
                        <tr><td colspan='2'>Resource Groups</td></tr>
                        ";
                    foreach (getResourceGroups() as $resourceGroup){
                        echo "<tr><td>".$resourceGroup['name']."</td><td><a href='index.php?view=ResourceGroup&uuid=".$resourceGroup['uuid']."'>View</a></td></tr>";
                    }
                    echo "</table>";
                    break;

                case "AllResources":
                    echo "
                        <table border=1 width=90%>
                        <tr><td><b>Resource Name</b></td><td><b>Resource Type</b></td><td><b>Action</b></td></tr>
                        ";
                    foreach (getResource() as $resource){
                        echo "<tr><td>".$resource['name']."</td><td>". $resource['resourceType'] ."</td><td><a href='index.php?view=Resource&uuid=".$resource['uuid']."'>View</a></td></tr>";
                    }
                    echo "</table>";
                    break;

                case "Resource":
                    $resource = getResource($uuid,null, 'fields=ParentRG,PolicySubscription');
                    echo "<h2>".$resource['name']."</h2><br>";
                    echo "<h3>Resource Details:</h3>";
                    echo "<b>Resource type:</b> ". $resource['resourceType']."<br>";
                    echo "<b>Compliance Status</b> ".$resource['complianceStatus']."<br>";
                    foreach ($resource['attributes'] as $attribute){
                        echo "<b>".$attribute['name']."</b> ".$attribute['value']."<br>";
                    }
                    echo "<br><br><h2>Actions:</h2>";

                    if($resource['policysubscription']['count']>0){
                        foreach ($resource['policysubscription']['members'] as $member) {
                            echo $member['policyName'] . " | <a href='index.php?view=AdhocJob&uuid=".$uuid . "&jobType=POLICY_SCAN&policyid=".$member['policyId']."'>Scan</a> | ";
                            echo "<a href='index.php?view=AdhocJob&uuid=".$uuid . "&jobType=POLICY_REMEDIATE&policyid=".$member['policyId']."'>Remediate</a><br>";
                        }
                    }

                    break;
                case "AddResources":
                    // you could handle an array of resources from the form
                    $resources = array(array(
                        'type' => $_POST['type'],
                        'name' => $_POST['name'],
                        'host_servertype' => $_POST['host_servertype'],
                        'os_family' => $_POST['os_family'],
                        'extended_os_family' => $_POST['extended_os_family'],
                        'display_label' => $_POST['display_label'],
                        'os_description' => $_POST['os_description'],
                        'credential_id' => $_POST['credential_id']
                    ));

                    addResources($resources);
                    header("Location: index.php?view=AllResources");


                    break;
                case "AddResourcesForm":
                    //you could use jquery to add more resource fields[] to allow for adding more than one resource at a time.
                    echo "Add Resource<br>";
                     echo "
                     <form method='POST' action='index.php'>
                     <input type='hidden' name='view' value='AddResources'>
                     <label>Type:<select name='type'><option value='host_node'>host_node</option> </select></label>
                     <label>Name:<input type='text' name='name'> </label> <br>
                     <label>Host Server Type:<select name='host_servertype'><option value='MANAGED'>Managed</option></select></label>
                     <label>OS Family:<select name='os_family'><option value='unix'>unix</option></select></label><br>
                     <label>Extended OS Family:<select name='extended_os_family'><option value='LINUX'>LINUX</option></select></label>
                     <label>Display Label:<input type='text' name='display_label'></label><br>
                     <label>OS Description:<select name='os_description'><option value='Red Hat Enterprise Linux Server 7 X86_64' >RHEL 7 X86_64</option></select></label>
                     <label>Credential (ID):<input type='text' name='credential_id'></label><br><br>
                     <input type='submit' value='Add'>
                    </form>
                     ";



                    break;
                case "AdhocJob":
                    $data = adhocScan($uuid,$_GET['policyid'],$_GET['jobType']);
                    header("Location: index.php?view=ViewAllJobs");
                    break;
                case "ViewAllJobs":

                    $data = viewAllJobs();
                    echo "<table border=1><tr><td><b>Job Type</b></td><td><b>Status</b></td><td><b>Start</b></td><td><b>End</b></td></tr>";
                    if ($data['count']>0){
                        foreach ($data['members'] as $member){
                            echo "<tr><td>".$member['jobName']."</td>" .
                                "<td>".$member['jobStatus']."</td>".
                                "<td>".$member['jobStartTime']."</td>".
                                "<td>".$member['jobEndTime']."</td>"
                            ;
                            echo "</tr>";

                        }
                    }
                    echo "</table>";

                    break;

                case "ResourceGroup":

                    $RGP = getResourceGroupDetails($uuid);
                    echo "<h2>".$RGP['name']."</h2><br>";
                    $uuids = array();
                    if($RGP['childresource']['total']>=1){
                        echo "<h2>Resources:</h2>";
                        echo "<table border=1><tr><td>Resource</td><td>";
                        echo "Actions:</td></tr>";
                        foreach ($RGP['childresource']['members'] as $RM){
                            echo "<tr><td>";
                            echo "<a href='index.php?view=Resource&uuid=".$RM['uuid']."'>".$RM['name']."</a></td><td>";
                            if($RGP['policysubscription']['count']>0){
                                foreach ($RGP['policysubscription']['members'] as $policy){
                                    echo "<b>".$policy['policyName'] . "</b> | <a href='index.php?view=AdhocJob&uuid=".$RM['uuid'] . "&jobType=POLICY_SCAN&policyid=".$policy['policyId']."'>Scan</a> | ";
                                    echo "<a href='index.php?view=AdhocJob&uuid=".$RM['uuid'] . "&jobType=POLICY_REMEDIATE&policyid=".$policy['policyId']."'>Remediate</a><br>";
                                }
                            }

                            echo "</td>";
                            $uuids[] = array("type" => "RESOURCE","uuid" => $RM['uuid']);
                            echo "</tr>";
                        }
                    }
                    echo "</table>";
                    $uuids = json_encode($uuids);
                    echo "<h3>Group Actions:</h3>";

                if($RGP['policysubscription']['count']>0){
                    foreach ($RGP['policysubscription']['members'] as $policy){
                        echo "<b>".$policy['policyName'] . "</b> | <a href='index.php?view=AdhocJob&uuid=".$uuids . "&jobType=POLICY_SCAN&policyid=".$policy['policyId']."'>Scan</a> | ";
                        echo "<a href='index.php?view=AdhocJob&uuid=".$uuids . "&jobType=POLICY_REMEDIATE&policyid=".$policy['policyId']."'>Remediate</a><br>";
                    }
                }
                    echo "<h3>Maintenance Windows:</h3>";
                    echo "<a href='index.php?view=AddMW&uuid=". $RGP['uuid']."'>Add window</a><br><br>";
                    if($RGP['maintenancewindow']['total']>=1){
                        foreach ($RGP['maintenancewindow']['members'] as $MW){
                            echo "<b>".$MW['name']."</b><br>";
                            echo "<b>Window type:</b> ". $MW['maintenanceWindowType']."<br>";
                            echo "<b>Start time</b> ".$MW['startTime']."<br>";
                            echo "<b>Durration:</b> ".$MW['mwDurationInMinutes']."<br>";
                            echo "<br><br>";
                        }
                    }else{
                        echo "no maintenance windows found";
                    }

                    break;
                case "AddMW":

                    echo"
                        **Note there are many more options for Maint. Windows, only Monthly shown.**<br>
                        <form name='addmw' method='POST' action='index.php?view=CreateMW'>
                        <input type='hidden' name='rguuid' value='".$uuid."'>
                        <label>Name</label><input type='text' name='name'><br>
                        <label>Window Type</label><select name='type'>
                        <option value='MAINTENANCE_WINDOW_TYPE_READ'>Scan Only</option>
                        <option value='MAINTENANCE_WINDOW_TYPE_READ_WRITE'>Scan and Remediate</option>
                        </select>
                        <label>Recurrence Period</label><select name='period'>
                        <option value='MONTHS'>MONTHLY</option>
                        </select><br>
                        <label>Recurrence Interval</label><input type='text' name='interval' value='2'><br>
                        <label>Day of Month To scan</label><input type='text' name='day'><br>
                        <label>Start Time</label><input type='text' name='starttime'><br>
                        <label>End Time</label><input type='text' name='endtime'><br>
                        <input type='submit' value='Add' name='Create MW'>
                        </form>
                        ";

                    break;
                case "CreateMW":

                    $mwPayload = array(
                        'daysOfMonth' => $_POST['day'],
                        'endTime' => $_POST['endtime'],
                        'maintenanceWindowDesc' => $_POST['name'],
                        'maintenanceWindowType' => $_POST['type'],
                        'name' => $_POST['name'],
                        'recurrencePeriod' => $_POST['period'],
                        'recurrenceInterval' => intval($_POST['interval']),
                        'resourceGroupUuid' => $_POST['rguuid'],
                        'startTime' => $_POST['starttime'],
                        'onDate' => null,
                        'daysOfWeek' => null,

                    );
                    $payload = json_encode($mwPayload);
                    $updateMW = callAPI('POST', "/urest/v1/resource_group/".$_POST['rguuid']."/maintenance_window", $payload);
                    header("Location: index.php?view=ResourceGroup&uuid=".$_POST['rguuid']);
                    break;
            }

        }else{
            echo "This is just a POC demo using the Data Center Automation API. It does not show the full capability of the API. You could also use Operations Orchestration to syncronize
            data from other applications to DCA using the API";
        }
        ?>
    </article>
</section>

<footer>
    <p>Data Center Automation API Demo</p>
</footer>

</body>
</html>

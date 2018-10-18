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
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        /* Style the header */
        header {
            background-color: #666;
            padding: 10px;
            text-align: center;
            font-size: 35px;
            color: white;
        }

        /* Create two columns/boxes that floats next to each other */
        nav {
            float: left;
            width: 30%;
            height: 400px; /* only for demonstration, should be removed */
            background: #ccc;
            padding: 20px;
        }

        /* Style the list inside the menu */
        nav ul {
            list-style-type: none;
            padding: 0;
        }

        article {
            float: left;
            padding: 20px;
            width: 70%;
            background-color: #f1f1f1;

        }

        /* Clear floats after the columns */
        section:after {
            content: "";
            display: table;
            clear: both;
        }

        /* Style the footer */
        footer {
            background-color: #777;
            padding: 10px;
            text-align: center;
            color: white;
        }

        /* Responsive layout - makes the two columns/boxes stack on top of each other instead of next to each other, on small screens */
        @media (max-width: 600px) {
            nav, article {
                width: 100%;
                height: auto;
            }
        }
    </style>
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
            <li><a href="index.php?view=AllResourceGroups">View all DCA Resource Groups</a><br></li>

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
                case "ResourceGroup":

                    $RGP = getResourceGroupDetails($uuid);
                    echo "<h2>".$RGP['name']."</h2><br>";

                    if($RGP['childresource']['total']>=1){
                        echo "<h2>Resources:</h2>";
                        foreach ($RGP['childresource']['members'] as $RM){
                            echo $RM['name']."<br>";
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
                        <form name='addmw' method='POST' action='index.php?view=CreateMW'>
                        <input type='hidden' name='rguuid' value='".$uuid."'>
                        <label>Name</label><input type='text' name='name'><br>
                        <label>Window Type</label><select name='type'><option value='MAINTENANCE_WINDOW_TYPE_READ'>SCAN ONLY</option></select>
                        <label>Recurrence Period</label><select name='period'><option value='MONTHS'>MONTHLY</option></select>
                        <label>Recurrence Interval</label><input type='text' name='interval' value='2'><br>
                        <label>Day of Month</label><input type='text' name='day'><br>
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

<?php
/**
 * Created by PhpStorm.
 * User: krugerd
 * Date: 9/23/2019
 * Time: 7:44 AM
 */
?>
<html>
<head>
    <title>Application Setup</title>
</head>

<body bgcolor='white'>
<h1>Initial Application Configuration</h1>
<p>Your application is not configured. Please setup the following information.</p>
<form name="config_settings" action="index.php" method="post">
    <table align="center" width="500">
        <tr><td>Application Domain Name</td><td><input type="text" name="app_domain_name"> </td></tr>
        <tr><td>Database Host Name</td><td><input type="text" name="db_host_name"></td></tr>
        <tr><td>Database Name</td><td><input type="text" name="db_name"></td></tr>
        <tr><td>Database User</td><td><input type="text" name="db_user"></td></tr>
        <tr><td>Database User Password</td><td><input type="password" name="db_user_pass"></td></tr>
        <tr><td colspan="2"><input type="submit" name="Save" value="Save"> </td></tr>

    </table>
</form>
</body>
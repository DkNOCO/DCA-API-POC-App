# DCA-API-POC-App


Populate the credentials in config.php
use index.php to use the application UI

NOTE to import resources you will need a dir in your webroot called csv with 777 privs. This is where it writes the resource import csv file.

Use utils.php endpoints to do GET requests (from OO or other source) to trigger actions in DCA.

Utils Endpoints:
<server-host>/utils.php?action=<endpoint>&<param>=<value>&<param>=<value>
endpoints (action): 

-AddResourceToGroup
action=AddResourceToGroup&groupuuid=<resource group uuid>&resourceuuid=<resource uuid>

-ImportLinuxResource
action=ImportLinuxResource&credentialid=<dca credential id>&FQDNorIP=<resource fqdn OR IP address>
** Returns the UUID of the new resource **

-AdHocJob
action=AdHocJob&resourceuuid=<target resource uuid>&policyuuid=<policy uuid to scan for>&jobtype=<type of job see below>
accepts jobtype POLICY_SCAN  or POLICY_REMEDIATE

to obtain transport user/pass:

Contact Derek Kruger for instructions derek.kruger@microfocus.com


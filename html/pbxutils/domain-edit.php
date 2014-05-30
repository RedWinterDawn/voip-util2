<html><head><link rel='stylesheet' href='stylesheet.css'></head><body>

<?php

$guiltyParty = $_SERVER['REMOTE_ADDR'];
$requestTime = strftime('%Y-%m-%d %H:%M:%S');

if (isset($_GET["domain"]))
{
    $domain = $_GET["domain"];
} else
{
    die("No domain specified");
}

if (isset($_GET["newPresenceServer"]))
{
    $updatePresence = true;
    $newPresenceServer = $_GET["newPresenceServer"];
}

if (isset($_GET["hideList"]))
{
    $showList = false;
} else
{
    $showList = true;
}

//#####################//
//   Change Presence   //
//#####################//
if (isset($newPresenceServer)){
	$rwdb = pg_connect("host=rwdb dbname=pbxs user=postgres ") or die('Could not connect: ' . pg_last_error());

	if ($newPresenceServer == 'v4') {
	  // set null for v4 presence
	  $domainUpdateQuery = "UPDATE resource_group SET presence_server = NULL WHERE domain='" . $domain . "';";
	} else {
	  $domainUpdateQuery = "UPDATE resource_group SET presence_server = '" . $newPresenceServer . "' WHERE domain='" . $domain . "';";
	}

	$domainUpdateResult = pg_query($rwdb,$domainUpdateQuery) or die('Presence update query failed: ' . pg_last_error());

	echo "<b>Updated " . $domain . " to use " . $newPresenceServer . " for presence</b><br/><br/>\n";
    pg_free_result($domainUpdateResult);
    pg_close($rwdb);
}

//#####################//
//        List         //
//#####################//
if ($showList == true){

	sleep(1);
	$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect: ' . pg_last_error());

	$domainQuery = "SELECT domain,name,assigned_server,id,outbound_proxy,presence_server,state,id FROM resource_group WHERE domain='" . $domain . "';";
	$domainResult = pg_query($domainQuery) or die('Domain query failed: ' . pg_last_error());

	if($domainRow = pg_fetch_array($domainResult, null, PGSQL_ASSOC)) {
        if ($domainRow['presence_server'] != ''){
            $santa = "v5 (" . $domainRow['presence_server'] . ")";
        } else {
            $santa = 'v4 presence';
        }

	    echo "<table border=1>\n";
	    echo "<tr><th>Domain</th><th>Name</th><th>Server</th><th>ID</th><th>Proxy</th><th>Presence</th><th>Customer State</th></tr>\n";
		echo "<tr>"
			. "<td>" . $domainRow['domain'] . "</td><td>" . $domainRow['name'] . "</td><td><a href='pbx-server-info.php?server=" . $domainRow['assigned_server']. "'>" 
			. $domainRow['assigned_server'] . "</a></td><td>" . $domainRow['id'] . "</td><td>" . $domainRow['outbound_proxy'] . "</td><td>" . $santa . "</td>" 
			. "<td>" . $domainRow['state'] . "</td>"
			. "</tr>\n";
	    echo "</table>\n";
		echo "<br/>\n";
		echo "<table border=1>\n";
		echo "<th colspan=2>Change Presence Server</th>\n";
		echo "<tr><td>Current Setting</td><td>" . $santa . "</td></tr>\n";
        echo "<tr><td colspan=2>"
			. " <a disabled href='domain-edit.php?domain=" . $domainRow['domain'] . "&newPresenceServer=10.101.40.1'>CHI Santa1 (10.101.40.1)</a><br/> "
            . " <a disabled href='domain-edit.php?domain=" . $domainRow['domain'] . "&newPresenceServer=10.101.40.2'>CHI Santa2 (10.101.40.2)</a><br/> "
            . " <a href='domain-edit.php?domain=" . $domainRow['domain'] . "&newPresenceServer=10.120.255.25'>NYC Santa1 (10.120.255.25)</a><br/> "
            . " <a href='domain-edit.php?domain=" . $domainRow['domain'] . "&newPresenceServer=10.120.255.225'>NYC Santa2 (10.120.255.225)</a><br/> "
            . " <a href='domain-edit.php?domain=" . $domainRow['domain'] . "&newPresenceServer=10.117.255.25'>PVU Santa1 (10.117.255.25)</a><br/> "
            . " <a href='domain-edit.php?domain=" . $domainRow['domain'] . "&newPresenceServer=10.117.255.225'>PVU Santa2 (10.117.255.225)</a><br/> "
            . " <a href='domain-edit.php?domain=" . $domainRow['domain'] . "&newPresenceServer=v4'>[v4]</a><br/> " 
            . "</td></tr>\n";
		echo "</table>\n";
		echo "<br/>\n";
	} else {
		// domain not found
		echo "Error: Domain " . $domain . " not found\r\n"; 
	}



	pg_free_result($domainResult);
	pg_close($dbconn);
}

?>

</body>
</html>

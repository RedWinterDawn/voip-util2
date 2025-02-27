<?php
include('checksession.php');
?>
<html><head><link rel='stylesheet' href='stylesheet.css'></head><body>

<?php

include 'menu.html';
include('guiltyParty.php');
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

//#########################//
//   Change v5candidate    //
//#########################//
if (isset($_GET["setV5candidate"])){
	$toggleSetting = $_GET["setV5candidate"];
	$rwdb = pg_connect("host=rwdb dbname=pbxs user=postgres ") or die('Could not connect: ' . pg_last_error());
	if ($toggleSetting == "toggle") {
		$v5candidateSelectQuery = "SELECT v5candidate FROM resource_group WHERE domain='$domain';";
		$v5candidateSelectResult = pg_query($rwdb,$v5candidateSelectQuery) or die('v5candidate select query failed (' . $v5candidateSelectQuery . '): ' . pg_last_error());
		$v5candidateSelectRow = pg_fetch_array($v5candidateSelectResult, null, PGSQL_ASSOC);
		if ($v5candidateSelectRow["v5candidate"] == 't') { $v5candidateNewValue = "false"; } 
		if ($v5candidateSelectRow["v5candidate"] == 'f') { $v5candidateNewValue = "true"; } 
		pg_free_result($v5candidateSelectResult);
	} else {
		#
		if ($toggleSetting == 'false') { $v5candidateNewValue = "false"; } 
		if ($toggleSetting == 'true') { $v5candidateNewValue = "true"; } 
	}
	echo "<pre>$domain v5candidate new value = [$v5candidateNewValue]</pre><br/>";
	$v5candidateUpdateQuery = "UPDATE resource_group SET v5candidate=$v5candidateNewValue WHERE domain='$domain';";
	$v5candidateUpdateResult = pg_query($rwdb,$v5candidateUpdateQuery) or die('v5candidate update query failed: ' . pg_last_error());
	pg_free_result($v5candidateUpdateResult);
    pg_close($rwdb);
}

//#########################//
//   Change sensitive    //
//#########################//
if (isset($_GET["setSensitive"])){
	$toggleSetting = $_GET["setSensitive"];
	$rwdb = pg_connect("host=rwdb dbname=pbxs user=postgres ") or die('Could not connect: ' . pg_last_error());
	if ($toggleSetting == "toggle") {
		$sensitiveSelectQuery = "SELECT sensitive FROM resource_group WHERE domain='$domain';";
		$sensitiveSelectResult = pg_query($rwdb,$sensitiveSelectQuery) or die('sensitive select query failed (' . $sensitiveSelectQuery . '): ' . pg_last_error());
		$sensitiveSelectRow = pg_fetch_array($sensitiveSelectResult, null, PGSQL_ASSOC);
		if ($sensitiveSelectRow["sensitive"] == 't') { $sensitiveNewValue = "false"; } 
		if ($sensitiveSelectRow["sensitive"] == 'f') { $sensitiveNewValue = "true"; } 
		pg_free_result($sensitiveSelectResult);
	} else {
		#
		if ($toggleSetting == 'false') { $sensitiveNewValue = "false"; } 
		if ($toggleSetting == 'true') { $sensitiveNewValue = "true"; } 
	}
	echo "<pre>$domain sensitive new value = [$sensitiveNewValue]</pre><br/>";
	$sensitiveUpdateQuery = "UPDATE resource_group SET sensitive=$sensitiveNewValue WHERE domain='$domain';";
	$sensitiveUpdateResult = pg_query($rwdb,$sensitiveUpdateQuery) or die('sensitive update query failed: ' . pg_last_error());
	pg_free_result($sensitiveUpdateResult);
    pg_close($rwdb);
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

	$message = "<b>Updated " . $domain . " to use " . $newPresenceServer . " for presence";
	echo $message;
	echo "</b><br/><br/>\n";

    pg_free_result($domainUpdateResult);

    //get domain id
    $domainID = pg_fetch_row(pg_query($rwdb, "SELECT id FROM resource_group WHERE domain='".$domain."';"));

    pg_close($rwdb);
    
    //record to events DB
	$rodb = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect to rodb' . pg_last_error());
	$prePresence = "SELECT presence_server FROM resource_group WHERE domain='".$domain."';";
	$prePresence = pg_fetch_row(pg_query($rodb, $prePresence));
	pg_close($rodb);
	$message = $message . " from " . $prePresence[0];
    $eventsdb = pg_connect("host=rwdb dbname=events user=postgres ") or die('Could not connect to events: ' . pg_last_error());
    $eventInsert = "INSERT INTO event (description) VALUES ('".$message."') RETURNING id;";
	$eventID = pg_fetch_row(pg_query($eventsdb, $eventInsert)) or die('Counld not insert into event');
    pg_query($eventsdb, "INSERT INTO event_domain VALUES('".$eventID[0]."', '".$domainID[0]."')") or die(pg_last_error());
    pg_close($eventsdb);

}

//#####################//
//        List         //
//#####################//
if ($showList == true){

	sleep(1);
	$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect: ' . pg_last_error());

	$domainQuery = "SELECT domain,name,assigned_server,id,outbound_proxy,presence_server,state,id,v5,v5candidate,sensitive FROM resource_group WHERE domain='" . $domain . "';";
	$domainResult = pg_query($domainQuery) or die('Domain query failed: ' . pg_last_error());

	//Get Santa servers
	$santadb = pg_connect("host=rwdb dbname=util user=postgres ") or die('Could not connect to util: '.pg_last_error());
	
	$santaQuery = "SELECT ip, name, site FROM presence WHERE status != 'Inactive' ORDER BY site, ip;";
	$santas = pg_fetch_all(pg_query($santadb, $santaQuery)) or die ('Failed to get santas: '.pg_last_error());

	if($domainRow = pg_fetch_array($domainResult, null, PGSQL_ASSOC)) {
        if ($domainRow['presence_server'] != ''){
            $santa = "v5 (" . $domainRow['presence_server'] . ")";
        } else {
            $santa = 'v4 presence';
        }

		if ($domainRow['v5candidate'] == 't') { $v5candidate = "TRUE"; }
		if ($domainRow['v5candidate'] == 'f') { $v5candidate = "false"; }
		if ($domainRow['sensitive'] == 't') { $sensitive = "TRUE"; }
		if ($domainRow['sensitive'] == 'f') { $sensitive = "false"; }

	    echo "<table border=1>\n";
	    echo "<tr><th>Domain</th><th>Name</th><th>Server</th><th>ID</th><th>Proxy</th><th>Presence</th><th>Customer State</th><th>v5</th><th>v5 candidate</th><th>Sensitive</th></tr>\n";
		echo "<tr>"
			. "<td>" . $domainRow['domain'] . "</td><td>" . $domainRow['name'] . "</td><td><a href='pbx-server-info.php?server=" . $domainRow['assigned_server']. "'>" 
			. $domainRow['assigned_server'] . "</a></td><td>" . $domainRow['id'] . "</td><td>" . $domainRow['outbound_proxy'] . "</td><td>" . $santa . "</td>" 
			. "<td>" . $domainRow['state'] . "</td>"
			. "<td>" . $domainRow['v5'] . "</td>"
			. "<td>" . $v5candidate . " <a href='domain-edit.php?domain=" . $domain . "&setV5candidate=toggle'>[toggle]</a></td>"
			. "<td>" . $sensitive . " <a href='domain-edit.php?domain=" . $domain . "&setSensitive=toggle'>[toggle]</a></td>"
			. "</tr>";
	    echo "</table>\n";
		echo "<br/>\n";
		echo "<table border=1>\n";
		echo "<th colspan=2>Change Presence Server</th>\n";
		echo "<tr><td>Current Setting</td><td>" . $santa . "</td></tr>\n";
        echo "<tr><td colspan=2>";
			foreach($santas as $santa)
			{
				echo " <a href='domain-edit.php?domain=" . $domainRow['domain'] . "&newPresenceServer=".$santa['ip']."'>".$santa['site']." ".$santa['name']." (".$santa['ip'].")</a><br/> ";
			}
        echo " <a href='domain-edit.php?domain=" . $domainRow['domain'] . "&newPresenceServer=v4'>[v4]</a><br/> " 
            . "</td></tr>\n";
		echo "</table>\n";
		echo "<br/>\n";
	} else {
		// domain not found
		echo "Error: Domain " . $domain . " not found\r\n"; 
	}

	pg_close($santadb);
	pg_free_result($domainResult);
	pg_close($dbconn);
}

?>

</body>
</html>

<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');
$guiltyParty = $_SERVER['REMOTE_ADDR'];
$requestTime = strftime('%Y-%m-%d %H:%M:%S');

function eventTable($id)
{
	// Get last 10 events for domain
	$eventdb = pg_connect("host=rodb dbname=events user=postgres") or die('Could not connect: ' . pg_last_error());
	$eventQuery = "SELECT added, description from event, (SELECT event_id FROM event_domain WHERE domain_id='".$id."') as domain WHERE event.id = domain.event_id order by number desc limit 10;";
	$eventArray = pg_fetch_all(pg_query($eventdb, $eventQuery)); //or die('Event query failed: ' . pg_last_error());
	pg_close($eventdb);
	echo "<tr><td></td><td></td><td colspan=5 rowspan=13 valign=top>
		<table>
		<tr><th colspan=2 width='700'>Last 10 Events</th></tr>
		<tr><th>Date</th><th>Description</th></tr>";
	foreach ($eventArray as $event)
	{
		echo "<tr><td>".strftime('%m-%d-%Y %T', strtotime($event['added']))."</td><td>".$event['description']."</td></tr>";
	}

	echo "</table></td>";
}

if (isset($_GET["domain"]))
{
	$domain = $_GET["domain"];
} else
{
	$domain = '%';
}

if (isset($_GET["action"]))
{
    $action = $_GET["action"];
} else
{
    $action = "List";
}

$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());

$domainQuery = "SELECT domain,name,assigned_server,id,outbound_proxy,presence_server,state,id FROM resource_group WHERE domain='" . $domain . "';";
$domainResult = pg_query($domainQuery) or die('Domain query failed: ' . pg_last_error());

while ($domainRow = pg_fetch_array($domainResult, null, PGSQL_ASSOC)) {

    // Get feature flags for domain
    $url = "http://feature-flags:8083/features/" . $domainRow['id'] . "/enabled";
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $domainFlags = curl_exec($curl);
    curl_close($curl);
	$domainFlags = str_replace(",","\n",$domainFlags);
	$domainFlags = str_replace("{","",$domainFlags);
	$domainFlags = str_replace("}","",$domainFlags);
	$domainFlags = str_replace("[","",$domainFlags);
	$domainFlags = str_replace("]","",$domainFlags);
	$domainFlags = str_replace("\"","",$domainFlags);

	if ($domainRow['presence_server'] != ''){
		$santa = "v5 (<a href='presence-server-info.php?server=" . $domainRow['presence_server'] . "'>" . $domainRow['presence_server'] . "</a>)";
	} else {
		$santa = 'v4 presence';
	}
    echo "<table border=1>\n";
    echo "<tr><th>Domain</th><th>Name</th><th>Server</th><th>ID</th><th>Proxy</th><th>Presence</th><th>State</th></tr>\n";
	echo "<th><a href='domain-edit.php?domain=" . $domainRow['domain'] . "'>" . $domainRow['domain'] . "</a></th>"
		. "<th>" . $domainRow['name'] . "</th>"
		. "<th><a href='pbx-server-info.php?server=" . $domainRow['assigned_server'] . "'>" . $domainRow['assigned_server'] . "</a></th>"
		. "<th>" . $domainRow['id'] . "</th>"
		. "<th>" . $domainRow['outbound_proxy'] . "</th>"
		. "<th>" . $santa . " </th>"
		. "<th>" . $domainRow['state'] . "</th>\n";

    $typeQuery = "SELECT type_id, count(type_id) as count FROM user_agent WHERE resource_group_id='" . $domainRow['id'] . "' GROUP BY type_id ORDER BY count DESC;";
    $typeResult = pg_query($typeQuery) or die('Type query failed: ' . pg_last_error());
	$event = true;
	$count = 0;

    echo "<tr><th>Type</th><th>Count</th><td colspan=5 rowspan=10><h3>Enabled Feature Flags</h3><pre>" . $domainFlags . "</pre></td></tr>\n";	
    while ($typeRow = pg_fetch_array($typeResult, null, PGSQL_ASSOC)) {
        echo "\t<tr>";
        foreach ($typeRow as $col_value) { echo "\t\t<td>$col_value</td>\n"; }
//		echo "<td>" . $typeRow[''] . "</td>";
		$count ++;
		if ($count == 9)
		{
			eventTable($domainRow['id']);
			$event = false;
		}
        echo "</tr>\n";
    }
	while ($count < 10)
	{
		echo "<tr></tr>";
		$count ++;
	}
	if ($event)			
	{
		eventTable($domainRow['id']);
	}		
    echo "</table>\n";
}

// Free resultset
pg_free_result($domainResult);
pg_free_result($typeResult);

// Closing connection
pg_close($dbconn);
?>




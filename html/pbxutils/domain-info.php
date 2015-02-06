<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');
include('guiltyParty.php');
$requestTime = strftime('%Y-%m-%d %H:%M:%S');
$resource_group_id = "";

function eventTable($id)
{
	// Get last 10 events for domain
	$eventdb = pg_connect("host=rodb dbname=events user=postgres") or die('Could not connect: ' . pg_last_error());
	$eventQuery = "SELECT added AT TIME ZONE 'UTC-7' as added, description from event, (SELECT event_id FROM event_domain WHERE domain_id='".$id."') as domain WHERE event.id = domain.event_id order by number desc limit 10;";
	$eventArray = pg_fetch_all(pg_query($eventdb, $eventQuery)); //or die('Event query failed: ' . pg_last_error());
	pg_close($eventdb);
	echo "<tr><td></td><td></td><td colspan=9 rowspan=13 valign=top>
		<table>
		<tr><th colspan=2 width='900'>Last 10 Events</th></tr>
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

$utildb = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connect to utildb: '.pg_last_error());
$mplsQuery = "SELECT id FROM mpls WHERE domain = '".$domain."';";
$mplsResults = pg_query($utildb, $mplsQuery) or die('Failed to get MPLS: '.pg_last_error());
if (pg_fetch_all($mplsResults))
{
	$mpls = "<a href='mpls-info.php?action=info&domain=".$domain."'>YES</a>";
}else
{
	$mpls = "NO";
}

$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());

$domainQuery = "SELECT domain,name,assigned_server,id,outbound_proxy,presence_server,state,local_area_code,id,v5,v5candidate,sensitive FROM resource_group WHERE domain='" . $domain . "';";
$domainResult = pg_query($domainQuery) or die('Domain query failed: ' . pg_last_error());

while ($domainRow = pg_fetch_array($domainResult, null, PGSQL_ASSOC)) {
	$resource_group_id = $domainRow['id'];
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
	if ($domainRow['v5'] == 't') { $v5 = "TRUE"; }
	if ($domainRow['v5'] == 'f') { $v5 = "false"; }
	if ($domainRow['v5candidate'] == 't') { $v5candidate = "TRUE"; }
	if ($domainRow['v5candidate'] == 'f') { $v5candidate = "false"; }
	if ($domainRow['sensitive'] == 't') { $sensitive = "TRUE"; }
	if ($domainRow['sensitive'] == 'f') { $sensitive = "false"; }
    $dom = $domainRow['domain'];
    $today = date("Y-m-d");
    echo "<table border=1><tr>
    <form action='simple-migration.php' method='POST'><td>
      <input type='hidden' name='action' value='search' />
      <input type='hidden' name='exact' value='true' />
      <input type='hidden' name='search' value='$dom' />
      <input type='submit' value='Go to Migration Page' />
      </td></form>
    <form action='events-report.php' method='POST'><td>
      <input type='hidden' name='action' value='eventList' />
      <input type='hidden' name='domain' value='$dom' />
      <input type='submit' value='Go to Events Page' />
    </td></form>
    <td><a href='customer-call-report.php?domain=$dom&birthday=$today&action=doSearch'>
      <input type='submit' value='Go to Call Reports' />
      </a></td>
    <td><a href='https://$dom.onjive.com/admin/'>
      <input type='submit' value='Go to Portal' />
      </a></td>
    </tr></table>";
    echo "<table border=1>\n";
    echo "<tr><th>Domain</th><th>Name</th><th>Server</th><th>ID</th><th>Proxy</th><th>Domain Status</th><th>Area Code</th><th>MPLS</th><th>v5 migrated</th><th>v5 candidate</th><th>Sensitive</th></tr>\n";
	echo "<th><a href='domain-edit.php?domain=" . $domainRow['domain'] . "'>" . $domainRow['domain'] . "</a></th>"
		. "<th>" . $domainRow['name'] . "</th>"
		. "<th><a href='pbx-server-info.php?server=" . $domainRow['assigned_server'] . "'>" . $domainRow['assigned_server'] . "</a></th>"
		. "<th>" . $domainRow['id'] . "</th>"
		. "<th>" . $domainRow['outbound_proxy'] . "</th>"
		. "<th>" . $domainRow['state'] . "</th>"
		. "<th>" . $domainRow['local_area_code'] . "</th>"
		. "<th>" . $mpls . "</th>"
		. "<th>$v5</th>"
		. "<th>$v5candidate</th>"
		. "<th>$sensitive</th>"
		. "\n";

    $typeQuery = "SELECT type_id, count(type_id) as count FROM user_agent WHERE resource_group_id='" . $domainRow['id'] . "' GROUP BY type_id ORDER BY count DESC;";
    $typeResult = pg_query($typeQuery) or die('Type query failed: ' . pg_last_error());
	$event = true;
	$count = 0;

    echo "<tr><th>Type</th><th>Count</th><td colspan=9 rowspan=10><h3>Enabled Feature Flags</h3><pre>" . $domainFlags . "</pre></td></tr>\n";	
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

echo "<br/>";

$didQuery = "SELECT number,outbound_routable,peer.name as peer,caller_id_name FROM master_did LEFT JOIN peer ON (master_did.source_peer_id = peer.id) WHERE destination_pbx_id = '" . $resource_group_id . "' AND active = 't' ORDER BY number ASC;";
$didResult = pg_query($dbconn,$didQuery) or die('DID query failed (for ' . $resource_group_id . ') ' . pg_last_error());

echo "<table border=2>\n";
echo "<th>number</th><th>outbound_routable</th><th>caller_id_name</th><th>source peer</th>";
while ($didRow = pg_fetch_array($didResult, null, PGSQL_ASSOC)) {
	if ($didRow['outbound_routable'] == 't') {
		$outbound = "<div class='green'>TRUE</div>";
	} else {
		$outbound = "<div class='yellow'>FALSE</div>";
	}

	echo "<tr>"
		. "<td>" . $didRow['number'] . "</td>"
		. "<td><center>" . $outbound . "</center></td>"
		. "<td>" . $didRow['caller_id_name'] . "</td>"
		. "<td>" . $didRow['peer'] . "</td>"
		. "</tr>\n";
}
echo "</table>\n";

// Free resultset
pg_free_result($domainResult);
pg_free_result($typeResult);
pg_free_result($didResult);

// Closing connection
pg_close($dbconn);
?>




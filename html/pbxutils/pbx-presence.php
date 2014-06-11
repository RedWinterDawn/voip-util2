<?php
echo "<html><body>\n\n";
echo "<html><head><link rel='stylesheet' href='stylesheet.css'></head><body>\n";
include('menu.html');
// Connecting, selecting database
$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());

$santaServer[0] = '';
$santaServerCount = 1;

//////////////////////////////////
// Count of deactivated domains assigned to Santa servers
$deactivatedCountQuery = "SELECT count(*) as count FROM resource_group WHERE state != 'ACTIVE' AND presence_server IS NOT NULL;";
$deactivatedCountResult = pg_query($deactivatedCountQuery) or die('deactivatedCountQuery failed: ' . pg_last_error());
$deactivatedCount = pg_fetch_array($deactivatedCountResult, null, PGSQL_ASSOC);

if ($deactivatedCount['count'] != 0) {
	echo "<table border=2><th>Deactivated count: </th><th>" . $deactivatedCount['count'] . "</th><th><a href=\"pbx-presence-cleanup.php\">remove inactive domains</a></th></table><br/>\n";
}

//////////////////////////////////
// Summary count by Santa server
$query = "select presence_server,count(*) from resource_group where state='ACTIVE' group by presence_server order by presence_server asc;";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

echo "<table border=2>\n";
echo "<th>server</th><th>count</th>\n";
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    echo "\t<tr><td><a href='presence-server-info.php?server=" . $line['presence_server'] . "'>" . $line['presence_server'] . "</a></td><td>" . $line['count'] . "</td></tr>\n";

	if ($line['presence_server'] != '') {
		$santaServer[$santaServerCount] = $line['presence_server'];
		// echo "DEBUG: Added " . $santaServer[$santaServerCount] . "\n";
		$santaServerCount = $santaServerCount + 1;
	}
}
echo "</table>\n";
echo "<br/>\n";

pg_free_result($result);
//
/////////////////////////////


/////////////////////////////
// Summary count by pbx for each active Santa server

$santaServerRemainingCount = $santaServerCount - 1;

while ($santaServerRemainingCount > 0) {
	$currentSantaServer = $santaServer[$santaServerRemainingCount];
	// echo "DEBUG: (" . $santaServerRemainingCount . ") " . $currentSantaServer . "\n";
	$santaServerRemainingCount = $santaServerRemainingCount - 1;
	
	$query = "select assigned_server,count(*) from resource_group where presence_server='" . $currentSantaServer . "' group by assigned_server order by assigned_server asc;";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());

	echo "<table><tr><td>\n";
	echo "<table border=2>\n";
	echo "<tr><th colspan='2'>" . $currentSantaServer . "</th></tr>\n";
	echo "<th>assigned_server</th><th>count</th>\n";
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo "\t<tr>\n";
		echo "\t\t<td><a href='pbx-server-info.php?server=" . $line['assigned_server'] . "'>" . $line['assigned_server'] . "</a></td><td>" . $line['count'] . "</td>\n";
		echo "\t</tr>\n";
	}
	//echo "</td>\n";
	echo "</table>\n";
	
	pg_free_result($result);
	
	echo "</td><td>\n";
	
	$query = "select presence_server,substring(user_agent.type_id for 11) as type,count(*)
	 from resource_group
	 left join user_agent on (user_agent.resource_group_id = resource_group.id)
	 where presence_server='" . $currentSantaServer . "'
	 group by presence_server,type
	 order by presence_server asc, count desc;";
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());
	
	echo "<table border=2>\n";
	echo "<tr><th colspan='3'>Phone Type Count</th></tr>\n";
	echo "<TH>presence_server</TH><TH>type</TH><th>count</th>\n";
	while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		if ($line['type'] == "polycom.vvx") {
			$style = " class=\"red\" ";
		} else {
			$style = "";
		}
	  echo "\t<tr>\n";
	  echo "\t\t<td><a href='presence-server-info.php?server=" . $line['presence_server'] . "'>" . $line['presence_server'] . "</a></td><td" . $style . ">" . $line['type'] . "</td><td>" . $line['count'] . "</td>\n";
	  echo "\t</tr>\n";
	}
	echo "</table>\n";
	
	pg_free_result($result);

	echo "</td></tr></table>\n";
	echo "<br/>\n";
}

//
/////////////////////////////

echo "</body></html>\n";

// Free resultset
pg_free_result($result);

// Closing connection
pg_close($dbconn);
?>




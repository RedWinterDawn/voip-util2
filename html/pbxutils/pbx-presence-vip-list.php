<?php
echo "<html><body>\n\n";
echo "<html><head><link rel='stylesheet' href='stylesheet.css'></head><body>\n";
include('menu.html');
if (isset($_GET["action"]))
{
	$action = $_GET["action"];
} else
{
	$action = "List";
}

if (isset($_GET["domain"])) { $domain = $_GET["domain"]; } else { $domain = ''; }

/////////////////////////////
// Add domain
if ($action == "Add") {
	$dbconnutil = pg_connect("host=rwdb dbname=util user=postgres ")
		or die('Could not connect: ' . pg_last_error());
	$query = "INSERT INTO vip (domain) VALUES ('" . $domain . "');";
	$result = pg_query($query) or die('Query failed while addin domain ' . $domain . ': ' . pg_last_error());
	echo "Added " . $domain . "<br/>\n";
	sleep(3);

	pg_free_result($result);
	pg_close($dbconnutil);
	$action = "List";
}


/////////////////////////////
// List VIPs
if ($action == "List") {

echo "<pre>\n";
$viplist[] = '';
$vipcount = 0;
$presence_server = '10.101.40.1';

$dbconnutil = pg_connect("host=rodb dbname=util user=postgres ")
    or die('Could not connect: ' . pg_last_error());

/////////////////////////////
// Read Santa users from VIP table
$query = "SELECT domain FROM vip ORDER BY domain desc;";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    foreach ($line as $col_value) {
      $vipcount = $vipcount + 1;
      $viplist[$vipcount] = $col_value; 
    }
}
//echo "Found " . $vipcount . " records\n\n";
//
/////////////////////////////

pg_free_result($result);
pg_close($dbconnutil);

/////////////////////////////
// Display per domain info
//echo "Listing $vipcount records... \n";

$dbconnpbxs = pg_connect("host=rodb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());

echo "<table border=1>\n";
echo "<tr><th colspan=7>VIP list</th></tr>\n";
echo "<tr><th>domain</th><th>assigned server</th><th>presence server</th><th>name</th><th>phone count</th><th>polycom vvx count</th></tr>\n";

while ($vipcount > 0) {
    echo "\t<tr>";

    $query = "SELECT domain,assigned_server,presence_server,state,name FROM resource_group WHERE domain = '" . $viplist[$vipcount] . "';";
    $result = pg_query($dbconnpbxs,$query) or die('Query failed: ' . pg_last_error());
	if ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		echo "<td><a href='domain-info.php?domain=" . $line['domain'] . "'>" . $viplist[$vipcount] . "</a></td>"
			. "<td><a href='pbx-server-info.php?server=" . $line['assigned_server'] . "'>" . $line['assigned_server'] . "</a></td>"
		   	. "<td><a href='presence-server-info.php?server=" . $line['presence_server'] . "'>" . $line['presence_server'] . "</a></td><td>" . $line['name'] . "</td>";

		$query = "SELECT count(*) FROM resource_group
			 left join user_agent on (user_agent.resource_group_id = resource_group.id) WHERE domain = '" . $viplist[$vipcount] . "';";
	    $result = pg_query($dbconnpbxs,$query) or die('Query failed: ' . pg_last_error());
		if ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
				echo "<td>" . $line['count'] . "</td>";
		}

		$query = "SELECT count(*) FROM resource_group
			left join user_agent on (user_agent.resource_group_id = resource_group.id) WHERE domain = '" . $viplist[$vipcount] . "'
			and substring(user_agent.type_id for 11)='polycom.vvx';";
	    $result = pg_query($dbconnpbxs,$query) or die('Query failed: ' . pg_last_error());
		if ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
				echo "<td>" . $line['count'] . "</td>";
		}
	} else {
		echo "<td>" . $viplist[$vipcount] . "</td><td colspan=5>*** not found ***</td>";
	}

    $vipcount = $vipcount - 1;
    pg_free_result($result);
    echo "</tr>\n";
}

echo "</table>\n";
echo "</pre>";

pg_close($dbconnpbxs);
//
/////////////////////////////

} // end if action == list

/////////////////////////////
// Control to add domain
echo '<div><form action="" method="get"><input type="hidden" name="action" value="Add" /><input type="text" name="domain" size="96" /></form></div><br/>';

echo "</body></html>\n";

?>







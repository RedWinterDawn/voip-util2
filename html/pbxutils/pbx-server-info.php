<html><head><link rel='stylesheet' href='stylesheet.css'></head><body>

<?php
include 'menu.html';
if (isset($_GET["server"]))
{
	$assigned_server = $_GET["server"];
} else
{
	die("No server specified");
}

if (isset($_GET["state"]))
{
	$requested_state = $_GET["state"];
	$query_state = " AND state = '" . $_GET["state"] . "' ";
} else
{
	$requested_state = "";
	$query_state = "";
}

$count = 0;
$limit = 5000;
if ($utilConn = pg_connect("host=rodb dbname=util user=postgres")) {
	$pbxDCresult = pg_fetch_array(pg_query($utilConn, "SELECT location FROM pbxstatus WHERE ip = '$assigned_server'"));
	$pbxDC = $pbxDCresult['location'];
	pg_close($utilConn);
} 
if (!$pbxDC) {
	$pbxDC = 'a site, somewhere, almost probably';
}
$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());

$query = "SELECT id,domain,name,state,assigned_server,presence_server,location,v5 FROM resource_group WHERE assigned_server='" . $assigned_server . "' " . $query_state . " ORDER BY state,domain LIMIT " . $limit;
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

echo "<br/><h2>$assigned_server in <a href='site-info.php#$pbxDC'>$pbxDC</a></h2><br/>\n";

echo "<table border=1>\n";
echo "<tr><th>domain</th><th>name</th><th>state</th><th>assigned_server</th><th>presence_server</th><th>location</th><th>v5 migrated</th></tr>\n";

while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    $count = $count + 1;
	if ($line['presence_server'] != '') { $santa = "v5 (<a href='presence-server-info.php?server=" . $line['presence_server'] . "'>" . $line['presence_server'] . "</a>)"; } else { $santa = "v4"; }
    echo "\t<tr>";
//    foreach ($line as $col_value) { echo "\t\t<td>$col_value</td>\n"; }
	echo "<td><a href='domain-info.php?domain=" . $line['domain'] . "'>" . $line['domain'] . "</a></td>"
		. "<td>" . $line['name'] . "</td>"
		. "<td>" . $line['state'] . "</td>"
		. "<td>" . $line['assigned_server'] . "</td>"
		. "<td><center>" . $santa . "</center></td>"
		. "<td>" . $line['location'] . "</td>"
		. "<td><center>" . $line['v5'] . "</center></td>";
    echo "</tr>\n";
}
echo "</table>\n";
echo "Total domains: " . $count . "<br/>\n";

if ($count == $limit) {
    echo "WARNING: Limit reached (" . $limit . ")!<br/>\n";
}

pg_free_result($result);
pg_close($dbconn);
?>

</body>
</html>

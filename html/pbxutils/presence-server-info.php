<html><head><link rel='stylesheet' href='stylesheet.css'></head><body>

<?php
include('menu.html');
if (isset($_GET["server"]))
{
	$presence_server = $_GET["server"];
} else
{
	die("No server specified");
}

$count = 0;
$limit = 5000;

$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());

if ($presence_server = 'Unassigned')
{
	$presenceQuerySegment = "presence_server is null";
} else {
	$presenceQuerySegment = "presence_server='" . $presence_server . "'";
}

$query = "SELECT id,domain,name,state,assigned_server,presence_server,bleeding,location FROM resource_group WHERE " . $presenceQuerySegment . " ORDER BY state,domain LIMIT " . $limit;
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

echo "<br/><h2>Presence server " . $presence_server . "</h2><br/>\n";

echo "<table border=1>\n";
echo "<tr><th>domain</th><th>name</th><th>state</th><th>assigned_server</th><th>presence_server</th><th>location</th></tr>\n";

while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    $count = $count + 1;
	if ($line['presence_server'] != '') { $santa = "v5 (" . $line['presence_server'] . ")"; } else { $santa = "v4"; }
    echo "\t<tr>";
	echo "<td><a href='domain-info.php?domain=" . $line['domain'] . "'>" . $line['domain'] . "</a></td>"
		. "<td>" . $line['name'] . "</td>"
		. "<td>" . $line['state'] . "</td>"
		. "<td><a href='pbx-server-info.php?server=" . $line['assigned_server'] . "'>" . $line['assigned_server'] . "</a></td>"
		. "<td>" . $santa . "</td>"
		. "<td>" . $line['location'] . "</td>";
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

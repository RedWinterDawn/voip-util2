<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');
$guiltyParty = $_SERVER['REMOTE_ADDR'];
$requestTime = strftime('%Y-%m-%d %H:%M:%S');

$alldomains = true;

if (isset($_GET["action"]))
{
    $action = $_GET["action"];
} else
{
    $action = "List";
}

// Connecting, selecting database
$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());

// SQL Query
// $typeQuery = "SELECT type_id, count(type_id) as count FROM user_agent GROUP BY type_id ORDER BY count DESC;";
$typeQuery = "SELECT type_id, count(type_id) as count FROM user_agent
	LEFT JOIN resource_group ON user_agent.resource_group_id = resource_group.id
	WHERE resource_group.state = 'ACTIVE'
	GROUP BY type_id ORDER BY count DESC;";
$typeResult = pg_query($typeQuery) or die('Type query failed: ' . pg_last_error());

// Printing results in HTML
$totalCount = 0;
echo "<table border=1>\n";
echo "<tr><th>Type</th><th>Count</th></tr>\n";	
while ($typeRow = pg_fetch_array($typeResult, null, PGSQL_ASSOC)) {
    echo "\t<tr>\n";
    // foreach ($typeRow as $col_value) {echo "\t\t<td>$col_value</td>\n";}
	echo "\t\t<td>" . $typeRow['type_id'] . "</td><td>" . $typeRow['count'] . "</td>\n";
	$totalCount = $totalCount + $typeRow['count'];
    echo "\t</tr>\n";
}
echo "</table>\n";

echo "<pre>Total: $totalCount</pre>";

// Free resultset
pg_free_result($typeResult);

// Closing connection
pg_close($dbconn);
?>




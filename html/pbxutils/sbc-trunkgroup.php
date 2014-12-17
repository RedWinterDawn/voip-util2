<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');

$dbconn = pg_connect("host=rodb dbname=siptrunk user=siptrunk ")
    or die('Could not connect: ' . pg_last_error());

$query = "SELECT trunkgroup_id,resource_group_id,user_id,channel_limit,description FROM trunkgroup";

$result = pg_query($query) or die('Query failed: ' . pg_last_error());

// Printing results in HTML
// // trunkgroup_id             |          resource_group_id           |            user_id             | channel_limit |   description
echo "<table border=1>\n";
echo "<th>trunkgroup_id</th><th>resource_group_id</th><th>user_id</th><th>channel_limit</th><th>description</th>\n";
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    echo "\t<tr>";
    foreach ($line as $col_value) {
		if ($col_value == "f")
		{
			echo '<td class="yellow">' . "<center>false</center>" . "</td>";
		}else if ($col_value == "t")
		{
			echo '<td class="green">' . "<center>true</center>" . "</td>";
		}else
		{
			echo "<td>" . $col_value . "</td>";
		}
    }
    echo "</tr>\n";
}
echo "</table>\n";

// Free resultset
pg_free_result($result);

// Closing connection
pg_close($dbconn);
?>

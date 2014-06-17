<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');
// Connecting, selecting database
$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());

// Performing SQL query
//$query = 'SELECT enabled,priority,name,number_format,originates,terminates,supports_fax,supports_emergency FROM peer ORDER BY enabled DESC,priority,name ';
 $query = "SELECT enabled,count(*) as count,priority,name,number_format,originates,terminates,supports_fax,supports_emergency
  FROM peer
  LEFT JOIN master_did on (peer.id = master_did.source_peer_id)
  WHERE enabled = 't' AND master_did.active = 't'
  GROUP BY peer.id
  ORDER BY count desc,priority,name";

$result = pg_query($query) or die('Query failed: ' . pg_last_error());

// Printing results in HTML
echo "<table border=1>\n";
echo "<th>enabled</th><th>count</th><th>priority</th><th>name</th><th>number_format</th><th>originates</th><th>terminates</th><th>supports_fax</th><th>supports_emergency</th>\n";
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    echo "\t<tr>";
    foreach ($line as $col_value) {
		if ($col_value == "f")
		{
			echo '<td class="red">';
		}else if ($col_value == "t")
		{
			echo '<td class="green">';
		}else
		{
			echo "<td>";
		}

		echo "$col_value";
		echo "</td>";
    }
    echo "</tr>\n";
}
echo "</table>\n";

// Free resultset
pg_free_result($result);

// Closing connection
pg_close($dbconn);
?>

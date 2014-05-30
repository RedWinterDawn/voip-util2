<link rel='stylesheet' href='stylesheet.css'>
<?php
// Connecting, selecting database
$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());

// Performing SQL query
$query = 'SELECT host,active,bucket FROM pbx_node ORDER BY active desc,host ';
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

// Printing results in HTML
echo "<table border=1>\n";
echo "<th>host</th><th>active</th><th>bucket</th>\n";

while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    echo "\t<tr>\n";
    foreach ($line as $col_value) {
        echo '		<td align="center"';
        if ($col_value=="t"){echo ' class="green"';}
		if ($col_value=="f"){echo ' class="red"';}
        echo ">$col_value</td>\n";
    }
    echo "\t</tr>\n";
}
echo "</table>\n";

// Free resultset
pg_free_result($result);

// Closing connection
pg_close($dbconn);
?>

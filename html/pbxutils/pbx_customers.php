<link rel='stylesheet' href='stylesheet.css'>
<?php

// determine response format
if (isset($_GET["format"]))
{
 $format = $_GET["format"]; // html, xml
} else
{
 $format = "xml";
}

if ($format == "html")
{
} else if ($format == "xml")
{
} else
{
 $format="invalid";
 die("invalid format (" . $format . ")");
}

// echo "DEBUG: " . $format . "\n";

// Connecting, selecting database
$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());

// Performing SQL query
$query = 'SELECT domain,name,state,assigned_server,bleeding,dialing_resource_id,location,resource_group_id,bucket FROM resource_group WHERE state!=\'DEACTIVATED\' ORDER BY domain LIMIT 100';
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

if ($format == "html") {
    // Printing results in HTML
    echo "<table border=1>\n";
    echo "<th>domain</th><th>name</th><th>state</th><th>assigned_server</th><th>bleeding</th><th>dialing_resource_id</th><th>location</th><th>resource_group_id</th><th>bucket</th>\n";
    while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
        echo "\t<tr>\n";
        foreach ($line as $col_value) {
            echo "\t\t<td>$col_value</td>\n";
        }
        echo "\t</tr>\n";
    }
    echo "</table>\n";
}

if ($format == "xml") {
	// Printing results in XML
    // Start the XML header
	header("Content-type: text/xml");
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

	echo "<NodeList>\n";
    while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
	    echo "\t<Node>\n";
        echo "\t\t<Host>" . $line["host"] . "</Host>\n\t\t<Active>" . $line["active"] . "</Active>\n\t\t<Bucket>" . $line["bucket"] . "</Bucket>\n";
	    echo "\t</Node>\n";
	}
	echo "</NodeList>\n";
}

// Free resultset
pg_free_result($result);

// Closing connection
pg_close($dbconn);
?>

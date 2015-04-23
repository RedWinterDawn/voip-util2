<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');
include('guiltyParty.php');
$requestTime = strftime('%Y-%m-%d %H:%M:%S');

if (isset($_GET["did"]))
{
	$did = pg_escape_string($_GET["did"]);
} else
{
	die("No DID requested");
}

// $utildb = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connect to utildb: '.pg_last_error());

$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());

// $didQuery = "SELECT number,active,outbound_routable,destination_pbx_id,source_peer_id,caller_id_name,e911_address_id,directory_address_id,phone_book_delivery_address_id
//    	FROM master_did WHERE number LIKE '" . $did . "%' ORDER BY number ASC LIMIT 10;";
$didQuery = "SELECT number,active,outbound_routable,destination_pbx_id,source_peer_id,caller_id_name,e911_address_id,directory_address_id,phone_book_delivery_address_id,peer.name as peer
	    FROM master_did
		LEFT JOIN peer ON (master_did.source_peer_id = peer.id)
		WHERE number LIKE '" . $did . "%'
		ORDER BY active DESC,number ASC;";
$didResult = pg_query($didQuery) or die('DID query failed: ' . pg_last_error() . '\n' . '<pre>' . $didQuery . '</pre>');

echo "<table border=2>\n";
echo "<th>number</th><th>outbound_routable</th><th>caller_id_name</th><th>source peer</th><th>active</th><th>destination pbx id</th><th>e911 address id</th>";
while ($didRow = pg_fetch_array($didResult, null, PGSQL_ASSOC)) {
	if ($didRow['outbound_routable'] == 't') {
		$didOutbound = "<div class='green'>TRUE</div>";
	} else {
		$didOutbound = "<div class='yellow'>FALSE</div>";
	}

	if ($didRow['active'] == 't') {
		$didActive = "<div class='green'>TRUE</div>";
	} else {
		$didActive = "<div class='yellow'>FALSE</div>";
	}

	echo "<tr>"
		. "<td>" . $didRow['number'] . "</td>"
		. "<td><center>" . $didOutbound . "</center></td>"
		. "<td>" . $didRow['caller_id_name'] . "</td>"
		. "<td>" . $didRow['peer'] . "</td>"
		. "<td>" . $didActive . "</td>"
		. "<td>" . $didRow['destination_pbx_id'] . "</td>"
		. "<td>" . $didRow['e911_address_id'] . "</td>"
		. "</tr>\n";
}
echo "</table>\n";

// Free resultset
pg_free_result($typeResult);
pg_free_result($didResult);

// Closing connection
pg_close($dbconn);
?>

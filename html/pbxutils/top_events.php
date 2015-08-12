<!doctype html>
<html>
<head>
<?php
$accesslevel = 1;
include('checksession.php');
?>
	<title>Top Events</title>
	<link rel='stylesheet' href="stylesheet.css">
</head>
<body>
<?
include('menu.html');
?>
<h2>Top Events</h2>

<form action='' method='GET'>
<input type='hidden' name='page' value=1 />
Start Date: <input type='date' name='start' placeholder='start: 2013-12-31' />
End Date:  <input type='date' name='end' placeholder='end: 2015-12-31' /> Note that the start and end dates cannont be the same.<br>
<select name='etype'>
	<option value='ABANDON'>Abandon</option>
	<option value='MASS'>Mass Exodus</option>
	<option value='SINGLE'>Single Migration</option>
	<option value='ARCHIVE'>Archive</option>
	<option value='PRESENCE'>Presence</option>
	<option value='DID'>DID</option>
	<option value='2V5'>Move to v5</option>
	<option value='2V4'>Move to v4</option>
</select>
<input type='submit' />
</form>

<?

if (isset($_GET['page'])) {
	$page = $_GET['page'];
} else {
	$page = 1;
}

if ($page < 1) {
	$page = 1;
}

$prev=$page-1;
$next=$page+1;

if (isset($_GET['etype'])) {
	$eType = $_GET['etype'];
} else {
	$eType = "ABANDON";
}

$start = $_GET['start'];
$end = $_GET['end'];
if ($start == '' || $end == '') {
	$start = 'ALL TIME';
	$end = 'ALL TIME';
}


echo "<br>Current Event Type: $eType<br>
	Starting on: $start<br>
	Ending before: $end<br><br>";
?>

<table border=1>
<tr><td colspan=4><center>
<?
echo "<a href='top_events.php?page=$prev&etype=$eType&start=$start&end=$end'>&laquo;</a>
	<b> Page $page </b>
	<a href='top_events.php?page=$next&etype=$eType&start=$start&end=$end'>&raquo;</a><br>";

?>
</center></td></tr>
<tr>
	<th>Count</th>
	<th>Domain</th>
	<th>Server IP</th>
	<th>Server Status</th>
</tr>

<?
//REKEY FUNCTION -------------------
// This function takes an array of arrays and returns a new array that has a single set of keys
// associated with a single set of values (for faster lookups later)
//
// Input: Array('0'=>Array('server'=>'10.0.0.0','load'=>'100'),'1'=>Array('server'=>'10.0.0.5','load'=>'150').... )
// Output: Array('10.0.0.0'=>'100','10.0.0.5'=>'150', ..... )
function rekey ($multiArray, $key, $value) {
    $newArray = Array();
    foreach ($multiArray as $array) {
        $newArray[$array[$key]] = $array[$value];
    }
    return $newArray;
}

$utildb = pg_connect("host=rodb user=postgres dbname=util");
$pbxsdb = pg_connect("host=rodb user=postgres dbname=pbxs");
$eventdb = pg_connect("host=rodb user=postgres dbname=events");

// Offset is how far from the start of records to look
$offset = 100 * ($page - 1);
$limit = 100;

$eventCountQ = "SELECT count(*), domain_id
	FROM event_domain as d, event as e
	WHERE e.id = d.event_id
	AND e.event_type = '$eType'";
if ($start != "" && $start != "ALL TIME") {
	$eventCountQ .= "
		AND e.added BETWEEN '$start' and '$end'";
}
$eventCountQ .= "
	GROUP BY domain_id
	ORDER BY count DESC
	LIMIT $limit
	OFFSET $offset";
$eventCount = pg_fetch_all(pg_query($eventdb, $eventCountQ)) or die ("Broke: QUERY[".$eventCountQ."]<br>".pg_last_error());

$domainQ = "SELECT id, domain, assigned_server
	FROM resource_group
	WHERE id in (";
foreach ($eventCount as $event) {
	$domainQ .= "'" . $event['domain_id'] . "',";
}
//Chop off the last comma and close the query
$domainQ = substr($domainQ, 0, -1) . ");";
$domains = pg_fetch_all(pg_query($pbxsdb, $domainQ)) or die ("Borked: QUERY[".$domainQ."]<br>".pg_last_error());

$pbxQ = "SELECT ip, status
	FROM pbxstatus
	WHERE ip in (";
foreach ($domains as $domain) {
	if ($domain['assigned_server'] != "") {
		$pbxQ .= "'" . $domain['assigned_server'] . "',";
	}
}
$pbxQ = substr($pbxQ, 0, -1) . ");";
$pbxs = pg_fetch_all(pg_query($utildb, $pbxQ)) or die ("Biernted: QUERY[".$pbxQ."]<br>".pg_last_error());

$IDdomains = rekey($domains, "id", "domain"); 
$IDaddrs = rekey($domains, "id", "assigned_server");
$pbxs = rekey($pbxs, "ip", "status");

foreach ($eventCount as $event) {
	$count = $event['count'];
	$id = $event['domain_id'];
	$domain = $IDdomains[$id];
	$ip = $IDaddrs[$id];
	$status = $pbxs[$ip];
	echo "<tr>";
	echo "<td>$count</td>";
	echo "<td>$domain</td>";
	echo "<td>$ip</td>";
	echo "<td>$status</td>";
	echo "</tr>";
}

?>

</table>

</body>
</html>

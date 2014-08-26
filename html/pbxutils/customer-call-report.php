<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');
$guiltyParty = $_SERVER['REMOTE_ADDR'];
$requestTime = strftime('%Y-%m-%d %H:%M:%S');
$resource_group_id = "";

$showSelector = true;
$todayDate = strftime('%Y-%m-%d');

if (isset($_GET["action"])) {
	$action = $_GET["action"];
} else {
	$action = "ShowSelector";
	$showSelector = true;
}

if (isset($_GET["domain"])) {
	$domain = pg_escape_string($_GET["domain"]);
} else {
	$domain = "";
	$action = "ShowSelector";
	$showSelector = true;
}

if (isset($_GET["birthday"])) {
	$reportDate = pg_escape_string($_GET["birthday"]);
} else {
	$reportDate = strftime('%Y-%m-%d');
	$action = "ShowSelector";
	$showSelector = true;
}

if ($showSelector == true) {
	// Show domain and date selection controls
	echo '<form>' . "\n";
	echo 'Domain: <input type="text" name="domain" value="' . $domain . '"><br/>' . "\n";
	echo 'Date: <input type="date" name="birthday" value="' . $reportDate . '"><br/>' . "\n";
	echo '<input type="hidden" name="action" value="doSearch"/>' . "\n";
	echo '<input type="submit"><br/>' . "\n";
	echo '</form><br/>' . "\n";
}

if ($action == "doSearch") {
	// Get pbxID
	$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
		or die('Could not connect: ' . pg_last_error());
	$pbxQuery = "SELECT id,name FROM resource_group WHERE domain = '" . $domain . "'";
	$pbxResult = pg_query($pbxQuery) or die('PBX query failed: ' . pg_last_error() . " \n" . $pbxQuery);
	pg_close($dbconn);
	if ($pbxRow = pg_fetch_array($pbxResult, null, PGSQL_ASSOC)) {
		$pbxID = $pbxRow['id'];
		$pbxName = $pbxRow['name'];
	} else {
		echo "Unable to locate domain '" . $domain . "'<br/>";
		break;
	}
	pg_free_result($pbxResult);

	echo "<br/>\n<pre>";
	echo "Domain = " . $domain . "\n";
	if ($todayDate == $reportDate) {
		echo "Date = " . $reportDate . " (partial)\n";
	} else {
		echo "Date = " . $reportDate . "\n";
	}
	echo "ID = " . $pbxID . "\n";
	echo "Name = " . $pbxName . "\n";
	echo "</pre><br/>\n";

	$dbconn = pg_connect("host=cdr dbname=asterisk user=postgres ")
		or die('Could not connect: ' . pg_last_error());
	$reportQuery = "SELECT * FROM cdr WHERE \"end\" > '$reportDate' AND \"end\" < (date '$reportDate' + interval '1 day')
		AND (source_pbx_id = '" . $pbxID . "' OR destination_pbx_id = '" . $pbxID . "')";
	$reportResult = pg_query($reportQuery) or die('Report query failed: ' . pg_last_error());
	pg_close($dbconn);

	// Puke to page
	$callCount = 0;
	$totalDuration = 0;
	echo "<table border=2>\n";
	echo "<th>start</th><th>answer</th><th>end</th><th>caller id</th><th>source</th><th>destination</th><th>answered duration</th>\n";
	while ($reportRow = pg_fetch_array($reportResult, null, PGSQL_ASSOC)) {
		echo "<tr>";
		echo "<td>" . $reportRow['start'] . "</td>";
		echo "<td>" . $reportRow['answer'] . "</td>";
		echo "<td>" . $reportRow['end'] . "</td>";
		echo "<td>" . $reportRow['clid'] . "</td>";
		echo "<td>" . $reportRow['src'] . "</td>";
		echo "<td>" . $reportRow['dst'] . "</td>";
		echo "<td>" . $reportRow['billsec'] . "</td>";
		echo "</tr>\n";
		$callCount = $callCount + 1;
		$totalDuration =$totalDuration + $reportRow['billsec'];
	}
	echo "</table><br/>\n";
	echo "Total calls: " . $callCount . "<br/>\n";
	echo "Total duration: " . $totalDuration . "<br/>\n";



	pg_free_result($reportResult);
}


?>

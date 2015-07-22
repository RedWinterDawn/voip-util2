<html><head>
<script src="js/sorttable.js"></script>
<link rel='stylesheet' href='stylesheet.css'></head><body>

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

//Status Changer
$statusQuery = "";
if (isset($_GET['status'])) {
  $newStatus = $_GET['status'];
  $statusQuery = "UPDATE pbxstatus SET status = '$newStatus', occupant = '${_GET['occupant']}', message = '${_GET['message']}'";
  if ($newStatus == 'dirty') {
    $statusQuery .= ", abandoned = 'now()'";
  }
  $statusQuery .= " WHERE ip = '$assigned_server';";
}

$count = 0;
$limit = 5000;
if ($utilConn = pg_connect("host=rodb dbname=util user=postgres")) {
  if ($statusQuery != "") {
    pg_query($utilConn, $statusQuery);
  }
  $pbxStatuses = pg_fetch_all(pg_query($utilConn, "SELECT name, description FROM status ORDER BY displayorder;"));
	$pbxDCresult = pg_fetch_array(pg_query($utilConn, "SELECT location,failgroup,message,occupant,status FROM pbxstatus WHERE ip = '$assigned_server'"));
	$pbxDC = $pbxDCresult['location'];
  $pbxFG = $pbxDCresult['failgroup'];
  $pbxOC = $pbxDCresult['occupant'];
  $pbxST = $pbxDCresult['status'];
  $pbxMess = $pbxDCresult['message'];
	pg_close($utilConn);
} 
if (!$pbxDC) {
	$pbxDC = 'a site, somewhere, almost probably';
  $pbxFG = 101;
}
$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());

// Get load values for each of the customers on this server. 
$cdrconn = pg_connect("host=cdr dbname=asterisk user=postgres");
$loadsQ = "SELECT id, (load_in + load_out + load_custom) as load FROM loadmetrics WHERE assigned_server = '$assigned_server'";
$loads = pg_query($cdrconn, $loadsQ);
$loadValues = Array();
$loadTotal = 0;
while ($load = pg_fetch_assoc($loads)) {
	$loadValues[$load['id']] = $load['load'];
	$loadTotal += $load['load'];
}
pg_close($cdrconn);

//Get device count for each domain on this server.
$query = "SELECT resource_group_id as id, count(id) as count FROM user_agent WHERE resource_group_id IN (SELECT id FROM resource_group WHERE assigned_server = '$assigned_server') GROUP BY resource_group_id ORDER BY count DESC;";
$dCount = pg_query($dbconn, $query) or die ('dCount failed: ' . pg_last_error());
$dValues = Array();
$cTotal = 0;
while ($count = pg_fetch_assoc($dCount)){
	$dValues[$count['id']] = $count['count'];
	$dTotal += $count['count'];
}


$query = "SELECT id,domain,name,state,assigned_server,location,v5candidate FROM resource_group WHERE assigned_server='" . $assigned_server . "' " . $query_state . " ORDER BY state,domain LIMIT " . $limit;
$result = pg_query($dbconn, $query) or die('Query failed: ' . pg_last_error());

echo "<br/><h2>$assigned_server in <a href='pbx-availability.php?display=$pbxFG'>$pbxDC</a></h2>\n";

$loadPercent = round($loadTotal / 140000, 0);
$color = 'green';
if ($loadPercent > 85) { $color = 'yellow'; }
if ($loadPercent > 95) { $color = 'red'; }

echo "Server load = $loadTotal, which is <span class='$color'>$loadPercent%</span>";
echo "<br>Server Device Count = $dTotal";
?>
<div>
<br>
<table border=1>
<tr><th>Status</th><th>Occupant</th><th>Message</th></tr>
<tr><form action='' method='GET'>
  <input type='hidden' name='server' value='<?=$assigned_server;?>' />
  <td>
  <select name='status'>
  <?
    foreach($pbxStatuses as $stat) {
      if ($stat['name'] == $pbxST) {
        echo "<option value='${stat['name']}' selected>${stat['name']}</option>";
      } else {
        echo "<option value='${stat['name']}'>${stat['name']}</option>";
      }
    }
  ?>
  </select></td>
  <td><input type='text' name='occupant' value='<?=$pbxOC;?>' /></td>
  <td><input type='text' name='message' size='80' value='<?=$pbxMess;?>' /></td>
  <td><input type='submit' value='Update' /></td>
</form></tr>
</table>
</div>
<br><br>
<div>
<table class="sortable"  border=1>
  <tr><th>domain</th>
    <th>name</th>
    <th>state</th>
    <th>assigned_server</th>
    <th>load</th>
    <th>device count</th>
    <th>data location</th>
    <th>v5 candidate</th></tr>

<?
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    $count = $count + 1;
	if ($line['location'] == $pbxDC) { $locationColor = 'green'; } else { $locationColor = 'red'; }
	if ($line['v5candidate'] == 't') { $v5candidate = "TRUE"; }
	if ($line['v5candidate'] == 'f') { $v5candidate = "false"; }
	$loadTotal = $loadValues[$line['id']];
	$loadPercent = round($loadTotal / 140000, 2);
	$color = 'sky';
	$dTotal = $dValues[$line['id']];
	if ($loadPercent > 1) { $color = 'green'; }
	if ($loadPercent > 10) { $color = 'yellow'; }
	if ($loadPercent > 20) { $color = 'red'; }
	if ($loadPercent > 30) { $color = 'purple'; }
    echo "\t<tr>";
//    foreach ($line as $col_value) { echo "\t\t<td>$col_value</td>\n"; }
	echo "<td><a href='domain-info.php?domain=" . $line['domain'] . "'>" . $line['domain'] . "</a></td>"
		. "<td>" . $line['name'] . "</td>"
		. "<td>" . $line['state'] . "</td>"
		. "<td>" . $line['assigned_server'] . "</td>"
		. "<td class='$color' align='right'>" . $loadPercent . "%</td>"
		. "<td>" . $dTotal . "</td>"
		. "<td class='$locationColor' align='center'>" . $line['location'] . "</td>"
		. "<td><center>" . $v5candidate . "</center></td>";
    echo "</tr>\n";
}
echo "</table></div>\n";
echo "Total domains: " . $count . "<br/>\n";

if ($count == $limit) {
    echo "WARNING: Limit reached (" . $limit . ")!<br/>\n";
}

pg_free_result($result);
pg_close($dbconn);
?>

</body>
</html>

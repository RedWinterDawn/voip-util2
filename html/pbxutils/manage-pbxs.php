<!DOCTYPE html>
<html>
<head>
	<title>PBX Management</title>
	<link rel='stylesheet' href='stylesheet.css' />
	<style>
	.fatty {
		align-self: center;
		font-size: 2em;
		font-weight: bolder;
	}
	.highlighted {
		background: #444;
	}
	</style>
	<script type='text/javascript'>
		function showhide(pbx) {
			row = document.getElementById(pbx);
			row.style.display = (row.style.display == 'none' ? '' : 'none');
			button = document.getElementById(pbx.concat('_expander'));
			button.innerHTML = (row.style.display == 'none' ? '<img src="rarrow.png" />' : '<img src="darrow.png" />');
		}
	</script>
</head>
<body>
<!-- "Preload" the down arrow image --> 
<img src='darrow.png' style='display:none' /> 

<?
include ('menu.html');
include('pbx-menu.html');
?>
<table border=1>
<?
if (isset($_REQUEST['site'])) {
	$site = $_REQUEST['site'];
} else {
	$site = 'chicago-legacy';
}

?>

<h2><?= $site ?></h2>

<table border=1 width='100%'> 
<tr><td></td><th>Host Name</th><th>Status</th><th>Load</th><th>Customers</th><th>Devices</th><th>MPLS</th></tr>

<?
$utilConn = pg_connect("host=rodb dbname=util user=postgres") or die ("Postgres connection failed");

$pbxQuery = "SELECT host, status, load, ip, vmhost, message FROM pbxstatus WHERE location = '$site' ORDER BY ip";
$pbxResults = pg_query($pbxQuery);

$i = 0;
while ($pbx = pg_fetch_assoc($pbxResults)) {
	$i++;	
	if ($i % 2 == 0) {
		$col = 'highlighted';
	} else {
		$col = '';
	}

	switch ($pbx['status']) {
		case "active":
			$c = "green";
		case "standby":
			$c = "yellow";
		case "graveyard":
			$c = "gray";
	}
	echo "<tr class='$col'>
			<td><a id='pbx${i}_expander' onclick='showhide(\"pbx$i\")' href='#'><img src='rarrow.png' /></a></td>
			<td>${pbx['host']}</td>
			<td>${pbx['status']}</td>
			<td>${pbx['load']}</td>
			<td>A bunch</td>
			<td>Even more</td>
			<td>Nope</td>
		</tr>
		<tr class='$col' id='pbx${i}' style='display:none;'>
		<td colspan='7'>Things things things<br>More things more things<br>Still more things</td>
		</tr>";
}
	
?>

</table>
</body>
</html>

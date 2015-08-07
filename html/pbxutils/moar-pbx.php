<!doctype html>
<?php
$accesslevel = 4;
include('checksession.php');
?>
<html>
<head>
	<title>MOAR PBX</title>
	<link rel="stylesheet" href="stylesheet.css">
</head>
<body>
<?
include("menu.html");
?>

<h2>Moar PBX</h2>
<p>Enter the first and last pbx in the range (inclusive). Then enter name and number for the datacenter. DC number for atl is 22, not 122. :)

<div>
<form method="post" action="">
<label for="first">First pbx: </label><input type="text" name="first" id="first" /><br>
<label for="last">Last pbx: </label><input type="text" name="last" id="last" /><br>
<label for="dcnum">Datacenter number: </label><input type="text" name="dcnum" id="dcnum" placeholder='e.g. 22'/><br>
<label for="dcname">Site name: </label><input type="text" name="dcname" id="dcname" placeholder='e.g. atl'/><br>
<input type="submit" value="Moar PBX!" />
</form>
<br><br>
<? 
//Set your specific stuff here
// First is which pbx to start with
// Last is which pbx to end with
// DCNum is which cluster the pbxs are in (e.g. lax=19, nyc=20, etc)
// DCName is the name associated with the cluster (e.g. 'lax', 'nyc')
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

if (isset($_POST['first'])) {
$first=intval($_POST['first']);
$last=intval($_POST['last']);
$dcnum=intval($_POST['dcnum']);
$dcname=$_POST['dcname'];

if (!isset($first)) {
	die('No first was set');
}
if (!isset($last)) {
	die('No last was set');
}
if (!isset($dcnum)) {
	die('No dc number was set');
}
if (!isset($dcname)) {
	die('No dc name was set');
}

$pbxs_query = "INSERT INTO pbx_node VALUES "; 
$util_query = "INSERT INTO pbxstatus (host, ip, location, status, failgroup, site_id) VALUES ";


for ($i = $first; $i <= $last; $i++) {
	$pbxs_query .= "('10.1$dcnum.60.$i', 'f', 'jive-4-10')";
	$util_query .= "('megapbx$i.c$dcnum.jiveip.net', '10.1$dcnum.60.$i', '$dcname', 'inactive', '1$dcnum', '$dcname')";
	if ($i == $last) {
		$pbxs_query .= ";";
		$util_query .= ";";
	} else {
		$pbxs_query .= ", ";
		$util_query .= ", ";
	}
}
$pbxsconn = pg_connect('host=db user=postgres dbname=pbxs') or die("Couldn't connect to pbxs");
$result = pg_query($pbxsconn, $pbxs_query) or die("pbx-node query didn't work: ".pg_last_error()); 
pg_close($pbxsconn);
$utilconn = pg_connect('host=db user=postgres dbname=util') or die ("Couldn't connect to util");
$result = pg_query($utilconn, $util_query) or die("pbxstatus query didn't work: ".pg_last_error()); 
pg_close($utilconn);
echo "The success was good for to have moar pbx!";
}
?>


</body>
</html>

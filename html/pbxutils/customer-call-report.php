<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');
$guiltyParty = $_SERVER['REMOTE_ADDR'];
$requestTime = strftime('%Y-%m-%d %H:%M:%S');
$resource_group_id = "";

if (isset($_GET["action"]))
{
	$action = $_GET["action"];
} else
{
	$action = "ShowSelector";
}

if ($action == "ShowSelector") {
	// Show domain and date selection controls
	echo '<form>' . "\n";
	echo 'Domain: <input type="search" name="domain"><br/>' . "\n";
	echo 'Date: <input type="date" name="birthday"><br/>' . "\n";
	echo '<input type="submit"><br/>' . "\n";
	echo '</form><br/>' . "\n";
}


?>

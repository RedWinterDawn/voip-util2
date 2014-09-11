<?php
/* V5 Customer Migration Queue
 * This script will run a prefilght check on the domain to be moved to V5
 * then it will add them to the queue to be moved (aka database)
 *
 */

//CSS Styling:
echo '<html><head><title>v5 Migration Queue</title>
<style type="text/css"></style>
<link rel="stylesheet" href="stylesheet.css"></head>';

//Header
echo '<body onload="init()">';
include('menu.html');

echo '<div id="head" class="head">
<h2>v5 Customer Migration</h2>
<h2><a href="index.php">Back to pbxutils</a></h2>';

//Get Variables
//action possibilites 
//  search
//  help
//  add
$action = $_REQUEST["action"];
$domain = $_REQUEST["domain"];
$guiltyParty = $_SERVER['REMOTE_ADDR'];

//============//
//Add to Queue//
//============//
//1. check/get location
//2. check/get v5 flag
//3. check/get assigned server
//4. add to queue or reject
if ($action=='add')
{
	$rodb = pg_connect("host=rodb user=postgres dbname=pbxs") or die ('Failed to connect to rodb: ' .pg_last_error());
	$query = "SELECT location, assigned_server, v5 FROM resource_group WHERE domain = '".$domain."';";
	$result = pg_query($rodb, $query) or die ('Can not find domain: '.$domain. ' ' .pg_last_error());
	$result = pg_fetch_row($result);
	$location = $result[0];
    $server = $result[1];
    $v5 = $result[2];
	print $location;
    print $server;
    print $v5;
	pg_close($rodb);
    $utildb = pg_connect("host=rwdb user=postgres dbname=util") or die ('Failed to connect to utildb: '.pg_last_error());
	$query = "SELECT migrate_vm_to_v5 FROM v5_migration WHERE domain ='".$domain."';";
	$inQueue = pg_query($utildb, $query);
	if ($inQueue)
	{
		$inQueue = pg_fetch_row($inQueue);
	    if ($inQueue[0] == 'Pending' || $inQueue[0] == 'In Progress')
		{
			die ("Migration of ".$domain. 
	}	
	if ($v5=='t')
	{
		die ($domain." Is already on v5");
	}
	if ($location='chicago-legacy' && preg_match("/10.101.7./", $server))
	{
		$migrateToChi = 'Completed';
		$preflight = 't';
	}else
	{
		$migratedToChi = 'Pending';
		$preflight = 'f';
	}

	$insert = "INSERT INTO v5_migration (domain, migrate_to_chi, preflight) VALUES ('".$domain."', '".$migrateToChi."', ".$preflight.");";
	print $insert;

}
?>

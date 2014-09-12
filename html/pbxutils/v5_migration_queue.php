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
//  search to be added
//  help to be added
//  add
if (isset ($_REQUEST["action"]))
{
  $action = $_REQUEST["action"];
}else
{
  $action = 'add';
}
if (isset ($_REQUEST["domain"]))
{
  $domain = $_REQUEST["domain"];
}else
{
  die (("No domain was passed in"));
}
include('guiltyParty.php');

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
	pg_close($rodb);
  $utildb = pg_connect("host=rwdb user=postgres dbname=util") or die ('Failed to connect to utildb: '.pg_last_error());
	$query = "SELECT migrate_vm_to_v5 FROM v5_migration WHERE domain ='".$domain."';";
	$inQueue = pg_query($utildb, $query);
	if ($inQueue)
	{
		$inQueue = pg_fetch_row($inQueue);
	  if ($inQueue[0] == 'Pending' || $inQueue[0] == 'In Progress')
		{
      die ("Migration of ".$domain. " is ".$inQueue);
    } 
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
		$migrateToChi = 'Pending';
		$preflight = 'f';
	}

	$insert = "INSERT INTO v5_migration (domain, migrate_to_chi, preflight) VALUES ('".$domain."', '".$migrateToChi."', '".$preflight."');";
  pg_query($utildb, $insert) or die ("failed to add ".$domain." to the queue: ".pg_last_error());
  print "Added ".$domain." to be migrated to v5";
  $pg_close($utildb);

   //Record event in the event database
/*  $eventDb = pg_connect("host=rwdb dbname=events user=postgres") or die('Could not connect: '. pg_last_error());
  $description = $guiltyParty." Queued ".$domain." to be migrated to v5";
  $eventID = pg_fetch_row(pg_query($eventDb, "INSERT INTO event(id, description) VALUES(DEFAULT, '" . $description . "') RETURNING id;"));
  pg_query($eventDb, "INSERT INTO event_domain VALUES('" . $eventID['0'] . "', '" .$id. "')");
  pg_close($eventDb); //Close the event DB connection
 */
}
?>

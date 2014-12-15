<?php

include('loadUpdate.php');
include('guiltyParty.php');
header('Cache-Control: no-cache');
//We actually have enough clients that this script runs out of memory 
//when working with the default 16 MB (can handle ~7,200 clients with 16 MB)
ini_set('memory_limit', '64M');

//Flushing the outbound cache allows us to send updates to the client 
//before the PHP is actually finished running. 
//Note that updates less than 256 bytes will not trigger a page update at the browser. 
function flushOutput() {
	echo(str_repeat(' ', 256));
	if (@ob_get_contents()) {
		@ob_end_flush();
	}
	flush();
}

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

//ADDKEY FUNCTION ---------------------
// This function adds a key to an array. The key must be taken from another array with a common key
//
// Example: 
// $array = Array (0 => Array( id => 5, server => 10), 1 => Array( .... ) ... )
// $valueSource = Array (0 => Array (id => 5, load => 15 ), 1 => Array ( .... ) ... )
// $commonKey = 'id'
// $newKey = 'load'
//
// Output = Array (0 => Array (id => 5, server => 10, load => 15), 1 => Array( .... ) ... )
function addKey ($array, $valueSource, $commonKey, $newKey, $default) {
	$len = sizeof($array); 
	$hash = array(); //Hash will be used to translate location of items in the array to the valuesource
	$newArray = array(); //The array we'll return
	for ($i = 0; $i < $len; $i++) {
		$hash[$array[$i][$commonKey]] = $i; //Add the id => index pair to the hash for later lookup
		$array[$i][$newKey] = $default; //Add a default value just to be sure that all records have something
	}
	$i = 0; // Using this to create indices in the new array
	foreach ($valueSource as $key => $value) {
		if (isset($hash[$key])) {
			$newArray[$i] = $array[$hash[$key]]; //Setting the values in the ordered new array to the corresponding original
			$newArray[$i][$newKey] = $value; //... and overwriting the default value with the desired one
			unset($hash[$key]); //Removing this index from the hash to keep track of which ones we've set.
			$i++; //Increment index
		}
	}

	//NOTE: Intentionally not setting $i = 0 !!! We want to keep adding at the END of the new array
	foreach ($hash as $h) {
		$newArray[$i] = $array[$h]; //This will happen only if there were hashes that didn't get unset previously
		$i++; //Keep adding to the end of newArray until all hashes are gone
	}
	return $newArray;
}

//LOADDISTRIBUTE FUNCTION -----------------------
// This function loops over the list of clients and assigns each client a new server and location.
// The selected server will be whichever has the least load according to the metrics we have.
function loadDistribute ($clients, $servers, $location, $max) {
	$len = sizeof($clients);

	//Loop over the client array
	for ($i = 0; $i < $len; $i++) {
		//Check to see if the least populated server is full
		//If so, die. 
		if (min($servers) >= $max) {
			echo $location, "<br>";
			print_r($servers);
			//When dying, return the number of clients not moved
			return Array($clients, $len-$i);
		}	

		//If for some reason the client is not currently assigned a location, don't assign one
		if ($clients[$i]['assigned_server'] == "") {
			continue;
		}

		//Get the server IP address for the least populated server
		$server = array_keys($servers, min($servers));
		//Assign the current client to the server and location, setting secondary location to previous location
		$clients[$i]['assigned_server'] = $server[0];
		$clients[$i]['secondary_location'] = $clients[$i]['location'];
		$clients[$i]['location'] = $location;
		//Update the load of this server by the amount of load we just added
		$servers[$server[0]] += $clients[$i]['load'];
	}
	//If we fit all of the clients, return the array and 0 (since 0 clients did not fit)
	return Array($clients, 0);
}

//POSTED INPUTS --------------------
// Action: submit, confirm, etc.
// method: pbx or site
// override: use custom destination?
// destination: where to really send clients
// source: where are we moving people from?
if (isset($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
	$method = $_REQUEST['method'];
	if ($_REQUEST['destination']!='') {
		$destination = $_REQUEST['destination'];
		$override = true;
	}
	$source = $_REQUEST['source'];
}

if (isset($argv[1])) {
	$action = $argv[1];

	if (isset($argv[2])) { $method = $argv[2]; }

	if (isset($argv[3])) 
	{
	   	$source = $argv[3];
	} else { 
		die ("You must provide 3 args: submit {site|pbx} {source-site|source-ip}"); 
	}
}

//THE ACTUAL HTML PAGE
echo "<html>
	<head>
	<title>Mass Exodus</title>
	<link rel='stylesheet' href='stylesheet.css'>
	</head>
	<body>";
include('menu.html');
echo "<h2>Mass Exodus</h2>
	<p class='yellow'>Please only use this from 10.101.8.1 (instead of prodtools) to avoid problems with NGINX buffering and gateway timeout</p>
	<p>Use this tool to move a large group from one site to another</p>
	<form onsubmit='return confirm(\"Please review your choices. Do you really want to continue?\");' action='' method='POST'>
		<div class='radio'>
		<input id='pbx' type='radio' name='method' value='pbx' ";
if ($method != "site") { echo "checked"; }
echo "/><label for='pbx' />By PBX</label><br>
	<input id='site' type='radio' name='method' value='site' ";
if ($method == "site") { echo "checked"; }
echo "/><label for='site' />By Site</label><br>
		</div>
		<br>
		<input type='hidden' name='action' value='submit' />
		<input type='text' name='source' placeholder='Source site or IP' />
		<input type='submit' value='Begin Exodus!' />
		<br>
		<br>
		Optionally, enter a site (not an IP) to override the secondary locations:
		<br>
		<input type='text' name='destination' placeholder='e.g. dfw' /> 
		</form>";
	echo "<br><br><form action='emhalt.php' method='POST' target='_blank'>
		<input type='hidden' name='stop' value='true' />
		<input type='submit' value='Stop Migration' /> 
		</form><form action='emhalt.php' method='POST' tartet='_blank'>
		<input type='hidden' name='clear' value='true' />
		<input type='submit' value='Unblock Migration' />
		</form>";

//MAIN BODY
// What to do when the user clicks "submit"
if (preg_match('/[^a-z\-0-9\.]/i', $source)) {
	die ("INVALID SOURCE!");
}

if (isset($destination) && preg_match('/[^a-z\-]/i', $destination)) {
	die ("INVALID DESTINATION. Give site code only, no IP addresses.");
}
if ($action == "submit")
{
	$maxLoad = 14000000; //How many seconds of RTP in 7 days can a server handle? default 14,000,000 
	//Find out how much load the clients cause on our servers: 
	$clientLoadQuery = "SELECT id, (load_in + load_out + load_custom) AS load FROM loadmetrics ORDER BY load DESC";
	//Find out how many secondary locations there are so we can loop over them
	$secondaryQuery = "SELECT DISTINCT secondary_location FROM resource_group WHERE (assigned_server = '$source' OR location = '$source') AND state = 'ACTIVE'";
	//Get a list of MPLS customers that we will avoid moving. These have to be moved manually
	$mplsQuery = "SELECT id, domain FROM mpls";

	//Connect to the databases
	$pbxsConn = pg_connect("host=db dbname=pbxs user=postgres ") or die('Could not connect to "pbxs" database: ' . pg_last_error());
	$utilConn = pg_connect("host=db dbname=util user=postgres ") or die('Could not connect to "util" database: ' . pg_last_error());
	$cdrConn = pg_connect("host=cdr dbname=asterisk user=postgres ") or die('Could not connect to "cdr" database: ' . pg_last_error());
	$eventDb = pg_connect("host=db dbname=events user=postgres") or die('Could not connect: '. pg_last_error());
	//Get the load results and rekey them to be easier to use
	$clientLoad = pg_fetch_all(pg_query($cdrConn, $clientLoadQuery)) or die ('Failed to get client specific load: '. pg_last_error());
	$clientLoad = rekey($clientLoad, "id", "load");

	//Set the PBX to status "moving". If source is a site, not a pbx, nothing will happen
	$setMoving = "UPDATE pbxstatus SET status = 'moving' WHERE ip = '$source';";
	pg_query($utilConn, $setMoving);
	
	$mpls = pg_fetch_all(pg_query($utilConn, $mplsQuery)) or die ('Failed to get MPLS data'.pg_last_error());
	$mpls = rekey($mpls, "id", "domain");
	//Determine how to move clients (autmoatically or by override)
	if (isset($override)) {
		$secondaries = Array ('0' => Array('secondary_location' => $destination)); //Really hacky way to not have to change other code
	} else {
		$secondaries = pg_fetch_all(pg_query($pbxsConn, $secondaryQuery)) or die('Could not fetch secondary locations' . pg_last_error());
	}

	//======================
	//Major Loop --- This Loop goes through the secondary locations one at a time and moves customers
	//======================
	foreach ($secondaries as $secondary) {
		$second = $secondary['secondary_location'];
		if ($second == $source) {
			echo "Skipping $second because it matches $source";
			continue;
		}
		echo "<h2>--- Currently moving customers from $source to $second --- </h2>";
    	$description = $guiltyParty." performed a large scale migration from ".$source." going to ".$second;
        $eventID = pg_fetch_row(pg_query($eventDb, "INSERT INTO event(id, description, event_type) VALUES(DEFAULT, '" . $description . "', 'MASS') RETURNING id;"));
		//SQL Select has to change based on whether or not an override is being used
		if (isset($override)) {
			$clientsQuery = "SELECT domain, id, assigned_server, location, secondary_location FROM resource_group WHERE (assigned_server = '$source' OR location = '$source') AND state = 'ACTIVE' AND assigned_server like '10.%';";
		} else {
			$clientsQuery = "SELECT domain, id, assigned_server, location, secondary_location FROM resource_group WHERE (assigned_server = '$source' OR location = '$source') AND secondary_location = '$second' AND state = 'ACTIVE' AND assigned_server like '10.%';";
		}
		//Find out which PBXs are most and least loaded--we want to fill the least loaded first
		$serverLoadQuery = "SELECT ip, load FROM pbxstatus WHERE location = '$second' AND status = 'active' ORDER BY load ASC;";	

		//Get the clients for this specific secondary_location
		$clients = pg_fetch_all(pg_query($pbxsConn, $clientsQuery)) or die('Could not find clients. Did you correctly select PBX/Site?<br>' . pg_last_error());
		$serverLoadResult = pg_fetch_all(pg_query($utilConn, $serverLoadQuery)) or die ('Error getting load levels'. pg_last_error());
		
		//Rekey the load data so it's easier to use
		$serverLoad = rekey($serverLoadResult, "ip", "load");
		//Add load metrics into the clients array so that we can adjust server load as we go
		$clients = addKey ($clients, $clientLoad, "id", "load", 0);
		//Load Distribute will assign the clients to new servers
		$distResults = loadDistribute($clients, $serverLoad, $second, $maxLoad);
		$clients = $distResults[0];
		//The second return value from loadDistribute is the number of clients that couldn't be assigned to a server at the secondary location. We can assume that if this is not == 0, then some people won't get moved
		if ($distResults[1] != 0) {
			echo "<p class='red'>Some clients will not be migrated because $second lacks space!</p>";
			echo "<p>".$distResults[1]." clients not being moved</p>";
		}
		//======================
		//Major Loop --- This loop actually moves the files and updates the database for each customer
		//based on the results of the loadDistribute function
		//======================
		foreach ($clients as $client) {
			flushOutput(); //Flushing the output works to update the webpage that the user is viewing and keeps the session alive

			//The if a file named "STOPMIGRATION is placed in the same directory as this script, it will stop. The emhalt.php script does this automatically for convenience. 
			if (file_exists("STOPMIGRATION")) {
				echo "<p class='red'>This script has been stopped</p>";
				echo "<br>If this was not intentional, check the contents of /var/www/html/pbxutils/STOPMIGRATION<br>";
				die ("--Exiting--");
			}
			//Check to see if the DATACENTER has been changed for the client. 
			//
			//This check is not perfect--it won't work if you're moving clients from a single PBX or if you're trying to redistribute load on a single datacenter. 
			if (in_array($client['domain'], $mpls)) {
				echo "<p class='purple'>${client['domain']} staying in .$source. because customer is MPLS! DATABASE NOT UPDATED.</p>";
				continue;
			}
			if ($client['location'] == $source) {
				echo "<p class='yellow'>${client['domain']} staying on ${client['assigned_server']} in $source. DATABASE NOT UPDATED.</p>";
			} else {
				$thisID = $client['id'];
				$thisName = $client['domain'];
				$thisServer = $client['assigned_server'];
				$thisLocation = $client['location'];
        if ($thisLocation == 'ord') {
          $thisLocation = 'chicago-legacy';
        }
				$thisSecLocation = $client['secondary_location'];
				$thisLoad = $client['load'];
				echo "<p class='green'>$thisName (load: $thisLoad) moving to $thisServer in $thisLocation</p>";	
				//Execute an SSH call to move the client's files
				exec("sudo ssh -T -o StrictHostKeyChecking=no -i /var/www/.ssh/internal-only root@enc1 /root/migrate-files.sh $thisName $thisLocation", $sshOutput, $sshStatus);
				//As long as the ssh returned an ok status, continue
				if ($sshStatus != 0) {
					echo "<br>ERROR<br>";
					print_r($sshOutput);
					echo "<br>";
					print_r($sshStatus);
					die();
				}
				//The database query for updating
				$thisMove = "UPDATE resource_group SET assigned_server = '$thisServer', secondary_location = location, location = '$thisLocation' WHERE id = '$thisID'";
				//The actual update
			    pg_query($pbxsConn, $thisMove) or die ('Updating the database failed! '.pg_last_error());	
				//And the event update
            	pg_query($eventDb, "INSERT INTO event_domain VALUES('" . $eventID['0'] . "', '" .$thisID. "')");
				flushOutput();
				//Update load to make sure that future moves will have accurate data
				if (!domainLoadUpdate($cdrConn, $utilConn, $thisServer, $thisID)) {
					echo "Load update failed for this client.<br>";
				}

			}
		}
		echo "<br>============================= flushing memcache between sites =================================</br>";
		exec('/root/flush_memcached ', $flushOutput, $exitcode);
	}

	//Set the PBX to status "moving". If source is a site, not a pbx, nothing will happen
	$setDirty = "UPDATE pbxstatus SET status = 'dirty', abandoned='now()', message='Mass Exodus was run leaving this pbx' WHERE ip = '$source';";
	pg_query($utilConn, $setDirty);

   	pg_close($eventDb); //Close the event DB connections
	pg_close($cdrConn);
	pg_close($utilConn);
	pg_close($pbxsConn);

echo "<h3>Migration Finished!</h3>";

}
echo"
	</body>
	</html>";
?>

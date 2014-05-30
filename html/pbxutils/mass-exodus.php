<?php
header('Cache-Control: no-cache');
$guiltyParty = $_SERVER['REMOTE_ADDR'];
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
function addKey ($array, $valueSource, $commonKey, $newKey) {
	$len = sizeof($array);
	for ($i = 0; $i < $len; $i++) {
	if (array_key_exists($array[$i][$commonKey], $valueSource)) {
		$array[$i][$newKey] = $valueSource[$array[$i][$commonKey]];
	} else {
			$array[$i][$newKey] = 0;
	}
	}
	return $array;
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
	$override = $_REQUEST['override'];
	if (isset($override)) {
		$destination = $_REQUEST['destination'];
	}
	$source = $_REQUEST['source'];
}

//THE ACTUAL HTML PAGE
echo "<html>
	<head>
	<title>Mass Exodus</title>
	<link rel='stylesheet' href='stylesheet.css'>
	</head>
	<body>
	<h2>Mass Exodus</h2>
	<p>Use this tool to move a large group from one site to another</p>
	<p><a href='index.php'>Back to PBX Utils</a></p>
	<form onsubmit='return confirm(\"WARNING: THIS SCRIPT IS FUNCTIONAL!!!\\n\\nPlease review your choices. Do you really want to continue?\\nCareless use of this script can cause DOWNTIME for REAL CLIENTS!\");' action='' method='POST'>
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
		<div class='checkbox'>
			<input id='override' type='checkbox' name='override'><label for='override' />Override Destination [optional]</label><br>
			<input type='text' name='destination' placeholder='e.g. dfw' /> 
	    </div>	
		</form>";

//MAIN BODY
// What to do when the user clicks "submit"
if (preg_match('/[^a-z\-0-9\.]/i', $source)) {
	die ("INVALID SOURCE!");
}

if (isset($destination) && preg_match('/[^a-z]/i', $destination)) {
	die ("INVALID DESTINATION. Give site code only, no IP addresses.");
}
if ($action == "submit")
{
	echo "<br><br><form action='emhalt.php' method='POST' target='_blank'>
		<input type='hidden' name='stop' value='true' />
		<input type='submit' value='KILL IT WITH FIRE' /> 
		</form>";
	$maxLoad = 14000000; //How many seconds of RTP in 7 days can a server handle? default 14,000,000 
	$clientLoadQuery = "SELECT id, (load_in + load_out + load_custom) as load from loadmetrics";
	$secondaryQuery = "SELECT DISTINCT secondary_location FROM resource_group WHERE assigned_server = '$source' OR location = '$source'";

	$pbxsConn = pg_connect("host=db dbname=pbxs user=postgres ") or die('Could not connect to "pbxs" database: ' . pg_last_error());
	$utilConn = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connect to "pbxs" database: ' . pg_last_error());
	$cdrConn = pg_connect("host=cdr dbname=asterisk user=postgres ") or die('Could not connect to "pbxs" database: ' . pg_last_error());
	$eventDb = pg_connect("host=db dbname=events user=postgres") or die('Could not connect: '. pg_last_error());
	$clientLoad = pg_fetch_all(pg_query($cdrConn, $clientLoadQuery)) or die ('Failed to get client specific load: '. pg_last_error());
	$clientLoad = rekey($clientLoad, "id", "load");
	
	if (isset($override)) {
		$secondaries = Array ('0' => Array('secondary_location' => $destination)); //Really hacky way to not have to change other code
	} else {
		$secondaries = pg_fetch_all(pg_query($pbxsConn, $secondaryQuery)) or die('Could not fetch secondary locations' . pg_last_error());
	}

	foreach ($secondaries as $secondary) {
		$second = $secondary['secondary_location'];
    	$description = $guiltyParty." performed a large scale migration from ".$source." going to ".$second;
        $eventID = pg_fetch_row(pg_query($eventDb, "INSERT INTO event(id, description) VALUES(DEFAULT, '" . $description . "') RETURNING id;"));
		if (isset($override)) {
			$clientsQuery = "SELECT domain, id, assigned_server, location, secondary_location FROM resource_group WHERE assigned_server = '$source' OR location = '$source';";
		} else {
			$clientsQuery = "SELECT domain, id, assigned_server, location, secondary_location FROM resource_group WHERE (assigned_server = '$source' OR location = '$source') AND secondary_location = '$second';";
		}
		$serverLoadQuery = "SELECT ip, load FROM pbxstatus WHERE location = '$second' AND status = 'active' ORDER BY load ASC;";	

		$clients = pg_fetch_all(pg_query($pbxsConn, $clientsQuery)) or die('Could not find clients. Did you correctly select PBX/Site?<br>' . pg_last_error());
		$serverLoadResult = pg_fetch_all(pg_query($utilConn, $serverLoadQuery)) or die ('Error getting load levels'. pg_last_error());
		
		$serverLoad = rekey($serverLoadResult, "ip", "load");
		$clients = addkey ($clients, $clientLoad, "id", "load");
		$distResults = loadDistribute($clients, $serverLoad, $second, $maxLoad);
		$clients = $distResults[0];
		if ($distResults[1] != 0) {
			echo "<p class='red'>Some clients were not migrated because $second ran out of space!</p>";
			echo "<p>".$distResults[1]." clients were not moved</p>";
		}
		foreach ($clients as $client) {
			flushOutput();
			sleep(1);
			if (file_exists("STOPMIGRATION")) {
				echo "<p class='red'>This script received an EMERGENCY HALT and has stopped</p>";
				echo "<br>If this was not intentional, check the contents of /var/www/html/pbxutils/STOPMIGRATION<br>";
				die ("--EXECUTION COULD NOT CONTINUE... DYING--");
			}
			if ($client['location'] == $source) {
				echo "<p class='yellow'>${client['domain']} staying on ${client['assigned_server']} in $source</p>";
			} else {
				$thisID = $client['id'];
				$thisName = $client['domain'];
				$thisServer = $client['assigned_server'];
				$thisLocation = $client['location'];
				$thisSecLocation = $client['secondary_location'];
				echo "<p class='green'>$thisName ($thisID) moving to $thisServer in $thisLocation</p>";	
				exec("ssh -T -o StrictHostKeyChecking=no -i /var/www/.ssh/internal-only root@enc1 /root/migrate-files.sh $thisName $thisLocation", $sshOutput, $sshStatus);
				if ($sshStatus != 0) {
					echo "<br>ERROR<br>";
					print_r($sshOutput);
					echo "<br>";
					print_r($sshStatus);
					die();
				}
				$thisMove = "UPDATE resource_group SET assigned_server = '$thisServer', secondary_location = location, location = '$thisLocation' WHERE id = '$thisID'";
			    pg_query($pbxsConn, $thisMove) or die ('Updating the database failed! '.pg_last_error());	

            	pg_query($eventDb, "INSERT INTO event_domain VALUES('" . $eventID['0'] . "', '" .$thisID. "')");

			}
		}
		echo "<br>=============================";
	}
   	pg_close($eventDb); //Close the event DB connection
	pg_close($cdrConn);
	pg_close($utilConn);
	pg_close($pbxsConn);

}

echo"
	</body>
	</html>";
?>

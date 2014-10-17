<?php
/* V5 CUSTOMER MIGRATION SCRIPT
 *
 * This is primarily for customer migration to v5.  Migration from v5 to v4 will also be included.
 *
 * The basic process is set the v5 flag for a customer domain in v4 resource_group tables,
 * then call the voicemail migration script.
 *
 */

include('guiltyParty.php');
//CSS Styling:
echo '<html><head><title>v5 Customer Migration</title>
<style type="text/css"> 
#pretty {vertical-align: bottom;}
</style><link rel="stylesheet" href="stylesheet.css"></head>';

function flushOutput() {
    echo(str_repeat(' ', 256));
    if (@ob_get_contents()) {
        @ob_end_flush();
    }
    flush();
}

if ($_SERVER['SERVER_ADDR'] != '10.101.8.1')
{
    echo "for v5 migrations go to ...add link here";
}

//"Header"
echo '<body onload="init()">';
include('menu.html');

echo '<div id="head" class="head">
	<h2>v5 Customer Migration';
echo '</h2>
	<a href="index.php">Back to pbxutils</a>';

//The action variable tells us if this page was called by itself and why
// [EMPTY] = First visit
// help = Help button clicked
// search = A search term has been given
// move = The user has selected a domain to move
// confirm = The user confirmed that the domain should be moved
$action = $_REQUEST["action"];
$domain = $_REQUEST["domain"];
$id = $_REQUEST["id"];
$flush="Y";

//Switch cases allow us to grab the right variables depending on which stage we're in. 
switch ($action) {
	case "help":
		//no other vars have been posted.
		break;
	case "search":
		//Assign the "search" variable
		$search = $_REQUEST["search"];
		//Make sure they're not trying to inject anything
		if (preg_match('/[^a-z\-0-9]/i', $search))
		{
			echo "<p class='red'> Invalid Input! <br/> Use numbers, letters, and dashes only.</p>";
			$action = null;
		}
		//If the search is exact, leave it alone, otherwise add wildcards
		if (isset($_REQUEST["exact"]))
		{
			//Search term is exact
		} else
		{
			//Search term needs wildcards
			$search = "%".$search."%";
		}
		break;
}

//========
// "MAIN" =============================================================================================================
//========
//This is the search box that appears at the top of the page
//It shows up every time, making it convenient to search for another domain
//or repeat the same search if you wish to start over
echo '<div class="checkbox"><form action="v5-migration.php" method="GET">
	<input type="hidden" name="action" value="search"> 
	<p>Enter a domain to search: </p>
	<p><input type="text" name="search" placeholder="Jive Domain" /></p>
	<p><input id="exact" class="checkbox" type="checkbox" name="exact"><label for="exact">Exact Search</label></p>
    <p><input type="submit" value="Search" />
	</form><form action="" method="POST">
	<input type="hidden" name="action" value="help">
	<input type="submit" value="Help" /></form></p><hr width="500px" align="left"></div></div>';

//===================
// DISPLAY HELP INFO ==================================================================================================
//===================
if ($action=="help")
{
	echo "<div id='help' class='help'>
		<p>This script will change the server used by a single customer's PBX. Calls in progress are not dropped. To get started, simply enter the all or part of a domain ('jive', 'jen', 'delano', etc.) in the search box and click search. The following items should help clarify what you see in this script</p>
		<p><b>Exact</b>: The exact checkbox can be used if you know the domain exactly, but for some reason it doesn't come up in the first 50 results. 
		<p><b>Location</b>: Each network (Chicago, Orem, Atlanta, etc) contains at least one network storage location. This is used for customer files such as uploaded recordings, voicemails, hold music, and faxes. Changing the location is only necessary if you are moving the PBX to a new network.</p>
		<p><b>Server</b>: This refers to the physical server where the customers calls, voicemails, etc. are handled. <font class='red'>Note: Just because a server is in the drop-down list doesn't mean that it is in working order. Only use this script if you're sure you know what to do.</font>
		<p><b>Memcache</b>: I don't have a good explanation for this, but know that flushing memcache is usually good. If you're worried about server stability for any reason, feel free to skip the flush.</p>
		<p>Finally, until you confirm the move, nothing has actually happened. You can abort a move at any time by simply leaving the page, entering another search, or clicking the back button. If you moved a client and want to see where they were before, click the back button.</p>";

}

//=====================
// SEARCH the DATABASE ================================================================================================
//=====================
if ($action=="search")
{
	$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect to "pbxs" database: ' . pg_last_error());
	$curAssignmentQ = "SELECT name, domain, assigned_server, location, id, v5, v5candidate FROM resource_group WHERE domain LIKE '".$search."' ORDER BY domain LIMIT 50;";
	$curAssignment = pg_fetch_all(pg_query($curAssignmentQ)) or die ("Current Placement Search Failed or No Results: ".pg_last_error());
	pg_close($dbconn);

	//Output HTML (note and the beginning of the table including column headers
	echo "<table border='1'><tr><th>v5</th><th>Name</th><th>Domain</th><th>Location</th><th>Server</th><th>Migrate to v4</th><th>Migrate to v5</th><th>v5 Candidate</th></tr>";

	foreach($curAssignment as $dom) //Loop through the domains we found in our first query
	{
		if ($dom['v5'] != 't') { $v5 = 'FALSE'; } else { $v5 = 'TRUE'; }
		if ($dom['v5candidate'] != 't') { $v5candidate = 'FALSE'; } else { $v5candidate = 'TRUE'; }
		$listDomain = $dom['domain'];
		echo "<tr>
			<td>".$v5."</td>
			<td>".$dom['name']."</td>
			<td><a href=\"domain-info.php?domain=" . $listDomain . "\">" . $listDomain . "</a></td>
			<td>".$dom['location']."</td>
			<td>".$dom['assigned_server']."</td>";

		if ($v5 == 'TRUE') {
			echo "<td>
				<a href='v5-migration.php?action=v4migrate&domain=" . $listDomain . "'>Migrate to v4</a>
				<list><li>dfw</li><li>pvu</li></list>
				</td>";
		} else {
			echo "<td></td>";
		}
		if ($v5 == 'FALSE' && $dom['location'] == 'chicago-legacy') {
			echo "<td>
				<a href='v5-migration.php?action=v5migrate&domain=" . $listDomain . "'>Migrate to v5</a>
				<list><li>dfw</li><li>pvu</li></list>
				</td>";
		} else if ($v5 == 'FALSE') {
			echo "<td>Please migrate to chicago before migrating to v5</td>";
		} else {
			echo "<td></td>";
		}
        
	    echo "<td>".$v5candidate."</td>";
		echo "</tr>"; 
	}
	echo "</table></div>";
}

//================
// MIGRATE to v5  ====================================================================================================
//================
if ($action=="v5migrate")
{
	$platform = "v5";
	set_time_limit(0);
	ignore_user_abort();
	// And now we migrate
	echo "<div id='final' class='final'><p> Migrating ".$domain." to ".$platform." with flush set to ".$flush."...</p>";
	syslog(LOG_INFO, $guiltyParty." migrated ".$domain." to ".$platform.". Flush memcache was set to: ".$flush);

	//Update the database
	echo "<p>Updating DB</p>";
	$dbconn = pg_connect("host=rwdb dbname=pbxs user=postgres ") or die('Could not connect: '.pg_last_error());
	$updateQuery = "UPDATE resource_group SET v5=true,v5candidate=true,assigned_server='199.36.251.38' WHERE domain = '".$domain."' AND location = 'chicago-legacy' RETURNING id;";
	$updateRow = pg_fetch_row(pg_query($dbconn, $updateQuery)) or die("<p class='red'>Failed to update database: ".pg_last_error()."</p></div>");
	$id = $updateRow['0'];
	pg_close($dbconn);

	if ($id == '')
	{
		die("Domain not in CHI: " . $domain);
	}

	// Flip the reports feature flag
	exec('curl -X PUT http://10.104.1.190:8083/features/' . $id . '/reports.beta?setting=ENABLED');
	echo "<p>Flipped reporting flag</p>\n";

	//Flush memchached
	if($flush=="Y")
	{
		echo "<p>Flushing memcache</p>";
		exec('sudo /root/flush_memcached ', $flushOutput, $exitcode);
		if($flushOutput[0] != "OK" OR $flushOutput[1] != "OK")
		{
			echo"<p class='red'> Flushing Memcached failed.";
			echo"<br> Exit code: ".$exitcode;
			foreach($flushOutput as $output)
			{
				echo "<br>".$output;
			}
			echo "</p></div>";
		}
	}

	//Record event in the event database
	echo "<p>Updating Event DB</p>\n";
	$eventDb = pg_connect("host=rwdb dbname=events user=postgres") or die('Could not connect: '. pg_last_error());
	$description = $guiltyParty." migrated ".$domain." to ".$platform;
	$eventID = pg_fetch_row(pg_query($eventDb, "INSERT INTO event(id, description) VALUES(DEFAULT, '" . $description . "') RETURNING id;"));
			
	pg_query($eventDb, "INSERT INTO event_domain VALUES('" . $eventID['0'] . "', '" .$id. "')");
	pg_close($eventDb); //Close the event DB connection

	// Execute voicemail migration
	echo "<p>Migrating Voicemail</p>\n";
	#exec('sudo ssh -T -o StrictHostChecking=no root@10.101.8.1 "python26 /opt/jive/voicemailMigration/migration/migration/masterMigration.py" '.$domain.' >>/tmp/v5migrate-$domain', $voicemailOutput, $exitcode);
	exec('python26 /opt/jive/voicemailMigration/migration/migration/masterMigration.py '.$domain.' >>/tmp/v5migrate-$domain', $voicemailOutput, $exitcode);
    print_r($voicemailOutput);
	echo "<br>" .$exitcode;
	echo "<div><p>Migration of $domain to v5 complete</p></div><hr/>";
} // End of Migrate to v5

//================
// MIGRATE to v4  ====================================================================================================
//================
if ($action=="v4migrate")
{
	$platform = "v4";
	set_time_limit(0);
	ignore_user_abort();
	// And now we migrate
	echo "<div id='final' class='final'><p> Migrating ".$domain." to ".$platform." with flush set to ".$flush."...</p>";
	syslog(LOG_INFO, $guiltyParty." migrated ".$domain." to ".$platform.". Flush memcache was set to: ".$flush);

	//Update the database
	echo "<p>Updating DB</p>";
	$dbconn = pg_connect("host=rwdb dbname=pbxs user=postgres ") or die('Could not connect: '.pg_last_error());
	$updateQuery = "UPDATE resource_group SET v5=false,assigned_server='10.101.7.1' WHERE domain = '".$domain."' RETURNING id;";
	$updateRow = pg_fetch_row(pg_query($dbconn, $updateQuery)) or die("<p class='red'>Failed to update database: ".pg_last_error()."</p></div>");
	$id = $updateRow['0'];
	pg_close($dbconn);

	//Flush memchaced
	if($flush=="Y")
	{
		echo "<p>Flushing memcache</p>";
		exec('sudo /root/flush_memcached ', $flushOutput, $exitcode);
		if($flushOutput[0] != "OK" OR $flushOutput[1] != "OK")
		{
			echo"<p class='red'> Flushing Memcached failed.";
			echo"<br> Exit code: ".$exitcode;
			foreach($flushOutput as $output)
			{
				echo "<br>".$output;
			}
			echo "</p></div>";
		}
	}

	//Record event in the event database
	echo "<p>Updating Event DB</p>";
	$eventDb = pg_connect("host=rwdb dbname=events user=postgres") or die('Could not connect: '. pg_last_error());
	$description = $guiltyParty." migrated ".$domain." to ".$platform;
	$eventID = pg_fetch_row(pg_query($eventDb, "INSERT INTO event(id, description) VALUES (DEFAULT, '" . $description . "') RETURNING id;"));
			
	pg_query($eventDb, "INSERT INTO event_domain VALUES('" . $eventID['0'] . "', '" .$id. "')");
	pg_close($eventDb); //Close the event DB connection

	// Execute voicemail migration
	echo "<p>Migrating Voicemail</p>";
    flushOutput();
	//exec('sudo ssh -T -o StricktHostChecking=no root@10.101.8.1 "python26 /opt/jive/voicemailMigration/migration/migration/masterUnmigration.py '.$domain. ' >>/tmp/v5unmigrate-$domain', $voicemailOutput, $exitcode);
	exec('python26 /opt/jive/voicemailMigration/migration/migration/masterMigration.py '.$domain , $voicemailOutput, $exitcode);
	foreach($voicemailOutput as $line)
	{
		echo "<br>".$line;
	}
	echo "<br>";

	echo "<div><p>Migration of $domain to v4 complete</p></div><hr/>";
} // End of Migrate to v4

echo "</body></html>";
?>

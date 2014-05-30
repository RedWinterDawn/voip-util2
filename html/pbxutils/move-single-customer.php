<?php
/* SINGLE CUSTOMER MIGRATION SCRIPT
 *
 * This script queries the read only database to find a domain based on a user defined search term
 * Results from the search are presented in a table, with each row containing a button to migrate the associated customer
 * A confirmation page is shown, followed by migration success/failure output.
 *
 * This page written by Adam Jensen
 *
 * The segments of this page are tagged with the following div tags (both id and class are set the same):
 * - head (the top part of every page)
 * - results
 * - confirm
 * - final
 *
 */

// THIS SCRIPT REQUIRES THE FOLLOWING FILES: /var/www/migrate-pbx.sh and /var/www/migrate-files.sh

//CSS Styling:
echo "<style type='text/css'> 
.red {color: red;}
.green {color: green;}
.active {background: #CCFFCC;}
.standby {background: #FFFFCC;}
.graveyard {background: #CCCCCC;}
.dirty {background: #FFCCCC;}
.moving {background: #FFCCCC;}
.special {background: #CCCCFF;}
.NEW {}
</style>";

//"Header"
echo '<div id="head" class="head"><head><title>Migration Page</title></head>
	<h2>Move a Single Customer</h2>
	<a href="http://10.199.8.1/pbxutils/">Back to pbxutils</a>';

//The action variable tells us if this page was called by itself and why
// [EMPTY] = First visit
// search = A search term has been given
// move = The user has selected a domain to move
// confirm = The user confirmed that the domain should be moved
$action = $_REQUEST["action"];
$guiltyParty = $_SERVER['REMOTE_ADDR'];
//Search term
if (isset($_REQUEST["search"]))
{
  $search = $_REQUEST["search"];
}	

//Make sure there's no SQL injection in the search term
if (preg_match('/[^a-z\-0-9]/i', $search))
{
	echo "<p class='red'> Invalid Input! <br/> Use numbers, letters, and dashes only.</p>";
	$action = null;
}

//Selected destination (drop down)
if (isset($_REQUEST["dest"]))
{
  $dest = explode('|', $_REQUEST["dest"]); //The $dest variable will receive host and status into indexes 0 and 1
} else
{
  $dest = null;
}

//Selected location (drop down)
if (isset($_REQUEST["location"]))
{
	$location = $_REQUEST["location"];
} else
{
	$location = null;
}

//Domain pertaining to the clicked move button
if (isset($_REQUEST["domain"]))
{
  $domain = $_REQUEST["domain"];
} else
{
  $domain = null;
}	

//Whether or not to flush memcache (confirmation page checkbox)
if (isset($_REQUEST["flush"]))
{
	$flush = "Y";
} else
{
	$flush = "N";
}

//========
// "MAIN" =============================================================================================================
//========
//This is the search box that appears at the top of the page
//It shows up every time, making it convenient to search for another domain
//or repeat the same search if you wish to start over
echo '<form action="" method="POST">
	<input type="hidden" name="action" value="search"> 
	<p>Enter a domain to search: </p>
	<p><input type="text" name="search" placeholder="Jive Domain" /></p>
    <p><input type="submit" value="Search" />
	</form><form action="" method="POST">
	<input type="hidden" name="action" value="help">
	<input type="submit" value="Help" /></form></p></div>';

//===================
// DISPLAY HELP INFO ==================================================================================================
//===================
if ($action=="help")
{
	echo "<div id='help' class='help'>
		<p>This script will change the server used by a single customer's PBX. Calls are unaffected by this unless you move the customer to a pbx that doesn't work. To get started, simply enter the all or part of a domain ('jive', 'jen', 'barryessa', etc.) in the search box and click search. The following items should help clarify what you see in this script</p>
		<p><b>Server</b>: This refers to the physical server where the customers calls, voicemails, etc. are handled. <font class='red'>Note: Just because a server is in the drop-down list doesn't mean that it is in working order. Only use this script if you're sure you know what to do.</font> The alternating colors in the servers drop down list are simply to make subnets easier to see. Colors signify NOTHING.</p>
		<p><b>Location</b>: Each network (Chicago, Orem, Atlanta, etc) contains at least one network storage location. This is used for customer files such as uploaded recordings, voicemails, hold music, and faxes. Changing the location is only necessary if you are moving the PBX to a new network.</p>
		<p><b>Memcache</b>: I don't have a good explanation for this, but know that flushing memcache is usually good. If you're worried about server stability for any reason, feel free to skip the flush.</p>
		<p>Finally, until you confirm the move, nothing has actually happened. You can abort a move at any time by simply leaving the page, entering another search, or clicking the back button. If you moved a client and want to see where they were before, click the back button.</p>";

}

//=====================
// SEARCH the DATABASE ================================================================================================
//=====================
if ($action=="search")
{
	//Output HTML (note and the beginning of the table including column headers
	echo "<div id='results' class='results'><p> Note: Results limited to max 50 hits";
	echo "<br> Please also make sure that the location correctly matches the chosen destination</p>";
	echo "<table border='1'><tr><th>Domain</th><th>Server</th><th>Location</th><th>New Server</th><th>New Location</th><th>Move this Domain</th></tr>";

	//Actually connect to postgres for the queries we'll be making
	$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect: ' . pg_last_error());

	//Define queries and then fetch info from the database
	$domainQuery = "SELECT domain, assigned_server, location FROM resource_group WHERE domain='" . $search . "' OR domain LIKE '%" . $search . "%' ORDER BY domain LIMIT 50;"; 
	$hostQuery = "SELECT host,active FROM pbx_node ORDER BY host;";
	$locationQuery = "SELECT id FROM nas_location;";
	$domains = pg_query($domainQuery) or die ('Search failed: ' . pg_last_error());
	$pbxs = pg_query($hostQuery) or die ('Search failed: ' . pg_last_error());
	$locations = pg_query($locationQuery) or die ('Search failed: ' . pg_last_error());
	$domainResults = pg_fetch_all($domains);
	$pbxResults = pg_fetch_all($pbxs);	
	$locResults = pg_fetch_all($locations);

    $litedb = sqlite_popen("/opt/jive/php/pbxutildb",0666,$sqliteerror) or die('Could not connect: ' . pg_last_error());
    $servers = sqlite_query($litedb, "SELECT host,status FROM pbxstatus ORDER BY host;") or die ('Search failed: '.sqlite_last_error());
    $serverResults = sqlite_fetch_all($servers);
	sqlite_close($litedb); 

	$striped=false; //This is just a bool that we use to alternate blue and white rows (for readability)
	//--------------
	//Start Looping
	//--------------
	
	$serverList = "";
	foreach ($serverResults as $server)
	{ //Here we color and label each host based on status. We also prepare to pass host and status to the next step.  		
		$serverList .= "<option class='".$server['status']."' value='".$server['host']."|".$server['status']."'>".$server['host']." (".$server['status'].")</option>";
	}

	$locList = "";
	foreach($locResults as $loc)
	{
		$locList .= "<option value='". $loc['id']."'>".$loc['id']."</option>";
	}

	foreach($domainResults as $dom) //Loop through the domains we found in our first query
	{
		if ($striped) //Should the line be blue or white? 
		{
			echo "<tr bgcolor='#DDEEFF'>";
			$striped=false;
		} else
		{
			echo "<tr bgcolor='#FFFFFF'>";
			$striped=true;
		}
		//-------------------
		// Building the table
		//-------------------
		echo "<td><a href=\"domain-info.php?domain=" . $dom['domain'] . "\">".$dom['domain']."</a></td><td>".$dom['assigned_server']."</td><td>".$dom['location']."</td><form action='' method='POST'><td><select name='dest'>"; //Output the domain, current server, current location; then start the form used to move a domain
		echo $serverList;//These parts are done outside the loop to keep from having nested loops (and thus increasing parse time for the page) 	
		echo "</select></td>";
		echo "<td><select name='location'>"; //This is the "new location" drop down list	
		echo $locList; //These parts are done outside the loop to keep from having nested loops (and thus increasing parse time for the page) 	
		echo "</select></td><td>
		    <input type='hidden' name='action' value='move'>
			<input type='hidden' name='domain' value='".$dom['domain']."'>
			<input type='submit' value='Move' />
		</td></form></tr>"; // This portion adds the move button and closes out the row. 
	}
	echo "</table></div>"; // Finally done building the table!

	pg_close($dbconn);
	
//================
// CONFIRM CHOICE =====================================================================================================
//================
} elseif ($action=="move")
{
if (isset($dest[0])) //Make sure we got a destination...
{
	if($dest[1] != "active")
	{
		echo "<p class='red'><b>WARNING!!!</b> You have selected a destination server that has the status: ".$dest[1]."</p>";
	}	
	if (isset($domain)) //... and a domain to move
	{
		//Then display a confirmation page, and ask if the user wants to flush memcache
		echo "<div id='confirm' class='confirm'><form action='' method='POST'>
			<input type='hidden' name='action' value='confirmed'>
			<input type='hidden' name='domain' value='".$domain."'>
			<input type='hidden' name='dest' value='".$dest[0]."'>
			<input type='hidden' name='location' value='".$location."'>
			<p>Are you sure you want to move <u class='red'>".$domain."</u> to the server at <u class='red'>".$dest[0]."</u>?
			<br>With this move, this domain's files will be migrated to <u class='red'>".$location."</u>
			<p>If not, simply leave this page</p>
			<p><input type='checkbox' name='flush' value='memcache' checked> Flush Memcache?</p>
			<input type='submit' value='Yes, proceed' />
			</form></div>";
		//This block contains a form that posts the same information we should already have plus:
		//* Action = Confirmed
		//* Flush = Yes/No
	} else
	{
		echo "<p> There was an error determining which domain to use </p>"; //For some reason we didn't get a domain
	}
} else
{
	echo "<p> You did not select a valid destination </p>"; //We didn't get a destination
}
//=================
// MOVE the DOMAIN ====================================================================================================
//=================
} elseif ($action=="confirmed")
{
	// Here we'll actually try to move the user to the selected destination
	echo "<div id='final' class='final'><p> Moving ".$domain." to ".$dest[0]." and ".$location." with flush set to ".$flush."...</p>";
	syslog(LOG_INFO, $guiltyParty." moved ".$domain." to ".$dest[0]." and ".$location.". Flush memcache was set to: ".$flush);

	//------------------
	// The 1st Migration
	//------------------
	//Call the "migrate-pbx.sh" script to update the database with the new location and optionally flush memcache
	exec('/var/www/migrate-pbx.sh '.$domain.' '.$dest[0].' '.$flush.' '.$location.' 2>&1', $moveOutput, $exitcode);
	if ($exitcode > 0) //If for some reason we received a non-zero exit code, display the script output and exit code
	{
		echo $moveOutput;
		echo "<p class='red'>Note: Migration finished with exit code ".$exitcode."</p>";
	} else
	{
		//echo "<p class='green'> Destination Set ... </p>";
	}

	sleep(1); //give the ro database time to replicate from master
	$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect: ' . pg_last_error());

	//Check the database to see if the user actually got moved to the new location or not
	$finalQuery = "SELECT domain, assigned_server, location FROM resource_group WHERE domain='".$domain."';"; 
	$final = pg_query($finalQuery) or die ('Search failed: ' . pg_last_error());
	$finalResults = pg_fetch_all($final);

	//If the new destination matches the one that the user selected, output the domain, assigned server, and location.
	if ($finalResults[0]['assigned_server']==$dest[0] && $finalResults[0]['location']==$location)
	{
		echo "<p class='green'>Domain ".$finalResults[0]['domain']." is now on ".$finalResults[0]['assigned_server']." with files going into ".$finalResults[0]['location']."</p></div>";
	} else
	//If the stuff doesn't match, tell the user. However, this is probably just waiting for replication, so we'll suggest that.
	{
		echo "<p class='red'>Hmm... script says it finished, but ".$finalResults[0]['domain']." is on ".$finalResults[0]['assigned_server']." and ".$finalResults[0]['location'];
		echo "<br>Maybe check back in a minute by searching for the domain again.</p></div>";
	}

	pg_close($dbconn); //Close the DB connection
} // And we're done!
?>

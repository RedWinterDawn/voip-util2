<?php
/* REPORT EVENTS BY DOMAIN SCRIPT
 *
 * DESCRIPTION OF THIS SCRIPT GOES HERE
 *
 * This page written by Jake Lasley
 * 	Large portions stolen from Adam Jensen's simple-migration script
 */

//CSS Styling:
echo '<html><head><title>Event Reports</title>
<style type="text/css">
#pretty {vertical-align: bottom;}
#</style><link rel="stylesheet" href="stylesheet.css"></head>';

//"Header"
echo '<body onload="init()"><div id="head" class="head">';
include('menu.html');
echo '<h2>Event Reports';
if ($_SERVER['SERVER_ADDR'] == '10.101.8.1')
{
	echo " (PRODUCTION)";
} else
{
	echo " (DEV)";
}
echo '</h2>
	<a href="index.php">Back to pbxutils</a>';

$action = $_REQUEST["action"];
$guiltyParty = $_SERVER['REMOTE_ADDR'];
$domain = false;

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
		if(!preg_match('/\d{1,2}\-\d{1,2}\-\d{1,4}/i', $search))
		{	
			$domain = true;
			if(isset($_REQUEST["exact"]))
			{
				//Search term is exact
			}else
			{
				//Search term nees wildcards
				$search = "%".$search."%";
			}
		}
		break;
	case "domainList":
		$eventID= $_REQUEST["eventID"];
		$eventDescrip = $_REQUEST["eventDescrip"];
		break;
	case "eventList":
		$domainName= $_REQUEST["domain"];
		break;
	default:
		$action = "search";
		$search = date('m-d-Y');
}

//========
// "MAIN" =============================================================================================================
//========
//This is the search box that appears at the top of the page
//It shows up every time, making it convenient to search for another domain
//or repeat the same search if you wish to start over
echo '<div class="checkbox"><form action="" method="POST">
    <input type="hidden" name="action" value="search">
	<p>Enter a domain or date to search: </p>
	<p><input type="text" name="search" placeholder="m-d-yy" /></p>
	<p>Enter date as m-d-yy or search will default to a domain search </p>
	<p><input id="exact" class="checkbox" type="checkbox" name="exact"><label for="exact">Exact Search</label></p>
    <p><input type="submit" value="Search" />
    </form><form action="" method="POST">
    <input type="hidden" name="action" value="help">
    <input type="submit" value="Help" /></form></p><hr width="500px" align="left"></div></div>';

//=====================
// SEARCH the DATABASE ================================================================================================
//=====================
if ($action=="search")
{
	if($domain)
	{
		//Connect to postgress and make queries

		$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect to "pbxs" database: ' . pg_last_error());
	    $curAssignmentQ = "SELECT domain, assigned_server, location, name FROM resource_group WHERE domain LIKE lower('".$search."') ORDER BY domain LIMIT 50;";
	    $curAssignment = pg_fetch_all(pg_query($curAssignmentQ)) or die ("Current Placement Search Failed or No Results: ".pg_last_error());
		pg_close($dbconn);

		//Output HTML (note and the beginning of the table including column headers
		echo "<table border='1'><tr><th>Domain</th><th>Name</th><th>Location</th><th>Server</th><th>Display Events</th></tr>";
		$striped=false; //This is just a bool that we use to alternate blue and white rows (for readability)
		$i = 0;
	    foreach($curAssignment as $dom) //Loop through the domains we found in our first query
		{
			$i++;
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
			echo "<td>
				<a href=\"domain-info.php?domain=" . $dom['domain'] . "\">".$dom['domain']."</a></td>
				<td>".$dom['name']."</td>
				<td>".$dom['location']."</td>
			    <td>".$dom['assigned_server']."</td>
				<form action='' method='POST'>
				<td>
				<input type='hidden' name='action' value='eventList'>
				<input type='hidden' name='domain' value='".$dom['domain']."'>
				<input type='submit' value='Events' />
			</td></form></tr>"; // This portion adds the move button and closes out the row.
		}
		echo"</table></div>"; //Done building table
	}else
	{	
		//Actually connect to postgres for the queries we'll be making
    	$eventsDB = pg_connect("host=rodb dbname=events user=postgres ") or die('Could not connect to "events" database: ' . pg_last_error());
		$eventQ = "SELECT added AT TIME ZONE 'UTC', id, description FROM event WHERE added BETWEEN (timestamp '".$search."' AT TIME ZONE 'America/Boise') AND ((timestamp '".$search."' + interval '1 day') AT TIME ZONE 'America/Boise');";
		$eventArray = pg_fetch_all(pg_query($eventsDB, $eventQ)); //or die("<h2>No events for: " .$search ."</h2>");
		$timeArray = pg_fetch_all(pg_query($eventsDB, "SELECT date '".$search."' + integer '1' as next, date '".$search."' - integer '1' as pre"));
		pg_close($eventsDB);
		echo '<h2>'.sizeof($eventArray)." Events that occured on: ".strftime('%m-%d-%Y', strtotime($search)) . "</h2>";

		echo "<form action='' method='POST'>
			  <input type='hidden' name='action' value='search'>
			  <input type='hidden' name='search' value='".$timeArray[0]['pre']."'>
  			  <input type='submit' value='previous'></form>";			  

		echo "<form action='' method='POST'>
			  <input type='hidden' name='action' value='search'>
			  <input type='hidden' name='search' value='".$timeArray[0]['next']."'>
			  <input type='submit' value='  next  '></form>";

		echo "<table border='1'><tr><th>Date</th><th>Description</th><th>Affected</th></tr>";
		foreach($eventArray as $event)
		{
			echo "<td> " . strftime('%m-%d-%Y %T', strtotime($event[timezone])) ." </td>
				  <td> " . $event[description] . " </td>
			  	  <td> 
			  	  </form><form action='' method='post'>
			      <input type='hidden' name='action' value='domainList'>
				  <input type='hidden' name='eventID' value='".$event[id]."'>
				  <input type='hidden' name='eventDescrip' value='".$event[description]."'>
				  <input type='submit' value='Domains' /></form>
			  </td></tr>";	
		}
		echo "</table></div>";
	}
}
//===============
//Display Domains=====================================================================================================
//===============
if ($action=="domainList" && isset($eventID) && isset($eventDescrip))
{
	//Connect to eventsDB and get all the domain_ids
	$eventsDB = pg_connect("host=rodb dbname=events user=postgres ") or die('Could not connect to "events" database: ' . pg_last_error());
	$eventQ = "SELECT domain_id FROM event_domain WHERE event_id = '".$eventID."';";
	$eventArray = pg_fetch_all(pg_query($eventsDB, $eventQ))or die("No domains for:: " . $eventID);
	pg_close($eventsDB);

	//Connect to the pbxsDB and get all the domains 
	$pbxsDB = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect to "pbxs" database: ' . pg_last_error());
	$pbxq = "SELECT domain, name FROM resource_group WHERE ";
	$first = true;
	foreach($eventArray as $event)
	{
		if($first)
		{
			$first=false;
		}else
		{
			$pbxq .= "OR ";
		}
		$pbxq .= "id = '" .$event[domain_id]. "' ";	
	}
	$pbxArray = pg_fetch_all(pg_query($pbxsDB, $pbxq)) or die("Domain search failed or no results: " . pg_last_error());
	pg_close($pbxsDB);
	echo "<h2>".sizeof($pbxArray)." Domains affected by: " .$eventDescrip. "</h2>";
	echo "<table border='1'<tr><th>Domain</th><th>Name</th></tr>";
	foreach($pbxArray as $pbx)
	{
	echo "<tr><td><a href=\"domain-info.php?domain=" . $pbx[domain] . "\">".$pbx[domain]."</a></td>
			  <td> " .$pbx[name]. "</td>
			  </tr>";
	}
	echo "</table>";
}
//===========
//List Events==========================================================================================================
//===========
if ($action=="eventList" && isset($domainName))
{
	//Connect to pbxs DB and get id
	$pbxsDB = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect to "pbxs" database: ' . pg_last_error());
	$pbxQ = "SELECT id FROM resource_group WHERE domain = '".$domainName."';";
	$pbxArray = pg_fetch_all(pg_query($pbxsDB, $pbxQ)) or die ("PBX search failed or no results: " . pg_last_error());
	pg_close($pbxsDB);

	//Connect to events DB and get events
	$eventsDB = pg_connect("host=rodb dbname=events user=postgres ") or die('Could not connect to "events" database: ' . pg_last_error());
	$eventQ = "SELECT added AT TIME ZONE 'UTC', description from event, event_domain WHERE domain_id ='".$pbxArray[0]['id']."' AND event_id = id ORDER BY number DESC";
	$eventArray = pg_fetch_all(pg_query($eventsDB, $eventQ)) or die ("Event search failed or no results: " . pg_last_error());
	pg_close($eventsDB);
	echo "<h2>".sizeof($eventArray)." Events that affected: " .$domainName . "</h2>";
	echo "<h4> All times are in MDT </h4>";
	echo "<table border='1'<tr><th>Date</th><th>Description</th></tr>";
	foreach($eventArray as $event)
	{
		echo "<tr><td> " . strftime('%m-%d-%Y %T', strtotime($event[timezone])) ."</td>
			  <td>" . $event[description] . "</td>
			  </tr>";
	}
	echo "</table></div>";
}

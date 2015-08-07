<?php
$accesslevel = 2;
include('checksession.php');
?>
<?php
/*
                                                                                                    
Just a friendly reminder of the guy who created this monstrosity. :D
                                                                                                    
                                      `-+ohddddmmddmdyo::`                                          
                                   .odNMMMMMMMMMMMMMMMMMMmho:.                                      
                                -+mMMMMMMMMMMMMMMMMMMMMMMMMMMNd+-                                   
                             `omMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMNdo/`                               
                           .omMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMNmhs:`                            
                         .+mMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMNdddo.                          
                        /dMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMNdhdh:`                        
                      .-sNMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMNmdmy`                       
                    `/dmmmMMMMMMMMMMMMMMMMMMMMMMMMMMNmmmmmddmNNNNmmNNNNmmdys/`                      
                   .hNMMMMMMMMMNNNmdmmdddyyyhdmmmmdyo+//::--.------:::+oyhhh/o/.                    
                  :yNMMMMMNmhso+/::-::---...--:/:/:`                   ``-/o::-o-                   
                `+mMMMMNmho:.`                                             .---./.                  
               :yhMMMNdo-`                                                   `-.`:-                 
              -hdNMMNs-                                                          `//                
             `hmMMMNo`                                                          `../.               
             yNMMMMh-                                                            ``.-.              
            +NMMMMNo.                                                               ``              
           -mMMMMMmo.                                                               `-`             
           yMMMMMMmo.                                                            ``                 
          `NMMMMMMd/`                                                            `.`                
          -MMMMMMNy.                                                              ``                
          `mMMMMMm/`     ``...```````                                             `.` `             
           yMMMMm+.   .:oyyhddddddhys+/:-.``             `.-/+ssyhys/:..`          .```             
           :mMMMo. `/ydmNMMMMMMMMMMMMNmhys+:-.``````  `.:+ymNNNNNNmmdyo/:.`        -`..             
            NMMM/``+dNNmmdhhyhdmmNNNNNmmdhyo/:--..`````-:+oosssssso/:-`            --:-             
           `NMMM+ .+syso+oosyhdmNNNNNNmddddhyo/-.`    `-/+ssyhdmmmmmdds/-``        .-/:             
           :MMMMs `:/:/ohmNNMMMMMMMMNMMNmmddhy/`      `./oymNMNmMMMMMN++so-.        `-`             
           yMMMMo `--:+hNNMMmmMMMMMNmNMMmhdyyy/         `-+ddmdyhhhhy/` `..`         ` ``           
           oNdNM/ ```.:+oossyhdhmdhhdddhyss+os-           `.::+/-.``                   :-           
           +d+yN. ``````..-.-://++++++/+////++.              `````                     .:           
           :s`/y`             `...`.....-::/+/`                                         .           
           -.-yd`                 ````..-:://-                                                      
            .ymN.                  ```.-:://-                                                       
            -hNN-                  ``.-:/+o:`                                        `              
           `./hN:                  `..:+so+-``                                       .              
              -d/               ````.:+o/://::.`                                     `              
              `o+ `   `` `      `...-:oo+oyhhhy+:-.``-//:`                                          
             ``/o   `````.` ````.--::/oyhmNNNMmdys/-:ohhh/                                          
               .o.` ``````````.--::://osdNMMMMNNmddo++/::.                                          
                +/.``..`````..-::::/:+shmMMMMMNNNNNho+.`                                            
                /y/.....```.-://+ossydmNMMNMMNNmNNNd:/.                                             
                -yo+:...``-/ooysshhddmmmNNmNNmdmmNNs:..``                                           
                 osss+-..-+yyyhhyyhdddddmdhddhhhsyy+-...```                                         
                 /dhhy+:-:sdhdmmmNNNNNNNmdhhyhhhysoso+/:::/.--/.:/:..`                              
                 `hmdhyo::sdmmNMmhdmmNNNmmdhhddddddhyys+/::://ssshdho:+.                            
                  /mdddso+shmNNNhsossyhhdhhyo/:---..``         `.:odyso-`       `.                  
                   smmmhdyyhmNMmhs+ooo+oyddhhso+:--:/-.`          .odo-`        -                   
                   .yNNNNdhdmNMNdysoo++oshyhdmhssysss/:`          -//-.       .-                    
                    `hMMNNNNNMMNmdso+osoyyhdmmyohdhho-:`         `::+``````  -:`                    
                     `yMMMNMMMMMMmdhsssosyhhdmsydmhdy/``         .--.`.//-`-:/`                     
                 ````:sdMMMMMMMMMNNmdhsssyyhhddddhhso:`         --:-./:+:.:+- .                     
             `-oo:ohymh-hMMMMMMMMMMNNmyssyyysso+oo:-`           `-:/-+o+//+- `.`                    
         `-+/: .++sdmMd`/ymMMMMMMMMMNmhyyyyo+/---.`            ..://:syo+:`  :-                     
        `-:://osssNNNNN-.-/hMMMMMMMMNNmmhys+//--```            :+::/shd/`   .+- `            `      
     .-/sddmmds/oyMMNNM/.--:shNMMMMMMMMmdhy+:-:-````           .oyo+os-     so`````     .` `        
  `..```.-..`  .+mNNmNMh::---:omMMMMMMMNhsyoo/-::.-..`   `  `  :hmdy/      -My.-./        ``        
```         ```::dmMNMMMo::-::/+yNMMMMMMdysdhyso++:.+:`. .--/+/ommo`      `mdo  ```  `:` .-   `     
`        `.``  /sMMNNMNMNo/:-:::/odNMMMMNdmmhmNsydhyyh+.`-yosdmms.       `hN:d/ ..:   .s    `       
    ..```.`   .:mNMmMNyMMmo/:/:///oydmMMMMMNNNMdmNmNNmsydohNdho:         sMd +y` .-`   o            
  -sMNmN+yydy`-hNMMmMydMMMms+/:/://+oymNMMMMMMMNMMNNMNdNMmdo.           +MNy .y: ```  .s            
 yhsNMNNohmMh:oMMMNNd+mMMMMms++////+ossydmmmmNmmmNNdmNmho:` `         `/NMhy. sh/`..  :y   `.-: `   
`mMddMMMNmhMmyNMMMmN-sMNMMMMmso+++++osssysyssyssso++++::-..```      `.oNMm/s: `/.     +y    `+..   .
 oNNmMMMMNoMhNMMMMNy/NMdNMMMMNhsoo+oooosyyysssooooooo+/::---.`     .:oNdNo-:/  /-  `` +o    .h `   `
`sdMNNMMMM+dmMMMMMm/sMhyMMMMMMNdsooo++ossssysssyssooso+//:---..``.:+yNmmh/-`+- .:` `  /`   `:o      
`osNMMMMMMsmNMMMMMN:mmsdMMMMMMMMmyo+++++ooosssssssso++/:::------:/shMmNo-s. ho .y:    :   `.ys      
 */
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
include('guiltyParty.php');

if ($_SERVER['SERVER_ADDR'] == '10.101.8.1')
{
    header( 'Location: http://prodtools.devops.jive.com/simple-migration.php');
}

//CSS Styling:
include('loadUpdate.php');
echo '<html><head><title>Single Customer Migration</title>
<style type="text/css"> 
#pretty {vertical-align: bottom;}
</style><link rel="stylesheet" href="stylesheet.css"></head>';

//"Header"
echo '<body onload="init()">';
include('menu.html');
echo '<div id="head" class="head">
	<h2>Move a Single Customer';
echo '</h2>';

//The action variable tells us if this page was called by itself and why
// [EMPTY] = First visit
// help = Help button clicked
// search = A search term has been given
// move = The user has selected a domain to move
// confirm = The user confirmed that the domain should be moved
$action = $_REQUEST["action"];

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
	case "move":
		//NO BREAK HERE! Careful if you decide to modify this.
		//This is intentional since move and confirmed need the same variables. 
	case "confirmed":
		//currently both IP address and status are carried together and separated by a pipe
		//dest will have indexes [0] and [1] containing the two results.
		$dest = explode('|', $_REQUEST["dest"]);
		$location = $_REQUEST["location"];
    if ($location == 'ord')
    {
      $location = 'chicago-legacy';
    }
		$domain = $_REQUEST["domain"];
		$oldLocation = $_REQUEST["oldlocation"];
    if ($oldLocation == 'ord')
    {
      $oldLocation = 'chicago-legacy';
    }
		$oldAssigned = $_REQUEST["oldassigned"];
		$id = $_REQUEST["id"];
		if (isset($_REQUEST["flush"])) //TECHNICALLY, this is confirmation specific, but can be included in the "move" step harmlessly. 
		{
			$flush = "Y";
		} else
		{
			$flush = "N";
    }
    if (isset($_REQUEST["reason"]))
    {
      $notes = $_REQUEST["reason"];
     } else
     {
       $notes = '';
     }
		break;
}

//========
// "MAIN" =============================================================================================================
//========
//This is the search box that appears at the top of the page
//It shows up every time, making it convenient to search for another domain
//or repeat the same search if you wish to start over
echo '<div class="checkbox"><form action="" method="POST">
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
	//Actually connect to postgres for the queries we'll be making

	$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect to "pbxs" database: ' . pg_last_error());
	$curAssignmentQ = "SELECT name, domain, assigned_server, location, id FROM resource_group WHERE domain LIKE '".$search."' AND (v5 is null or v5=false) ORDER BY domain LIMIT 50;";
	$curAssignment = pg_fetch_all(pg_query($curAssignmentQ)) or die ("Current Placement Search Failed or No Results: ".pg_last_error());
	pg_close($dbconn);

	$dbconn = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connect to "util" database: ' . pg_last_error());
	$availablePBXsQ = "SELECT ip, location, status FROM pbxstatus ORDER BY status, ip;";
	$availablePBXs = pg_fetch_all(pg_query($availablePBXsQ)) or die ("Available PBXs Search Failed: ".pg_last_error());
	pg_close($dbconn);

	//Get a list of unique locations that have available pbxs
	$clean = array();
	foreach ($availablePBXs as $arr)
	{
		$clean[] = $arr['location'];
	}
	$uniqueLoc = array_unique($clean);
	//-----------------
	// This JavaScript is used to populate the second drop down list
	// based on the item selected in the first.
	//-----------------
	echo "<div id='results' class='results'><p> Note: Results limited to max 50 hits";
	echo "<br> Please also make sure that the location correctly matches the chosen destination</p>";
	echo '<script type="text/javascript">

	var locationData = [';
	foreach ($uniqueLoc as $aloc)
	{
		echo '{locationArr: "'.$aloc.'", pbxs: [';
		foreach ($availablePBXs as $apbx){
			if ($apbx['location'] == $aloc)
			{
				echo '"'.$apbx['ip'].'|'.$apbx['status'].'",';
			}
		}
		echo ']},';
	}
	   // The nested loops above should generate something like this: 
	   // 
	   // {locationArr: "chicago-legacy", pbxs: ["1|active","2|active","3|dirty","4|stinky","5|dead",]}, 
	   // {locationArr: "orem", pbxs: ["1|active","2|active","3|standby",]},
	   // {locationArr: "atlanta", pbxs: ["1|active","2|dirty","3|dirty","4|dirty","5|active",]},
	   // {locationArr: "lax", pbxs: ["1|albi the racist dragon","2|badly burnt albanian boy",]},
	
	echo '
	]

	function showPBXs(dropdown, sLocation)
	{
		pbxdropdown = dropdown.id.concat("pbx");
		var oList = document.getElementById(pbxdropdown);
	    oList.options.length = 0;
	    var colPBXs = getPBXsForLocation(sLocation);
	    if (colPBXs)
    	{
        	for (var i = 0; i < colPBXs.length; i++)
        	{
            	var sPBX = colPBXs[i];
            	addOptionToList(oList, sPBX, sPBX);
        	}
    	}
	}

	function getPBXsForLocation(sLocation)
	{
    	for (var i = 0; i < locationData.length; i++)
    	{
        	if (locationData[i].locationArr == sLocation)
        	{
            	return locationData[i].pbxs;
        	}   
    	}
    	return null;
	}

	function addOptionToList(parent, value, text)
	{
    	var oOption = new Option(text, value);
    	parent.options.add(oOption);
	}

	function init()
	{
		var drops = document.getElementsByClassName("location");
		for (var j = 0; j < drops.length; j++)
		{
	    	drops[j].options.length = 0;
	    	for (var i = 0; i < locationData.length; i++)
	    	{
	        	var sText = locationData[i].locationArr;
	        	addOptionToList(drops[j], sText, sText);
			}
		var selectedLoc = drops[j].options[0].value;
    	showPBXs(drops[j], selectedLoc);
		}	
	}

	</script>';

	//Output HTML (note and the beginning of the table including column headers
	echo "<table border='1'><tr><th>Name</th><th>Domain</th><th>Location</th><th>Server</th><th>New Location</th><th>New Server</th><th>Move this Domain</th></tr>";
	$striped=false; //This is just a bool that we use to alternate blue and white rows (for readability)
	$i = 0;
	foreach($curAssignment as $dom) //Loop through the domains we found in our first query
	{
		$i++;
		if ($striped) //Should the line be blue or white? 
		{
			echo "<tr bgcolor='#222'>";
			$striped=false;
		} else
		{
			echo "<tr bgcolor='#333'>";
			$striped=true;
		}
		//-------------------
		// Building the table
		//-------------------
		echo "<td>".$dom['name']."</td>
			<td><a href=\"domain-info.php?domain=" . $dom['domain'] . "\">".$dom['domain']."</a></td>
			<td>".$dom['location']."</td>
			<td>".$dom['assigned_server']."</td>
			<form action='' method='POST'>
			<td>
			<select name='location' class='location' id='location".$i."' onchange='showPBXs(this, this.options[this.options.selectedIndex].value)'></select>
			</td>
			<td>
			<select name='dest' id='location".$i."pbx'></select></td>
			<td>
		    <input type='hidden' name='action' value='move'>
			<input type='hidden' name='domain' value='".$dom['domain']."'>
			<input type='hidden' name='oldlocation' value='".$dom['location']."'>
			<input type='hidden' name='oldassigned' value='".$dom['assigned_server']."'>
			<input type='hidden' name='id' value='".$dom['id']."'>
			<input type='submit' value='Move' />
		</td></form></tr>"; // This portion adds the move button and closes out the row. 
	}
	echo "</table></div>"; // Finally done building the table!

	
//================
// CONFIRM CHOICE =====================================================================================================
//================
} elseif ($action=="move")
{
	$utilconn = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connectto utildb: '.pg_last_error());
	$mpls = pg_fetch_row(pg_query($utilconn, "SELECT id FROM mpls WHERE domain='".$domain."'"));
	if ($mpls !='')
	{
		echo "<p class='red'><b>WARNING!!!</b> ".$domain." is an MPLS customer. </p>";
	}	
	echo "<div id='confirm' class='confirm'>
		<script type='text/javascript'>
		function working()
		{
			document.getElementById('working').style.display = 'block';
		}
		</script>";
if (isset($dest[0])) //Make sure we got a destination...
{
	if($dest[1] != "active")
	{
		echo "<p class='red'><b>WARNING!!!</b> You have selected a destination server that has the status: ".$dest[1]."</p>";
	}	
	if (isset($domain)) //... and a domain to move
	{
		//Then display a confirmation page, and ask if the user wants to flush memcache
		echo "<form action='' method='POST'>
			<input type='hidden' name='action' value='confirmed'>
			<input type='hidden' name='oldlocation' value='".$oldLocation."'>
			<input type='hidden' name='oldassigned' value='".$oldAssigned."'>
			<input type='hidden' name='id' value='".$id."'>
			<input type='hidden' name='domain' value='".$domain."'>
			<input type='hidden' name='dest' value='".$dest[0]."'>
			<input type='hidden' name='location' value='".$location."'>
			<p>Are you sure you want to move <u class='yellow'>".$domain."</u> to the server at <u class='yellow'>".$dest[0]."</u>?
			<br>With this move, this domain's files will be migrated to <u class='yellow'>".$location."</u>
			<p>If not, simply leave this page</p>
			<div class='checkbox'>
			<p><input id='flush' type='checkbox' name='flush' value='memcache' checked><label for='flush'>Flush Memcache?</label></p>
      </div>
      <input type='text' size='60' name='reason' Placeholder='Reason' />
      </div>
			<input type='submit' value='Yes, proceed' onClick='working()' />
			</form>
			<form action='' method='POST'>
			<input type='hidden' name='action' value='search'>
			<input type='hidden' name='search' value='".$domain."'>
			<input type='submit' value='Go Back'><small>(This doesn't stop a migration if you already clicked 'Yes, proceed')</small>
			</form>
			</div><p style='display:none' class='green' id='working'><img src='loading_transparent.gif'></p>";
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
	//Call the "migrate-files.sh" script to move the files to the new location
	exec('sudo ssh -T -o StrictHostKeyChecking=no root@enc1 "/root/migrate-files.sh" '.$domain.' '.$location.' 2>/dev/null', $moveOutput, $exitcode);
	if ($exitcode > 9) //If for some reason we received a non-zero exit code, display the script output and exit code
	{
		echo "<p class='red'>".$domain." was NOT moved to ".$dest[0]." in ".$location."</p>";
		echo "<p class='red'>Error: Migration failed with exit code ".$exitcode;
		foreach($moveOutput as $output)
		{
			echo "<br>".$output;
		}
		echo "</p></div>";
	} else
	{
		
		if($exitcode==5)
		{
			echo "<p class='green'>Note: Your files were already in the specified location</p>";
		}
		
		//Make sure the files are in the datacenter
		$filepath = "/cluster/sites/".$location."/pbxs/".$id."/";
	//	$dirExists = http_get("http://10.101.8.1/".$filepath, $info);
  //  print_r($info);
  //  print "made it to here";
		if($info['response_code']==200)
		{
			echo "<p class='red'> Files failed to copy to: ".$filepath."</p></div>";
		}else
		{
			//Update the database
			$dbconn = pg_connect("host=db dbname=pbxs user=postgres ") or die('Could not connect: '.pg_last_error());
			if($oldLocation == $location)
			{
				$updateQuery = "UPDATE resource_group SET assigned_server = '".$dest[0]."', location = '".$location."' WHERE domain = '".$domain."';";
			}else
			{	
				$updateQuery = "UPDATE resource_group SET assigned_server = '".$dest[0]."', location = '".$location."', secondary_location = '".$oldLocation."' WHERE domain = '".$domain."';";
			}	
			pg_query($dbconn, $updateQuery) or die("<p class='red'>Failed to update database: ".pg_last_error()."</p></div>");

			$cdrConn = pg_connect("dbname=asterisk user=postgres host=cdr");
			if (!$cdrConn) { echo "CDR Connection failed"; }
			$utilConn = pg_connect("dbname=util user=postgres host=db");
			if (!$utilConn) { echo "UTIL Connection failed"; }
			$clientID = pg_fetch_result(pg_query($dbconn, "SELECT id FROM resource_group WHERE domain = '$domain';"), 0);
			if (!domainLoadUpdate($cdrConn, $utilConn, $dest[0], $clientID)) {
				echo "<p class='yellow'>Load for this client failed to update</p>This is doesn't affect the client at all... but maybe tell ajensen@getjive.com.<br>";
			}	

			pg_close($cdrConn);
			pg_close($utilConn);
			pg_close($dbconn);

			//Flush memchaced
			if($flush=="Y")
			{
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

      $eventDb = pg_connect("host=rwdb dbname=events user=postgres") or die('Could not connect: '. pg_last_error());
			$description = $guiltyParty." moved ".$domain." from ".$oldAssigned." in ".$oldLocation." to ".$dest[0]." in ".$location;
      if($notes=="")
      {
        $event_query = "INSERT INTO event(id, description, event_type, notes) VALUES(DEFAULT, '" . $description . "', 'SINGLE', NULL ) RETURNING id;";
      }else
      {
        $event_query = "INSERT INTO event(id, description, event_type, notes) VALUES(DEFAULT, '" . $description . "', 'SINGLE', '".$notes."' ) RETURNING id;";
      } 
			$eventID = pg_fetch_row(pg_query($eventDb, $event_query));
    
			pg_query($eventDb, "INSERT INTO event_domain VALUES('" . $eventID['0'] . "', '" .$id. "')");
	    pg_close($eventDb); //Close the event DB connection
    }
    
		sleep(1); //give the ro database time to replicate from master
		$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect: ' . pg_last_error());

		//Check the database to see if the user actually got moved to the new location or not
		$finalQuery = "SELECT domain, assigned_server, location, secondary_location FROM resource_group WHERE domain='".$domain."';";
		$final = pg_query($finalQuery) or die ('Search failed: ' . pg_last_error());
		$finalResults = pg_fetch_all($final);

		//If the new destination matches the one that the user selected, output the domain, assigned server, and location.
		if ($finalResults[0]['assigned_server']==$dest[0] && $finalResults[0]['location']==$location)
		{
			echo "<p class='green'>Domain ".$finalResults[0]['domain']." is now on ".$finalResults[0]['assigned_server']." with files in ".$finalResults[0]['location']." and secondary location as ".$finalResults[0]['secondary_location']."</p></div>";
		} else
		//If the stuff doesn't match, tell the user. However, this is probably just waiting for replication, so we'll suggest that.
		{
			echo "<p class='red'>Hmm... script says it finished, but ".$finalResults[0]['domain']." is on ".$finalResults[0]['assigned_server']." and ".$finalResults[0]['location'];
			echo "<br>Maybe check back in a minute by searching for the domain again.</p></div>";
		}

		pg_close($dbconn); //Close the DB connection
	}
} // And we're done!

echo "</body></html>";
?>

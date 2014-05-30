<?php
echo "<html><head><title>Manage PBXs</title>
<style type='text/css'> 
.red {color: red;}
.green {color: green;}
.active {background: #CCFFCC;}
.standby {background: #FFFFCC;}
.graveyard {background: #CCCCCC;}
.dirty {background: #FFCCCC;}
.moving {background: #FFCCCC;}
.special {background: #CCCCFF;}
.NEW {}
#topleft {width: 60%; float: left;}
#topright {width: 40%; float: right;}
</style>
<link rel='stylesheet' href='stylesheet.css'>
<script type='text/javascript'>

  function iframeLoaded() {
      var iFrameID = document.getElementById('theframe');
      if(iFrameID) {
            // here you can make the height, I delete it first, then I make it again
            iFrameID.height = '';
            iFrameID.height = iFrameID.contentWindow.document.body.scrollHeight + 'px';
      }   
  }

</script>
</head><body>";

$gobutton = "I'm feeling lucky!";
$killbutton = "Destroy things!";

$dbconn = pg_connect("host=db dbname=util user=postgres ") or die('Could not connect to "util" database: ' . pg_last_error());

$guiltyParty = $_SERVER['REMOTE_ADDR'];
if (isset($_REQUEST['action']))
{
	$action = $_REQUEST['action'];
	$pbx = $_REQUEST['pbx'];
	$status = $_REQUEST['status'];
	$location = $_REQUEST['location'];
	$fgroup = $_REQUEST['fgroup'];
//if (!filter_var($pbx, FILTER_VALIDATE_IP)) // <-- This is the easy way, but we have php 5.1 so ... 
if (($pbx != "") && (!preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $pbx)))
{
	    echo "<p class='red'> Invalid Input for Server! <br/> Use numbers and periods only (valid IP address required).</p>";
		echo "<p>You gave the following: ".$pbx." and ".$status."</p>";
		echo "<hr>";
		$pbx = null;
		$status = null;
		$action = null;
		$fgroup = null;
}
if (preg_match('/[^a-z]/i', $status))
{
	    echo "<p class='red'> Invalid Input for Status! <br/> Use letters only.</p>";
		echo "<p>You gave the following: ".$pbx." and ".$status."</p>";
		echo "<hr>";
		$pbx = null;
		$status = null;
		$action = null;
		$fgroup = null;
}
if (preg_match('/[^a-z_\-0-9]/i', $location))
{
	    echo "<p class='red'> Invalid Input for Location! <br/> Use letters, numbers, underscores, and hyphens only.</p>";
		echo "<p>You gave the following: ".$pbx." and ".$location."</p>";
		echo "<hr>";
		$pbx = null;
		$status = null;
		$action = null;
		$fgroup = null;
}
if (preg_match('/[^a-z_\-0-9]/i', $fgroup))
{
	    echo "<p class='red'> Invalid Input for Fail Group! <br/> Use letters, numbers, underscores, and hyphens only.</p>";
		echo "<p>You gave the following: ".$pbx." and ".$fgroup."</p>";
		echo "<hr>";
		$pbx = null;
		$status = null;
		$action = null;
		$fgroup = null;
}
if ($action == $gobutton)
{
	if ($pbx != "")
	{
		//Check fgroup first. If it's set, assume fgroup. If the user is adding a new server
		//the "action" variable will get overwritten later anyway. 
		if ($fgroup != "")
		{
			$action = "fgroup";
		}
		if ($status != "")
		{
			$action = "update";
		}
		if ($location != "")
		{
			$action = "location";
		}
		//If both status and location were set, "action" would have been set at least twice by now
		//So we'll check to see if they're both set and update "action" if they are.
		if (($status != "") && ($location != ""))
		{
			$action = "add";
		}
		//If we have reached this point without setting action to anything, then assume "terminate"
		if ($action == $gobutton)
		{
			$action = "remove";
		}
	} else
	{
		echo '<p class="red">Um... ALL of the options require an IP address. Please enter one.</p>';
	}
}
if ($action == $killbutton)
{
	$action = "terminate";
}
}
if (isset($action))
{
	syslog(LOG_INFO, $guiltyParty." performed action: ".$action.", with (".$pbx.", ".$status.", ".$location.") using the manage-pbxs.php script");
}
switch ($action)
{
	case "add":
		if (isset($fgroup))
		{
			pg_query($dbconn, "INSERT INTO pbxstatus (host,ip,status,location,failgroup) VALUES ('".$pbx."','".$pbx."','".$status."','".$location."','".$fgroup."');") or die('Could not add server! ' . pg_last_error());
		} else {
		pg_query($dbconn, "INSERT INTO pbxstatus (host,ip,status,location) VALUES ('".$pbx."','".$pbx."','".$status."','".$location."');") or die('Could not add server! ' . pg_last_error());
		}
		echo "<p>Server ".$pbx." has been added to the database with the status ".$status."</p>";
		echo "<hr>";
		break;
	case "update":
		pg_query($dbconn, "UPDATE pbxstatus SET status='".$status."' WHERE ip='".$pbx."';") or die('Could not update status! ' . pg_last_error());
		echo "<p>Server ".$pbx." has been updated with the status ".$status."</p>";
		echo "<hr>";
		break;
	case "remove":
		echo "<p class='red'>Please confirm that you wish to permanently delete the server ".$pbx." from the database</p>";
		echo "<form action='' method='POST'><input type='hidden' name='pbx' value='".$pbx."'><input type='submit' name='action' value='".$killbutton."'></form><form action='' method='POST'><input type='hidden' name='action' value=''><input type='submit' value='Cancel that...'></form>";
		echo "<hr>";
		break;	
	case "location":
		pg_query($dbconn, "UPDATE pbxstatus SET location='".$location."' WHERE ip='".$pbx."';") or die('Could not update location! ' . pg_last_error());
		echo "<p>Server ".$pbx." has been updated with the location ".$location."</p>";
		echo "<hr>";
		break;
	case "fgroup":
		pg_query($dbconn, "UPDATE pbxstatus SET failgroup='".$fgroup."' WHERE ip='".$pbx."';") or die('Could not update failgroup! ' . pg_last_error());
		echo "<p>Server ".$pbx." has been added to failgroup ".$fgroup."</p>";
		echo "<hr>";
		break;
	case "terminate":
		pg_query($dbconn, "DELETE FROM pbxstatus WHERE ip='".$pbx."';") or die('Failed to delete row! '.pg_last_error());
		echo "<p>Server ".$pbx." has been deleted from the database</p>";
		echo "<hr>";
		break;
}

$servers = "SELECT ip, location, status FROM pbxstatus ORDER BY location;";
$serverResults = pg_fetch_all(pg_query($servers)) or die ("Available PBXs Search Failed: ".pg_last_error());
pg_close($dbconn);

$clean = array();
foreach ($serverResults as $arr)
{   
    $clean[] = $arr['location'];
}
$unique = array_unique($clean);
$locationOpts = "";
foreach ($unique as $uni)
{
	$locationOpts .= "<option value='".$uni."'>".$uni."</option>";
}

$serverList = ""; 
foreach ($serverResults as $server)
{ //Here we color and label each host based on status. We also prepare to pass host and status to the next step.        
    $serverList .= "<option class='".$server['status']."' value='".$server['ip']."'>".$server['ip']." (".$server['status'].")</option>";
}   
echo '<div id="topleft">';
echo '<a href="index.php">Back to pbxutils</a>';

echo "<table><tr><th>PBX IP</th><th>Status</th><th>Location</th><th>Fail Group</th></tr>";
echo "<form action='' method='POST'><tr><td><input type='text' name='pbx' placeholder='e.g. 10.100.7.1'></td>";
echo "<td><input type='text' name='status' placeholder='e.g. active'></td>";
echo "<td><input type='text' name='location' placeholder='e.g. c98-dev'></td>";
echo "<td><input type='text' name='fgroup' placeholder='e.g. 0'></td></tr>";
echo '<tr><td colspan="4" align="center"><input type="submit" name="action" value="'.$gobutton.'"></td></tr></table>';

echo '</div><div id="topright">';
echo '<img src="options.png"><br><br><br><br>';
echo '</div>';
sleep(1);
echo '<iframe id="theframe" onload="iframeLoaded()" src="pbx-availability.php" width="100%" height="100%" frameborder="0" seamless>';

echo "</body></html>";
?>

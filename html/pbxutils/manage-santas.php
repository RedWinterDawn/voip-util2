<?php
echo "<html><head><title>Manage Santas</title>
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
$requestTime = strftime('%Y-%m-%d %H:%M:%S');

$dbconn = pg_connect("host=db dbname=util user=postgres ") or die('Could not connect to "util" database: ' . pg_last_error());

$guiltyParty = $_SERVER['REMOTE_ADDR'];
if (isset($_REQUEST['action']))
{
	$action = $_REQUEST['action'];
	$santa = $_REQUEST['santa'];
	$name = $_REQUEST['name'];
	$site = $_REQUEST['site'];
	$status = $_REQUEST['status'];
//if (!filter_var($pbx, FILTER_VALIDATE_IP)) // <-- This is the easy way, but we have php 5.1 so ... 
if (($santa != "") && (!preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $santa)))
{
	    echo "<p class='red'> Invalid Input for Server! <br/> Use numbers and periods only (valid IP address required).</p>";
		echo "<p>You gave the following: ".$santa." and ".$status."</p>";
		echo "<hr>";
		$santa = null;
		$status = null;
		$action = null;
		$site = null;
}
if (preg_match('/[^a-z]/i', $status))
{
	    echo "<p class='red'> Invalid Input for Status! <br/> Use letters only.</p>";
		echo "<p>You gave the following: ".$santa." and ".$status."</p>";
		echo "<hr>";
		$santa = null;
		$status = null;
		$action = null;
		$site = null;
}
if (preg_match('/[^a-z_\-0-9]/i', $site))
{
	    echo "<p class='red'> Invalid Input for Site! <br/> Use letters, numbers, underscores, and hyphens only.</p>";
		echo "<p>You gave the following: ".$santa." and ".$location."</p>";
		echo "<hr>";
		$santa = null;
		$status = null;
		$action = null;
		$site = null;
}
if (preg_match('/[^a-z_\-0-9]/i', $name))
{
	    echo "<p class='red'> Invalid Input for Name! <br/> Use letters, numbers, underscores, and hyphens only.</p>";
		echo "<p>You gave the following: ".$santa." and ".$fgroup."</p>";
		echo "<hr>";
		$santa = null;
		$status = null;
		$action = null;
		$site = null;
}
if ($action == $gobutton)
{
	if ($santa != "")
	{
		//Check site first. If it's set, assume site. If the user is adding a new server
		//the "action" variable will get overwritten later anyway. 
		if ($site != "")
		{
			$action = "site";
		}
		if ($status != "")
		{
			$action = "update";
		}
		if ($name != "")
		{
			$action = "name";
		}
		//If both status and location were set, "action" would have been set at least twice by now
		//So we'll check to see if they're both set and update "action" if they are.
		if (($status != "") && ($name != ""))
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
	syslog(LOG_INFO, $guiltyParty." performed action: ".$action.", with (".$santa.", ".$status.", ".$name.", ".$site.") using the manage-pbxs.php script");
}
switch ($action)
{
	case "add":
		if (isset($site))
		{
			pg_query($dbconn, "INSERT INTO presence (ip,status,name,site,message) VALUES ('".$santa."','".$status."','".$name."','".$site."','".$requestTime." added to database per ".$guiltyParty."');") or die('Could not add server! ' . pg_last_error());
		} 
		echo "<p>Santa ".$santa." has been added to the database with the status ".$status."</p>";
		echo "<hr>";
		break;
	case "update":
		pg_query($dbconn, "UPDATE presence SET status='".$status."', updated=now(), message='".$requestTime." set to ".$status." per ".$guiltyParty."' WHERE ip='".$santa."';") or die('Could not update status! ' . pg_last_error());
		echo "<p>Server ".$santa." has been updated with the status ".$status."</p>";
		echo "<hr>";
		break;
	case "remove":
		echo "<p class='red'>Please confirm that you wish to permanently delete Santa server ".$santa." from the database</p>";
		echo "<form action='' method='POST'><input type='hidden' name='santa' value='".$santa."'><input type='submit' name='action' value='".$killbutton."'></form><form action='' method='POST'><input type='hidden' name='action' value=''><input type='submit' value='Cancel that...'></form>";
		echo "<hr>";
		break;	
	case "name":
		pg_query($dbconn, "UPDATE presence SET name='".$name."', updated=now(), message='".$requestTime." set name to ".$name." per ".$guiltyParty."' WHERE ip='".$santa."';") or die('Could not update location! ' . pg_last_error());
		echo "<p>Server ".$santa." has been updated with the location ".$name."</p>";
		echo "<hr>";
		break;
	case "site":
		pg_query($dbconn, "UPDATE presence SET site='".$site."', updated=now(), message='".$requestTime." set site to ".$site." per ".$guiltyParty."' WHERE ip='".$santa."';") or die('Could not update failgroup! ' . pg_last_error());
		echo "<p>Server ".$santa." has been added to failgroup ".$site."</p>";
		echo "<hr>";
		break;
	case "terminate":
		pg_query($dbconn, "DELETE FROM presence WHERE ip='".$santa."';") or die('Failed to delete row! '.pg_last_error());
		echo "<p>Server ".$santa." has been deleted from the database</p>";
		echo "<hr>";
		break;
}

$servers = "SELECT ip, site, status FROM presence ORDER BY site;";
$serverResults = pg_fetch_all(pg_query($servers)) or die ("Available PBXs Search Failed: ".pg_last_error());
pg_close($dbconn);

$clean = array();
foreach ($serverResults as $arr)
{   
    $clean[] = $arr['site'];
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
echo '<div id="topright">';
echo "<table><tr><th width='100'></th><th width='75'>Santa IP</th><th width='75'>Name</th><th width='75'>Site</th><th width='75'>Status</th></tr>
	<tr><td>Delete</td><td>X</td><td></td><td></td><td></td></tr>
	<tr><td>Change Name</td><td>X</td><td>X</td><td></td><td></td></tr>
	<tr><td>Change Site</td><td>X</td><td></td><td>X</td><td></td></tr>
	<tr><td>Change Status</td><td>X</td><td></td><td></td><td>X</td></tr>
	<tr><td>Add New</td><td>X</td><td>X</td><td>X</td><td>X</td></tr>
	</table>";
echo '</div>';
echo '<div id="topleft">';
echo '<a href="index.php">Back to pbxutils</a>';

echo "<table><tr><th>Santa IP</th><th>Name</th><th>Site</th><th>Status</th></tr>";
echo "<form action='' method='POST'><tr><td><input type='text' name='santa' placeholder='e.g. 10.117.255.25'></td>";
echo "<td><input type='text' name='name' placeholder='e.g. santa1'></td>";
echo "<td><input type='text' name='site' placeholder='e.g. pvu'></td>";
echo "<td><input type='text' name='status' placeholder='e.g. Active'></td></tr>";
echo '<tr><td colspan="4" align="center"><input type="submit" name="action" value="'.$gobutton.'"></td></tr></table>';
echo '</div>';
sleep(1);
echo '<iframe id="theframe" onload="iframeLoaded()" src="presence-status.php" width="100%" height="100%" frameborder="0" seamless>';

echo "</body></html>";
?>

<link rel='stylesheet' href='stylesheet.css'>
<?php
$guiltyParty = $_SERVER['REMOTE_ADDR'];
$requestTime = strftime('%Y-%m-%d %H:%M:%S');
$gobutton = "I'm feeling lucky!";
$killbutton = "Kill it!";

if (isset($_GET["server"]))
{
	$server = $_GET["server"];
} else
{
	$server = $guiltyParty;
}

if (isset($_GET["action"]))
{
    $action = $_GET["action"];
} else
{
    $action = "ListStatus";
}

if (isset($_GET["SetMessage"]))
{
	$action = "AutoCleanComplete";
}

if (isset($_GET["display"]))
{
	$display = $_GET['display'];
} else {
	$display = "chicago-legacy";
}
//Don't display the message for auto-clean because its output is sent to a pbx
if ($action != "AutoCleanComplete") {
	include('menu.html');
}

if (isset($_POST['action']))
{
	$action = "ListStatus";
    $postAction = $_POST['action'];
    $pbx = $_POST['pbx'];
	$ipPieces = explode('.',$pbx);
	if ($ipPieces[2] == '60') {
		$pbxType = "megapbx";
	} else {
		$pbxType = "pbx";
	}
	if ($ipPieces[1] == 101) {
		$cType = "c1";
	} else {
		$cType = substr($ipPieces[1], -2);
	}
	$hostname = $pbxType.$ipPieces[3].'.'.$cType.'.jiveip.net';	
    $status = $_POST['status'];
    $location = $_POST['location'];
    $fgroup = $_POST['fgroup'];
//if (!filter_var($pbx, FILTER_VALIDATE_IP)) // <-- This is the easy way, but we have php 5.1 so ... 
if (($pbx != "") && (!preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $pbx)))
{
        echo "<p class='red'> Invalid Input for Server! <br/> Use numbers and periods only (valid IP address required).</p>";
        echo "<p>You gave the following: ".$pbx." and ".$status."</p>";
        $pbx = null;
        $status = null;
        $postAction = null;
        $fgroup = null;
}
if (preg_match('/[^a-z]/i', $status))
{
        echo "<p class='red'> Invalid Input for Status! <br/> Use letters only.</p>";
        echo "<p>You gave the following: ".$pbx." and ".$status."</p>";
        $pbx = null;
        $status = null;
        $postAction = null;
        $fgroup = null;
}
if (preg_match('/[^a-z_\-0-9]/i', $location))
{
        echo "<p class='red'> Invalid Input for Location! <br/> Use letters, numbers, underscores, and hyphens only.</p>";
        echo "<p>You gave the following: ".$pbx." and ".$location."</p>";
        $pbx = null;
        $status = null;
        $postAction = null;
        $fgroup = null;
}
if (preg_match('/[^a-z_\-0-9]/i', $fgroup))
{
        echo "<p class='red'> Invalid Input for Fail Group! <br/> Use letters, numbers, underscores, and hyphens only.</p>";
        echo "<p>You gave the following: ".$pbx." and ".$fgroup."</p>";
        $pbx = null;
        $status = null;
        $postAction = null;
        $fgroup = null;
}
if ($postAction == $gobutton)
{
    if ($pbx != "")
    {
        //Check fgroup first. If it's set, assume fgroup. If the user is adding a new server
        //the "action" variable will get overwritten later anyway. 
        if ($fgroup != "")
        {
            $postAction = "fgroup";
        }
        if ($status != "")
        {
            $postAction = "update";
        }
        if ($location != "")
        {
            $postAction = "location";
        }
        //If both status and location were set, "action" would have been set at least twice by now
        //So we'll check to see if they're both set and update "action" if they are.
        if (($status != "") && ($location != ""))
        {
            $postAction = "add";
        }
        //If we have reached this point without setting action to anything, then assume "terminate"
        if ($postAction == $gobutton)
        {
            $postAction = "remove";
        }
    } else
    {
        echo '<p class="red">Um... ALL of the options require an IP address. Please enter one.</p>';
    }
}
if ($postAction == $killbutton)
{
    $postAction = "terminate";
}
}
if (isset($postAction))
{
    syslog(LOG_INFO, $guiltyParty." performed action: ".$postAction.", with (".$pbx.", ".$status.", ".$location.") using the pbx-availability.php script");
	$dbconn = pg_connect("host=db dbname=util user=postgres") or die ("Could not connect to the util database: ".$pg_last_error());	
	switch ($postAction)
	{
	    case "add":
	        if (isset($fgroup))
	        {
	            pg_query($dbconn, "INSERT INTO pbxstatus (host,ip,status,location,failgroup) VALUES ('".$hostname."','".$pbx."','".$status."','".$location."','".$fgroup."');") or die('Could not add server! ' . pg_last_error());
	        } else {
	        pg_query($dbconn, "INSERT INTO pbxstatus (host,ip,status,location) VALUES ('".$pbx."','".$pbx."','".$status."','".$location."');") or die('Could not add server! ' . pg_last_error());
	        }
	        echo "<p class='green'>Server ".$pbx." has been added to the database with the status ".$status."</p>";
	        break;
	    case "update":
	        pg_query($dbconn, "UPDATE pbxstatus SET status='".$status."' WHERE ip='".$pbx."';") or die('Could not update status! ' . pg_last_error());
	        echo "<p class='sky'>Server ".$pbx." has been updated with the status ".$status."</p>";
	        break;
	    case "remove":
	        echo "<p class='red'>Please confirm that you wish to permanently delete the server ".$pbx." from the database</p>";
	        echo "<form action='' method='POST'><input type='hidden' name='pbx' value='".$pbx."'><input type='submit' name='action' value='".$killbutton."'></form><form action='' method='POST'><input type='hidden' name='action' value=''><input type='submit' value='Cancel that...'></form>";
	        break;
	    case "location":
	        pg_query($dbconn, "UPDATE pbxstatus SET location='".$location."' WHERE ip='".$pbx."';") or die('Could not update location! ' . pg_last_error());
	        echo "<p class='sky'>Server ".$pbx." has been updated with the location ".$location."</p>";
	        break;
	    case "fgroup":
	        pg_query($dbconn, "UPDATE pbxstatus SET failgroup='".$fgroup."' WHERE ip='".$pbx."';") or die('Could not update failgroup! ' . pg_last_error());
	        echo "<p class='sky'>Server ".$pbx." has been added to failgroup ".$fgroup."</p>";
	        break;
	    case "terminate":
	        pg_query($dbconn, "DELETE FROM pbxstatus WHERE ip='".$pbx."';") or die('Failed to delete row! '.pg_last_error());
	        echo "<p class='red'>Server ".$pbx." has been deleted from the database</p>";
	        break;
	}
sleep(1);
}
if ($action == "AutoCleanComplete") {
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($rwutil, "UPDATE pbxstatus SET status='standby' WHERE ip='$server' ");
		echo "$server now clean";
		syslog(LOG_INFO, "application=pbx-availability server=$server action=SetClean newState=clean guiltyParty=$guiltyParty");

		pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " scripted cleanup reported complete' WHERE ip='" . $server . "'");
	}else
	{
		echo "Error opening DB (rwdb.util) " . pg_last_error();
		die();
	}
}

if ($action == "MessageUpdate") {
	$server = $_GET["server"];
	$message = $_GET["message"];

    if (preg_match('/[^a-z\-\. 0-9]/i', $message))
    {   
        echo "<p class='red'> Invalid Input for Message! <br/> Use numbers, letters, periods, and dashes only!</p>";
    } else {
    	$updateQuery = "UPDATE pbxstatus SET message = '$message' WHERE host = '$server'";
	    $dbconn = pg_connect("host=db dbname=util user=postgres ") or die('Could not connect to "util" database: ' . pg_last_error());
		pg_query($dbconn, $updateQuery) or die ('Comment update failed!' . pg_last_error());
    	pg_close($dbconn);
		sleep(1);
	}
	$action = "ListStatus";
}

if ($action == "Add")
{
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($rwutil, "INSERT INTO pbxstatus (host, ip, status, failgroup) VALUES ('" . $server . "','" . $server . "','NEW', '0')");

		echo "$server now NEW";
		syslog(LOG_INFO, "application=pbx-availability server=$server action=Add newState=NEW guiltyParty=$guiltyParty");
	}else
	{
		echo "Error opening DB (rwdb.util) " . pg_last_error();
		die();
	}

	$action = "ReturnToSender";
}

if ($action == "SetActive")
{
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($rwutil, "UPDATE pbxstatus SET status='active' WHERE ip='$server' ");
		echo "$server now active";
		syslog(LOG_INFO, "application=pbx-availability server=$server action=SetActive newState=active guiltyParty=$guiltyParty");

		pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " set active by " . $guiltyParty . "' WHERE ip='" . $server . "'");
	}else
	{
		echo "Error opening DB (rwdb.util) " . pg_last_error();
		die();
	}

	$action = "ReturnToSender";
}

if ($action == "SetStandby")
{
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($rwutil, "UPDATE pbxstatus SET status='standby' WHERE ip='$server' ");

		echo "$server now standby";
		syslog(LOG_INFO, "application=pbx-availability server=$server action=SetStandby newState=standby guiltyParty=$guiltyParty");

		pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " set standby by " . $guiltyParty . "' WHERE ip='" . $server . "'");
	}else
	{
		echo "Error opening DB (rwdb.util) " . pg_last_error();
		die();
	}

	$action = "ReturnToSender";
}

if ($action == "Delete")
{
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($rwutil, "DELETE FROM pbxstatus WHERE ip='$server' ");

		echo "$server deleted";
		syslog(LOG_INFO, "application=pbx-availability server=$server action=Delete newState=deleted guiltyParty=$guiltyParty");
	}else
	{
		echo "Error opening DB (rwdb.util)";
		die();
	}

	$action = "ReturnToSender";
}

if ($action == "SetMigrate")
{
    if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
    {   
        pg_query($rwutil, "UPDATE pbxstatus SET status='migrating' WHERE ip='$server' ");

        echo "$server is migrating...";
        syslog(LOG_INFO, "application=pbx-availability server=$server action=SetMigrate newState=migrating guiltyParty=$guiltyParty");

        pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " set migrating by " . $guiltyParty . "' WHERE ip='" . $server . "'");
    }else
    {   
        echo "Error opening DB (rwdb.util)";
        die();
    }   

    $action = "ReturnToSender";
}

if ($action == "SetRollback")
{
    if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
    {   
        pg_query($rwutil, "UPDATE pbxstatus SET status='rollback' WHERE ip='$server' ");

        echo "$server is setup for rollback...";
        syslog(LOG_INFO, "application=pbx-availability server=$server action=SetRollback newState=rollback guiltyParty=$guiltyParty");

        pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " set rollback by " . $guiltyParty . "' WHERE ip='" . $server . "'");
    }else
    {   
        echo "Error opening DB (rwdb.util)";
        die();
    }   

    $action = "ReturnToSender";
}

if ($action == "SetSpecial")
{
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($db, "UPDATE pbxstatus SET status='special' WHERE ip='$server' ");

		echo "$server now special";
		syslog(LOG_INFO, "application=pbx-availability server=$server action=SetSpecial newState=special guiltyParty=$guiltyParty");
	}else
	{
		echo "Error opening DB (rwdb.util)";
		die();
	}

	$action = "ReturnToSender";
}

if ($action == "ReturnToSender")
{
	echo '<br/><br/><a href="pbx-availability.php">Return to list</a><br/>';
}

if ($action == "ListStatus")
{
	if ($routil = pg_connect("host=rodb dbname=util user=postgres "))
	{
		echo "<style type='text/css'>
			.group {
				display=none;
			}
			</style>
			<script type='text/javascript'>
				function showPage(_site) {
					elems = document.getElementsByClassName('group');
					for (var i = 0; i < elems.length; i++) {
						elems[i].style.display=\"none\";
					}
					document.getElementById(_site).style.display=\"block\";
				}		
			</script>";
		echo "<body onload='showPage(\"$display\")'>";
		echo "Enter IP alone to delete. IP + new field to update. All fields to add new.";
		echo "<table><tr><th>PBX IP</th><th>Status</th><th>Location</th><th>Fail Group</th></tr>";
		echo "<form action='' method='POST'><tr><td><input type='text' name='pbx' placeholder='e.g. 10.119.7.1'></td>";
		echo "<td><input type='text' name='status' placeholder='e.g. active'></td>";
		echo "<td><input type='text' name='location' placeholder='e.g. lax'></td>";
		echo "<td><input type='text' name='fgroup' placeholder='e.g. 119'></td></tr>";
		echo '<tr><td colspan="4" align="center"><input type="submit" name="action" value="'.$gobutton.'"></td></tr></form></table><br>';
		// Find unique sites
		$uniqueResult = pg_fetch_all_columns(pg_query("SELECT DISTINCT location FROM pbxstatus ORDER BY location"), 0);
		// query status table for all hosts
		$result = pg_query($routil, "SELECT failgroup,location,vmhost,host,ip,status,message FROM pbxstatus ORDER BY failgroup,\"order\",status desc,ip limit 1000;");
		echo "<hr align='left' width='330'>|";	
		foreach ($uniqueResult as $unique) {
			echo " <a href='javascript:showPage(\"$unique\")'>$unique</a> |";
		}
		echo "<br><hr align='left' width='330'>";
		/*$currentSite = "notset";
		echo "<div class='group' id='$currentSite'>";
		echo "<table border='1'>\n";
		echo "<th>failgroup</th><th>vmhost</th><th>host</th><th>ip</th><th>status</th><th>activate</th><th>standby</th><th>abandon ship</th><th>message</th>\n";
		 */
		while ($row = pg_fetch_array($result, null, PGSQL_ASSOC))
		{
			if ($row['location'] != $currentSite) {
				echo "</table></div>";
				$currentSite = $row['location'];
				echo "<div class='group' id='$currentSite'>";
				echo "<table border='1'>\n";
				echo "<th>failgroup</th><th>vmhost</th><th>host</th><th>ip</th><th>status</th><th>activate</th><th>standby</th><th>abandon ship</th><th>message</th>\n";
			}
			$showControls = false;
			echo "<tr>
				<td class='group".$row['failgroup']."'>" . $row['failgroup'] . "</td>
				<td>" . $row['vmhost'] . "</td>
				<td>" . $row['host'] . "</td>
				<td><a href='pbx-server-info.php?server=" . $row['ip'] . "'>" . $row['ip'] . "</a></td>
				<td><div";
			if ($row['status'] == "active") { echo " class=\"green\" "; }
			if ($row['status'] == "standby") { echo " class=\"yellow\" "; $showControls = true; }
			if ($row['status'] == "graveyard") { echo " class=\"gray\" "; }
			if ($row['status'] == "dirty") { echo " class=\"red\" "; }
			if ($row['status'] == "moving") { echo " class=\"pink\" "; $showControls = true; }
			if ($row['status'] == "migrating") { echo " class=\"purple\" "; }
			if ($row['status'] == "rollback") { echo " class=\"lightbrown\" "; }
			if ($row['status'] == "special") { echo " class=\"sky\" "; }
			if ($row['status'] == "quarantine") { echo " class=\"orange\" "; }
			if ($row['status'] == "clean") { echo " class=\"purple\" "; }
			echo ">". $row['status'] . "</div></td>";
			
			if ($row['status'] == "active"){
				echo "<td>-</td>";
				echo "<td><a href=\"pbx-availability.php?action=SetStandby&server=" . $row['ip'] . "\">set standby</a></td>";
				echo '<td><a href="pbx-sip-failure.php?server=' . $row['ip'] . '">abandon ship</a></td>';
			} else if ($row['status'] == "clean") {
				echo "<td>-</td>";
				echo "<td><a href=\"pbx-availability.php?action=SetStandby&server=" . $row['ip'] . "\">set standby</a></td>";
				echo "<td>-</td>";
			} else if ($row['status'] == "dirty") {
				echo "<td><a href=\"pbx-availability.php?action=SetActive&server=" . $row['ip'] . "\">set active</a></td>";
				echo "<td><a href=\"pbx-availability.php?action=SetStandby&server=" . $row['ip'] . "\">set standby</a></td>";
				echo "<td><a href=\"clean.php?server=" . $row['host'] . "\">clean me</a></td>";
			} else if ($showControls) {
				echo "<td><a href=\"pbx-availability.php?action=SetActive&server=" . $row['ip'] . "\">set active</a></td>";
				echo "<td><a href=\"pbx-availability.php?action=SetStandby&server=" . $row['ip'] . "\">set standby</a></td>";
				echo "<td>-</td>";
			} else {
				echo "<td>-</td>";
				echo "<td>-</td>";
				echo "<td>-</td>";
			}
			
			echo "<form action='' method='get'><td>
				<input type='hidden' name='server' value='" . $row['ip'] ."' />
				<input type='hidden' name='action' value='MessageUpdate' />
				<input type='text' name='message' placeholder='" . $row['message'] . "' size='96' />
				</td></form>";
			echo "</tr>\n";
		}
		echo "</table><br/>\n";
	
		echo "</body>";
	}else
	{
		echo "Error opening DB (rodb.util)";
		die();
	}
}

?>

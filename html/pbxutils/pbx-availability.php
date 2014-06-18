<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');
$guiltyParty = $_SERVER['REMOTE_ADDR'];
$requestTime = strftime('%Y-%m-%d %H:%M:%S');

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

if ($action == "AutoCleanComplete") {
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($rwutil, "UPDATE pbxstatus SET status='clean' WHERE ip='$server' ");
		echo "$server now clean";
		syslog(LOG_INFO, "application=pbx-availability server=$server action=SetClean newState=clean guiltyParty=$guiltyParty");

		pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " scripted cleanup reported complete WHERE host='" . $server . "'");
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

		pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " set active by " . $guiltyParty . "' WHERE host='" . $server . "'");
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

		pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " set standby by " . $guiltyParty . "' WHERE host='" . $server . "'");
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

        pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " set migrating by " . $guiltyParty . "' WHERE host='" . $server . "'");
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

        pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " set rollback by " . $guiltyParty . "' WHERE host='" . $server . "'");
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
		// management link
		echo "<a href='index.php'>Return to PBX Utils</a><br/>";
		echo "<a href='manage-pbxs.php'>Manage PBXs</a><br/>\n";
		echo "<hr/>\n";

		// query status table for all hosts
		$result = pg_query($routil, "SELECT failgroup,vmhost,host,ip,status,message FROM pbxstatus ORDER BY failgroup,\"order\",status desc,ip limit 1000;");
	
		echo "<table border='1'>\n";
		echo "<th>failgroup</th><th>vmhost</th><th>host</th><th>ip</th><th>status</th><th>activate</th><th>standby</th><th>abandon ship</th><th>message</th>\n";
		while ($row = pg_fetch_array($result, null, PGSQL_ASSOC))
		{
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
			if ($row['status'] == "dirty") { echo " class=\"red\" "; $showControls = true; }
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
	
	}else
	{
		echo "Error opening DB (rodb.util)";
		die();
	}
}

?>

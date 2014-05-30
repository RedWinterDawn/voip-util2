<link rel='stylesheet' href='stylesheet.css'>
<?php

$guiltyParty = $_SERVER['REMOTE_ADDR'];
$requestTime = strftime('%Y-%m-%d %H:%M:%S');

if (isset($_GET["server"]))
{
	$server = $_GET["server"];
}else
{
	$server = $guiltyParty;
}

if (isset($_GET["action"]))
{
	$action = $_GET["action"];
}else
{
	$action = "ListStatus";
}

if ($action == "SetActive")
{
	if($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($rwutil, "UPDATE presence SET status='Active', updated=now(), message='".$requestTime." set active by ".$guiltyParty."' WHERE ip='$server' ");
		echo "$server Now Active";
		echo "<p><a href='presence-status.php'>Pressence List</a></p>";
		syslog(LOG_INFO, "application=presence-status server=$server action=SetActive newState=active guiltyParty=$guiltyParty");
	}else
	{
		echo "Error opening DB rwdb.util ".pg_last_error();
		die();
	}

	$action = "ReturnToSender";
}

if ($action == "SetStandby")
{
	if($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($rwutil, "UPDATE presence SET status='Standby', updated=now(), message='".$requestTime." set standby by ".$guiltyParty."' WHERE ip='$server' ");
		echo "$server Now Standby";
		echo "<p><a href='presence-status.php'>Pressence List</a></p>";
		syslog(LOG_INFO, "application=presence-status server=$server action=SetStandby newState=active guiltyParty=$guiltyParty");
	}else
	{
		echo "Error opening DB rwdb.util ".pg_last_error();
		die();
	}

	$action = "ReturnToSender";
}

if ($action == "ListStatus")
{
	if($routil = pg_connect("host=rodb dbname=util user=postgres "))
	{
		//pbxutils link
		echo "<a href='index.php'>PBX UTILS</a><br/>\n";
		echo "<hr\>\n";

		//query status table for all sleighs
		$sleighs = pg_fetch_all(pg_query($routil, "SELECT * FROM presence order by ip;"));
		echo "<table border='1'>\n";
		echo "<th>IP</th><th>Name</th><th>Site</th><th>Status</th><th>Activate</th><th>Standby<th>Abandon Sleigh</th></th><th>Message</th>";
		foreach($sleighs as $sleigh)
		{
			$controls = false;
			echo "<tr>
				<td><a href=\"presence-server-info.php?server=".$sleigh['ip']."\">".$sleigh['ip']."</a></td>
				<td>".$sleigh['name']."</td>
				<td>".$sleigh['site']."</td>
				<td>".$sleigh['status']."</td";

			if ($sleigh['status'] == "Active") {echo " class=\"green\" "; $controls= true; }
			if ($sleigh['status'] == "Standby") {echo " class=\"yellow\" "; $controls= true; }
			if ($sleigh['status'] == "Dirty") { echo " class=\"red\" "; $controls = true; }
			if ($sleigh['status'] == "Special") { echo " class=\"sky\" "; }

			if($controls)
			{
				echo "><td><a href=\"presence-status.php?action=SetActive&server=" . $sleigh['ip'] . "\">Set Active</a></td>
					  <td><a href=\"presence-status.php?action=SetStandby&server=" . $sleigh['ip'] . "\">Set Standby</a></td>
					  ";
			}else
			{
				echo "><td></td><td></td>";
			}

			if ($sleigh['status'] == "Active")
			{
				echo "<td><a href=\"presence-abandon.php?server=".$sleigh['ip']."\">Abandon Sleigh</a></td>";
			}else
			{
				echo "<td></td>";
			}
			
			echo "<td>".$sleigh['message']."</td>";

			echo "</tr>\n";
		}

		echo "</table><br/>\n";
	}else
	{
		echo "Error opening DB";
		die();
	}

}
?>		

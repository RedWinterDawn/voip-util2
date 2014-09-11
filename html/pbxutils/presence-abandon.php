<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');
include('guiltyParty.php');
$requestTime = strftime('%Y-%m-%d %H:%M:%S');
$mail_to='noc@getjive.com';

if (isset($_GET["server"]))
{
	$server = $_GET["server"];
}else
{
	$server = $guiltyParty;
}

if ($server == $guiltyParty)
{
	$guiltyParty = "AutoAbandon@" . $guiltyParty;
}

if ($routil = pg_connect("host=rodb dbname=util user=postgres"))
{
}else
{
	echo "Error opening DB" .pg_last_error();
	syslog(LOG_ALERT, "application=presence-abandon server=$server action=errorOpeningDB db=rodb.util state=other guiltyParty=$guiltyParty customMessage='presence $server unknown - no action taken'");
	die();
}

if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres"))
{
	//query presence for this host
	$santa = pg_query($routil, "SELECT ip, status, name, site FROM presence WHERE ip ='".$server."';");

	if ($santaRow = pg_fetch_array($santa,null,PGSQL_ASSOC))
	{
		//found santa server
		if ($santaRow['status'] == "Active")
		{
			// get standby 
			$standbySanta = pg_query($routil, "SELECT name, site, ip, status FROM presence WHERE status='Standby';");
			
			if($standbySantaRow = pg_fetch_array($standbySanta,null,PGSQL_ASSOC))
			{
				//make standby active
				pg_query($rwutil, "UPDATE presence SET status = 'Active', message='".$requestTime." accepted abandon from ".$santaRow['ip']." per ".$guiltyParty."', updated=now() WHERE ip='".$standbySantaRow['ip']."';");

				//mark santa as moving
				pg_query($rwutil, "UPDATE presence SET status = 'Moving', message='".$requestTime." abandoned to ".$standbySantaRow['ip']." per ".$guiltyParty."', updated=now() WHERE ip='".$santaRow['ip']."';");

				//move domains to standby
				//connect to pbxs database
				$rwpbxs = pg_connect("host=rwdb dbname=pbxs user=postgres ")
					or die("Failed to connect to pbxs rwdb".pg_last_error());

				//get the domains for event logging
				$domainResultQ = "SELECT id FROM resource_group WHERE presence_server='".$santaRow['ip']."';";
				$domainResult = pg_query($rwpbxs, $domainResultQ);

				//make the change in the pbxs db
				$query = "UPDATE resource_group SET presence_server='".$standbySantaRow['ip']."' WHERE presence_server='".$santaRow['ip']."';";
				$result = pg_query($rwpbxs, $query) or die("Failed to make the change in pbxs db: ".pg_last_error());
				pg_free_result($result);
				
				//make santa dirty
				pg_query($rwutil, "UPDATE presence SET status='Dirty', updated=now() WHERE ip='".$santaRow['ip']."';");

				//send email
				//
				$mail_subject=$santaRow['name'].".".$santaRow['site']." (".$santaRow['ip'].") abandoned to ".$standbySantaRow['name'].".".$standbySantaRow['site']." (".$standbySantaRow['ip'].") per ".$guiltyParty;
				//update events db
				$events = pg_connect("host=rwdb dbname=events user=postgres")
                     or die('Could not connect: ' . pg_last_error());

				//insert event into event table
                 $eventID = pg_fetch_row(pg_query($events, "INSERT INTO event(id, description) VALUES(DEFAULT, '" . $mail_subject . "') RETURNING id;"));

				if ($domains = pg_fetch_all($domainResult))
				{
                     foreach ($domains as $domainID)
					 {
                         pg_query($events, "INSERT INTO event_domain VALUES('" . $eventID['0'] . "', '" . $domainID['id'] . "')");
	                 }
                 }else
                     {
                         //no domains available
                         echo " No Domains to record";
					 }

                 // Close connection
                 pg_close($events);

				echo "<p>".$santaRow['ip']." abandoned to ".$standbySantaRow['ip']."</p>";
				echo "<p><a href='presence-status.php'>Pressence List</a></p>
					<p> After 5 min run the Evict commad on the santa server.    curl -XDELETE http://localhost/system/subscriptions</p>";
				echo "<img src=\"abandonSleigh.jpg\" alt=\"Santa Gave UP!\">";
				
			}else
			{
				echo "No Standby Available";
				echo "<p><a href='presence-status.php'>Pressence List</a></p>";

				pg_query($rwutil, "UPDATE presence SET message='" . $requestTime . " failed to abandon per No Standby Available', updated=now() WHERE  ip='" . $santaRow['ip'] . "'"); 
				syslog(LOG_WARNING, "application=presence-abandon server=$server action=NoStandbyAvailable state=active guiltyParty=$guiltyParty customMessage='no standby available for santa $server - no action taken'");

				//send email
				//
			}
		}else if ($santaRow == "Dirty")
		{
			echo "Santa ".$server." is Dirty can not abandon";
			echo "<p><a href='presence-status.php'>Pressence List</a></p>";
			syslog(LOG_INFO, "application=presence-abandon server=$server action=DoNotRestart state=dirty guiltyParty=$guiltyParty customMessage='santa $server already dirty - no action taken'");
		}else
		{
			//not active or dirty
			echo "Santa ".$server." is not Active";
			echo "<p><a href='presence-status.php'>Pressence List</a></p>";
			pg_query($rwutil, "UPDATE presence SET message='".$requestTime." failed to abandon per NotActive', updated=now() WHERE  ip='".$santaRow['ip']."'");
			syslog(LOG_INFO, "application=presence-abandon server=$server action=NotActive state=other guiltyParty=$guiltyParty customMessage='santa $server not active or dirty - no action taken'");
		}

	}else
	{
		//santa not in sleigh add santa as new
		echo "Santa ".$server." is not in the DB please add as new";
		echo "<p><a href='presence-status.php'>Pressence List</a></p>";
	}
	pg_close($rwutil);
			
}else
{
	echo "Error opening DB: ".pg_last_error();
	die();
}
pg_close($routil);
?>


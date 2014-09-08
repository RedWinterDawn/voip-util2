<?php

include('loadUpdate.php');
$guiltyParty = $_SERVER['REMOTE_ADDR'];
$requestTime = strftime('%Y-%m-%d %H:%M:%S');
$mail_to='noc@getjive.com';

if (isset($_GET["server"]))
{
	$server = $_GET["server"];
} else
{
	$server = $guiltyParty;
}

if ($server == $guiltyParty)
{
	$guiltyParty = "AutoAbandon";
}

if ($routil = pg_connect("host=rodb dbname=util user=postgres"))
{
}else
{
    echo "Error opening DB (rodb.util) " . pg_last_error();
    syslog(LOG_ALERT, "application=pbx-sip-failure server=$server action=errorOpeningDB db=rodb.util state=other guiltyParty=$guiltyParty customMessage='pbx $server unknown - no action taken'");
    die();
}

if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres"))
{
	// query status table for this host
	$result = pg_query($routil, "SELECT host,ip,status,failgroup FROM pbxstatus WHERE ip='" . $server . "';");
	
	if ($row = pg_fetch_array($result,null,PGSQL_ASSOC))
	{
		// found an entry for this host
		$failgroup = $row['failgroup'];
		if ($row['status'] == "active")
		{

			// this status is active, find standby
			$standbyResult = pg_query($rwutil, "UPDATE pbxstatus SET status = 'active' WHERE ip = (SELECT ip FROM pbxstatus WHERE status = 'standby' AND failgroup = '" . $failgroup . "' LIMIT 1) RETURNING host, ip;") or die ('ohno! '.pg_last_error());

			if ($standbyRow = pg_fetch_array($standbyResult, null, PGSQL_ASSOC))
			{
				// respond do not restart
				echo "DoNotRestart";
				
				// flag as moving in case pgsql connect fails
				pg_query($rwutil, "UPDATE pbxstatus SET status='moving' WHERE ip='" . $row['ip'] . "'");

				// move domains to standby
				
				// Connecting, selecting database
				$rwpbxs = pg_connect("host=rwdb dbname=pbxs user=postgres ")
					or die('Could not connect: ' . pg_last_error());

				//get domains to be changed for event logging
				$domainResultQ = "SELECT id FROM resource_group WHERE assigned_server='" . $row['ip'] . "';";
				$domainResult = pg_query($rwpbxs, $domainResultQ);

				// Make the change in the pbxs DB
				$query = "UPDATE resource_group SET assigned_server='" . $standbyRow['ip'] . "' WHERE assigned_server='" . $row['ip'] . "';" ;
				$result = pg_query($rwpbxs,$query) or die('Query failed: ' . pg_last_error());

				// Free resultset
				pg_free_result($result);

				// Closing connection
				pg_close($rwpbxs);

				// mark this host dirty
				pg_query($rwutil, "UPDATE pbxstatus SET status='dirty' WHERE ip='" . $row['ip'] . "'");
				pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " abandoned to " . $standbyRow['ip'] . " per " . $guiltyParty . "' WHERE ip='" . $row['ip'] . "'");
				pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " accepted abandon from " . $row['host'] . " per " . $guiltyParty . "' WHERE ip='" . $standbyRow['ip'] . "'");
				syslog(LOG_INFO, "application=pbx-sip-failure server=$server action=abandonShip state=dirty guiltyParty=$guiltyParty failgroup=$failgroup customMessage='pbx $server has abandoned ship'");

				$mail_subject=$row['host'] . " abandoned to " . $standbyRow['ip'] . " per " . $guiltyParty;
				$mail_body=$requestTime . " " . $mail_subject;
				$mail_headers='From: pbx-sip-failure@jive.com' . "\r\n";
				mail($mail_to, $mail_subject,$mail_body,$mail_headers);

				$rwCDR = pg_connect("host=cdr user=postgres dbname=asterisk");
				if (!serverLoadUpdate($rwCDR, $rwutil, $row['ip'], $standbyRow['ip'])) {
					$mail_subject = "${row['ip']}, ${standbyRow['ip']} failed abandon";
					$mail_body = "$guiltyParty, $requestTime";
					mail("ajensen@getjive.com",$mail_subject, $mail_body);
				}					
				pg_close($rwCDR);
				//update events db
				//Conecting to event db
				
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
						// echo " No Domains to record";
					}

				// Close connection 
				pg_close($events);

			}else
			{
				// no standby available
				echo "NoStandbyAvailable";

				pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " failed to abandon per NoStandbyAvailable' WHERE  host='" . $row['host'] . "'");
				syslog(LOG_WARNING, "application=pbx-sip-failure server=$server action=NoStandbyAvailable state=active guiltyParty=$guiltyParty customMessage='no standby available for pbx $server - no action taken'");

                $mail_subject=$row['host'] . " FAILED to abandon per " . $guiltyParty;
				$mail_body=$requestTime . " " . $mail_subject;
				mail($mail_to, $mail_subject,$mail_body);
			}
		}else if ($row['status'] == "dirty")
		{
			// already marked dirty, continue to send do not restart requests
			echo "DoNotRestart";
			syslog(LOG_INFO, "application=pbx-sip-failure server=$server action=DoNotRestart state=dirty guiltyParty=$guiltyParty customMessage='pbx $server already dirty - no action taken'");
		}else
		{
			// this host is not active or dirty - do not change status
			echo "NotActive";
			pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " failed to abandon per NotActive' WHERE  host='" . $row['host'] . "'");
			syslog(LOG_INFO, "application=pbx-sip-failure server=$server action=NotActive state=other guiltyParty=$guiltyParty customMessage='pbx $server not active or dirty - no action taken'");
		}
		
	}else
	{
		// no entry for this host
		// flag new in status table
		pg_query($rwutil, "INSERT INTO pbxstatus (host,ip,status) VALUES ('" . $server . "','" . $server . "','NEW')");
		echo "UnknownHost";

		// TODO: alert for this host indicating no entry
		syslog(LOG_INFO, "application=pbx-sip-failure server=$server action=UnknownHost state=other guiltyParty=$guiltyParty customMessage='pbx $server unknown - no action taken'");

        $mail_subject="NEW host " . $row['host'] . " has no entry - added by " . $guiltyParty;
        $mail_body=$requestTime . " " . $mail_subject;
        mail($mail_to, $mail_subject,$mail_body);
	}

	pg_close($rwutil);
}else
{
	echo "Error opening DB (rwdb.util) " . pg_last_error();
	syslog(LOG_ALERT, "application=pbx-sip-failure server=$server action=errorOpeningDB db=rwdb.util state=other guiltyParty=$guiltyParty customMessage='pbx $server unknown - no action taken'");
	die();
}

?>

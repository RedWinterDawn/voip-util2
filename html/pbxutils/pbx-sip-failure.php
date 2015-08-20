<?php

include('loadUpdate.php');
include('guiltyParty.php');
$requestTime = strftime('%Y-%m-%d %H:%M:%S');
$mail_to='noc@getjive.com';

if (isset($_GET["server"]))
{
	$server = $_GET["server"];
} else
{
	$server = $guiltyParty;
}

switch ($guiltyParty) {
  case $server:
    $guiltyParty = "AutoAbandon@" . $guiltyParty;
    break;
  case "10.101.24.2":
  case "10.119.255.224":
  case "10.120.255.224":
  case "10.122.255.224":
    $guiltyParty = "Xymon";
    break;
}

if (!$routil = pg_connect("host=rodb dbname=util user=postgres")) {
    echo "Error opening DB (rodb.util) " . pg_last_error();
    syslog(LOG_ALERT, "application=pbx-sip-failure server=$server action=errorOpeningDB db=rodb.util state=other guiltyParty=$guiltyParty customMessage='pbx $server unknown - no action taken'");
    die();
}

if (!$rwutil = pg_connect("host=rwdb dbname=util user=postgres")) {
	echo "Error opening DB (rwdb.util) " . pg_last_error();
	syslog(LOG_ALERT, "application=pbx-sip-failure server=$server action=errorOpeningDB db=rwdb.util state=other guiltyParty=$guiltyParty customMessage='pbx $server unknown - no action taken'");
	die();
}

$failable = pg_fetch_assoc(pg_query($routil, "SELECT pbx.failable as pbx_failable, 
        site.failable as site_failable, 
        site.universal_failable as universal_failable, 
        status.failable as status_failable
    FROM pbxstatus pbx
    INNER JOIN sitestatus site ON pbx.site_id = site.site_id
    INNER JOIN status ON pbx.status = status.name
    WHERE pbx.ip = '$server';"));
	
if ($failable['pbx_failable'] == 'f') {
  exit("PbxNotFailable");
		syslog(LOG_INFO, "application=pbx-sip-failure server=$server action=PbxNotFailable state=dirty guiltyParty=$guiltyParty customMessage='pbx $server not in abandonable state - no action taken'");
}
if ($failable['site_failable'] == 'f') {
  exit("SiteNotFailable");
		syslog(LOG_INFO, "application=pbx-sip-failure server=$server action=SiteNotFailable state=dirty guiltyParty=$guiltyParty customMessage='pbx $server in non-abandonable site - no action taken'");
}
if ($failable['universal_failable'] == 'f') {
  exit("v4NotFailable");
		syslog(LOG_WARNING, "application=pbx-sip-failure server=$server action=v4NotFailable state=dirty guiltyParty=$guiltyParty customMessage='abandons turned off for v4 - no action taken'");
}
if ($failable['status_failable'] == 'f') {
  exit("StatusNotFailable");
		syslog(LOG_INFO, "application=pbx-sip-failure server=$server action=StatusNotFailable state=dirty guiltyParty=$guiltyParty customMessage='pbx $server has non-abandonable status - no action taken'");
}

// query status table for this host
$result = pg_query($routil, "SELECT host,ip,status,failgroup,occupant FROM pbxstatus WHERE ip='" . $server . "';");
if (!$row = pg_fetch_assoc($result))
{
	// no entry for this host
	echo "UnknownHost";

	syslog(LOG_INFO, "application=pbx-sip-failure server=$server action=UnknownHost state=other guiltyParty=$guiltyParty customMessage='pbx $server unknown - no action taken'");
  $mail_subject="Unknown host " . $row['host'] . " has no entry - requested by " . $guiltyParty . " Server: " .$server;
  $mail_body=$requestTime . " " . $mail_subject;
	$mail_headers='From: pbx-sip-failure@jive.com' . "\r\n";
  mail($mail_to, $mail_subject,$mail_body,$mail_headers);
  exit();
} else {
	$failgroup = $row['failgroup'];
  $curStatus = $row['status'];
  $curOccupant = $row['occupant'];
  //Find a standby and set its status to this pbx's current one. 
  $standbyResult = pg_query($rwutil, "UPDATE pbxstatus SET status = '$curStatus', occupant = '$curOccupant' 
    WHERE ip = (SELECT ip FROM pbxstatus WHERE status = 'standby' AND failgroup = '" . $failgroup . "' LIMIT 1) 
    RETURNING host, ip;") or die ('ohno! '.pg_last_error());

	if ($standbyRow = pg_fetch_assoc($standbyResult))
	{
		// respond do not restart
		echo "DoNotRestart";
		
		// flag as moving in case pgsql connect fails
		pg_query($rwutil, "UPDATE pbxstatus SET status='moving' WHERE ip='" . $row['ip'] . "'");

		// move domains to standby
		
		// Connecting, selecting database
		$rwpbxs = pg_connect("host=rwdb dbname=pbxs user=postgres ")
			or die('Could not connect: ' . pg_last_error());
		$ropbxs = pg_connect("host=rwdb dbname=pbxs user=postgres ")
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
		syslog(LOG_INFO, "application=pbx-sip-failure server=$server action=abandonShip state=dirty guiltyParty=$guiltyParty failgroup=$failgroup customMessage='pbx $server has abandoned ship'");
		pg_query($rwutil, "UPDATE pbxstatus SET status='dirty' WHERE ip='" . $row['ip'] . "'");
		pg_query($rwutil, "UPDATE pbxstatus SET occupant='' WHERE ip='" . $row['ip'] . "'");
		pg_query($rwutil, "UPDATE pbxstatus SET abandoned='now()' WHERE ip='" . $row['ip'] . "'");
		pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " abandoned to " . $standbyRow['ip'] . " per " . $guiltyParty . "' WHERE ip='" . $row['ip'] . "'");
		pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " accepted abandon from " . $row['host'] . " per " . $guiltyParty . "' WHERE ip='" . $standbyRow['ip'] . "'");

    //################## SEND OUT EMAIL INFORMING ABANDONS ON SPECIAL PBXS ######################//

    $resourceQ = "SELECT domain FROM resource_group WHERE assigned_server = '".$standbyRow['ip']."';";
$affectedDomains = pg_query($ropbxs, $resourceQ) or die('Query failed: ' . pg_last_error());
$affectedArray = array();
while ($rows = pg_fetch_row($affectedDomains)) {
  $affectedArray[] = $rows['0'];
}
$mailListQ = "SELECT domain FROM special_pbxs WHERE mail_list = 't';";
$mailDomains = pg_query($routil, $mailListQ) or die('Query failed: ' . pg_last_error());
$mailArray = array();
while ($rows = pg_fetch_row($mailDomains)) {
  $mailArray[] = $rows['0'];
}
$mailList = array_intersect($affectedArray, $mailArray);
$list = "";
foreach ($mailList as $domain) {
$list .= $domain.', ';
}

    //################## END ######################//

		$mail_subject=$row['host'] ." ".$list." abandoned to " . $standbyRow['ip'] . " per " . $guiltyParty;
		$mail_body=$requestTime . " " . $mail_subject;
		$mail_headers='From: pbx-sip-failure@jive.com' . "\r\n";
		mail($mail_to, $mail_subject,$mail_body,$mail_headers);

		$rwCDR = pg_connect("host=cdr user=postgres dbname=asterisk");
		if (!serverLoadUpdate($rwCDR, $rwutil, $row['ip'], $standbyRow['ip'])) {
			$mail_subject = "${row['ip']}, ${standbyRow['ip']} failed abandon";
			$mail_body = "$guiltyParty, $requestTime";
      $mail_headers='From: load-update@jive.com' . "\r\n";
			mail("ajensen@jive.com",$mail_subject, $mail_body, $mail_headers);
		}					
		pg_close($rwCDR);
		//update events db
		//Conecting to event db
		
		$events = pg_connect("host=rwdb dbname=events user=postgres")
			or die('Could not connect: ' . pg_last_error());

		//insert event into event table
		$eventID = pg_fetch_row(pg_query($events, "INSERT INTO event(id, description, event_type) VALUES(DEFAULT, '" . $mail_subject . "', 'ABANDON') RETURNING id;"));
		$rgidArray = array();

		if ($domains = pg_fetch_all($domainResult))
		{
			foreach ($domains as $domainID)
			{
				pg_query($events, "INSERT INTO event_domain VALUES('" . $eventID['0'] . "', '" . $domainID['id'] . "')");
    			$rgidArray[] = $domainID['id'];
			}
		}
		// Close connection 
		pg_close($events);

		//#################################################
		// Send customer id list to proactive notification
		//#################################################

		// make a json string (not using the function json_encode because this version of php is too old)
		$rgidJsonString = '[';
		foreach ($rgidArray as $thisID)
		{
			if ($rgidJsonString == '[') 
			{
				$rgidJsonString = $rgidJsonString . '"' . $thisID . '"';
			} else {
				$rgidJsonString = $rgidJsonString . ',"' . $thisID . '"';
			}
		}
		$rgidJsonString = $rgidJsonString . ']';

		// red rover, red rover, curl it on over
		// http://10.118.252.48:7676/notify/migrationWatch/
		$ch = curl_init("http://10.118.252.48:7676/notify/migrationWatch/");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_PORT, 7676);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $rgidJsonString);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($rgidJsonString))); 
		curl_exec($ch);
		curl_close($ch);


		//#################################################
		// Notify icalls
		//#################################################
		//send -2 to icalls for bar indicating abandon
		exec("/opt/jive/icalls_abandon.py ".$row['ip']." ".$standbyRow['ip']." ", $output, $exitcode);
	}else
	{
		// no standby available
		echo "NoStandbyAvailable";

		pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " failed to abandon per NoStandbyAvailable' WHERE  host='" . $row['host'] . "'");
		syslog(LOG_WARNING, "application=pbx-sip-failure server=$server action=NoStandbyAvailable state=active guiltyParty=$guiltyParty customMessage='no standby available for pbx $server - no action taken'");


       //################## SEND OUT EMAIL INFORMING ABANDONS ON SPECIAL PBXS ######################//

        $resourceQ = "SELECT domain FROM resource_group WHERE assigned_server = '".$row['host']."';";
    $affectedDomains = pg_query($ropbxs, $resourceQ) or die('Query failed: ' . pg_last_error());
    $affectedArray = array();
    while ($rows = pg_fetch_row($affectedDomains)) {
        $affectedArray[] = $rows['0'];
    }
    $mailListQ = "SELECT domain FROM special_pbxs WHERE mail_list = 't';";
    $mailDomains = pg_query($routil, $mailListQ) or die('Query failed: ' . pg_last_error());
    $mailArray = array();
    while ($rows = pg_fetch_row($mailDomains)) {
        $mailArray[] = $rows['0'];
    }
    $mailList = array_intersect($affectedArray, $mailArray);
    $list = "";
    foreach ($mailList as $domain) {
      $list .= $domain.', ';
    }

        //################## END ######################//

    $mail_subject=$row['host'] ." ". $list. " FAILED to abandon (no standby available) per " . $guiltyParty;
		$mail_body=$requestTime . " " . $mail_subject;
		$mail_headers='From: autoabandon-failure@jive.com' . "\r\n";
		mail($mail_to, $mail_subject,$mail_body,$mail_headers);
	}
}  

pg_close($rwutil);

?>

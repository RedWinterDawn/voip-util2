<?php
include('guiltyParty.php');
$mail_to="noc@getjive.com";
$isEverything = false;
if (isset($_REQUEST['addr']) && isset($_REQUEST['failable'])) {
  if (strtoupper($_REQUEST['failable']) == "F" || strtoupper($_REQUEST['failable']) == "FALSE") {
    $failable = "f";
  } else {
    $failable = "t";
  }
  $addr = $_REQUEST['addr'];
  if (preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]?|[0-9])$/', $addr)) {
    $query = "UPDATE pbxstatus SET failable = '$failable' WHERE ip = '$addr';";
  } else if (preg_match('/^[\w-]*$/', $addr)) {
    $query = "UPDATE sitestatus SET failable = '$failable' WHERE site_id = '$addr';";
	  $wholeSite = true;
    if ($addr == "all" || $addr == "universal") {
      $query = "UPDATE sitestatus SET universal_failable = '$failable';";
      $isEverything = true;
    }
  } else { 
    die("Error 4");
  }
} else {
  die("Error 1");
}
if ($failable == 't') {
  $insertEvent = "INSERT INTO event (description, event_type) VALUES ('$guiltyParty turned on abandons for $addr', 'FAILABILITY')";
  $curState = "ON";
} else {
  $insertEvent = "INSERT INTO event (description, event_type) VALUES ('$guiltyParty turned off abandons for $addr', 'FAILABILITY')";
  $curState = "OFF";
} 

$message="Abandons toggled for $addr -- now: $curState";
syslog(LOG_INFO, "application=failable-updater action=abandonToggle guiltyParty=$guiltyParty customMessage='$message'");

$dbconn = pg_connect("host=rwdb dbname=util user=postgres") or die("Error 2");
pg_query($dbconn, $query) or die("Error 3".pg_last_error());
pg_close($dbconn);

$evconn = pg_connect("host=rwdb dbname=events user=postgres") or die ("Error 5");
$result = pg_query($evconn, $insertEvent) or die ("Error 6");
pg_close($evconn);

if ($isEverything || $wholeSite) {
  
  //If the mail subject isn't empty, it must be for a site or ALL
  $mail_headers="From: abandon-toggle@jive.com" . "\r\n";
  $request_time=strftime('%Y-%m-%d %H:%M:%S');
  $mail_body="User: $guiltyParty\nDate: $request_time\nState: $curState";
  if ($isEverything) {
      $mail_subject="V4 Abandons turned $curState";
  } else {
	  $mail_subject="Abandons turned $curState for $addr";
  }

  mail($mail_to, $mail_subject, $mail_body, $mail_headers);
}
  
?>

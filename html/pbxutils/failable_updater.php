<?php
include('guiltyParty.php');
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
    if ($addr == "all" || $addr == "universal") {
      $query = "UPDATE sitestatus SET universal_failable = '$failable';";
    }
  } else { 
    die("Error 4");
  }
} else {
  die("Error 1");
}

$dbconn = pg_connect("host=rwdb dbname=util user=postgres") or die("Error 2");

pg_query($dbconn, $query) or die("Error 3".pg_last_error());

pg_close($dbconn);

//$evconn = pg_connect("host=rwdb dbname=events user=postgres") or die ("Error 5");
//
//if ($failable == 't') {
//  $insertEvent = "INSERT INTO event (description, event_type) VALUES ('$guiltyParty turned on abandons for $addr', 'FAILABILITY')";
//} else {
//  $insertEvent = "INSERT INTO event (description, event_type) VALUES ('$guiltyParty turned off abandons for $addr', 'FAILABILITY')";
//} 
//
//$result = pg_query($evconn, $insertEvent) or die ("Error 6");
//
//pg_close($evconn);
//
?>

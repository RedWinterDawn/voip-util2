<?php

if(isset($_POST['ip'])) {
  $ip = $_POST['ip'];
}

$pieces = explode("x", $ip);

$dc =  $pieces[1];
$pbx = $pieces[3];

if ($dc == '101') {
  $loc = 'ORDL-PBX';
  $host = '10.101.24.2';
}
if ($dc == '125') {
  $loc = 'ORD-MegaPBX';
  $host = '10.125.255.226';
}
if ($dc == '119') {
  $loc = 'LAX-MegaPBX';
  $host = '10.119.255.224';
}
if ($dc == '120') {
  $loc = 'NYC-MegaPBX';
  $host = '10.120.255.224';
}
if ($dc == '122') {
  $loc = 'atl-MegaPBX';
  $host = '10.122.255.224';
}

$msg = $host.' '.$loc.$pbx.'.sip 60 pbx-availability';
$cmd = 'perl /var/www/html/pbxutils/disablealert.pl '.$msg;
$output = exec($cmd);



?>

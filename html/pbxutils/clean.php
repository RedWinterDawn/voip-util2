<!doctype html>
<?php
$accesslevel = 4;
?>
<html>
<head>
<title>
Clean Script
</title>
<link rel='stylesheet' href='stylesheet.css'>
</head>
<body>
<?
$display = $_GET['display'];
echo "<a href='http://prodtools.devops.jive.com/pbx-availability.php?display=$display'>Back to PBX Availability</a>";
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

function flushOutput() {
    echo(str_repeat(' ', 2048));
    if (@ob_get_contents()) {
        @ob_end_flush();
    }   
    flush();
}

if (isset($_GET['server'])) {
	$server = $_GET['server'];
	echo "<br>Initiated clean on PBX $server<br>";
  if ($_SERVER['SERVER_ADDR'] == '10.101.8.1')
  {
	  exec('sudo salt -b 1 "'.$server.'" cmd.run "/opt/jive/asterisk-cleanup" 1>/dev/null 2>&1 &', $output, $exitcode);
  }else
  {
    include('checksession.php');
	  exec('sudo ssh root@10.101.8.1 \'salt -b 1 "'.$server.'" cmd.run "/opt/jive/asterisk-cleanup" 1>/dev/null 2>&1 &\'', $output, $exitcode);
  }
	echo "You can close this page. The server will automatically change status to standby when it is finished cleaning.";
	echo "<br><br>";
	if ($exitcode != 0) {
		foreach ($output as $out) {
			echo "$out <br>";
		}
	}
	echo "<br><br> $exitcode";
	die ();
}

?>

You are not welcome here.
</body>
</html>

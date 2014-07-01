<!doctype html>
<html>
<head>
<title>
Clean Script
</title>
<link rel='stylesheet' href='stylesheet.css'>
</head>
<body>
<a href='pbx-availability.php'>Back to PBX Availability</a>
<br>Please wait for more information.<br>
<?
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
	echo "<br>Welcome.<br>Cleaning PBX $server<br>";
	flushOutput();
	exec('sudo salt -b 1 "'.$server.'" cmd.run "/opt/jive/asterisk-cleanup" 2>&1', $output, $exitcode);
	echo "<br> Finished.";
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

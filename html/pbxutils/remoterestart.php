<?php

if(isset($_POST['dc'])) {
$dc = $_POST['dc'];
}

$cmd = "sudo salt '*pbx*.".$dc.".*' cmd.run 'service service-asterisk-remote restart'";
exec($cmd, $output);
$test
   	= implode("\n", $output);
echo $cmd;
echo $test;


?>

<?php

if(isset($_POST['dc'])) {
$dc = $_POST['dc'];
}

$cmd = "sudo salt 'enc1.".$dc.".*' cmd.run 'retry-old-recordings'";
exec($cmd, $output);
$test
   	= implode("\n", $output);
echo $cmd;
echo $test;


?>

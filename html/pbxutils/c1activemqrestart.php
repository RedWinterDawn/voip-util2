<?php

if(isset($_POST['dc'])) {
$dc = $_POST['dc'];
}

$cmd = "sudo salt 'mq1.c1.*' cmd.run 'service activemq restart'";
exec($cmd, $output);
$test = implode("\n", $output);
echo $cmd;
print_r($test);


?>

<?php

$cmd = "python2.6 pbxcallstats.py";
exec($cmd, $output);
$test = implode("\n", $output);
echo $test;

?>

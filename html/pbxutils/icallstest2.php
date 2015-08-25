<?php

if(isset($_POST['dc'])) {
        $ip = $_POST['dc'];
}

if ($ip == '101') {
  $cmd = "python2.6 pbxcallstats-chi.py";
}
elseif ($ip == '125') {
  $cmd = "python2.6 pbxcallstats-ord.py";
}
elseif ($ip == '119') {
  $cmd = "python2.6 pbxcallstats-lax.py";
}
elseif ($ip == '120') {
  $cmd = "python2.6 pbxcallstats-nyc.py";
}
elseif ($ip == '122') {
  $cmd = "python2.6 pbxcallstats-atl.py";
}
$cmd = "python2.6 pbxcallstats.py";
exec($cmd, $output);
$test = implode("\n", $output);
echo $test;

?>

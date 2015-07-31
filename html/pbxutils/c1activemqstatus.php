<?php
header('Content-Type: application/json');
$cmd = 'curl http://10.101.8.1/pbxutils/c1activemqstatus2.php';
exec($cmd, $output);
$test = implode("\n", $output);

if (preg_match("/ActiveMQ is running/", $test)) {
    $status = 'finished';
}
else {
    $status = 'pending';
}

echo json_encode(array(
  'jinstLog' => $test,
  'statusalert' => $status));
?>

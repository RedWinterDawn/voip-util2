<?php
header('Content-Type: application/json');
if(isset($_POST['dc'])) {
      $ip = $_POST['dc'];
        }
$cmd = 'curl --form "dc='.$ip.'" http://172.31.1.10/getlogs.php';
exec($cmd, $output);
$test = implode("\n", $output);

if (preg_match("/Jinst complete/", $test)) {
  $status = 'finished';
}
else {
  $status = 'pending';
}

echo json_encode(array(
  'jinstLog' => $test,
  'statusalert' => $status
));
?>

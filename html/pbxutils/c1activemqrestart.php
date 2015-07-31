<?php
header('Content-Type: application/json');
$cmd = 'curl http://10.101.8.1/pbxutils/c1activemqrestart2.php';
exec($cmd, $output);
$test = implode("\n", $output);

echo json_encode(array(
                            'jinstLog' => $test));
?>

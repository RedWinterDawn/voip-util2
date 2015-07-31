<?php
header('Content-Type: application/json');
if(isset($_POST['dc'])) {
      $ip = $_POST['dc'];
        }
$cmd = 'curl --form "dc='.$ip.'" http://172.31.1.10/mqrebuild.php';
exec($cmd, $output);
$test = implode("\n", $output);

echo json_encode(array(
                            'jinstLog' => $test));
?>

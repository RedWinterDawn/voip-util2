<?php
header('Content-Type: application/json');
if(isset($_POST['dc'])) {
      $dc = $_POST['dc'];
        }
$cmd = 'curl --form "dc='.$dc.'" http://10.101.8.1/pbxutils/remoterestart2.php';
exec($cmd, $output);
$test = implode("\n", $output);

echo json_encode(array(
                            'jinstLog' => $test));
?>

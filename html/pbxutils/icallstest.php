<?php
header('Content-Type: application/json');
if(isset($_POST['dc'])) {
        $ip = $_POST['dc'];
                }

$cmd = 'curl -d "dc='.$ip.'" http://10.101.8.1/pbxutils/icallstest2.php';
exec($cmd, $output);
$test = implode("\n", $output);
echo json_encode(array(
                           'jinstLog' => $output));
?>

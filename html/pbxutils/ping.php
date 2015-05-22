<?php
header('Content-Type: application/json');

  if (isset($_POST["remoteHost"]) && !empty($_POST["remoteHost"])) { //Checks if action value exists
        $host = $_POST["remoteHost"];
  }

  if (isset($_POST["dc"]) && !empty($_POST["dc"])) { //Checks if action value exists
             $dc = $_POST["dc"];
               }

/* Get the port for the WWW service. */

$service_port = 21928;

/* Get the IP address for the target host. */
if ($dc == 'chi') {
  $address = "172.20.9.5";
}
if ($dc == 'lax') {
    $address = "172.20.9.5";
}
if ($dc == 'nyc') {
    $address = "172.20.9.5";
}
if ($dc == 'atl') {
    $address = "172.20.9.5";
}
if ($dc == 'ord') {
    $address = "172.20.9.5";
}
if ($dc == 'dfw') {
      $address = "172.20.9.5";
}
$address = "172.20.9.5";
/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
} else {
}

$result = socket_connect($socket, $address, $service_port);
if ($result === false) {
    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
} else {
}

$in = "PING ". $host. " 1 10 1000 -1 64\r\n";
$out = '';

socket_write($socket, $in, strlen($in));

$buf = 'buffer';
$out = socket_recv($socket, $buf, 33, MSG_WAITALL);
socket_close($socket);
$phrase = str_replace(':', ' ', $buf);
$phrase = str_replace("\n", '', $phrase);
$pieces = explode(' ', $phrase);
$time = $pieces[4];
$ttl = $pieces[5];
if ($ttl == -1) {
  echo json_encode(array(
           'pingInfo' => '<div class="rowping-ip">'.$host. ' ('. $host. ')</div><div class="rowping-ms">-</div><div class="rowping-ttl">-</div><div class="rowping-lo    st pass">yes</div><div id="break"></div>',
                 'status' => 'fail'
               ));
}
else {
echo json_encode(array(
      'pingInfo' => '<div class="rowping-ip">'.$host. ' ('. $host. ')</div><div class="rowping-ms">'.$time.'</div><div class="rowping-ttl">'.$ttl.'</div><div class="rowping-lost pass">no</div><div id="break"></div>',
          'status' => 'pass'
        ));
}

?>

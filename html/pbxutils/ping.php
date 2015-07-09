<?php
header('Content-Type: application/json');
  if (isset($_POST['remoteHost']) && !empty($_POST['remoteHost'])) { //Checks if action value exists
        $host = $_POST['remoteHost'];
  }

  if (isset($_POST['dc']) && !empty($_POST['dc'])) { //Checks if action value exists
             $dc = $_POST['dc'];
               }

  if(!isset($_POST['hop'])) {
    $trace = false;
    $hop = 64;
  }
  else {
    $hop = $_POST['hop'];
    $trace = true;
  }
  if(isset($_POST['round'])) {
    $round = $_POST['round'];
  }
/* Get the port for the WWW service. */
$service_port = 21928;
/* Get the IP address for the target host. */
if ($dc === 'chi') {
  $address = "172.20.9.5";
}
if ($dc == 'lax') {
    $address = "172.19.9.5";
}
if ($dc == 'nyc') {
    $address = "172.20.9.5";
}
if ($dc == 'atl') {
    $address = "172.22.9.5";
}
if ($dc == 'ord') {
    $address = "172.25.9.107";
}
if ($dc == 'dfw') {
      $address = "172.18.9.5";
}
if ($dc == 'geg') {
      $address = "172.23.9.5";
}
if ($dc == 'pvu') {
      $address = "172.17.9.5";
}
$dc = strtoupper($dc); /* Capitalize the DC */
$ttl2 = true;
/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
  $ttl2 = false;
} else {
}

$result = socket_connect($socket, $address, $service_port);
if ($result === false) {
  $ttl2 = false;
} else {
}
socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>1,"usec"=>0));

$in = "PING ". $host. " 1 10 1000 -1 ".$hop."\r\n";
$out = '';

socket_write($socket, $in, strlen($in));

$buf = 'buffer';
$out = socket_recv($socket, $buf, 70, MSG_WAITALL);
socket_close($socket);
$phrase = str_replace(':', ' ', $buf);
$phrase = str_replace("\n", '', $phrase);
$phrase = str_replace('/', ' ', $phrase);
$pieces = explode(' ', $phrase);
$dnslookup = 'host '.$host;
$dnslookup = exec($dnslookup);
$name = explode(' ', $dnslookup);
$name = $name[4];
if ($name == '3(NXDOMAIN)') {
  $name = '-';
  }

// IF statement to process Traceroute requests:

if ($trace) {

// If statement to filter between final hop and previous.

  if ($pieces[4] == 'E') {
    $dest = $pieces[5];
    $time = $pieces[8];
    $ttl = $pieces[9];
    $dnslookup = 'host '.$dest;
     $dnslookup = exec($dnslookup);
     $name = explode(' ', $dnslookup);
      $name = $name[4];
      if ($name == '3(NXDOMAIN)') {
              $name = '-';
               }
      if ($time == "") {
              $time = "*";
                  }
    if ($round == 1) {
    echo json_encode(array(
            'pingInfo' => '<div class="tracerow-source">' .$address.' ('.$dc.')</div><div class="tracerow-dest">'.$dest.' ('.$name.')</div><div class="tracerow-ms1">'.$time.'</div>',
                      'status' => 'pass'
                              ));
    }
  elseif ($round ==2) {

    echo json_encode(array(
              'pingInfo' => '<div class="tracerow-ms2">'.$time.'</div>',
                        'status' => 'pass'
                                ));
    }
    
    elseif ($round ==3) {

          echo json_encode(array(
                          'pingInfo' => '<div class="tracerow-ms3">'.$time.'</div><div id="break"></div>',
                                                  'status' => 'pass'
                                                                                  ));
    }
  }
  else {
    $time = $pieces[4];
    if ($time == "") {
      $host = "-";
      $name = "-";
      $time = "*";
    }
    if ($round == 1) {
    echo json_encode(array(
                  'pingInfo' => '<div class="tracerow-source">' .$address.' ('.$dc.')</div><div class="tracerow-dest">'.$host.' ('.$name.')</div><div class="tracerow-ms1">'.$time.'</div>',
                                        'status' => $host
                                                                      ));
  }
    elseif ($round ==2) {

          echo json_encode(array(
                          'pingInfo' => '<div class="tracerow-ms2">'.$time.'</div>',
                                                  'status' => $host
                                                                                  ));
              }

        elseif ($round ==3) {

                    echo json_encode(array(
                                                'pingInfo' => '<div class="tracerow-ms3">'.$time.'</div><div id="break"></div>',
                                                                                                  'status' => $host
                                                                                                                                                                                    ));
                        }
  }    

}

else {

$time = $pieces[4];
$ttl = $pieces[5];
if (!$ttl2) {
  echo json_encode(array(
               'pingInfo' => '<div class="rowping-source">' .$address.' ('.$dc.')</div><div class="rowping-ip">'.$host. ' ('. $host. ')</div><div class="rowping-msfail">Could not connect to ping server!</div><div id="break"></div>',
                                'status' => 'fail'
                                               ));
}
elseif ($ttl == "") {
  echo json_encode(array(
           'pingInfo' => '<div class="rowping-source">' .$address.' ('.$dc.')</div><div class="rowping-ip">'.$host. ' ('. $host. ')</div><div class="rowping-ms">-</div><div class="rowping-ttl">-</div><div class="rowping-lost fail">yes</div><div id="break"></div>',
                 'status' => 'fail'
               ));
}

else {
echo json_encode(array(
      'pingInfo' => '<div class="rowping-source">' .$address.' ('.$dc.')</div><div class="rowping-ip">'.$host. ' ('. $host. ')</div><div class="rowping-ms">'.$time.'</div><div class="rowping-ttl">'.$ttl.'</div><div class="rowping-lost pass">no</div><div id="break"></div>',
          'status' => 'pass'
        ));
}
}
?>

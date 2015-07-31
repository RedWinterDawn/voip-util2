<?
function flushOutput()
{
  echo(str_repeat(' ', 256));
  if (@ob_get_contents())
  {
    @ob_end_flush();
  }
  flush();
}

function printArray($array)
{
  foreach ($array as $value)
  {
    echo $value . "<br>";
  }
}

$carrierArray = array(
                      array('bandwidth', 'text/csv', 'bandwidth-ratedeck.csv', 'bandwidth-domestic-upload.py', '1', 'domestic'),
                      array('iristel', 'text/csv', 'iristel-ratedeck.csv', 'iristel-upload.py', '2', 'domestic'),
                      array('onvoy', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'onvoy-ratedeck.xlsx', 'onvoy-domestic-upload.py', '4', 'domestic'),
                      array('voip', 'text/csv', 'voip-ratedeck.csv', 'voip-domestic-upload.py', '6', 'domestic'),
                      array('thinq', 'application/zip', 'thinq-ratedeck.zip', 'thinq-domestic-upload.py', '5', 'domestic'),
                      array('level3', 'text/csv', 'level3-ratedeck.csv', 'level3-domestic-upload.py', '3', 'domestic'),
                      array('level3ext', 'text/csv', 'level3-ext-ratedeck.csv', 'level3-domestic-upload.py', '3', 'domestic'),
                      array('bandwidthint', 'text/csv', 'bandwidth-int-ratedeck.csv', 'bandwidth-international-upload.py', '1', 'international'),
                      array('level3int', 'text/csv', 'level3-int-ratedeck.csv', 'level3-international-upload.py', '3', 'international'),
                      array('onvoyint', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'onvoy-int-ratedeck.xlsx', 'onvoy-international-upload.py', '4', 'international'),
                      array('thinqint', 'application/zip', 'thinq-int-ratedeck.zip', 'thinq-international-upload.py', '5', 'international'),
                    );

$target_path = "/var/www/uploads/";

$dbconn = pg_connect("host=cdr dbname=lcr user=postgres ") or die ('Could not connect: ' . pg_last_error());

foreach ($carrierArray as $carrier)
{
  if(isset($_FILES[$carrier[0]]) and $_FILES[$carrier[0]]['type'] != $carrier[1] and $_FILES[$carrier[0]]['size'] > 0)
  {
    echo '<br>'.$carrier[0].' Rate Deck must be a '.$carrier[1].' file not a: ' . $_FILES[$carrier[0]]['type'];
    $valid = 'False';
  }
  if(isset($_FILES[$carrier[0]]) and $_FILES[$carrier[0]]['size'] > 0 and $valid != 'False')
  {
    $ratePath = $target_path . $carrier[2];
    if(move_uploaded_file($_FILES[$carrier[0]]['tmp_name'], $ratePath))
    {
      $insert = "INSERT INTO current_ratedeck (carrier_id, region, file) VALUES ('".$carrier[4]."', '".$carrier[5]."', '".$_FILES[$carrier[0]]['name']."')";
      pg_query($insert) or die ('current_ratedeck query failed: ' . pg_last_error());
      $output = array();
      exec("/var/www/".$carrier[3], $output);
      printArray($output);
      $valid = 'True';
    }else
    {
      echo "<br>Failed to upload ".$carrier[0]." ratedeck<br>";
    }
  }
  flushOutput();
}

pg_close($dbconn);

/*
if(isset($onvoy) and $onvoy == 'True')
{
  flushOutput();
  $command = '/var/www/onvoy-domestic-upload.py /var/www/uploads/onvoy-ratedeck.xlsx';
  $proc = popen($command, 'r');
  echo '<pre>';
  while (!feof($proc))
  {
    echo fread($proc, 4096);
    flushOutput();
  }
  echo '</pre>';
  echo '<br>updated Onvoy ratedeck'; 
}
if(isset($thinq) and $thinq == 'True')
{
  flushOutput();
  $command = '/var/www/thinq-full-upload.py';
  $proc = popen($command, 'r');
  echo '<pre>';
  while (!feof($proc))
  {
    echo fread($proc, 4096);
    flush();
  }
  echo '</pre>';
  echo '<br>updated ThinQ ratedeck'; 
}
if(isset($voip) and $voip == 'True')
{
  $command = '/var/www/voip-standard-upload.py /var/www/uploads/voip-ratedeck.csv';
  exec($command, $voipOutput, $exitCode);
  if ($exitcode != 0)
  {
    echo 'Failed to run: ' . $command;
    print_r($voipOutput);
  }else 
  {
    foreach ($voipOutput as $row)
    {
      echo '<br>'.$row;
    }
    echo '<br>updated VoIp ratedeck';
  } 
}*/
?>



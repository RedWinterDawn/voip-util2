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

$target_path = "/var/www/uploads/";

## check file types
if(isset($_FILES['bandwidth']) and $_FILES['bandwidth']['type'] != 'text/csv' and $_FILES['bandwidth']['size'] > 0)
{
  echo '<br>Bandwidth Rate Deck must be a csv file not a: ' . $_FILES['bandwidth']['type'];  
  $bandwidth = 'False';
}
if(isset($_FILES['iristel']) and $_FILES['iristel']['type'] != 'text/csv' and $_FILES['iristel']['size'] > 0)
{
  echo '<br>Iristel Rate Deck must be a csv file not a: ' . $_FILES['iristel']['type'];  
  $iristel = 'False';
}
if(isset($_FILES['onvoy']) and $_FILES['onvoy']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' and $_FILES['onvoy']['size'] > 0)
{
  echo '<br>Onvoy Rate Deck must be a xlsx file not a: ' . $_FILES['onvoy']['type'];  
  $onvoy = 'False';
}
if(isset($_FILES['voip']) and $_FILES['voip']['type'] != 'text/csv' and $_FILES['voip']['size'] > 0)
{
  echo '<br>VoIp Rate Deck must be a csv file not a: ' . $_FILES['voip']['type'];  
  $voip = 'False';
}
if(isset($_FILES['thinq']) and $_FILES['thinq']['type'] != 'application/zip' and $_FILES['thinq']['size'] > 0)
{
  echo 'ThinQ Rate Deck must be a zip file not a: ' . $_FILES['thinq']['type'];  
  $thinq = 'False';
}
if(isset($_FILES['level3']) and $_FILES['level3']['type'] != 'text/csv' and $_FILES['level3']['size'] > 0)
{
  echo '<br>Level3 Rate Deck must be a csv file not a: ' . $_FILES['level3']['type'];  
  $level3 = 'False';
}
if(isset($_FILES['level3ext']) and $_FILES['level3ext']['type'] != 'text/csv' and $_FILES['level3ext']['size'] > 0)
{
  echo '<br>Level3 Extended Rate Deck must be a csv file not a: ' . $_FILES['level3ext']['type'];  
  $level3ext = 'False';
}

## check international file types
if(isset($_FILES['level3int']) and $_FILES['level3int']['type'] != 'text/csv' and $_FILES['level3int']['size'] > 0)
{
  echo '<br>Level3 International Rate Deck must be a csv file not a: ' . $_FILES['level3int']['type'];  
  $level3int = 'False';
}
if(isset($_FILES['bandwidthint']) and $_FILES['bandwidthint']['type'] != 'text/csv' and $_FILES['bandwidthint']['size'] > 0)
{
  echo '<br>Bandwidth International Rate Deck must be a csv file not a: ' . $_FILES['bandwidthint']['type'];  
  $bandwidthint = 'False';
}
if(isset($_FILES['onvoyint']) and $_FILES['onvoyint']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' and $_FILES['onvoyint']['size'] > 0)
{
  echo '<br>Onvoy International Rate Deck must be a xlsx file not a: ' . $_FILES['onvoyint']['type'];  
  $onvoyint = 'False';
}
if(isset($_FILES['voipint']) and $_FILES['voipint']['type'] != 'text/csv' and $_FILES['voipint']['size'] > 0)
{
  echo '<br>VoIp International Rate Deck must be a csv file not a: ' . $_FILES['voipint']['type'];  
  $voipint = 'False';
}
if(isset($_FILES['thinqint']) and $_FILES['thinqint']['type'] != 'application/zip' and $_FILES['thinqint']['size'] > 0)
{
  echo 'ThinQ International Rate Deck must be a zip file not a: ' . $_FILES['thinqint']['type'];  
  $thinqint = 'False';
}

##move uploaded files
$dbconn = pg_connect("host=cdr dbname=lcr user=postgres ") or die ('Could not connect: ' . pg_last_error());



if(isset($_FILES['bandwidth']) and $_FILES['bandwidth']['size'] > 0 and $bandwidth != 'False')
{
  $bandwidthPath = $target_path . "bandwidth-ratedeck.csv";
  if(move_uploaded_file($_FILES['bandwidth']['tmp_name'], $bandwidthPath)) 
  {
    pg_query("INSERT INTO current_ratedeck (carrier_id, region, file) VALUES ('1', 'domestic', '".$_FILES['bandwidth']['name']."')") or die ('current_ratedeck query failed: ' . pg_last_error());
    $output = array();
    exec("/var/www/bandwidth-domestic-upload2.py", $output);
    printArray($output);
    $bandwidth = 'True';
    echo "<br>Bandwith ratedeck has been uploaded<br>";
  }else
  {
    echo "<br>Failed to upload Bandwith ratedeck<br>";
  }
}
if(isset($_FILES['iristel']) and $_FILES['iristel']['size'] > 0 and $iristel != 'False')
{
  $iristelPath = $target_path . "iristel-ratedeck.csv";
  if(move_uploaded_file($_FILES['iristel']['tmp_name'], $iristelPath)) 
  {
    echo "<br>Iristel ratedeck has been uploaded<br>";
    $Iristel = 'True';
  }else
  {
    echo "<br>Failed to upload Iristel ratedeck<br>";
  }
}
if(isset($_FILES['onvoy']) and $_FILES['onvoy']['size'] > 0 and $onvoy != 'False')
{
  $onvoyPath = $target_path . "onvoy-ratedeck.xlsx";
  if(move_uploaded_file($_FILES['onvoy']['tmp_name'], $onvoyPath)) 
  {
    echo "<br>Onvoy ratedeck has been uploaded<br>";
    $onvoy = 'True';
  }else
  {
    echo "<br>Failed to upload Onvoy ratedeck<br>";
  }
}
if(isset($_FILES['voip']) and $_FILES['voip']['size'] > 0 and $voip != 'False')
{
  $voipPath = $target_path . "voip-ratedeck.csv";
  if(move_uploaded_file($_FILES['voip']['tmp_name'], $voipPath)) 
  {
    echo "<br>VoIp ratedeck has been uploaded<br>";
    $voip = 'True';
  }else
  {
    echo "<br>Failed to upload VoIp ratedeck<br>";
  }
}
if(isset($_FILES['thinq']) and $_FILES['thinq']['size'] > 0 and $thinq != 'False')
{
  $thinqPath = $target_path . "thinq-ratedeck.zip";
  if(move_uploaded_file($_FILES['thinq']['tmp_name'], $thinqPath)) 
  {
    echo "<br>ThinQ ratedeck has been uploaded<br>";
    $thinq = 'True';
  }else
  {
    echo "<br>Failed to upload ThinQ ratedeck<br>";
  }
}
if(isset($_FILES['level3']) and $_FILES['level3']['size'] > 0 and $level3 != 'False')
{
  $level3Path = $target_path . "level3-ratedeck.csv";
  if(move_uploaded_file($_FILES['level3']['tmp_name'], $level3Path)) 
  {
    echo "<br>Level3 ratedeck has been uploaded<br>";
    $level3 = 'True';
  }else
  {
    echo "<br>Failed to upload Level3 ratedeck<br>";
  }
}
if(isset($_FILES['level3ext']) and $_FILES['level3ext']['size'] > 0 and $level3ext != 'False')
{
  $level3Path = $target_path . "level3-ext-ratedeck.csv";
  if(move_uploaded_file($_FILES['level3ext']['tmp_name'], $level3Path)) 
  {
    echo "<br>Level3 Extended ratedeck has been uploaded<br>";
    $level3ext = 'True';
  }else
  {
    echo "<br>Failed to upload Level3 Extended Ratedeck<br>";
  }
}

##move uploaded International files
if(isset($_FILES['level3int']) and $_FILES['level3int']['size'] > 0 and $level3int != 'False')
{
  $level3intPath = $target_path . "level3-int-ratedeck.csv";
  if(move_uploaded_file($_FILES['level3int']['tmp_name'], $level3intPath)) 
  {
    echo "<br>Level3 International ratedeck has been uploaded<br>";
    $level3int = 'True';
  }else
  {
    echo "<br>Failed to upload Level3 International ratedeck<br>";
  }
}
if(isset($_FILES['bandwidthint']) and $_FILES['bandwidthint']['size'] > 0 and $bandwidthint != 'False')
{
  $bandwidthintPath = $target_path . "bandwidth-int-ratedeck.csv";
  if(move_uploaded_file($_FILES['bandwidthint']['tmp_name'], $bandwidthintPath)) 
  {
    echo "<br>Bandwith International ratedeck has been uploaded<br>";
    $bandwidthint = 'True';
  }else
  {
    echo "<br>Failed to upload Bandwith International ratedeck<br>";
  }
}
if(isset($_FILES['onvoyint']) and $_FILES['onvoyint']['size'] > 0 and $onvoyint != 'False')
{
  $onvoyintPath = $target_path . "onvoy-int-ratedeck.xlsx";
  if(move_uploaded_file($_FILES['onvoyint']['tmp_name'], $onvoyintPath)) 
  {
    echo "<br>Onvoy International ratedeck has been uploaded<br>";
    $onvoyint = 'True';
  }else
  {
    echo "<br>Failed to upload Onvoy International ratedeck<br>";
  }
}
if(isset($_FILES['voipint']) and $_FILES['voipint']['size'] > 0 and $voipint != 'False')
{
  $voipintPath = $target_path . "voip-int-ratedeck.csv";
  if(move_uploaded_file($_FILES['voipint']['tmp_name'], $voipintPath)) 
  {
    echo "<br>VoIp International ratedeck has been uploaded<br>";
    $voipint = 'True';
  }else
  {
    echo "<br>Failed to upload VoIp International ratedeck<br>";
  }
}
if(isset($_FILES['thinqint']) and $_FILES['thinqint']['size'] > 0 and $thinqint != 'False')
{
  $thinqintPath = $target_path . "thinq-int-ratedeck.zip";
  if(move_uploaded_file($_FILES['thinqint']['tmp_name'], $thinqintPath)) 
  {
    echo "<br>ThinQ International ratedeck has been uploaded<br>";
    $thinqint = 'True';
  }else
  {
    echo "<br>Failed to upload ThinQ International ratedeck<br>";
  }
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



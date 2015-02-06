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

$target_path = "/var/www/uploads/";

## check file types
if(isset($_FILES['bandwidth']) and $_FILES['bandwidth']['type'] != 'text/csv' and $_FILES['bandwidth']['size'] > 0)
{
  echo '<br>Bandwidth Rate Deck must be a csv file not a: ' . $_FILES['bandwidth']['type'];  
  $bandwidth = 'False';
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

## check international file types
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
if(isset($_FILES['bandwidth']) and $_FILES['bandwidth']['size'] > 0 and $bandwidth != 'False')
{
  $bandwidthPath = $target_path . "bandwidth-ratedeck.csv";
  if(move_uploaded_file($_FILES['bandwidth']['tmp_name'], $bandwidthPath)) 
  {
    echo "<br>Bandwith ratedeck has been uploaded<br>";
    $bandwidth = 'True';
  }else
  {
    echo "<br>Failed to upload Bandwith ratedeck<br>";
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

##move uploaded International files
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



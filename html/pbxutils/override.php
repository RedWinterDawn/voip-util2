<?php
$accesslevel = 4;
include('checksession.php');

echo "<html><head><title>OverrideLCR</title>
      <link rel='stylesheet' href='stylesheet.css'>";
include ('menu.html');

if (isset($_REQUEST["action"]))
{
    $action = $_REQUEST["action"];
}

if ($action == 'submit')
{
  echo "process submit";
  if (isset($_REQUEST["prefix"]) AND !preg_match('/[^0-9]/i', $_REQUEST["prefix"]))
  {
    $prefix = $_REQUEST["prefix"];
  }else 
  {
    echo "Please enter a valid prefix";
    die;
  }
  if (isset($_REQUEST["reason"]) AND !preg_match('/[^a-z\-0-9]/i', $_REQUEST["reason"]))
  {
    $reason = $_REQUEST["reason"];
  }else
  {
    echo "<p class='red'> Invalid Input! <br/> Use numbers, letters, and dashes only in reason field.</p>";
    die;
  }

  $keys = array();
  $v4keys = array();
  $x = 0;
  while ($x < 6)
  {
    if (isset($_REQUEST["priority".$x]) AND $_REQUEST["priority".$x] != '')
    {
       array_push($keys, $_REQUEST["priority".$x]);
    }
    $x++;
  }
  foreach ($keys as $key)
  {
    if ($key == "ONVOY")
    {
      $key = "360_NETWORKS";
    }elseif ($key == "THINQ")
    {
      $key = "SIPROUTES:PRIME";
    }elseif ($key =="VOIP_INNOVATIONS")
    {
      $key = "VOIP_INNOVATIONS:LCR";
    }
    array_push($v4keys, $key);
  }

  $keys = array('results' => $keys);
  $v4keys = array('results' => $v4keys);
  $keys = json_encode($keys);
  $v4keys = json_encode($v4keys);
  $connections = array('10.104.0.220', '10.117.253.121', '10.118.252.190', '10.119.252.43', '10.120.253.226', '10.122.252.38', '10.123.253.89', '10.125.252.170');

  foreach ($connections as $connection)
  {
      $lcrConn = pg_connect("host='".$connection."' dbname=lcr user=postgres") or die ('Could not connect to '.$connection.' "lcr" database: ' . pg_last_error());
      $insert = "INSERT INTO override (prefix, v4codes, codes, reason) VALUES ('".$prefix."', '".$v4keys."', '".$keys."', '".$reason."')";
      pg_query($lcrConn, $insert) or die ('Failed to submit override on '.$connection.': ' . pg_last_error());
      pg_close($lcrConn);
  }
}


echo '<h2>Override LCR</h2>  
      <p><h2>Current Overrides</h2>';

$lcrConn = pg_connect("host=10.104.0.220 dbname=lcr user=postgres") or die ('Could not connect to "lcr" database: ' . pg_last_error());
$overrides = pg_fetch_all(pg_query($lcrConn, "SELECT * FROM override order by added asc")) or die ('Failed to get overrides: ' . pg_last_error());
echo " <table border='1'>
          <tr><th>Prefix</th><th>Keys</th><th>Added</th><th>Reason</th></tr>";
foreach ($overrides as $override)
{
  echo " <tr><td>".$override['prefix']."</td><td>".$override['codes']."</td><td>".$override['added']."</td><td>".$override['reason']."</td></tr>";
}
echo "</table>";


echo "<h2>Add Override</h2>
      <form action='' method='POST'>
      Enter prefix with country code <br>
      Prefix
      <input type = 'text' name = 'prefix' placeholder = 'e.g. 1999765'/>
      <br><br>
      Select carriers for override
      ";
$x = 0;
while ($x < 6)
{
  echo "
      <br>Priority ".$x."
      <select name = 'priority".$x."'>
      <option value = ''> Select Carrier </option>
      <option value = 'BANDWIDTH'> Bandwidth </option>
      <option value = 'IRISTEL'> Iristel </option>
      <option value = 'LEVEL3'> Level3 </option>
      <option value = 'ONVOY'> Onvoy </option>
      <option value = 'THINQ'> Thinq </option>
      <option value = 'VOIP_INNOVATIONS'> VoIp Innovations </option>
      </select>
      ";
  $x++;
}
echo "
      <br><br>
      Enter reason for override<br>
      Reason
      <input type = 'text' name = 'reason' />
      <br><br>
      <input type='hidden' name='action' value='submit' />
      <input type='submit' value='Submit' />
      </form>
      ";
pg_close($lcrConn);


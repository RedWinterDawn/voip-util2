<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');
include('guiltyParty.php');

if ($_SERVER['SERVER_ADDR'] == '10.101.8.1')
{
    header('Location: http://prodtools.devops.jive.com/ebc-status.php');
}

//update db from global.yml
function updateDB()
{
    $update = 1;
    $tries = 0;
    while ($update == 1)
    {
        exec('sudo /var/www/ebc_status.py', $output, $exitcode);
        if ($exitcode == 0)
        {
            $update = 0;
        }
        if ($tries > 99)
        {
            die('Failed to updateDB');
        }
        $tries = $tries + 1;
    }
}

updateDB();

//get args
if (isset($_REQUEST["action"]))
{
  $action = $_REQUEST["action"];
}else
{
  $action = "info";
}

//update a site
if ($action == 'site')
{
  //Get values
    if(isset($_REQUEST["site"]))
    {
      $site = $_REQUEST["site"];
    }else
    {
      die('missing site');
    }
    if(isset($_REQUEST["state"]))
    {
      $state = $_REQUEST["state"];
    }else
    {
      die('missing state');
    }
  //Update Site
    $command = 'sudo  /var/www/ebc_status.py '.$state.' '.$site;
    exec($command, $siteoutput, $exitcode);
    if ($exitcode != 0 or $siteoutput[41] != 'OK')
    {
      echo 'Exit code: '.$exitcode.'<br>';
      print_r($siteoutput);
      echo'<br><br>failed to update: '.$site.'<br>';
    }else
    {
      echo 'Succesfully updated: '.$site.'<br><pre>';
      print_r($siteoutput);
      echo '</pre>';
      
     //Record event in the event database
      if ($state =='a') {$state ='Enabled SITE';} else {$state='Disabled SITE';}
      $eventDb = pg_connect("host=rwdb dbname=events user=postgres") or die('Could not connect: '. pg_last_error());
      $description = $guiltyParty." ".$state." ".$site;
      $eventID = pg_fetch_row(pg_query($eventDb, "INSERT INTO event(id, description, event_type) VALUES(DEFAULT, '" . $description . "', 'EBC');"));
      pg_close($eventDb); //Close the event DB connection
    }
}  

//update a host
if ($action =='host')
{
  //Get values
    if(isset($_REQUEST["host"]))
    {
      $host = $_REQUEST["host"];
    }else
    {
      die('missing site');
    }
    if(isset($_REQUEST["state"]))
    {
      $state = $_REQUEST["state"];
    }else
    {
      die('missing state');
    }
    if(isset($_REQUEST["arecord"]))
    {
      $arecord = $_REQUEST["arecord"];
    }else
    {
      die('missing arecord');
    }
  //Update host
    $command = "sudo  /var/www/ebc_status.py ".$state." '".$host."' '".$arecord."'";
    exec($command, $siteoutput, $exitcode);
    if ($exitcode != 0 or $siteoutput[41] != 'OK')
    {
      echo 'Exit code: '.$exitcode.'<br>';
      print_r($siteoutput);
      print'<br><br>failed to update: '.$host.'<br>';
    }else
    {
      echo 'Succesfully updated host: '.$host.'<br><pre>';
      print_r($siteoutput);
      echo '</pre>';

      //Record event in the event database
      if ($state =='a') {$state ='Enabled EBC';} else {$state='Disabled EBC';}
      $eventDb = pg_connect("host=rwdb dbname=events user=postgres") or die('Could not connect: '. pg_last_error());
      $description = $guiltyParty." ".$state." ".$host." ".$arecord;
      $eventID = pg_fetch_row(pg_query($eventDb, "INSERT INTO event(id, description, event_type) VALUES(DEFAULT, '" . $description . "', 'EBC');"));
      pg_close($eventDb); //Close the event DB connection
    }
}

updateDB();

//Connecting to DB
$dbconn = pg_connect("host=10.125.252.170 dbname=ebc user=ebc password=md5b5f5ba1a423792b526f799ae4eb3d59e ") or die('Could not connect: ' . pg_last_error());

//Get active sites from db
$query = "SELECT * from site_status ORDER BY id ASC";
$result = pg_query($query) or die ("unable to get site status: " . pg_last_error());

echo "<table border=1>\n";
while ($row = pg_fetch_array($result, null, PGSQL_ASSOC))
{
    if ($row['active'] == 'f')
    {
        echo "<tr><th>" . $row['site'] . "</th><th colspan=2>Non-Active</th><th><a href=\"ebc-status.php?action=site&site=".$row['site']."&state=a\">Enable Site</a></th></tr>\n";
    }else
    {
      echo "<tr><th>" . $row['site'] . "</th><th colspan=2>Active</th><th><a href=\"ebc-status.php?action=site&site=".$row['site']."&state=d\">Disable Site</a></th></tr>\n";
        $query2 = "SELECT host, arecord, status FROM srv WHERE site = '" . $row['site'] . "' ORDER BY host";
        $result2 = pg_query($query2) or die ("unable to get srv records: " . pg_last_error());
        while ($row2 = pg_fetch_array($result2, null, PGSQL_ASSOC))
        {
            if ($row2['status'] == 't')
            {
                echo "<tr><td>" . $row2['host'] . "</td><td>" . $row2['arecord'] . "</td><td>Active</td><td><a href=\"ebc-status.php?action=host&host=".$row2['host']."&arecord=".$row2['arecord']."&state=d\">Disable EBC</a></td></tr>\n";
            }else
            {
                echo "<tr><td>" . $row2['host'] . "</td><td>" . $row2['arecord'] . "</td><td>Non-Active</td><td><a href=\"ebc-status.php?action=host&host=".$row2['host']."&arecord=".$row2['arecord']."&state=a\">Enable EBC</td></tr>\n";
            }
        }
    }
}
echo "</table>";      

//free result
pg_free_result($result);

//close connection
pg_close($dbconn);


?>

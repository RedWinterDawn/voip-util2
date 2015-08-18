<?php
$accesslevel = 3;
include('checksession.php');
?>
<?php

echo '<html><head><title>WA/AGI Versions</title>
    <style type="text/css">
    #pretty {vertical-align: bottom;}
    #</style><link rel="stylesheet" href="stylesheet.css"></head>';

//"Header"
echo '<body onload="init()"><div id="head" class="head">';
include('menu.html');
echo '<h2>WA/AGI Versions';
echo '</h2>';

## active agi servers
exec('sudo ssh root@10.101.8.1 \'grep -F "add" agi.srv.txt | cut -d " " -f 10 | cut -d "." -f 1 | cut -d "i" -f 2\'', $chiagi, $exitcode);
exec('sudo ssh root@10.101.8.1 \'grep -F "add" agi.c25.srv.txt | cut -d " " -f 10 | cut -d "." -f 1 | cut -d "i" -f 2\'', $ordagi, $exitcode);
exec('sudo ssh root@10.101.8.1 \'salt "net.c19*" cmd.run "grep -F add agi.srv.txt | cut -d\  -f 10 | cut -d \'.\' -f 1"\'', $laxagi, $exitcode);
exec('sudo ssh root@10.101.8.1 \'salt "net.c20*" cmd.run "grep -F add agi.srv.txt | cut -d\  -f 10 | cut -d \'.\' -f 1"\'', $nycagi, $exitcode);
exec('sudo ssh root@10.101.8.1 \'salt "net.c22*" cmd.run "grep -F add agi.srv.txt | cut -d\  -f 10 | cut -d \'.\' -f 1"\'', $atlagi, $exitcode);

## agi versions
exec('sudo ssh root@10.101.8.1 \'salt "agi*" cmd.run "tail -n 1 /root/jive.deploy.log | cut -d \'-\' -f8"\'', $output1, $exitcode);
$agiVersions = array();
for ($i = 0; $i <47; $i++)
{
  $j = $i + 1;
  $agiVersions[$output1[$i]]=$output1[$j];
  $i = $j;
}

## active wa servers
exec('sudo ssh root@10.101.8.1 \'salt "wb*" cmd.run "grep -A 2 mobility /etc/httpd/conf/workers.properties | grep -o wa[0-9]; grep -A 2 partner-portal /etc/httpd/conf/workers.properties | grep -o wa[0-9]"\'', $output2, $exitcode);

## wa versions
exec('sudo ssh root@10.101.8.1 \'salt "wa*" cmd.run "tail -n 1 /root/jive.deploy.log | cut -d \'/\' -f12"\'', $waVersions, $exitcode);

$wa = array();
for ($i = 0; $i <47; $i++)
{
    $j = $i + 1;
    $wa[$waVersions[$i]]=$waVersions[$j];
    $i = $j;
}

echo 'WA Versions<br>';
echo '<table border="1"><tr><th></th><th>Chicago</th></tr>';
$curVersion = 0;
$x = 1;
$curVersion = 1;
$oldVersion = 1;
while ($x < 6)
{
  $color = '';
  $waNode = 'wa'.$x;
  $waNodeSite = $waNode.'.c1.jiveip.net:';
  $y = substr($output2[2], 4, 10); 
  $z = substr($output2[1], 4, 10); 
  if ($y == $waNode || $z ==$waNode)
  {
    if ($curVersion == 1)
    {
      $curVersion = $wa[$waNodeSite];
    }
    if ($curVersion != $wa[$waNodeSite])
    {
      $color = 'red';
    }else
    {
      $color = 'green';
    }   
  }else
  {
    if ($oldVersion == 1)
    {
      $oldVersion = $wa[$waNodeSite];
    }
    if ($oldVersion != $wa[$waNodeSite])
    {
      $color = 'yellow';
    }    
  }
  echo "<tr><th>".$waNode."</th><td class='".$color."'>";
  echo $wa[$waNodeSite].'</td></tr>';
  $x = $x + 1;
  if ($x == 3)
  {
    $x = 4;
  }
}
echo'</table>';

echo '<br>AGI Versions<br><table border="1"><tr><th></th><th>Chicago Legacy</th><th>Chicago (ORD)</th><th>Los Angeles</th><th>New York</th><th>Atlanta</th></tr>';
$x = 1;
while ($x < 7)
{
  $color = '';
  $agiNode = 'agi' .$x;
  $agiNodeSite = $agiNode .'.c1.jiveip.net:';
  if ($x == $chiagi[1] || $x == $chiagi[2] || $x == $chiagi[0])
  {
    $color = 'green';
    if ($agiVersions[$agiNodeSite] != $curVersion)
    {
     $color = 'red';
    }
  }else
  {
    if ($agiVersions[$agiNodeSite] != $oldVersion)
    {
       $color = 'yellow';
    }
  }   


  echo "<tr><th>".$agiNode."</th><td class='".$color."'>";
  echo $agiVersions[$agiNodeSite].'</td>';

  if($x <5)
  {
    $color = '';
    $agiNodeSite = $agiNode .'.c25.jiveip.net:';
    $y = substr($ordagi[2], 4, 10);
    $z = substr($ordagi[1], 4, 10);
    if ($agiNode == $y || $agiNode == $z)
    {
      $color = 'green';
      if ($agiVersions[$agiNodeSite] != $curVersion)
      {
        $color = 'red';
      }
    }else
    {
      if ($agiVersions[$agiNodeSite] != $oldVersion)
      {
        $color = 'yellow';
      }
    }
    echo "<td class='".$color."'>";
    echo $agiVersions[$agiNodeSite].'</td>';

    $color = '';
    $agiNodeSite = $agiNode .'.c19.jiveip.net:';
    $y = substr($laxagi[2], 4, 10);
    $z = substr($laxagi[1], 4, 10);
    if ($agiNode == $y || $agiNode == $z)
    {
      $color = 'green';
      if ($agiVersions[$agiNodeSite] != $curVersion)
      {
        $color = 'red';
      }
    }else
    {
      if ($agiVersions[$agiNodeSite] != $oldVersion)
      {
        $color = 'yellow';
      }
    }
    echo "<td class='".$color."'>";
    echo $agiVersions[$agiNodeSite].'</td>';

    $color = '';
    $agiNodeSite = $agiNode .'.c20.jiveip.net:';
    $y = substr($nycagi[2], 4, 10);
    $z = substr($nycagi[1], 4, 10);
    if ($agiNode == $y || $agiNode == $z)
    {
      $color = 'green';
      if ($agiVersions[$agiNodeSite] != $curVersion)
      {
        $color = 'red';
      }
    }else
    {
      if ($agiVersions[$agiNodeSite] != $oldVersion)
      {
        $color = 'yellow';
      }
    }
    echo "<td class='".$color."'>";
    echo $agiVersions[$agiNodeSite].'</td>';

    $color = '';
    $agiNodeSite = $agiNode .'.c22.jiveip.net:';
    $y = substr($atlagi[2], 4, 10);
    $z = substr($atlagi[1], 4, 10);
    if ($agiNode == $y || $agiNode == $z)
    {
      $color = 'green';
      if ($agiVersions[$agiNodeSite] != $curVersion)
      {
        $color = 'red';
      }
    }else
    {
      if ($agiVersions[$agiNodeSite] != $oldVersion)
      {
        $color = 'yellow';
      }
    }
    echo "<td class='".$color."'>";
    echo $agiVersions[$agiNodeSite].'</td></tr>';
  }
  $x = $x + 1;
}
echo '</table>';

#print_r($agiVersions);












<!DOCTYPE html>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

function verifyDomains($domainList) {
  $conn = pg_connect("host=rodb dbname=pbxs user=postgres");
  $badList = [];
  foreach ($domainList as $domain) {
    $query = "SELECT id FROM resource_group WHERE domain = '$domain';";
    $result = pg_fetch_assoc(pg_query($conn, $query));
    if (strlen($result['id']) < 1) {
      array_push($badList, $domain);
    }
  }
  pg_close($conn);
  return implode(",", $badList);
}

function domainSeparator($domainBlob) {
  $domainList = preg_split("/, +|,| +/", $domainBlob);
  return $domainList;
}
?>
<html>
<head>
<link rel='stylesheet' href='stylesheet.css'>
</head>
<body>
<?php
include('menu.html');
?>
<h2>Feature Flag Flipper</h2>
<p>
Current data<br>
Flag:<?=$_REQUEST['flag'];?><br>
Toggle:<?=$_REQUEST['toggle'];?><br>
Domains:<?=implode(",", domainSeparator($_REQUEST['domains']));?><br>
InvalidDomainList:<?=verifyDomains(domainSeparator($_REQUEST['domains']));?>
<br>
<form action="" method="POST">
  Select a flag to toggle: <select name='flag'>
    <option value="a">Calls that work</option>
    <option value="a">Voicemails that work</option>
    <option value="a">Crap service</option>
    <option value="a">Calls that don't work</option>
    <option value="a">Mystery flag</option>
  </select>
  <br><br>Set to:<span class='radio'>
  <input type='radio' name='toggle' value='on' id='on'><label for='on'>On</label></input>
  <input type='radio' name='toggle' value='off' id='off'><label for='off'>Off</label></input>
  </span>
  <br><br>Domain list:<br> <textarea name='domains' cols='60' rows='20'></textarea>
  <br><br><input type="submit" value="Flag those features!" />
</form>
</body>
<html>

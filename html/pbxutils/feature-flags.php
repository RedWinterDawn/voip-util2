<!DOCTYPE html>
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
Flag:<?=$_REQUEST['flag']?><br>
Toggle:<?=$_REQUEST['toggle']?><br>
Domains:<?=$_REQUEST['domains']?><br>
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

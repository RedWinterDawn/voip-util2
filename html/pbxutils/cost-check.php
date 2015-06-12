<!DOCTYPE html>
<html>
<head>
	<link rel='stylesheet' href='stylesheet.css'>
	<script src="libphonenumber-demo.js"></script>
</head>
<body>

<?
include('menu.html');
header('Content-Type:text/html');

if (isset($_REQUEST['to'])) {
	$to = $_REQUEST['to'];
} else {
		$to = "";
}

if (isset($_REQUEST['from'])) {
	$from = $_REQUEST['from'];
} else {
	$from = false;
}

if (isset($_REQUEST['domain'])) {
  $domain = $_REQUEST['domain'];
} else {
  $domain = "";
}

function printLcrLookup($to,$from,$address){
	if ($from) {
		$url = "http://" . $address . "/lcr/lookup/e164/$to?cli=$from";
	}else
	{
		$url = "http://" . $address . "/lcr/lookup/e164/$to";
	}
	print_r($url);
	echo "<br/>";
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT ,1);
	curl_setopt($curl, CURLOPT_TIMEOUT,2);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($curl);
	$result = str_replace('"results" : [ ]','<font color="red">"results" : [ ]</font>',$result);
	$curl_errno = curl_errno($curl);
	$curl_error = curl_error($curl);
	curl_close();
	if ($curl_errno > 0) {
		echo "cURL Error ($curl_errno): $curl_error\n";
	} else {
        print_r($result);
	}
}

echo "<h2>Number Cost Finder</h2>";
echo "<p>Enter an E.164 without the '+'</p></br>";
echo "<form action='' method='GET'>
		To Number:+<input type='text' name='to' onchange='document.getElementById(\"phoneNumber\").value=this.value;' value='" . $to . "' />
        <br>From Number:+<input type='text' name='from' value='" . $from . "'/>
        <br>Domain (for Sherlock): <input type='text' name='domain' value='".$domain."'/>
		<br><input type='submit' value='Search' />
		</form>";

if ($to != "") {
	if (!preg_match('/^\d*$/', $to)) {
		die ('To is not a valid number [' . $to . ']');
	}
	if ($from){
		if (!preg_match('/^\d*$/', $from)) {
			die ('From is not a valid number [' . $from . ']');
		}
	}
  if ($domain != "" || $domain != null) {
    echo "DOMAIN == |$domain|";
    try {
      $dbconn = pg_connect("host=rodb dbname=pbxs user=postgres");
      $result = pg_fetch_assoc(pg_query($dbconn, "SELECT id FROM resource_group WHERE domain = '$domain'"));
	  $id = $result['id'];
    } catch (Exception $e) {
      echo "Didn't find your domain";
      $id = 'a';
    }
  } else {
    $id = 'a';
  }
	if (substr($to,0,1) == '1'){
		$toDisplay = $to . " <font color='green'>US Domestic</font>";
	} else {
		$toDisplay = $to . ' <font color="yellow">International</font>';
	}
	if (substr($from,0,1) == '1'){
		$fromDisplay = $from . " <font color='green'>US Domestic</font>";
	} else if ($from == '') {
		$fromDisplay = '';
	} else {
		$fromDisplay = $from . ' <font color="yellow">International</font>';
	}
	echo "<p>To Number: $toDisplay
		  <br>From Number: $fromDisplay
      <br>ID: $id</p>
		  <p>Sherlock:<br>";
	$url = "http://10.125.255.66:6666/score/$id/$to" . "?queryOnly=true";
	print_r($url);
    echo "<br/>";
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$sherlockResult=curl_exec($curl);
	print_r(str_replace("DestinationForbidden","<font color='red'>DestinationForbidden</font>",$sherlockResult));
	curl_close();
    echo "</p>";

	echo "<hr/><p>v5 PVU:<br><pre>";
	printLcrLookup($to,$from,"10.117.253.121:9997");
    echo "</p></pre>";

	echo "<hr/><p>v4 DFW:<br>";
	printLcrLookup($from,$to,"10.118.252.190:9998");
    echo "</p>";
	
	echo "<hr/><p>v5 DFW LCR:<br><pre>";
	printLcrLookup($to,$from,"10.118.252.190:9997");
    echo "</p></pre>";
	
	echo "<hr/><p>v5 GEG:<br/>";
	printLcrLookup($to,$from,"10.123.253.89:9997");
    echo "</p>";

	echo "<hr/><p>v5 ORD LCR:<br><pre>";
	printLcrLookup($to,$from,"10.125.252.170:9997");
    echo "</p></pre>";
	
  echo "<hr/><p>ORD LCR<br/>";
  $url = "http://10.125.252.170/sbc/lcr.php?destNumber=".$to."&cli=".$from;
  print_r($url);
  echo "<br/>";
  $curl = curl_init($url);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,1);
  curl_setopt($curl,CURLOPT_TIMEOUT,2);
  $result = curl_exec($curl);
  $curl_errno = curl_errno($curl);
  $curl_error = curl_error($curl);
  curl_close();
  if ($curl_errno > 0) {
    echo "cURL Error ($curl_errno): $curl_error\n";
  } else {
        print_r($result);
  }
}
?>

<br/>
<hr/>

<!--
Copyright (C) 2010 The Libphonenumber Authors
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at
     http://www.apache.org/licenses/LICENSE-2.0
-->
<!--
  Original Author: Nikolaos Trogkanis
-->
<h2>Phone Number Parser Demo</h2>

<form>
  <p>
  Specify a Phone Number:
  <input type="text" name="phoneNumber" id="phoneNumber" size="25" value="<? echo $to ?>" />
  </p>
  <p>
  Specify a Default Country:
  <input type="text" name="defaultCountry" id="defaultCountry" size="2" value="US" />
  (ISO 3166-1 two-letter country code)
  </p>
  <p>
  Specify a Carrier Code:
  <input type="text" name="carrierCode" id="carrierCode" size="2" />
  (optional, only valid for some countries)
  </p>
  <input type="button" value="Parse" onclick="document.getElementById("output").value = phoneNumberParser();" />
  <p>
  <!-- <textarea id="output" rows="30" cols="120"></textarea> -->
  </p>
</form>

</body>
</html>

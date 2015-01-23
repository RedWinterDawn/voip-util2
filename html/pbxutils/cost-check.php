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

echo "<h2>Number Cost Finder</h2>";
echo "<p>Enter an E.164 without the '+'</p></br>";
echo "<form action='' method='GET'>
		To Number:+<input type='text' name='to' onchange='document.getElementById(\"phoneNumber\").value=this.value;' value='" . $to . "' />
        <br>From Number:+<input type='text' name='from' value='" . $from . "'/>
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
	echo "<p>To Number: $to
		  <br>From Number: $from</p>
		  <p>Sherlock:<br>";
	$url = "http://10.125.255.66:6666/score/a/$to";
	print_r($url);
    echo "<br/>";
	$curl = curl_init($url);
	print_r(curl_exec($curl));
	curl_close();
    echo "</p>";

	echo "<hr/><p>V4 LCR:<br>";
	printLcrLookup($to,$from,"10.103.0.197:9998");
    echo "</p>";
	
	echo "<hr/><p>PVU LCR:<br>";
	printLcrLookup($to,$from,"10.117.255.41:9998");
    echo "</p>";
	
	echo "<hr/><p>DFW LCR:<br><pre>";
	printLcrLookup($to,$from,"10.118.255.41:9998");
    echo "</p></pre>";
	
	echo "<hr/><p>GEG LCR:<br/>";
	printLcrLookup($to,$from,"10.123.253.130:9998");
    echo "</p>";
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

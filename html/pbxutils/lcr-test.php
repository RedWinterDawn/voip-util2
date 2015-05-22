<!DOCTYPE html>
<html>
<head>
	<link rel='stylesheet' href='stylesheet.css'>
	<!-- <script src="libphonenumber-demo.js"></script> -->
</head>
<body>

<?
include('menu.html');
header('Content-Type:text/html');

if (isset($_REQUEST['site'])) {
	$site = $_REQUEST['site'];
} else {
	$site = "ORD";
}

function testLcrLookup($url){
	echo "<br/><pre>";
	echo "URL: $url \n";
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT ,1);
	curl_setopt($curl,CURLOPT_TIMEOUT,2);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER ,1);
	curl_setopt($curl, CURLOPT_FRESH_CONNECT ,1);
	curl_setopt($curl, CURLOPT_FORBID_REUSE ,1);
	$result = curl_exec($curl);
	$contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
	$curl_errno = curl_errno($curl);
	$curl_error = curl_error($curl);
	curl_close();
	if ($curl_errno != 0) { echo "<font color='red'>cURL Error ($curl_errno): $curl_error</font>\n"; }
	if ($result == '{"results":[]}') { echo "<font color='red'>FAIL: "; } else { echo "<font color='white'>"; }
	#echo "\nResult: [" . print_r($result) . "]";
	echo "\nResult: [" . $result . "]";
	echo "</font>\n";

	if ($contentType == 'application/json') {
	    echo "<font color='green'>Content Type: [" . $contentType . "]</font>\n";
	}else{
	    echo "<font color='red'>Content Type: [" . $contentType . "]</font>\n";
	}

	// echo "<font color='blue'>Blue</font>";
	echo "</pre>";
}

function doAllTheTests($ip){
	echo "<font color='lightgreen'>=== Pass these =============================</font><br/>";
	testLcrLookup('http://' . $ip . ':9998/lcr/lookup/e164/16785712512?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=18016992000');
	testLcrLookup('http://' . $ip . ':9998/lcr/lookup/e164/16785712512?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=180006992000');
	testLcrLookup('http://' . $ip . ':9998/lcr/lookup/e164/16785712512?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=011320123456');
	testLcrLookup('http://' . $ip . ':9998/lcr/lookup/e164/16785712512?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=19998887777');
	testLcrLookup('http://' . $ip . ':9997/lcr/lookup/e164/18016992000?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512');
	testLcrLookup('http://' . $ip . ':9997/lcr/lookup/e164/180006992000?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512');
	testLcrLookup('http://' . $ip . ':9997/lcr/lookup/e164/011320123456?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512');
	testLcrLookup('http://' . $ip . ':9997/lcr/lookup/e164/19998887777?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=18019600060&cli=');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=18019600060&cli=19998887777');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=19998887777&cli=');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=011320123456&cli=');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=011320123456&cli=02');
	echo "<font color='lightblue'>=== Undefined =============================</font><br/>";
	testLcrLookup('http://' . $ip . ':9998/lcr/lookup/e164/1801?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512');
	testLcrLookup('http://' . $ip . ':9997/lcr/lookup/e164/1801?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=1801&cli=');
	echo "<font color='orange'>=== Fail these =============================</font><br/>";
	testLcrLookup('http://' . $ip . ':9998/lcr/lookup/e164/0118?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512');
	testLcrLookup('http://' . $ip . ':9997/lcr/lookup/e164/0118?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=0118&cli=');
}

echo "<h2>LCR Validator tester</h2>";

if ($site == 'ORD' || $site == 'ALL'){
	echo "<hr><h3>ORD</h3>";
	doAllTheTests("10.125.252.170");
}

if ($site == 'LAX' || $site == 'ALL'){
	echo "<hr><h3>LAX</h3>";
	doAllTheTests("10.119.252.43");
}

if ($site == 'DFW' || $site == 'ALL'){
	echo "<hr><h3>DFW</h3>";
	doAllTheTests("10.118.252.190");
}

if ($site == 'ATL' || $site == 'ALL'){
	echo "<hr><h3>ATL</h3>";
	doAllTheTests("10.122.252.38");
}

if ($site == 'NYC' || $site == 'ALL'){
	echo "<hr><h3>NYC</h3>";
	doAllTheTests("10.120.253.226");
}

if ($site == 'GEG' || $site == 'DEV'){
	echo "<hr><h3>GEG</h3>";
	doAllTheTests("10.123.253.89");
}

if ($site == 'PVU' || $site == 'DEV'){
	echo "<hr><h3>PVU</h3>";
	doAllTheTests("10.117.253.121");
}

// testLcrLookup('');

?>
</body>
</html>

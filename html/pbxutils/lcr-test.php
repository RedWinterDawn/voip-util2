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

$testStatus = "PASS";

function testLcrLookup($url,$expectedResult){
  global $testStatus;
	echo "<br/><pre>";
	echo "URL: $url \n";
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT ,1);
	curl_setopt($curl,CURLOPT_TIMEOUT,2);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER ,1);
	curl_setopt($curl, CURLOPT_FRESH_CONNECT ,1);
	curl_setopt($curl, CURLOPT_FORBID_REUSE ,1);
	$result = curl_exec($curl);
	$result = ltrim ($result, "\n");
	$contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
	$curl_errno = curl_errno($curl);
	$curl_error = curl_error($curl);
	curl_close();

	// Check for curl failure
	if ($curl_errno != 0) { echo "<font color='red'>cURL Error ($curl_errno): $curl_error</font>\n"; $testStatus='ERROR'; return $testStatus; }
	
    // Check vs expected result, display result
	if ($result != $expectedResult) {
		echo "<font color='red'>FAIL: "; 
	    echo "Result: [" . $result . "]\n";
	    echo "Expected: [" . $expectedResult . "]";
		$testStatus='FAIL';
	} else {
		echo "<font color='lightgreen'>"; 
	    echo "Result: [" . $result . "]";
	}
	echo "</font>\n";

	if ($contentType == 'application/json') {
	    echo "<font color='green'>Content Type: [" . $contentType . "]</font>\n";
	}else{
	    echo "<font color='red'>Content Type: [" . $contentType . "]</font>\n";
		  $testStatus='FAIL';
	}

	echo "</pre>";
  return $testStatus;
}

function doAllTheTests($ip){
	echo "<font color='lightgreen'>=== Pass these =============================</font><br/>";
	testLcrLookup('http://' . $ip . ':9998/lcr/lookup/e164/16785712512?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=18016992000','{"results":["BANDWIDTH", "360_NETWORKS", "SIPROUTES:PRIME", "VOIP_INNOVATIONS:LCR", "LEVEL3"]}');
	testLcrLookup('http://' . $ip . ':9998/lcr/lookup/e164/16785712512?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=180006992000','{"results":["BANDWIDTH", "IRISTEL", "VOIP_INNOVATIONS:LCR", "360_NETWORKS", "LEVEL3"]}');
	testLcrLookup('http://' . $ip . ':9998/lcr/lookup/e164/16785712512?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=011320123456','{"results":["360_NETWORKS", "SIPROUTES:PRIME", "LEVEL3", "BANDWIDTH"]}');
	testLcrLookup('http://' . $ip . ':9998/lcr/lookup/e164/16785712512?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=19998887777','{"results":["BANDWIDTH", "VOIP_INNOVATIONS:LCR", "360_NETWORKS", "LEVEL3"]}');
	testLcrLookup('http://' . $ip . ':9997/lcr/lookup/e164/18016992000?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512','{"results":["BANDWIDTH", "ONVOY", "THINQ", "VOIP_INNOVATIONS", "LEVEL3"]}');
	testLcrLookup('http://' . $ip . ':9997/lcr/lookup/e164/180006992000?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512','{"results":["BANDWIDTH", "IRISTEL", "VOIP_INNOVATIONS", "ONVOY", "LEVEL3"]}');
	testLcrLookup('http://' . $ip . ':9997/lcr/lookup/e164/011320123456?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512','{"results":["ONVOY", "THINQ", "LEVEL3", "BANDWIDTH"]}');
	testLcrLookup('http://' . $ip . ':9997/lcr/lookup/e164/19998887777?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512','{"results":["BANDWIDTH", "VOIP_INNOVATIONS", "ONVOY", "LEVEL3"]}');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=18019600060&cli=','{"results":["BANDWIDTH", "THINQ", "ONVOY", "VOIP_INNOVATIONS", "LEVEL3"]}');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=18019600060&cli=19998887777','{"results":["BANDWIDTH", "THINQ", "ONVOY", "VOIP_INNOVATIONS", "LEVEL3"]}');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=19998887777&cli=','{"results":["BANDWIDTH", "VOIP_INNOVATIONS", "ONVOY", "LEVEL3"]}');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=011320123456&cli=','{"results":["ONVOY", "THINQ", "LEVEL3", "BANDWIDTH"]}');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=011320123456&cli=02','{"results":["ONVOY", "THINQ", "LEVEL3", "BANDWIDTH"]}');
	testLcrLookup('http://' . $ip . ':9997/lcr/lookup/e164/19996660000?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512','{"results":["JIVEBGW"]}');
	echo "<font color='lightblue'>=== Undefined =============================</font><br/>";
	testLcrLookup('http://' . $ip . ':9998/lcr/lookup/e164/1801?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512','{"results":["BANDWIDTH", "360_NETWORKS", "SIPROUTES:PRIME", "VOIP_INNOVATIONS:LCR", "LEVEL3"]}');
	testLcrLookup('http://' . $ip . ':9997/lcr/lookup/e164/1801?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512','{"results":["BANDWIDTH", "VOIP_INNOVATIONS", "ONVOY", "LEVEL3"]}');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=1801&cli=','{"results":["BANDWIDTH", "VOIP_INNOVATIONS", "ONVOY", "LEVEL3"]}');
	testLcrLookup('http://' . $ip . ':9998/lcr/lookup/e164/16785712512?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=0118','{"results":[]}');
	echo "<font color='orange'>=== Fail these =============================</font><br/>";
	testLcrLookup('http://' . $ip . ':9997/lcr/lookup/e164/0118?contextId=014035c0-01ad-da24-1a10-000100420005&callId=b54eb20-6eb37c1b-6ceef172@10.50.40.58&cli=16785712512','{"results":[]}');
	testLcrLookup('http://' . $ip . '/sbc/lcr.php?destNumber=0118&cli=','{"results":[]}');
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

echo "<font color='white'>=== Test results =============================</font><br/>";
echo "<pre>";
echo "TEST COMPLETE\n";
echo "STATUS: ";

if ($testStatus == 'PASS') {
	echo "<font color='lightgreen'>ALL TESTS PASSED</font>";
} else {
	echo "<font color='red'>" . $testStatus . "</font>";
}

echo "</pre>";

?>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
	<link rel='stylesheet' href='stylesheet.css'>
</head>
<body>

<?
include('menu.html');
header('Content-Type:text/html');
	echo "<h2>Number Cost Finder</h2>";
	echo "<p>Enter an E.164 without the '+'</p></br>";
	echo "<form action='' method='GET'>
		To Number:<input type='text' name='to' />
        <br>From Number:<input type='text' name='from' />
		<br><input type='submit' value='Search' />
		</form>";

if (isset($_REQUEST['to'])) {
	$to = $_REQUEST['to'];
	if (!preg_match('/^\d*$/', $to)) {
		die ('Not a valid number');
	}
	if (isset($_REQUEST['from'])){
		$from = $_REQUEST['from'];
		if (!preg_match('/^\d*$/', $from)) {
			die ('From is not a valid number');
		}
	}
	echo "<p>To Number: $to
		  <br>From Number: $from</p>
		  <p>Sherlock:<br>";
	$curl = curl_init("http://10.125.255.66:6666/score/a/$to");
	print_r(curl_exec($curl));
	curl_close();
    echo "</p>";

	echo "<p>V4 LCR:<br>";
	if ($from) {
		$curl = curl_init("http://10.103.0.197:9998/lcr/lookup/e164/$to?cli=$from");
	}else
	{
		$curl = curl_init("http://10.103.0.197:9998/lcr/lookup/e164/$to");
	}
	print_r(curl_exec($curl));
	curl_close();
    echo "</p>";
	
	echo "<p>PVU LCR:<br>";
	if ($from) {
		$curl = curl_init("http://10.117.255.41:9998/lcr/lookup/e164/$to?cli=$from");
	}else
	{
		$curl = curl_init("http://10.117.255.41:9998/lcr/lookup/e164/$to");
	}
	print_r(curl_exec($curl));
	curl_close();
    echo "</p>";
	
	echo "<p>DFW LCR:<br>";
	if ($from) {
		$curl = curl_init("http://10.118.255.41:9998/lcr/lookup/e164/$to?cli=$from");
	}else
	{
		$curl = curl_init("http://10.118.255.41:9998/lcr/lookup/e164/$to");
	}
	print_r(curl_exec($curl));
	curl_close();
    echo "</p>";
}
?>

</body>
</html>

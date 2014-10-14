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
		<input type='text' name='number' />
		<input type='submit' value='Search' />
		</form>";

if (isset($_REQUEST['number'])) {
	$number = $_REQUEST['number'];
	if (!preg_match('/^\d*$/', $number)) {
		die ('Not a valid number');
	}
	echo "<p>Number: $number</p>";
	$curl = curl_init("http://10.125.255.66:6666/score/a/$number");
	print_r(curl_exec($curl));
	curl_close();
}
?>

</body>
</html>

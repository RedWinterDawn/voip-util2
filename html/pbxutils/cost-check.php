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
	echo "<p>Enter an international number</p></br>";
	echo "<form action='' method='GET'>
		<input type='text' name='number' />
		<input type='submit' value='Search' />
		</form>";

if (isset($_REQUEST['number'])) {
	$number = $_REQUEST['number'];
	if (!preg_match('/^\d*$/', $number)) {
		die ('Not a valid number');
	}
	$number = preg_replace('/^011/', '', $number);
	echo "<p>Number: $number</p>";
	$curl = curl_init("http://10.117.252.242:6666/score/a/$number");
	print_r(curl_exec($curl));
	curl_close();
}
?>

</body>
</html>

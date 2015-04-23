
<html>
<head>
<title>Pull data from Sherlock</title>
</head>
<body>

<?php

	   $pbxid = $_POST['pbxid'];
	      


$domainroot = "http://10.125.255.66:6666/tenant/";
$region = '/region';
$url = $domainroot.$pbxid.$region;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	     'Accept: application/json'
		                  ));
$data = curl_exec($ch);
curl_close($ch);
echo $data;

?>
	<form method="post" action="<?php $_PHP_SELF ?>">
<table width="400" border="0" cellspacing="1" cellpadding="2">
<tr>
<td width="100">PBX</td>
<td><input name="pbxid" type="text" id="pbxid"></td>

</tr>
</table>
</form>
</body>
</html>

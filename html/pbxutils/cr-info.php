<html>
<body>

<table border="1">
<th>target</th><th>type</th><th>weight</th>
<?php

$cr_hostname="_sip._udp.cr.4-10.c1.jiveip.net.";
$dns_record = dns_get_record($cr_hostname);

//var_dump($dns_record);

foreach ($dns_record as $host) {
	echo "<tr><td>" . $host["target"] . "</td><td>" . $host["type"] . "</td><td>" . $host["weight"] . "</td></tr>\n";
}

?>

</table>

</body>
</html>

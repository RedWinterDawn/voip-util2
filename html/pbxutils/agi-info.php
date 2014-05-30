<html>
<head>
<link rel='stylesheet' href='stylesheet.css'>
</head>
<body>

<table border="1">
<th>target</th><th>type</th><th>weight</th>
<?php

$agi_hostname="_agi._tcp.agi.4-10.c1.jiveip.net.";
$dns_record = dns_get_record($agi_hostname);

//var_dump($dns_record);

foreach ($dns_record as $host) {
	echo "<tr><td>" . $host["target"] . "</td><td>" . $host["type"] . "</td><td>" . $host["weight"] . "</td></tr>\n";
}

?>

</table>

<textarea type="hidden" rows="10" cols="100">
<?php
$dnsConfig = file_get_contents('/root/c1.srvrecords.txt');
echo $dnsConfig;
?>
</textarea>

<?php
$startTime = strtotime("-180 minutes") . '000';
$endTime = time() . '000';
?>

<table border="1">
<th>server</th><th>active calls</th><th>system cpu</th>
  <tr>
    <td>agi1</td>
	<td><img src="https://mc.c1.jiveip.net/opennms/graph/graph.png?report=com.jive.callhandling.asterisk.Statistics.0ActiveCalls.AttributeReport&resourceId=node[9].interfaceSnmp[agi]&start=<?php echo $startTime; ?>&end=<?php echo $endTime; ?>" alt="agi1 Active Calls"/></td>
	<td><img src="https://mc.c1.jiveip.net/opennms/graph/graph.png?report=java.lang.OperatingSystem.0SysCpuLoad.AttributeReport&resourceId=node[9].interfaceSnmp[agi]&start=<?php echo $startTime; ?>&end=<?php echo $endTime; ?>" alt="agi1 System CPU" /></td>
  </tr>
  <tr>
    <td>agi4</td>
	<td><img src="https://mc.c1.jiveip.net/opennms/graph/graph.png?report=com.jive.callhandling.asterisk.Statistics.0ActiveCalls.AttributeReport&resourceId=node[232].interfaceSnmp[agi]&start=<?php echo $startTime; ?>&end=<?php echo $endTime; ?>" alt="agi1 Active Calls"/></td>
	<td><img src="https://mc.c1.jiveip.net/opennms/graph/graph.png?report=java.lang.OperatingSystem.0SysCpuLoad.AttributeReport&resourceId=node[232].interfaceSnmp[agi]&start=<?php echo $startTime; ?>&end=<?php echo $endTime; ?>" alt="agi1 System CPU" /></td>
  </tr>
</table>

</body>
</html>

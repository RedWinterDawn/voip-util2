<html>
<head><link rel="stylesheet" href="../pbxutils/stylesheet.css"></head>
<body>
<?php

function printRecordInfo($host) {
	$dnsRecords = dns_get_record($host, DNS_ALL);
	if (count($dnsRecords == 0)) {
		$dnsRecords[0]['host'] = $host;
	}
	reset($dnsRecords); // This resets the array pointer
	while ($record = current($dnsRecords)) {
		$recordHost = $record['host'];
		$recordType = $record['type'];
	    echo "<tr>";
	    echo "<td>" . $recordHost . "</td>";
	    echo "<td>" . $recordType . "</td>";
	    echo "<td>" . $record['pri'] . "</td>";
	    echo "<td>" . $record['weight'] . "</td>";
	    echo "<td>" . $record['port'] . "</td>";
	    echo "<td>" . $record['target'] . "</td>";
	    echo "<td>" . $record['ip'] . "</td>";
	    echo "<td>" . $record['class'] . "</td>";
	    echo "<td>" . $record['ttl'] . "</td>";
	    echo "<td>" . $record['txt'] . "</td>";
		echo "</tr>\n";
		if (next($dnsRecords))
		{
		} else {
			break;
		}
		//echo "<pre border=1>" . print_r($dnsRecords) . "</pre>";
	}
	return;
}

echo "<table border=3>";
echo "<tr><th>host</th><th>type</th><th>pri</th><th>weight</th><th>port</th><th>target</th><th>ip</th><th>class</th><th>ttl</th><th>txt</th></tr>\n";

printRecordInfo("_agi._tcp.agi.4-10.c1.jiveip.net");
printRecordInfo("_sip._udp.cr.4-10.c1.jiveip.net");
printRecordInfo("_sip._udp.gw.c1.jiveip.net");
printRecordInfo("_sip._udp.tcr");
printRecordInfo("lbgw.c1.jiveip.net");
printRecordInfo('agi1.c1.jiveip.net');
printRecordInfo('agi2.c1.jiveip.net');
printRecordInfo('agi3.c1.jiveip.net');
printRecordInfo('agi4.c1.jiveip.net');
printRecordInfo('agi5.c1.jiveip.net');
printRecordInfo('agi6.c1.jiveip.net');
printRecordInfo('reg.jiveip.net');
printRecordInfo('proxy.jiveip.net');


echo "</table>";


?>

</body>
</html>

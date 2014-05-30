<?

if (isset($_GET["writeFile"]))
{
	$writeFile = $_GET["writeFile"];
} else
{
	$writeFile = false;
}

$fileContent='';

$fieldarray=array("number","active","outbound_routable","master_did.id","location_code","caller_id_name","destination_pbx_id","domain","customer_name");

// database connection
$conn="host=rodb dbname=pbxs user=snapshot password=1rSENCUfrUCwTq60";
pg_connect($conn) or die("Couldn't connect to database.".pg_last_error());

$q="select number,active,outbound_routable,master_did.id,location_code,caller_id_name,destination_pbx_id,resource_group.domain AS domain,resource_group.name AS customer_name from master_did left join resource_group on master_did.destination_pbx_id=resource_group.id order by domain,destination_pbx_id;";

$result=pg_query($q) or die("Couldn't execute: $q\n\n".pg_last_error());

echo "<table border='1'>\n";
foreach ($fieldarray as $field) { echo "<th>$field</th>"; $fileContent=$fileContent . "$field,"; }
$fileContent=$fileContent . ",0\n";

while($row=pg_fetch_array($result, null, PGSQL_ASSOC)){
	echo "\t<tr>\n";
	foreach ($fieldarray as $field) { echo "<td>" . $row[$field] . "</td>\n"; $fileContent=$fileContent . "$field,"; }
	$fileContent=$fileContent . ",0\n";
	echo "\t</tr>\n";
}

echo "</table><br/>\n";

pg_free_result($result);
// pg_close($conn);

if ($writeFile == "true")
{
	$fileName = "/tmp/pbx-did-list.csv";
	$handle=fopen($fileName,"w");
	fwrite($handle,$fileContent);
	fclose($handle);
	echo "<br/>Results written to " . $fileName . " <br/>\n";
}

?>

<?

if (isset($_GET["writeFile"]))
{
	$writeFile = $_GET["writeFile"];
} else
{
	$writeFile = false;
}

// echo "DEBUG: Showing from one month starting date " . $startDate . "<br/>";
$fileContent='';

// database connection
$conn="host=rodb dbname=pbxs user=snapshot password=1rSENCUfrUCwTq60";
pg_connect($conn) or die("Couldn't connect to database.".pg_last_error());

$q="
select
    split_part(rg.path,',',1) as pbx_id,
	rg.domain as pbx_domain,
	count(*) as devices,
	rg.state as pbx_state,
	lic.date_enabled as date_enabled
from
    mobility_license lic
    left join resource_group rg on lic.resource_group_id = rg.id
group by
    pbx_id, pbx_domain, pbx_state, lic.date_enabled
order by
	lic.date_enabled;";

$result=pg_query($q) or die("Couldn't execute: $q\n\n".pg_last_error());

$deviceTotal=0;

echo "<table border='1'>\n";
echo "<th>pbx_id</th><th>pbx_domain</th><th>devices</th><th>pbx_state</th><th>date_enabled</th>";
while($row=pg_fetch_array($result, null, PGSQL_ASSOC)){
	echo "\t<tr>\n";
	echo "<td>" . $row['pbx_id'] . "</td>\n";
	echo "<td>" . $row['pbx_domain'] . "</td>\n";
	echo "<td>" . $row['devices'] . "</td>\n";
	$deviceTotal = $deviceTotal + $row['devices'];
	echo "<td>" . $row['pbx_state'] . "</td>\n";
	echo "<td>" . $row['date_enabled'] . "</td>\n";
	// TODO: build file data // $fileContent .= $col_value . ",";	
	echo "\t</tr>\n";
	$fileContent .= "0 \n";
}

echo "</table><br/>\n";
echo "Total: " . $deviceTotal . "<br/>\n";

pg_free_result($result);
// pg_close($conn);

if ($writeFile == "true")
{
	$fileName = "/tmp/mobility-activations-alltime.csv";
	$handle=fopen($fileName,"w");
	fwrite($handle,$fileContent);
	fclose($handle);
	echo "<br/>Results written to " . $fileName . " <br/>\n";
}

?>

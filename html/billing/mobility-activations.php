<?
if (isset($_GET["month"]))
{
	$month = $_GET["month"];
	$startDate=$month . "-01";
} else
{
	echo "No month requested";
	die();
}

if (isset($_GET["writeFile"]))
{
	$writeFile = $_GET["writeFile"];
} else
{
	$writeFile = false;
}

if ($month == "last")
{
	// this is meant to be called by the monthly billing script on the
	//   first day of the following month
	$month = date("Y-m", strtotime( '-1 months' ));
	$startDate= $month . "-01";
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
	lic.date_enabled
from
    mobility_license lic
    left join resource_group rg on lic.resource_group_id = rg.id
where
    lic.date_enabled >= date_trunc('MONTH', timestamp '" . $startDate . "')
    AND lic.date_enabled <= date_trunc('MONTH', timestamp '" . $startDate . "' + '1 month'::interval)
group by
    pbx_id, pbx_domain, pbx_state, lic.date_enabled
order by
	pbx_id;";

$result=pg_query($q) or die("Couldn't execute: $q\n\n".pg_last_error());

echo "<table>\n";

while($line=pg_fetch_array($result, null, PGSQL_ASSOC)){
	echo "\t<tr>\n";
	foreach ($line as $col_value) {	
		echo "\t\t<td>$col_value</td>\n";
	    $fileContent .= $col_value . ",";	
	}
	echo "\t</tr>\n";
	$fileContent .= "0 \n";
}

echo "</table>\n";

pg_free_result($result);
// pg_close($conn);

if ($writeFile == "true")
{
	$fileName = "/tmp/" . $month . "-mobility-activations.csv";
	$handle=fopen($fileName,"w");
	fwrite($handle,$fileContent);
	fclose($handle);
	echo "<br/>Results written to " . $fileName . " <br/>\n";
}

?>

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

// database connection
$conn="host=rodb dbname=pbxs user=postgres";
pg_connect($conn) or die("Couldn't connect to database.".pg_last_error());

$q="
select
    id,external_user_id,resource_group_id,reset_timestamp
from
    mobility_reset_log
where
    mobility_reset_log.reset_timestamp >= date_trunc('MONTH', timestamp '" . $startDate . "')
    AND mobility_reset_log.reset_timestamp <= date_trunc('MONTH', timestamp '" . $startDate . "' + '1 month'::interval);
";

$result=pg_query($q) or die("Couldn't execute: $q\n\n".pg_last_error());

echo "<table>\n";
echo "<th>id</th><th>external_user_id</th><th>resource_group_id</th><th>reset_timestamp</th>\n";
$fileContent .= "id,external_user_id,resource_group_id,reset_timestamp\n";

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

//echo "<pre>\n";
//echo $fileContent;
//echo "</pre>\n";

pg_free_result($result);
pg_close($conn);

if ($writeFile == "true")
{
	$fileName = "/tmp/" . $month . "-mobility-resets.csv";
	$handle=fopen($fileName,"w");
	fwrite($handle,$fileContent);
	fclose($handle);
	echo "<br/>Results written to " . $fileName . " <br/>\n";
}
?>

<?
$cliday=date("Y-m-d", strtotime( '-1 days' ));
//if(!$cliday){$cliday="2009-07-01";}

$startDate='2013-12-01';
$fileContent='';

// database connection
$conn="host=rodb dbname=pbxs user=snapshot password=1rSENCUfrUCwTq60";
pg_connect($conn) or die("Couldn't connect to database.".pg_last_error());

$q="
select
    split_part(rg.path,',',1) as pbx_id,
	rg.domain as pbx_domain,
	count(*) as devices,
	rg.state as pbx_state
from
    mobility_license lic
    left join resource_group rg on lic.resource_group_id = rg.id
group by
    pbx_id, pbx_domain, pbx_state
order by
	pbx_id;";

// date_trunc('month', timestamp '2013-11-01')

$result=pg_query($q) or die("Couldn't execute: $q\n\n".pg_last_error());

echo "<table>\n";

while($line=pg_fetch_array($result, null, PGSQL_ASSOC)){
	echo "\t<tr>\n";
	foreach ($line as $col_value) {	
		echo "\t\t<td>$col_value</td>\n";
	    $fileContent .= $col_value . ",";	
	}
	echo "\t</tr>\n";
	$fileContent .= $col_value . " \n";
}

echo "</table>\n";

//echo "<pre>\n";
//echo $fileContent;
//echo "</pre>\n";

pg_free_result($result);
pg_close($conn);

$handle=fopen("/tmp/" . $startDate . "-mobility.csv","w");
fwrite($handle,$fileContent);
fclose($handle);
?>

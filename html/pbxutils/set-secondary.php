<?php
include('menu.html');
$lax = array('WA', 'OR', 'CA', 'ID', 'NV', 'AZ', 'AK', 'HI');
$dfw = array('MT', 'WY', 'UT', 'CO',  'NM', 'TX');
$atl = array('LA', 'AR', 'MO', 'FL', 'MS', 'AL', 'GA', 'SC', 'TN', 'NC', 'KY', 'OK', 'KS', 'ND', 'SD', 'NE');
$nyc = array('DC', 'MN', 'IA', 'WI', 'IL', 'MI', 'IN', 'OH', 'WV', 'VA', 'PA', 'MD', 'NJ', 'DE', 'NY', 'VT', 'NH', 'MA', 'CT', 'RI', 'ME');
echo "script inactive";
//$utildb = pg_connect("host=rodb dbname=util user=postgres ") or die('could not connect to util' . pg_last_error());

function getAreaCodes ($siteArray)
{
	$utildb = pg_connect("host=rwdb dbname=util user=postgres ") or die('could not connect to util' . pg_last_error());
	$utilQ = "SELECT area_code from area_code where";
	$first = true;
	foreach ($siteArray as $state)
	{
		if ($first)
		{
			$utilQ = $utilQ . " state='".$state."'";
			$first = false;
		}else
		{
			$utilQ = $utilQ . " OR state='".$state."'";
		}
	}
	$results = pg_fetch_all(pg_query($utildb, $utilQ));
	pg_close($utildb);
	$count = 0;
	foreach($results as $key)
	{
		$codes[] = $key['area_code'];
	}
	return $codes;
}

$areaCodes = getAreaCodes ($nyc);
//print_r($areaCodes);

$pbxsdb = pg_connect("host=rwdb dbname=pbxs user=postgres ") or die('could not connect to pbxs' . pg_last_error());
$updateQ = "SELECT q.domain from (SELECT domain, local_area_code, location from resource_group where location='chicago-legacy' AND state='ACTIVE' AND secondary_location='chicago-legacy') as q where";
$first = true;
foreach ($areaCodes as $area)
{
	if ($first)
	{
		$updateQ = $updateQ . " local_area_code='".$area."'";
		$first = false;
	}else
	{
		$updateQ = $updateQ . " OR local_area_code='".$area."'";
	}
}
//$updateQ = $updateQ . " limit 500";
$result = pg_query($pbxsdb, $updateQ) or die(pg_last_error());
$result = pg_fetch_all($result);

pg_close($pbxsdb);

foreach($result as $key)
{
	$domains[] = $key['domain'];
}
print_r($domains);

foreach($domains as $key)
{
	$inserts[] = "UPDATE resource_group SET secondary_location='nyc' WHERE domain='".$key."'";
}
echo "<br><br><br>";
print_r($inserts);

$pbxsdb = pg_connect("host=rwdb dbname=pbxs user=postgres ") or die('could not connect to rwpbxs' . pg_last_error());
$count = 0;
foreach($inserts as $key)
{
	pg_query($pbxsdb, $key) or die('failed to update: '.$key. ' '. pg_last_error());
}

echo "<br>Done";
pg_close($pbxsdb);

?>


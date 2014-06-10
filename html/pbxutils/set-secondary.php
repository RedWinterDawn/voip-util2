<?php
include('menu.html');
$lax = array('WA', 'OR', 'CA', 'ID', 'NV', 'AZ');
$dfw = array('MT', 'WY', 'UT', 'CO',  'NM');
$atl = array('LA', 'AR', 'MO', 'FL', 'MS', 'AL', 'GA', 'SC', 'TN', 'NC', 'KY', 'OK', 'KS', 'ND', 'SD', 'NE');
$nyc = array('MN', 'IA', 'WI', 'IL', 'MI', 'IN', 'OH', 'WV', 'VA', 'PA', 'MD', 'NJ', 'DE', 'NY', 'VT', 'NH', 'MA', 'CT', 'RI', 'ME');

//$utildb = pg_connect("host=rodb dbname=util user=postgres ") or die('could not connect to util' . pg_last_error());

function getAreaCodes ($siteArray)
{
	$utildb = pg_connect("host=rodb dbname=util user=postgres ") or die('could not connect to util' . pg_last_error());
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

$areaCodes = getAreaCodes ($dfw);
print_r($areaCodes);

$pbxsdb = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('could not connect to pbxs' . pg_last_error());
$updateQ = "SELECT q.domain from (SELECT domain, local_area_code, location from resource_group where location='chicago-legacy' AND state='ACTIVE') as q where";
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
$result = pg_query($pbxsdb, $updateQ) or die(pg_last_error());
$result = pg_fetch_all($result);
print_r($result);
pg_close($pbxsdb);

?>


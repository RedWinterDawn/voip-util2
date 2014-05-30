<?php
echo "<html><body>\n\n";
echo "<html><head><link rel='stylesheet' href='stylesheet.css'></head><body>\n";
echo "<pre>\n";
$viplist[] = '';
$vipcount = 0;
$presence_server = '10.101.40.1';

// this script is outdated
// TODO: Add new tooling to allow presence migration
die("negative");

$dbconnutil = pg_connect("host=rodb dbname=util user=postgres ")
    or die('Could not connect: ' . pg_last_error());

/////////////////////////////
// Read Santa users from VIP table
$query = "SELECT domain FROM vip WHERE santa = TRUE;";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    foreach ($line as $col_value) {
      $vipcount = $vipcount + 1;
      $viplist[$vipcount] = $col_value; 
    }
}
echo "Found " . $vipcount . " records\n\n";
//
/////////////////////////////

pg_free_result($result);
pg_close($dbconnutil);

/////////////////////////////
// Update VIPs to use Santa
echo "Writing $vipcount records... \n";

$dbconnpbxs = pg_connect("host=rwdb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());

while ($vipcount > 0) {
    $query = "UPDATE resource_group SET presence_server = '" . $presence_server . "' WHERE domain = '" . $viplist[$vipcount] . "';";
    echo "($vipcount) " . $query . "\n";
    $result = pg_query($dbconnpbxs,$query) or die('Query failed: ' . pg_last_error());
    $vipcount = $vipcount - 1;
}

pg_free_result($result);
pg_close($dbconnpbxs);
//
/////////////////////////////

echo "</pre>";
echo "</body></html>\n";

?>







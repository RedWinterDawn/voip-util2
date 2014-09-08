<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');
$guiltyParty = $_SERVER['REMOTE_ADDR'];
$requestTime = strftime('%Y-%m-%d %H:%M:%S');
$resource_group_id = "";

$reportDate = strftime('%Y-%m-%d');

if (isset($_REQUEST["action"])) {
	$action = $_REQUEST["action"];
} else {
	$action = "doSearch";
}

if ($action == "doSearch") {
	// Get pbxID
	$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
		or die('Could not connect: ' . pg_last_error());
	$pbxQuery = "SELECT id FROM resource_group WHERE state = 'DEACTIVATED' AND assigned_server = '' AND archived is null;";
	$pbxResult = pg_query($pbxQuery) or die('PBX query failed: ' . pg_last_error() . " \n" . $pbxQuery);
	pg_close($dbconn);

    $dbconn = pg_connect("host=cdr dbname=asterisk user=postgres ")
	        or die('Could not connect: ' . pg_last_error());	
	
	$total = 0;
	$count = 0;
	echo '<table><tr><th>pbxID</th><th>Source Count</th><th>Dest Count</th><th>Archive</th></tr>';

	while ($pbxRow = pg_fetch_array($pbxResult, null, PGSQL_ASSOC)) {
		// query cdr
		$sourceQuery = "SELECT source_pbx_id,count(*) FROM cdr
				WHERE \"end\" > (date '" . $reportDate . "' - interval '3 month')
			    AND (source_pbx_id = '" . $pbxRow["id"] . "')
				GROUP BY source_pbx_id";
		$destQuery = "SELECT destination_pbx_id,count(*) FROM cdr
				WHERE \"end\" > (date '" . $reportDate . "' - interval '3 month')
			    AND (destination_pbx_id = '" . $pbxRow["id"] . "')
				GROUP BY destination_pbx_id";
		$sourceResult = pg_query($sourceQuery) or die ('Source Query failed');
		$sourceResult = pg_fetch_row($sourceResult, null, PGSQL_ASSOC);
		$destResult = pg_query($destQuery) or die ('Dest Query failed');
		$destResult = pg_fetch_row($destResult, null, PGSQL_ASSOC);
		$total ++;
		echo '<tr><td>'.$pbxRow['id'].'</td><td>'.$sourceResult['count'].'</td><td>'.$destResult['count'].'</td>';
		if($sourceResult['count'] or $destResult['count']){
			echo '<td></td></tr>';
		}else
		{
			echo "<td><a href='deactivated-cdr-search.php?action=archive&id=".$pbxRow["id"]."'>Archive</td></tr>";
			$count ++;
		}
		//die('test to here');
	}
	echo '</table><br>total looked up: ' . $total;
	echo '<br>total needing archiving: ' .$count;
	pg_free_result($pbxResult);
	pg_close($dbconn);

}

if ($action == 'archive')
{
	if (isset ($_REQUEST["id"]))
	{
		$id = $_REQUEST["id"];	
	    exec('sudo /opt/jive/v4_domain_archive.py '.$id, $archiveOutput, $exitcode);
		if ($exitcode == 0)
		{
			echo 'Archived '.$id.' successfully!!<br>';
			print_r ($archiveOutput);

			//Record event in the event database
			$eventDb = pg_connect("host=rwdb dbname=events user=postgres") or die('Could not connect: '. pg_last_error());
			$description = $guiltyParty." archived ".$id." to Amazon S3";
			$eventID = pg_fetch_row(pg_query($eventDb, "INSERT INTO event(id, description) VALUES(DEFAULT, '" . $description . "') RETURNING id;"));
			pg_query($eventDb, "INSERT INTO event_domain VALUES('" . $eventID['0'] . "', '" .$id. "')");
			pg_close($eventDb); //Close the event DB connection

			//set flag to archived
			//need to do this!!!!!!!!
			$requestTime = strftime('%Y-%m-%d %H:%M:%S');
			$rwdb = pg_connect("host=rwdb dbname=pbxs user=postgres ");
			$flagUpdate = "UPDATE resource_group set archived = '".$requestTime."' WHERE id='".$id."'";
			//echo $flagUpdate;
			pg_query($rwdb, $flagUpdate);
			pg_close($rwdb);


		}else
		{
			echo 'Failed to archive '.$id.' with exit code: '.$exitcode;
		}
	}else
	{
		echo 'no id set';
	}
}

?>

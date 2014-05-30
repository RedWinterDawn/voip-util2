<?php

die("changes in progress");

$guiltyParty = $_SERVER['REMOTE_ADDR'];
$requestTime = strftime('%Y-%m-%d %H:%M:%S');
syslog(LOG_INFO, "application=db-create server=$server action=$action guiltyParty=$guiltyParty");

if (isset($_GET["action"]))
{
	$action = $_GET["action"];
	echo "Request: " . $action . "<br/>\n";
} else
{
	die("nope");
}

$pbxdb="/opt/jive/php/pbxutildb";

if ($action == "Create")
{
	if ($rwdb = pg_connect("host=rwdb user=postgres"))
	{
		$dbquery="CREATE DATABASE util WITH OWNER postgres;";
		$dbresult = pg_query($dbquery);
		   // 	or die('Query failed: ' . pg_last_error());
		echo "$dbresult <br/>\n";
		echo "Created DB <br/>\n";
		pg_free_result($dbresult);
		pg_close($rwdb);
	}else
	{
		echo "Error creating DB " . pg_last_error();
		die();
	}

	if ($rwdb = pg_connect("host=rwdb dbname=util user=postgres"))
	{
		$query = "CREATE TABLE pbxstatus (
			host varchar(255),
			ip varchar(40),
			location varchar(20),
			status varchar(20),
			message varchar(255),
			failgroup varchar(20),
			vmhost varchar(20),
			order integer DEFAULT 1000
		);";
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
		echo "$result <br/>\n";
		echo "Created table <br/>\n";
		pg_free_result($result);
		pg_close($rwdb);
	}else
	{
		echo "Error creating DB " . pg_last_error();
		die();
	}
}

if ($action == "Migrate" && false)
{
	if ($sqdb = sqlite_open($pbxdb,0666,$sqliteerror))
	{
		// $query = "SELECT host,ip,location,status,message,note,failgroup,vmhost FROM pbxstatus;";
		$query = "SELECT * FROM pbxstatus;";
		$sqliteresult = sqlite_query($sqdb,$query) or die('Sqlite query failed: ' . sqlite_last_error());

		if ($rwdb = pg_connect("host=rwdb dbname=util user=postgres"))
		{
			// write each record to psql
			while ($row = sqlite_fetch_array($sqliteresult, SQLITE_ASSOC))
			{
				$pgquery = "INSERT INTO pbxstatus (host,ip,status,message,failgroup,vmhost,location,note) 
					VALUES ('" . $row['host'] . "','" . $row['ip'] . "','" . $row['status'] . "','" . $row['message'] . "','" . $row['failgroup'] . "','" . $row['vmhost'] . "','" . $row['location'] . "','" . $row['note'] . "');";
				echo "$pgquery<br/>";
				$pgresult = pg_query($pgquery);
				echo "$pgresult<br/>";
			}

			pg_free_result($pgresult);
			pg_close($rwdb);
		}else
		{
			echo "Error opening postgres DB " . pg_last_error();
			die();
		}

		// cleanup
		sqlite_close($sqdb);
	}else
	{
		echo "Error opening sqlite DB (really)";
		die();
	}
}

if ($action == "Drop" && false)
{
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres"))
	{
		$result = pg_query($rwutil, "DROP TABLE pbxstatus");
		var_dump(pg_fetch_array($result));
		echo "<br/>\n";

		echo "Dropped table <br/>\n";
		pg_close($rwutil);
	}else
	{
		echo "Error opening DB";
		die();
	}
}

?>

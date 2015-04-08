<link rel='stylesheet' href='stylesheet.css'>
<?php
#include('menu.html');

if (isset($_GET["server"]))
{
	    $IP = $_GET["server"];
} else
{
	    $IP = "10.103.1.90";
}

if (isset($_GET["ip"]))
{
	    $IP = $_GET["ip"];
} else
{
	    $IP = "10.103.1.90";
}

if (isset($_GET["port"]))
{
	    $PORT = $_GET["port"];
} else
{
	    $PORT = "5432";
}

function nowQueryThis($query)	
{
	echo "##############################################################################################################\n";
	echo $query;
	echo "\n##############################################################################################################\n";

	if ($result = pg_query($query))
	{
		while ($row = pg_fetch_array($result, null, PGSQL_ASSOC))
		{
			print_r($row) . "\n";
		}
	} else
	{
		echo "<font color='red'>ERROR: Query failed: </font>" . pg_last_error();
	}

	echo "\n";
	pg_free_result($result);
}

// Connecting, selecting database
$dbconn = pg_connect("host=" . $IP . " port = " . $PORT . " dbname=postgres user=postgres ")
    or die('Could not connect: ' . pg_last_error());

echo "<pre>";

nowQueryThis("select pg_is_in_recovery(),pg_is_xlog_replay_paused(),pg_last_xlog_receive_location(),pg_last_xlog_replay_location(),pg_last_xact_replay_timestamp()");
nowQueryThis("select count(*),mode from pg_locks group by mode");

nowQueryThis("select pg_locks.mode as mode,datid,pg_stat_activity.pid as pid,client_addr,backend_start,query_start,waiting,state,now()-query_start as duration
   from pg_stat_activity
   left join pg_locks on pg_stat_activity.pid = pg_locks.pid
   where pg_locks.mode != 'AccessShareLock'
   order by state_change asc;");

nowQueryThis("select * from pg_stat_replication ;");
nowQueryThis("select * from pg_stat_bgwriter ;");

echo "<font color=orange>\n#####\n This one should fail indicating recovery is in progress:\n</font>\n";
nowQueryThis("select pg_current_xlog_location()");
#nowQueryThis("");

echo "</pre>";

// Closing connection
pg_close($dbconn);
?></body></html>

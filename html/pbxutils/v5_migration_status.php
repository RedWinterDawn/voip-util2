<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');

function pTable($results)
{
  echo "<table border=1>\n";
  echo "<th>Domain</th><th>In Chicago</th><th>Preflight</th><th>Voicemail to V5</th><th>DB Updated</th><th>Added</th><th>Updated</th><th>Completed</th>\n";
  while ($line = pg_fetch_array($results, null, PGSQL_ASSOC)) {
      echo "\t<tr>";
      $count = 0;
      foreach ($line as $col_value) {
          $count ++;
          if ($col_value == "f")
          {
              echo '<td class="yellow">' . "<center>false</center>" . "</td>";
          }else if ($col_value == "t")
          {
              echo '<td class="green">' . "<center>true</center>" . "</td>";
		  }else if ($count > 5)
		  {
		      echo "<td>" . strftime('%m-%d-%Y %T', strtotime($col_value)) ."</td>";
          }else
          {
              echo "<td>" . $col_value . "</td>";
          }
	  }
      echo "</tr>\n";
  }
  echo "</table>\n";
}
// Connecting, selecting database
$dbconn = pg_connect("host=rodb dbname=util user=postgres ")
    or die('Could not connect: ' . pg_last_error());

// Performing SQL query
$query = "SELECT domain, migrate_to_chi, preflight, migrate_vm_to_v5, pbxs_db_changes, added AT TIME ZONE 'UTC-6', updated AT TIME ZONE 'UTC-6' as b, completed AT TIME ZONE 'UTC-6' as c from v5_migration where completed is NULL and migrate_to_chi != 'Failed' order by domain";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

// Printing results in HTML
echo "<h2>Pending:</h2>\n";
pTable($result);

// Free resultset
pg_free_result($result);

// Performing SQL query
$query = "SELECT domain, migrate_to_chi, preflight, migrate_vm_to_v5, pbxs_db_changes, added AT TIME ZONE 'UTC-6', updated AT TIME ZONE 'UTC-6' as b, completed AT TIME ZONE 'UTC-6' as c from v5_migration where migrate_vm_to_v5 = 'Failed' or migrate_to_chi = 'Failed' order by domain";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

// Printing results in HTML
echo "<h2>Failed:</h2>\n";
pTable($result);

// Free resultset
pg_free_result($result);

// Performing SQL query
$query = "SELECT domain, migrate_to_chi, preflight, migrate_vm_to_v5, pbxs_db_changes, added AT TIME ZONE 'UTC-6', updated AT TIME ZONE 'UTC-6' as b, completed AT TIME ZONE 'UTC-6' as c from v5_migration where completed is not NULL order by domain";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

// Printing results in HTML
echo "<h2>Completed:</h2>\n";
pTable($result);

// Free resultset
pg_free_result($result);

// Closing connection
pg_close($dbconn);
?>

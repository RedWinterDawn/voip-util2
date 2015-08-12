<!DOCTYPE html>
<?php
$accesslevel = 3;
include('checksession.php');
?>
<html>
<head>
  <title>Sensitive Customer List</title>
  <link rel='stylesheet' href='stylesheet.css'>

  <script type='text/javascript'>
(function(document) {
	'use strict';

	var LightTableFilter = (function(Arr) {

		var _input;

		function _onInputEvent(e) {
			_input = e.target;
			var tables = document.getElementsByClassName(_input.getAttribute('data-table'));
			Arr.forEach.call(tables, function(table) {
				Arr.forEach.call(table.tBodies, function(tbody) {
					Arr.forEach.call(tbody.rows, _filter);
				});
			});
		}

		function _filter(row) {
			var text = row.textContent.toLowerCase(), val = _input.value.toLowerCase();
			row.style.display = text.indexOf(val) === -1 ? 'none' : 'table-row';
		}

		return {
			init: function() {
				var inputs = document.getElementsByClassName('light-table-filter');
				Arr.forEach.call(inputs, function(input) {
					input.oninput = _onInputEvent;
				});
			}
		};
	})(Array.prototype);

	document.addEventListener('readystatechange', function() {
		if (document.readyState === 'complete') {
			LightTableFilter.init();
		}
	});

})(document);
  </script>
</head>
<body>
<?
include('menu.html');
?>

<h2>Sensitive Customer List</h2>
<p>Add customer</p>
<form action='' method='POST'>
  <input type='text' name='domain' placeholder='Customer Domain' />
  <input type='submit' name='action' value='Add' />
</form>

<br><br>
<input type="search" class="light-table-filter" data-table="sensitives" placeholder="Filter">
<br>
<br>

<? 
if (isset($_REQUEST['action'])) {
  $action = $_REQUEST['action'];
  $domain = $_REQUEST['domain'];
}

if ($action == 'Add') {
  $rwconn = pg_connect("host=rwdb user=postgres dbname=pbxs");
  $query = "UPDATE resource_group SET sensitive = 't' WHERE domain = '$domain'";
  if (pg_query($query)) {
    echo "<p class='green'>Success!</p>";
  } else {
    echo "<p class='red'>Domain not found or unable to update</p>";
  }
  pg_close($rwconn);
}

if ($action == 'Remove') {
  $rwconn = pg_connect("host=rwdb user=postgres dbname=pbxs");
  $query = "UPDATE resource_group SET sensitive = 'f' WHERE domain = '$domain'";
  if (pg_query($query)) {
    echo "<p class='green'>Success!</p>";
  } else {
    echo "<p class='red'>Domain not found or unable to update</p>";
  }
  pg_close($rwconn);

  //Have to sleep or while db replication happens
  sleep(2);
}

$dbconn = pg_connect("host=rodb user=postgres dbname=pbxs") or die ("Failed to connect to the db");
$senseQuery = "SELECT domain, location, assigned_server FROM resource_group WHERE sensitive ORDER BY domain";
$senseResult = pg_query($dbconn, $senseQuery) or die ("Failed to query the db: ".pg_last_error());

?>
<table border=1 class="sensitives table">
<tr>
  <th>Domain</th><th>Location</th><th>Server</th>
</tr>
<?
while ($row = pg_fetch_assoc($senseResult)) {
  $curDomain = $row['domain'];
  $curLocation = $row['location'];
  $curServer = $row['assigned_server'];
  //Random colors available for if you happen to need them
  $color = sprintf("#%06x",rand(0,16777215));
  echo "<tr>
	  <td><a href='domain-info.php?domain=$curDomain'>$curDomain</a></td>
	  <td><a href='pbx-availability.php?display=$curLocation'>$curLocation</a></td>
	  <td><a href='pbx-server-info.php?server=$curServer'>$curServer</a></td>
	  <td>
	    <form action='' method='POST'>
	      <input type='hidden' name='domain' value='$curDomain']}' />
		  <input type='submit' name='action' value='Remove' />
		</form>
	  </td>
	  </tr>";
}
pg_close($dbconn);
?>
</table>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
	<title>ProdTools Main</title>
<?php
$accesslevel = 1;
include('checksession.php');
?>
	<? include 'menu.html'; ?>
	<link rel='stylesheet' href='stylesheet.css'>
    <script type='text/javascript'>
    function setFocus() {
		document.getElementById('search').focus();
	}
	</script>
</head>
<body onload='setFocus()'>
<h2>Welcome to ProdTools</h2>
<div class="checkbox">
	<form action="" method="POST">
		<!--<input type="hidden" name="action" value="search"> -->
		<p>Enter a domain/name to search: </p>
		<p><input id="search" type="text" name="search" placeholder="Jive Domain/Name" /></p>
		<p><input id="exact" class="checkbox" type="checkbox" name="exact"><label for="exact">Exact Search</label></p>
		<p><input type="submit" name="action" value="Search Domain" />
		   <input type="submit" name="action" value="Search Name" />
	</form>
</div>

<?
$action = $_REQUEST['action'];
include('guiltyParty.php');

function search($searchTerm) {
	$dbconn = pg_connect("host=rodb user=postgres dbname=pbxs") or die ('Could not connect to database '.pg_last_error());
	$query = "SELECT name, domain, assigned_server, location, presence_server FROM resource_group WHERE LOWER(domain) like LOWER('$searchTerm')";
	$result = pg_fetch_all(pg_query($dbconn, $query)) or die ('No search results! '.pg_last_error());
	pg_close($dbconn);
	return $result;
}

function searchName($searchTerm) {
	$dbconn = pg_connect("host=rodb user=postgres dbname=pbxs") or die ('Could not connect to database '.pg_last_error());
	$query = "SELECT name, domain, assigned_server, location, presence_server FROM resource_group WHERE LOWER(name) like LOWER('$searchTerm')";
	$result = pg_fetch_all(pg_query($dbconn, $query)) or die ('No search results! '.pg_last_error());
	pg_close($dbconn);
	return $result;
}

function drawTables($domains) {
	echo "<br><br><table border='1'>
		<tr><th>Name</th><th>Domain</th><th>Server</th><th>Location</th><th>Move this Domain</th><th>Events</th><th>Call Reports</th><th>Portal</th></tr>";
	foreach ($domains as $domain) {
		$name = $domain['name'];
		$dom = $domain['domain'];
		$server = $domain['assigned_server'];
		$location = $domain['location'];
		$today = date("Y-m-d");
		echo "<tr>
		<td>$name</td>
		<td><a href='domain-info.php?domain=$dom'>$dom</a></td>
		<td><a href='pbx-server-info.php?server=$server'>$server</a></td>
		<td>$location</td>
		<td><form action='simple-migration.php' method='POST'>
			<input type='hidden' name='action' value='search' />
			<input type='hidden' name='exact' value='true' />
			<input type='hidden' name='search' value='$dom' />
			<input type='submit' value='Go to Migration Page' /> 
			</form></td>
		<td><form action='events-report.php' method='POST'>
			<input type='hidden' name='action' value='eventList' />
			<input type='hidden' name='domain' value='$dom' />
			<input type='submit' value='Go to Events Page' /> 
			</form></td>
		<td><a href='customer-call-report.php?domain=$dom&birthday=$today&action=doSearch'>
			<input type='submit' value='Go to Call Reports' />
			</a></td>
    <td><a href='https://$dom.onjive.com/admin/'>
      <input type='submit' value='Go to Portal' />
      </a></td>
		</tr>";
		 	
	} 
	echo "</table>";
	return;
}
switch ($action) {
    case "Search Domain":
        //Assign the "search" variable
        $search = $_REQUEST["search"];
        //Make sure they're not trying to inject anything
        if (preg_match('/[^a-z\-0-9]/i', $search))
        {
            echo "<p class='red'> Invalid Input! <br/> Use numbers, letters, and dashes only.</p>";
            $action = null;
			break;
        }
        //If the search is exact, leave it alone, otherwise add wildcards
        if (isset($_REQUEST["exact"]))
        {
            //Search term is exact
        } else
        {
            //Search term needs wildcards
            $search = "%".$search."%";
        }
		$domainInfo = search($search);
		drawTables($domainInfo);
        break;
	case "Search Name":
		$search = $_REQUEST["search"];
		if(isset($_REQUEST["exact"]))
		{
			//search is exact
		}else
		{
			$search = "%".$search."%";
		}
		$domainInfo=searchName($search);
		drawTables($domainInfo);
		break;
}
?>
</body>
</html>

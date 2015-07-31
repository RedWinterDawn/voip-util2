<!DOCTYPE html>
<link rel='stylesheet' href='stylesheet.css?v=1.5'>
<link rel='stylesheet' href='toggle.css'>
<script type="text/javascript">
  var xmlhttp = new XMLHttpRequest();
  function toggle(addr) {
    console.log(addr);
    console.log(addr.id);
    if (addr.checked) {
      xmlhttp.open("GET","failable_updater.php?addr="+addr.id+"&failable=t", true);
      xmlhttp.send();
      console.log("Set to true");
    } else {
      xmlhttp.open("GET","failable_updater.php?addr="+addr.id+"&failable=f", true);
      xmlhttp.send();
      console.log("Set to false");
    }
  }
</script>
<script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
            <script type="text/javascript" src="js/jquery-ui-1.7.2.custom.min.js"></script>
<script type="text/javascript" src="js/icalls.js"></script>
<?php
include('guiltyParty.php');
$requestTime = strftime('%Y-%m-%d %H:%M:%S');

if (isset($_GET["server"]))
{
	$server = $_GET["server"];
} else
{
	$server = $guiltyParty;
}

if (isset($_GET["action"]))
{
    $action = $_GET["action"];
} else
{
    $action = "ListStatus";
}

if (isset($_GET["SetMessage"]))
{
	$action = "AutoCleanComplete";
}

if (isset($_GET["display"]))
{
	$display = $_GET['display'];
} else {
	$display = "101";
}
//Don't display the message for auto-clean because its output is sent to a pbx
if ($action != "AutoCleanComplete") {
	include('menu.html');
}

if ($action == "AutoCleanComplete") {
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		syslog(LOG_INFO, "application=pbx-availability server=$server action=SetClean newState=clean guiltyParty=$guiltyParty");
		pg_query($rwutil, "UPDATE pbxstatus SET status='standby' WHERE ip='$server' ");
		pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " scripted cleanup reported complete' WHERE ip='" . $server . "'");
		pg_query($rwutil, "UPDATE pbxstatus SET cleanme='f' WHERE ip='" . $server . "'");
		echo "$server now clean";
	}else
	{
		echo "Error opening DB (rwdb.util) " . pg_last_error();
		die();
	}
}

if ($action == "Add")
{
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($rwutil, "INSERT INTO pbxstatus (host, ip, status, failgroup) VALUES ('" . $server . "','" . $server . "','NEW', '0')");

		echo "$server now NEW";
		syslog(LOG_INFO, "application=pbx-availability server=$server action=Add newState=NEW guiltyParty=$guiltyParty");
	}else
	{
		echo "Error opening DB (rwdb.util) " . pg_last_error();
		die();
	}

	$action = "ReturnToSender";
}

if ($action == "SetActive")
{
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($rwutil, "UPDATE pbxstatus SET status='active' WHERE ip='$server' ");
		echo "$server now active";
		syslog(LOG_INFO, "application=pbx-availability server=$server action=SetActive newState=active guiltyParty=$guiltyParty");

		pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " set active by " . $guiltyParty . "' WHERE ip='" . $server . "'");
	}else
	{
		echo "Error opening DB (rwdb.util) " . pg_last_error();
		die();
	}

	$action = "ReturnToSender";
}

if ($action == "SetStandby")
{
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($rwutil, "UPDATE pbxstatus SET status='standby' WHERE ip='$server' ");

		echo "$server now standby";
		syslog(LOG_INFO, "application=pbx-availability server=$server action=SetStandby newState=standby guiltyParty=$guiltyParty");

		pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " set standby by " . $guiltyParty . "' WHERE ip='" . $server . "'");
	}else
	{
		echo "Error opening DB (rwdb.util) " . pg_last_error();
		die();
	}

	$action = "ReturnToSender";
}

if ($action == "Delete")
{
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($rwutil, "DELETE FROM pbxstatus WHERE ip='$server' ");

		echo "$server deleted";
		syslog(LOG_INFO, "application=pbx-availability server=$server action=Delete newState=deleted guiltyParty=$guiltyParty");
	}else
	{
		echo "Error opening DB (rwdb.util)";
		die();
	}

	$action = "ReturnToSender";
}

if ($action == "SetMigrate")
{
    if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
    {   
        pg_query($rwutil, "UPDATE pbxstatus SET status='migrating' WHERE ip='$server' ");

        echo "$server is migrating...";
        syslog(LOG_INFO, "application=pbx-availability server=$server action=SetMigrate newState=migrating guiltyParty=$guiltyParty");

        pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " set migrating by " . $guiltyParty . "' WHERE ip='" . $server . "'");
    }else
    {   
        echo "Error opening DB (rwdb.util)";
        die();
    }   

    $action = "ReturnToSender";
}

if ($action == "SetRollback")
{
    if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
    {   
        pg_query($rwutil, "UPDATE pbxstatus SET status='rollback' WHERE ip='$server' ");

        echo "$server is setup for rollback...";
        syslog(LOG_INFO, "application=pbx-availability server=$server action=SetRollback newState=rollback guiltyParty=$guiltyParty");

        pg_query($rwutil, "UPDATE pbxstatus SET message='" . $requestTime . " set rollback by " . $guiltyParty . "' WHERE ip='" . $server . "'");
    }else
    {   
        echo "Error opening DB (rwdb.util)";
        die();
    }   

    $action = "ReturnToSender";
}

if ($action == "SetSpecial")
{
	if ($rwutil = pg_connect("host=rwdb dbname=util user=postgres "))
	{
		pg_query($db, "UPDATE pbxstatus SET status='special' WHERE ip='$server' ");

		echo "$server now special";
		syslog(LOG_INFO, "application=pbx-availability server=$server action=SetSpecial newState=special guiltyParty=$guiltyParty");
	}else
	{
		echo "Error opening DB (rwdb.util)";
		die();
	}

	$action = "ReturnToSender";
}

if ($action == "ReturnToSender")
{
	echo '<br/><br/><a href="pbx-availability.php?display='.$display.'">Return to list</a><br/>';
}

if ($action == "ListStatus")
{
	if ($routil = pg_connect("host=rodb dbname=util user=postgres "))
	{
		$dirties = pg_query($routil, "SELECT DISTINCT location FROM pbxstatus WHERE status = 'dirty'");
		echo "<style>";
		while ($d = pg_fetch_assoc($dirties)) {
			echo "#${d['location']}-link { color: red; }";
		}
		echo "</style>\n";
    echo "<script>\n";
    echo "function setActive (location) {
              var elm = document.getElementById(location+'-link');
              elm.className = elm.className + ' active';
          }";
    echo "</script>\n";
    echo "<body>";
    $siteStatus = pg_fetch_assoc(pg_query($routil, "SELECT universal_failable FROM sitestatus LIMIT 1;"));
    $togglePosn = '';
    if ($siteStatus['universal_failable'] == 't') {
      $togglePosn = 'checked';
    }
    echo '<h2>PBX Availability</h2>';
    echo '<table>
      <tr><td>Abandons for all of v4: </td><td><div class="onoffswitch">
    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="all" '.$togglePosn.' onclick="toggle(this)">
    <label class="onoffswitch-label" for="all">
    <span class="onoffswitch-inner"></span>
    <span class="onoffswitch-switch"></span>
    </label>
    </div></td></tr></table>';	
		// query status table for all hosts
    $result = pg_query($routil, "SELECT failgroup, location, vmhost, host, ip, status, color, load, pbx.failable, message, occupant 
      FROM pbxstatus pbx
      INNER JOIN status ON pbx.status = status.name 
      WHERE failgroup = '$display' 
      ORDER BY failgroup,status.displayorder,ip limit 1000;");

		// Menu with red labels where the dirty pbxs are
		include('pbx-menu.html'); 

    $site = array('101' => 'Chicago Legacy', '117' => 'Provo', '119' => 'L.A.', '122a' => 'Atlanta 2', '120' => 'New York', '122' => 'Atlanta', '123' => 'Spokane', '125' => 'Chicago (ORD)','v5' => 'v5');
    $site_id = array('101' => 'chicago-legacy', '117' => 'pvu', '119' => 'lax', '120' => 'nyc', '122' => 'atl', '123' => 'geg', '125' => 'ord','v5' => 'v5', '122a' => 'atlantaA');

		echo "<table><tr><td><h2>$site[$display]</h2><td>";
    $siteStatus = pg_fetch_assoc(pg_query($routil, "SELECT failable FROM sitestatus WHERE site_id = '".$site_id[$display]."';"));
    $togglePosn = '';
    if ($siteStatus['failable'] == 't') {
      $togglePosn = 'checked';
    }
		echo '<td>&nbsp;&nbsp;Abandons for this site:</td><td><div class="onoffswitch">
    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="'.$site_id[$display].'" '.$togglePosn.' onclick="toggle(this)">
    <label class="onoffswitch-label" for="'.$site_id[$display].'">
    <span class="onoffswitch-inner"></span>
    <span class="onoffswitch-switch"></span>
    </label>
    </div></td></tr></table>';	
		echo "<table border=1>";
		echo "<tr><th>failgroup</th><th>load</th><th onclick=\"getCalls();\">calls</th><th>ip</th><th>status</th><th>activate</th><th>standby</th><th>abandon ship</th><th>failable</th><th>message</th></tr>\n";
    $oneTimer = true;
		while ($row = pg_fetch_array($result, null, PGSQL_ASSOC))
		{
      if ($oneTimer) {
        echo '<body onload="setActive(\''.$row['location'].'\')"></body>';
        $oneTimer = false;
      }
			$showControls = false;
			$load = round($row['load'] / 140000,0);
			$color = 'green';
			if ($display == "101" OR $display == "125") {	
				if ($load > 85) { $color = 'yellow'; }
				if ($load > 95) { $color = 'red'; }
			} else {
				if ($load > 60) { $color = 'yellow'; }
				if ($load > 69) { $color = 'red'; }
			}

      $tempip = preg_replace('/[.,]/', '', $row['ip']);
			echo "<tr>
				<td class='group".$row['failgroup']."'>" . $row['failgroup'] . "</td>
				<td class='$color'>" . $load . "%</td>
        <td id='calls".$tempip."'></td>
				<td><a href='pbx-server-info.php?server=" . $row['ip'] . "'>" . $row['ip'] . "</a></td>
        <td><div style='color:".$row['color']."'>". $row['status'];
      if ($row['status'] == "special" || $row['status'] == "nightly") { echo " [".$row['occupant']."]";}
        echo "</div></td>";
			if ($row['status'] == "standby") { $showControls = true; }
			if ($row['status'] == "moving") { $showControls = true; }
			
			if ($row['status'] == "active" || $row['status'] == "nightly"){
				echo "<td>-</td>";
				echo "<td><a href=\"pbx-availability.php?action=SetStandby&server=" . $row['ip'] . "&display=$display\">set standby</a></td>";
				echo "<td><a href=\"http://10.101.8.1/pbxutils/pbx-sip-failure.php?server=" . $row['ip'] . '" target="_blank">abandon ship</a></td>';
			} else if ($row['status'] == "clean") {
				echo "<td>-</td>";
				echo "<td><a href=\"pbx-availability.php?action=SetStandby&server=" . $row['ip'] . "&display=$display\">set standby</a></td>";
				echo "<td>-</td>";
			} else if ($row['status'] == "dirty") {
				echo "<td><a href=\"pbx-availability.php?action=SetActive&server=" . $row['ip'] . "&display=$display\">set active</a></td>";
				echo "<td><a href=\"pbx-availability.php?action=SetStandby&server=" . $row['ip'] . "&display=$display\">set standby</a></td>";
				#echo "<td><a href=\"http://10.101.8.1/pbxutils/clean.php?server=" . $row['host'] . "&display=$display\">clean me</a></td>";
				echo "<td><a href=\"clean.php?server=" . $row['host'] . "&display=$display\">clean me</a></td>";
			} else if ($showControls) {
				echo "<td><a href=\"pbx-availability.php?action=SetActive&server=" . $row['ip'] . "&display=$display\">set active</a></td>";
				echo "<td><a href=\"pbx-availability.php?action=SetStandby&server=" . $row['ip'] . "&display=$display\">set standby</a></td>";
				echo "<td>-</td>";
			} else {
				echo "<td>-</td>";
				echo "<td>-</td>";
				echo "<td>-</td>";
			}
      $togglePosn = ''; 
      if ($row['failable'] == 't') {
        $togglePosn = 'checked'; 
      }
		  echo '<td><div class="onoffswitch">
    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="'.$row['ip'].'" '.$togglePosn.' onclick="toggle(this)">
    <label class="onoffswitch-label" for="'.$row['ip'].'">
    <span class="onoffswitch-inner"></span>
    <span class="onoffswitch-switch"></span>
    </label>
    </div>
    </td>';	
			echo "<td>" . $row['message'] . "</td>";
			echo "</tr>\n";
		}
		echo "</table><br/>\n";
	
		echo "</body>";
	}else
	{
		echo "Error opening DB (rodb.util)";
		die();
	}
}

?>

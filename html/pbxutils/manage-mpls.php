<?php
echo "<html><head><title>Insert MPLS</title>
<style type='text/css'> 
.red {color: red;}
.green {color: green;}
.active {background: #CCFFCC;}
.standby {background: #FFFFCC;}
.graveyard {background: #CCCCCC;}
.dirty {background: #FFCCCC;}
.moving {background: #FFCCCC;}
.special {background: #CCCCFF;}
.NEW {}
#topleft {width: 60%; float: left;}
#topright {width: 40%; float: right;}
</style>
<link rel='stylesheet' href='stylesheet.css'>";

echo"</head><body>";


$gobutton = "Insert";
$killbutton = "Destroy things!";
$requestTime = strftime('%Y-%m-%d %H:%M:%S');

$dbconn = pg_connect("host=db dbname=util user=postgres ") or die('Could not connect to "util" database: ' . pg_last_error());

$guiltyParty = $_SERVER['REMOTE_ADDR'];
if (isset($_REQUEST['action']))
{
	$action = $_REQUEST['action'];
	$domain = $_REQUEST['domain'];
	$order = $_REQUEST['order'];
	$customer = $_REQUEST['customer'];
	$location = $_REQUEST['location'];
	$carrier = $_REQUEST['carrier'];
    $lec = $_REQUEST['lec'];
    $lan = $_REQUEST['lan'];
    $wan = $_REQUEST['wan'];

//if (!filter_var($pbx, FILTER_VALIDATE_IP)) // <-- This is the easy way, but we have php 5.1 so ... 
if (($lan != "") && (!preg_match('&^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/[0-9]{1,2}$&', $lan)))
{
	    echo "<p class='red'> Invalid Input for LAN IP! <br/> Use numbers and periods only (valid LAN IP address required).</p>";
		echo "<p>You gave the following: ".$lan."</p>";
		echo "<hr>";
		$action = null;
}
if (($wan != "") && (!preg_match('&^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/[0-9]{1,2}$&', $wan)))
{
	    echo "<p class='red'> Invalid Input for WAN IP! <br/> Use numbers and periods only (valid WAN IP address required).</p>";
		echo "<p>You gave the following: ".$wan."</p>";
		echo "<hr>";
		$action = null;
}
if (preg_match('/[^0-9]/i', $order))
{
	    echo "<p class='red'> Invalid Input for Order#! <br/> Use numbers only.</p>";
		echo "<p>You gave the following: ".$order."</p>";
		echo "<hr>";
		$action = null;
}
if (preg_match('/[^0-9]/i', $customer))
{
	    echo "<p class='red'> Invalid Input for Customer#! <br/> Use numbers only.</p>";
		echo "<p>You gave the following: ".$customer."</p>";
		echo "<hr>";
		$action = null;
}
//if (preg_match('/[^a-z]/i', $domain))
//{
//	    echo "<p class='red'> Invalid Input for Domain! <br/> Use letters only.</p>";
//		echo "<p>You gave the following: ".$domain."</p>";
//		echo "<hr>";
//		$action = null;
//}
//if (preg_match('/[^a-z_\-0-9]/i', $location))
//{
//	    echo "<p class='red'> Invalid Input for Location! <br/> Use letters, numbers, underscores, and hyphens only.</p>";
//		echo "<p>You gave the following: ".$location."</p>";
//		echo "<hr>";
//		$action = null;
//}
if (preg_match('/[^a-z_\-0-9]/i', $name))
{
	    echo "<p class='red'> Invalid Input for Name! <br/> Use letters, numbers, underscores, and hyphens only.</p>";
		echo "<p>You gave the following: ".$santa." and ".$fgroup."</p>";
		echo "<hr>";
		$santa = null;
		$status = null;
		$action = null;
		$site = null;
}
if ($action == $gobutton)
{
	if ($domain != "")
	{
		//Check site first. If it's set, assume site. If the user is adding a new server
		//the "action" variable will get overwritten later anyway. 
		if ($site != "")
		{
			$action = "site";
		}
		if ($status != "")
		{
			$action = "update";
		}
		if ($name != "")
		{
			$action = "name";
		}
		//If both status and location were set, "action" would have been set at least twice by now
		//So we'll check to see if they're both set and update "action" if they are.
		if (($order != "") && ($customer != "") && ($location != ""))
		{
			$action = "add";
		}
		//If we have reached this point without setting action to anything, then assume "terminate"
		//if ($action == $gobutton)
		//{
		//	$action = "remove";
		//}
	} else
	{
		echo '<p class="red">Um... ALL of the options require an IP address. Please enter one.</p>';
	}
}
if ($action == $killbutton)
{
	$action = "terminate";
}
}
//if (isset($action))
//{
	//syslog(LOG_INFO, $guiltyParty." performed action: ".$action.", with (".$santa.", ".$status.", ".$name.", ".$site.") using the manage-pbxs.php script");
//}
switch ($action)
{
	case "add":
		$rodb = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect to "pbxs" database: ' . pg_last_error());
		$name = pg_fetch_row(pg_query($rodb, "SELECT name FROM resource_group WHERE domain='".$domain."'")) or die('Could not find domain: '.$domain. ' '.pg_last_error());
		pg_close($rodb);

		$insert = "INSERT INTO mpls VALUES ('".$carrier."', ".$customer.", '".$domain."', DEFAULT, '".$lec."', '".$location."', '".$name[0]."', ".$order.", '".$wan."', '".$lan."');";
		pg_query($dbconn, $insert) or die('Could not add to mpls! ' . pg_last_error());
		 
		// echo what was added need to do 
		echo "Inserted the following into mpls:<br>";
		echo "Domain: ".$domain."<br>";
		echo "Order#: ".$order."<br>";
		echo "Customer#: ".$customer."<br>";
		echo "Location: ".$location."<br>";
		echo "Carrier Circuit ID: ".$carrier."<br>";
		echo "LEC Circuit ID: ".$lec."<br>";
		echo "Public LAN IPs: ".$lan."<br>";
		echo "Private WAN IPs: ".$wan."<br>";
		echo "<hr>";
		break;
}
 
$servers = "SELECT * FROM mpls ORDER BY domain;";
$serverResults = pg_query($dbconn, $servers) or die ("Available mpls Search Failed: ".pg_last_error());
$serverResults = pg_fetch_all($serverResults) or die (" Fetch Failed: ".pg_last_error());
pg_close($dbconn);
///echo '<div id="topleft">';
echo '<a href="index.php">Back to pbxutils</a>';

echo "<table><tr><th>Domain</th><th>Order#</th><th>Customer#</th><th>Location</th><th>Carrier Circuit ID</th><th>LEC Circuit ID</th><th>Public LAN IPs</th><th>Private WAN IPs</th></tr>";
echo "<form action='' method='POST'><tr><td><input type='text' name='domain'></td>";
echo "<td><input type='text' name='order'></td>";
echo "<td><input type='text' name='customer'></td>";
echo "<td><input type='text' name='location'></td>";
echo "<td><input type='text' name='carrier'></td>";
echo "<td><input type='text' name='lec'></td>";
echo "<td><input type='text' name='lan'></td>";
echo "<td><input type='text' name='wan'></td></tr>";
echo '<tr><td colspan="8" align="center"><input type="submit" name="action" value="'.$gobutton.'"></td></tr></table>';
//echo '</div>';
sleep(1);
echo "<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br><table><tr><th colspan='9'>MPLS Circuits</th></tr><tr><th>Customer Name</th><th>Domain</th><th>Order#</th><th>Customer#</th><th>Location</th><th>Carrier Circuit ID</th><th>LEC Circuit ID</th><th>Public LAN IPs</th><th>Private WAN IPs</th></tr>";
foreach ($serverResults as $mpls)
{
	echo "<tr><td>".$mpls['name']."</td><td>".$mpls['domain']."</td><td>".$mpls['order_number']."</td><td>".$mpls['customer']."</td><td>".$mpls['location']."</td><td>".$mpls['carrier_circuit_id']."</td><td>".$mpls['lec_circuit_id']."</td><td>".$mpls['public_lan_ips']."</td><td>".$mpls['private_wan_ips']."</td></tr>";
}
echo "</table>";
echo "</body></html>";
?>

<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');
include('guiltyParty.php');

$requestTime = strftime('%Y-%m-%d %H:%M:%S');

//get all args

if (isset($_REQUEST["action"]))
{
	$args['action']=$_REQUEST["action"];
}else
{
	$args['action']="list";
}
if (isset($_REQUEST["domain"]))
{
	$args['domain']=$_REQUEST["domain"];
}
if (isset($_REQUEST["id"]))
{
	$args['id']=$_REQUEST["id"];
}
if (isset($_REQUEST["order"]))
{
	$args['order']=$_REQUEST["order"];
}
if (isset($_REQUEST["customer"]))
{
	$args['customer']=$_REQUEST["customer"];
}
if (isset($_REQUEST["location"]))
{
	$args['location']=$_REQUEST["location"];
}
if (isset($_REQUEST["carrier"]))
{
	$args['carrier']=$_REQUEST["carrier"];
}
if (isset($_REQUEST["lec"]))
{
	$args['lec']=$_REQUEST["lec"];
}
if (isset($_REQUEST["lan"]))
{
	$args['lan']=$_REQUEST["lan"];
}
if (isset($_REQUEST["wan"]))
{
	$args['wan']=$_REQUEST["wan"];
}
if (isset($_REQUEST["pre"]))
{
	$args['pre']=$_REQUEST["pre"];
}
if ($args['action'] == "update")
{
	//database upadte goes here//
	$dbconn = pg_connect("host=rwdb dbname=util user=postgres ") or die('Could not connect to utildb' . pg_last_error());
	$update = "UPDATE mpls SET order_number=".$args['order'].", customer=".$args['customer'].", location='".$args['location']."', carrier_circuit_id='".$args['carrier']."', lec_circuit_id='".$args['lec']."', public_lan_ips='".$args['lan']."', private_wan_ips='".$args['wan']."' WHERE id=".$args['id'];
	pg_query($dbconn, $update);
	pg_close($dbconn);
	echo "Update successful!";
	sleep(2);
	//database upadte goes here//
	$args['action'] = "info";
}
if ($args['action'] == "insert")
{
	$rodb = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect to "pbxs" database: ' . pg_last_error());
	$name = pg_fetch_row(pg_query($rodb, "SELECT name FROM resource_group WHERE domain='".$args['domain']."'")) or die('Could not find domain: '.$domain. ' '.pg_last_error());
	pg_close($rodb);

	$dbconn = pg_connect("host=rwdb dbname=util user=postgres ") or die('Could not connect to rwdb database: ' . pg_last_error());

	$insert = "INSERT INTO mpls VALUES ('".$args['carrier']."', ".$args['customer'].", '".$args['domain']."', DEFAULT, '".$args['lec']."', '".$args['location']."', '".$name[0]."', ".$args['order'].", '".$args['wan']."', '".$args['lan']."');";
	pg_query($dbconn, $insert) or die('Could not add to mpls! ' . pg_last_error());
	pg_close($dbconn);
	echo "Insert successful!";
	sleep(2);
	$args['action'] == "info";	
}
if ($args['action'] == "validate")
{
	$invalid = false;

	if (preg_match('/[^0-9]/i', $args['order']) OR $args['order']=='')
	{
	    echo "<p class='red'> Invalid Input for Order#! <br/> Use numbers only.</p><hr>";
		$invalid = true;
	}
	if (preg_match('/[^0-9]/i', $args['customer']) OR $args['customer']=='')
	{
	    echo "<p class='red'> Invalid Input for Customer#! <br/> Use numbers only.</p><hr>";
		$invalid = true;
	}
	if (!preg_match('&^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/[0-9]{1,2}$&', $args['lan']))
	{
		echo "<p class='red'> Invalid Input for LAN IP! <br/> Use numbers and periods only (valid LAN IP address required).</p><hr>";
		$invalid = true;
	}
	if (!preg_match('&^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/[0-9]{1,2}$&', $args['wan']))
	{
		echo "<p class='red'> Invalid Input for WAN IP! <br/> Use numbers and periods only (valid LAN IP address required).</p><hr>";
		$invalid = true;
	}
	if (preg_match('/[\'\"]/i', $args['location']) OR $args['location']=='')
	{
		echo "<p class='red'> Invalid Input for Location!</p><hr>";
		$invalid = true;
	}
	if (preg_match('/[\'\"]/i', $args['carrier']))
	{
		echo "<p class='red'> Invalid Input for Carrier Circuit ID! <br/> Use commas (no spaces) to seperate multiple ids.</p><hr>";
		$invalid = true;
	}
	if (preg_match('/[\'\"]/i', $args['lec']))
	{
		echo "<p class='red'> Invalid Input for LEC Circuit ID! <br/> Use commas (no spaces) to seperate multiple ids.</p><hr>";
		$invalid = true;
	}
	if (!$invalid)
	{
		if($args['pre']=='edit')
		{
			$args['action'] = 'confirmedit';
		}else
		{
			$args['action'] = 'confirmadd';
		}
	}else
	{
		$args['action'] = $args['pre'];
	}
}
if ($args['action'] == "info")
{
	$dbconn = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connect to database' . pg_last_error());
    $mplsQ = "SELECT * FROM mpls WHERE domain='".$args['domain']."';";
	$mplsArray = pg_fetch_all(pg_query($dbconn, $mplsQ));
	foreach($mplsArray as $mpls)
	{
      	$carrier = explode( ',' , $mpls['carrier_circuit_id']);
     	$lec = explode( ',' , $mpls['lec_circuit_id']);
   	    echo "<table><tr><th colspan='2'>".$mpls['name']." MPLS Circuits</th></tr>
			<tr><th>Domain</th><td>".$mpls['domain']."</td></tr>
       	    <tr><th>Order #</th><td>".$mpls['order_number']."</td></tr>
       	    <tr><th>Customer #</th><td>".$mpls['customer']."</td></tr>
       	    <tr><th>Location</th><td>".$mpls['location']."</td></tr>
       	    <tr><th>Carrier Circuit ID</th><td>";
   		foreach ($carrier as $id)
        {
            echo $id."<br>";
        }
		echo "</td></tr><tr><th>LEC Circuit ID</th><td>";
		foreach ($lec as $id)
		{
			echo $id."<br>";
		}
		echo "</td></tr>
			<tr><th>Public LAN IPs</th><td>".$mpls['public_lan_ips']."</td></tr>
			<tr><th>Private WAN IPs</th><td>".$mpls['private_wan_ips']."</td></tr>
			<form action='' method='POST'>
			</table>
			<input type='hidden' name='action' value='edit'>
			<input type='hidden' name='id' value='".$mpls['id']."'>
			<input type='submit' value='Edit'><br>
			</form><br>";
	}
	pg_close($dbconn);
}
if ($args['action'] == "edit")
{
		$dbconn = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connect to database' . pg_last_error());
		$mplsQ = "SELECT * FROM mpls WHERE id='".$args['id']."';";
		$mpls = pg_fetch_row(pg_query($dbconn, $mplsQ));
		echo "<table><tr><th colspan='2'>".$mpls['6']." MPLS Circuits</th></tr>
			<tr><th>Domain</th><td>".$mpls['2']."</td></tr>
			<form action='' method='POST'>
			<tr><th>Order #</th><td><input type='text' name='order' value='".$mpls['7']."' size=150/></td></tr>
			<tr><th>Customer #</th><td><input type='text' name='customer' value='".$mpls['1']."' size=150/></td></tr>
			<tr><th>Location</th><td><input type='text' name='location' value='".$mpls['5']."' size=150/></td></tr>
			<tr><th>Carrier Circuit ID</th><td><input type='text' name='carrier' value='".$mpls['0']."' size=150/></td></tr>
			<tr><th>LEC Circuit ID</th><td><input type='text' name='lec' value='".$mpls['4']."' size=150/></td></tr>
			<tr><th>Public LAN IPs</th><td><input type='text' name='lan' value='".$mpls['9']."' size=150/></td></tr>
			<tr><th>Private WAN IPs</th><td><input type='text' name='wan' value='".$mpls['8']."' size=150/></td></tr>
			<input type='hidden' name='action' value='validate'>
			<input type='hidden' name='pre' value='edit'>
			<input type='hidden' name='id' value='".$mpls['3']."'>
			<tr><th><a href='mpls-info.php?action=info&domain=".$mpls['2']."'>Cancel</a></th>
			<th><input type='submit' value='Save' name='confirm'></th></tr></table>
			</form>";
		pg_close($db_conn);
}
if ($args['action'] == "confirmedit")
{
        $dbconn = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connect to database' . pg_last_error());
        $mplsQ = "SELECT * FROM mpls WHERE id='".$args['id']."';";
		$mpls = pg_fetch_row(pg_query($dbconn, $mplsQ));
       	$carrierA = explode( ',' , $args['carrier']);
      	$carrier0 = explode( ',' , $mpls['0']);
    	$lec0 = explode( ',' , $mpls['4']);
     	$lecA = explode( ',' , $args['lec']);
		pg_close($dbconn);
		echo "<p class='red'>Please confirm your changes.</p>
			<table><tr><th colspan='3'>".$mpls['6']." MPLS Circuits</th></tr>
			<tr><th></th><th>Current</th><th>Changes</th></tr>
			<tr><th>Domain</th><td>".$mpls['2']."</td><td>".$mpls['2']."</td></tr>
       	    <tr><th>Order #</th><td>".$mpls['7']."</td><td>".$args['order']."</td></tr>
       	    <tr><th>Customer #</th><td>".$mpls['1']."</td><td>".$args['customer']."</td></tr>
       	    <tr><th>Location</th><td>".$mpls['5']."</td><td>".$args['location']."</td></tr>
       	    <tr><th>Carrier Circuit ID</th><td>";
   		foreach ($carrier0 as $value)
        {
            echo $value."<br>";
        }
		echo "</td><td>";
   		foreach ($carrierA as $value)
        {
            echo $value."<br>";
		}
		echo "</td></tr><tr><th>LEC Circuit ID</th><td>";
		foreach ($lec0 as $value)
		{
			echo $value."<br>";
		}
		echo "</td><td>";
		foreach ($lecA as $value)
		{
			echo $value."<br>";
		}
		echo "</td></tr>
			<tr><th>Public LAN IPs</th><td>".$mpls['9']."</td><td>".$args['lan']."</td></tr>
			<tr><th>Private WAN IPs</th><td>".$mpls['8']."</td><td>".$args['wan']."</td></tr>
			<tr><th></th>
			<form action='' method='POST'>
			<input type='hidden' name='action' value='edit'>
			<input type='hidden' name='id' value='".$args['id']."'>
			<th><input type='submit' value='Keep Current' /></th>
			</form>
			<form action='' method='POST'>
			<input type='hidden' name='domain' value='".$mpls['2']."'/>
			<input type='hidden' name='order' value='".$args['order']."'/>
			<input type='hidden' name='customer' value='".$args['customer']."'/>
			<input type='hidden' name='location' value='".$args['location']."'/>
			<input type='hidden' name='carrier' value='".$args['carrier']."'/>
			<input type='hidden' name='lec' value='".$args['lec']."'/>
			<input type='hidden' name='lan' value='".$args['lan']."'/>
			<input type='hidden' name='wan' value='".$args['wan']."'/>
			<input type='hidden' name='action' value='update'>
			<input type='hidden' name='id' value='".$args['id']."'>
			<th><input type='submit' value='Save Changes' name='confirm'></th></tr>
			</table>";
	
}
if ($args['action'] == 'confirmadd')
{
		echo "<p class='red'>Please confirm entry.</p>
			<table><tr><th colspan='2'>MPLS Circuit entry</th></tr>
			<tr><th>Domain</th><td>".$args['domain']."</td></tr>
       	    <tr><th>Order #</th><td>".$args['order']."</td></tr>
       	    <tr><th>Customer #</th><td>".$args['customer']."</td></tr>
       	    <tr><th>Location</th><td>".$args['location']."</td></tr>
       	    <tr><th>Carrier Circuit ID</th><td>";
   		foreach ($carrierA as $value)
        {
            echo $value."<br>";
		}
		echo "</td></tr><tr><th>LEC Circuit ID</th><td>";
		foreach ($lecA as $value)
		{
			echo $value."<br>";
		}
		echo "</td></tr>
			<tr><th>Public LAN IPs</th><td>".$args['lan']."</td></tr>
			<tr><th>Private WAN IPs</th><td>".$args['wan']."</td></tr>
			<tr><th></th>
			<form action='' method='POST'>
			<input type='hidden' name='domain' value='".$args['domain']."'/>
			<input type='hidden' name='order' value='".$args['order']."'/>
			<input type='hidden' name='customer' value='".$args['customer']."'/>
			<input type='hidden' name='location' value='".$args['location']."'/>
			<input type='hidden' name='carrier' value='".$args['carrier']."'/>
			<input type='hidden' name='lec' value='".$args['lec']."'/>
			<input type='hidden' name='lan' value='".$args['lan']."'/>
			<input type='hidden' name='wan' value='".$args['wan']."'/>
			<input type='hidden' name='action' value='insert'>
			<input type='hidden' name='id' value='".$args['id']."'>
			<th><input type='submit' value='Add Record' name='confirm'></th></tr>
			</table>";
}
if ($args['action'] == "list")
{
	$dbconn = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connect to database' . pg_last_error());
    $mplsQ = "SELECT domain, name, location FROM mpls ORDER BY name;";
	$mpls = pg_fetch_all(pg_query($dbconn, $mplsQ));
	pg_close($dbconn);
	echo "<table><th colspan='3'>MPLS CIRCUITS</th></tr>
		<tr><th>Domain</th><th>Name</th><th>Location</th></tr>";
	foreach($mpls as $cir)
	{
		echo "<tr><td>".$cir['domain']."</td><td>".$cir['name']."</td><td><a href='mpls-info.php?action=info&domain=".$cir['domain']."'>".$cir['location']."</a></td></tr>";
	}
	echo "</table>";
}
if ($args['action'] == "add")
{
	echo "<table><tr><th colspan='2'>Add MPLS Circuit</th></tr>
		<form action='' method='POST'>
		<tr><th>Domain</th><td><input type='text' name='domain' value='".$args['domain']."' size=150/></td></tr>
		<tr><th>Order #</th><td><input type='text' name='order' value='".$args['order']."' size=150/></td></tr>
		<tr><th>Customer #</th><td><input type='text' name='customer' value='".$args['customer']."' size=150/></td></tr>
		<tr><th>Location</th><td><input type='text' name='location' value='".$args['location']."' size=150/></td></tr>
		<tr><th>Carrier Circuit ID</th><td><input type='text' name='carrier' value='".$args['carrier']."' size=150/></td></tr>
		<tr><th>LEC Circuit ID</th><td><input type='text' name='lec' value='".$args['lec']."' size=150/></td></tr>
		<tr><th>Public LAN IPs</th><td><input type='text' name='lan' value='".$args['lan']."' size=150/></td></tr>
		<tr><th>Private WAN IPs</th><td><input type='text' name='wan' value='".$args['wan']."' size=150/></td></tr>
		<input type='hidden' name='action' value='validate'>
		<input type='hidden' name='pre' value='add'>
		<th colspan='2'><input type='submit' value='Save' name='confirm'></th></tr></table>
		</form>";
		
}
?>

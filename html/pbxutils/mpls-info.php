<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');

$guiltyParty = $_SERVER['REMOTE_ADDR'];
$requestTime = strftime('%Y-%m-%d %H:%M:%S');

if (isset($_REQUEST["action"]))
{
    $action=$_REQUEST["action"];
}else
{
    $action = "list";
}

if ($action == "info")
{
    if(isset($_REQUEST["domain"]))
    {
        $domain=$_REQUEST["domain"];
        
        $dbconn = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connect to database' . pg_last_error());
        $mplsQ = "SELECT * FROM mpls WHERE domain='".$domain."';";
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
				</table>";
		}
	
		pg_close($dbconn);
	        
	}else
    {
        echo "No domain set!";
    }
}
if ($action == "edit")
{
	if(isset($_REQUEST["domain"]))
	{
		$domain = $_REQUEST["domain"];
		
		$dbconn = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connect to database' . pg_last_error());
		$mplsQ = "SELECT * FROM mpls WHERE domain='".$domain."';";
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
			<input type='hidden' name='action' value='confirm'>
			<input type='hidden' name='id' value='".$mpls['3']."'>
			<tr><th><a href='mpls-info.php?action=info&domain=".$domain."'>Cancel</a></th>
			<th><input type='submit' value='Save' name='confirm'></th></tr></table>
			</form>";
		
		pg_close($db_conn);

	}else
	{
		echo "No domain set!";
	}
}
if ($action == "confirm")
{
	if(isset($_REQUEST["id"]))
	{
		$id = $_REQUEST["id"];
		$order = $_REQUEST["order"];
		$customer = $_REQUEST["customer"];
		$location = $_REQUEST["location"];
		$carrier = $_REQUEST["carrier"];
		$lec = $_REQUEST["lec"];
		$lan = $_REQUEST["lan"];
		$wan = $_REQUEST["wan"];

        $dbconn = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connect to database' . pg_last_error());
        $mplsQ = "SELECT * FROM mpls WHERE id='".$id."';";
		$mpls = pg_fetch_row(pg_query($dbconn, $mplsQ));
       	$carrier = explode( ',' , $carrier);
      	$carrier0 = explode( ',' , $mpls['0']);
    	$lec0 = explode( ',' , $mpls['4']);
     	$lec = explode( ',' , $lec);
		pg_close($dbconn);
		echo "<p class='red'>Please confirm your changes.</p>
			<table><tr><th colspan='3'>".$mpls['6']." MPLS Circuits</th></tr>
			<tr><th></th><th>Current</th><th>Changes</th></tr>
			<tr><th>Domain</th><td>".$mpls['2']."</td><td>".$mpls['2']."</td></tr>
       	    <tr><th>Order #</th><td>".$mpls['7']."</td><td>".$order."</td></tr>
       	    <tr><th>Customer #</th><td>".$mpls['1']."</td><td>".$customer."</td></tr>
       	    <tr><th>Location</th><td>".$mpls['5']."</td><td>".$location."</td></tr>
       	    <tr><th>Carrier Circuit ID</th><td>";
   		foreach ($carrier0 as $id)
        {
            echo $id."<br>";
        }
		echo "</td><td>";
   		foreach ($carrier as $id)
        {
            echo $id."<br>";
		}
		echo "</td></tr><tr><th>LEC Circuit ID</th><td>";
		foreach ($lec0 as $id)
		{
			echo $id."<br>";
		}
		echo "</td><td>";
		foreach ($lec as $id)
		{
			echo $id."<br>";
		}
		echo "</td></tr>
			<tr><th>Public LAN IPs</th><td>".$mpls['9']."</td><td>".$lan."</td></tr>
			<tr><th>Private WAN IPs</th><td>".$mpls['8']."</td><td>".$wan."</td></tr>
			
			<form action='' method='POST'>
			<input type='hidden' name='order' value='".$order."'/></td></tr>
			<input type='hidden' name='customer' value='".$customer."'/></td></tr>
			<input type='hidden' name='location' value='".$location."'/></td></tr>
			<input type='hidden' name='carrier' value='".$carrier."'/></td></tr>
			<input type='hidden' name='lec' value='".$lec."'/></td></tr>
			<input type='hidden' name='lan' value='".$lan."'/></td></tr>
			<input type='hidden' name='wan' value='".$wan."'/></td></tr>
			<input type='hidden' name='action' value='insert'>
			<input type='hidden' name='id' value='".$id."'>
			<tr><th><input type='submit' value='Confirm' name='confirm'></th>
			</form>
			<form action='' method='get'>
			<input type='hidden' name='action' value='edit'>
			<input type='hidden' name='domain' value='".$mpls['2']."'>
			<th><input type='submit' value='Cancel' /></th></tr>
			</form>
			</table>";
	
	}else
	{
		echo "No ID set";
	}
}
if ($action == "list")
{
	$dbconn = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connect to database' . pg_last_error());
    $mplsQ = "SELECT domain, name, location FROM mpls ORDER BY name;";
	$mpls = pg_fetch_all(pg_query($dbconn, $mplsQ));
	pg_close($dbconn);
	echo "<table><th colspan='3'>MPLS CIRCUITS</th></tr>
		<tr><th>Domain</th><th>Name</th><th>Location</th></tr>";
	foreach($mpls as $cir)
	{
		echo "<tr><td>".$cir['domain']."</td><td>".$cir['name']."</td><td>".$cir['location']."</td></tr>";
	}
	echo "</table>";
}
?>

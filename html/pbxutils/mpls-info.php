<link rel='stylesheet' href='stylesheet.css'>
<?php
include('menu.html');

$guiltyParty = $_SERVER['REMOTE_ADDR'];
$requestTime = strftime('%Y-%m-%d %H:%M:%S');

if (isset($_GET["action"]))
{
    $action=$_GET["action"];
}else
{
    $action = "list";
}

if ($action == "info")
{
    if(isset($_GET["domain"]))
    {
        $domain=$_GET["domain"];
        
        $dbconn = pg_connect("host=rodb dbname=util user=postgres ") or die('Could not connect to database' . pg_last_error());
        $mplsQ = "SELECT * FROM mpls WHERE domain='".$domain."';";
        $mplsArray = pg_fetch_row(pg_query($dbconn, $mplsQ));
        print_r($mplsArray);
        $carrier = explode( ',' , $mplsArray['carrier_circuit_id']);
        $lec = explode( ',' , $mplsArray['lec_circuit_id']);
        echo "<table><tr><th colspan='2'>".$mplsArray['name']." MPLS Circuits</th></tr>
            <tr><th>Domain</th><td>".$mplsArray['domain']."</td></tr>
            <tr><th>Order #</th><td>".$mplsArray['order_number']."</td></tr>
            <tr><th>Customer #</th><td>".$mplsArray['customer']."</td></tr>
            <tr><th>Location</th><td>".$mplsArray['location']."</td></tr>
            <tr><th rowspan='".sizeof($carrier)."'>Carrier Circuit ID</th><td>";
        foreach ($carrier as $id)
        {
            echo $id."<br>";
        }
        echo "</td></tr>

        pg_close($dbconn);
        
    }else
    {
        echo "No domain set!";
    }
}
?>

 <!DOCTYPE html>
<html>
<head>
  <title>ProdTools Main</title>
<?php
include('checksession.php');

 //rekey function stolen from top_events.php, authorized by Adam Jensen :)
function rekey ($multiArray, $key, $value) {
      $newArray = Array();
          foreach ($multiArray as $array) {
                    $newArray[$array[$key]] = $array[$value];
                        }
          return $newArray;
}


$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ") or die('Could not connect to util to look up util_files: '.pg_last_error());
$resultQuery = '';
if (isset($_POST['count'])) {
  $count = $_POST['count'];
$query = 'select resource_group_id, count(*) from user_agent GROUP BY resource_group_id HAVING count(*) >= '.$count.' ORDER BY count DESC;';
    $resultQuery = pg_query($dbconn, $query);
  $query2 = pg_query($dbconn, $query);
      if (!$resultQuery) {
        $failure = "An error occurred on the query request!";
        exit;
       }
      else {
           }
  $domainQ = "SELECT domain, id, assigned_server, state, v5 FROM resource_group WHERE id in (";
      while ($rows = pg_fetch_row($query2)) {
      $domainQ .= "'" . $rows['0'] . "',";
      }
  $domainQ = substr($domainQ, 0, -1) . ");";
  $domains = pg_fetch_all(pg_query($dbconn, $domainQ)) or die ("Broken: QUERY[".$domainQ."]<br>".pg_last_error());

  $IDdomains = rekey($domains, "id", "domain");
  $IDserver = rekey($domains, "id", "assigned_server");
  $IDstate = rekey($domains, "id", "state");
  $IDversion =rekey($domains, "id", "v5");



}
?>
  <? include 'menu.html'; ?>
  <link rel='stylesheet' href='stylesheet.css'>
<body>
<div id="title" onclick="highLight()"> <h2> Domain Phone Count </h2></div>
<div id="count-form"><form method="post" action="">Search for accounts with a phone count greater than <input type="text" name="count"><input type="submit"></div>
<?php 
$x = 0;
if ($resultQuery != "") {
  echo '<table class="sortable" border="1">
        <tr>
        <th>ID</th>
        <th>Domain</th>
        <th>Server</th>
        <th>Phone Count</th></tr>
        ';
  while ($rows = pg_fetch_row($resultQuery)) {
    $id = $rows[0];
    if ($IDstate[$id] == 'ACTIVE') {
      if($IDversion[$id] == 'f') {
    $x++;
    echo '<tr>
          <td>'.$x.'</td>
          <td>'.$IDdomains[$id].'</td>
          <td>'.$IDserver[$id].'</td>
          <td>'. $rows[1].'</td>
          </tr>';
      }
    }
  }
}
?>



</body>
</html>
<script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
<script src="js/sorttable.js"></script>

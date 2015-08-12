
<?php
// Make the script run only if there is a page number posted to this script
if(isset($_POST['pn'])){
  $rpp = preg_replace('#[^0-9]#', '', $_POST['rpp']);
  $last = preg_replace('#[^0-9]#', '', $_POST['last']);
  $pn = preg_replace('#[^0-9]#', '', $_POST['pn']);
  // This makes sure the page number isn't below 1, or more than our $last page
  if ($pn < 1) { 
      $pn = 1; 
  } else if ($pn > $last) { 
      $pn = $last; 
  }
  // Connect to our database here
$conn = pg_connect("host=172.25.9.34 dbname=sshkeys user=sshkeys")
  or die('Could not connect: ' . pg_last_error());
  // This sets the range of rows to query for the chosen $pn
  $limit = 'LIMIT '.$rpp. ' OFFSET ' .($pn - 1) * $rpp;
  // This is your query again, it is for grabbing just one page worth of rows by applying $limit
  $sql = "SELECT * FROM featurerequest ORDER BY id DESC $limit";
  $result = pg_query($conn, $sql);
  $dataString = '';
  while($row = pg_fetch_array($result, null, PGSQL_ASSOC)){
    $id = $row["id"];
    $username = $row["username"];
    $feature = $row["feature"];
    $datemade = ""; //code is borrowed from another website. If I remove this, the JS doesn't work. I don't want to bother figuring it out because as long as I set it to nothing we don't get any errors.
    $dataString .= $id.'|'.$username.'|'.$feature.'|'.$datemade.'|';
  }
  // Close your database connection
    pg_close($conn);
  // Echo the results back to Ajax
  echo $dataString;
  exit();
}
?>

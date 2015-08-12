<?php

if ($_SERVER['SERVER_ADDR'] = '10.101.8.1') {
  // skip security for util so pbxs can report status from the clean script and junk
} else {

include("authenticate.php");
$status = session_status();
$filename = $_SERVER['PHP_SELF'];
$filename = preg_replace('!/!', '', $filename, 1);
$dbconn = pg_connect("host=rwdb dbname=util user=postgres ") or die('Could not connect to util to look up util_files: '.pg_last_error());
$queryaccess = "SELECT access_level FROM util_files Where filename = '".$filename."';";
$result = pg_query($dbconn, $queryaccess);
    if (!$result) {
            echo "An error occurred while checking the database for the page's required access level.\n";
                  exit;
                  }
    while ($row = pg_fetch_row($result)) {
            $accesslevel =  $row[0];
    }
if (!isset($_SESSION['access'])) {
  header("Location: login.php");
}
elseif ($_SESSION['access'] < $accesslevel) {
  header("location: restrictedaccess.php");
}

}
?>

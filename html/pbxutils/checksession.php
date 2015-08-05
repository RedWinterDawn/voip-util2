<?php
include("authenticate.php");
$status = session_status();
if (!isset($_SESSION['access'])) {
  header("Location: login.php");
}
elseif ($_SESSION['access'] < $accesslevel) {
  header("location: restrictedaccess.php");
}
?>

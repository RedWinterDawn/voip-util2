<?

if (isset($_SERVER["PHP_AUTH_USER"])) {
  $guiltyParty = $_SERVER["PHP_AUTH_USER"] . "@" . $_SERVER["REMOTE_ADDR"];
} else {
  $guiltyParty = $_SERVER["REMOTE_ADDR"];
}

?>

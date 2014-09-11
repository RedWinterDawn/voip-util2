<?

if (isset($_SERVER["PHP_AUTH_USER"])) {
  $GuiltyParty = $_SERVER["PHP_AUTH_USER"] . "@" . $_SERVER["REMOTE_ADDR"];
} else {
  $GuiltyParty = $_SERVER["REMOTE_ADDR"];
}

?>

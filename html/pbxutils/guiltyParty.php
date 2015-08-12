<?

if (isset($_SESSION["user"])) {
  $guiltyParty = $_SESSION["user"] . "@" . $_SERVER["REMOTE_ADDR"];
} else {
  $guiltyParty = $_SERVER["REMOTE_ADDR"];
}

?>

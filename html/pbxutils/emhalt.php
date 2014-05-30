<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

if (isset($_REQUEST['clear'])) {
	unlink("/var/www/html/pbxutils/STOPMIGRATION");
} else {
	file_put_contents("/var/www/html/pbxutils/STOPMIGRATION", "Someone pushed the emergency halt button. Check file timestamp for date and time");
}

echo "<h2>You halted the migration!</h2>";
echo "<br>Please verify that the migration has stopped. Then you can click the button below to enable future migrations to run.";
echo "<br>If you do not click the button below, mass migrations will not be possible until the /var/www/html/pbxutils/STOPMIGRATION file is manually removed.<br>";
echo "<form action='' method='POST'>
	<input type='hidden' name='clear' value='true' />
	<input type='submit' value='Clear Migration Block' />
	</form>";
?>

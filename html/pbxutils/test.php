<?php

if (isset($_REQUEST['content'])) {
	$content = $_REQUEST['content'];
} else {
	$content = "Enter your content here";
}

echo "<form action='' method='POST'>
	<input type='text' name='content' placeholder='$content' size='96' />
	</form>";

?>

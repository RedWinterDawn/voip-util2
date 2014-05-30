<?php
echo '<html><head><title>Fix events reports</title>
<style type ="text/css">
#pretty {vertical-align: bottom;}
</style><link rel="stylesheet" href"stylesheet.css"></head>';


echo '<body onload="init()"><div id="head" class="head">
	 <h2>Move a Single Customer';
if ($_SERVER['SERVER_ADDR'] == '10.101.8.1')
{
	    echo " (PRODUCTION)";
} else
{
	    echo " (DEV)";
}
echo '</h2>
	    <a href="index.php">Back to pbxutils</a>';

echo '</html></body>';

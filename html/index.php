<?php 
function cidr_match($ip, $range)
{
    list ($subnet, $bits) = explode('/', $range);
    $ip = ip2long($ip);
    $subnet = ip2long($subnet);
    $mask = -1 << (32 - $bits);
    $subnet &= $mask; 
    return ($ip & $mask) == $subnet;
}
if (cidr_match($_SERVER['REMOTE_ADDR'], "10.0.0.0/8"))
{
header ('Location: pbxutils/index.php' );
} else
{
echo $_SERVER['REMOTE_ADDR']; 
}
?>

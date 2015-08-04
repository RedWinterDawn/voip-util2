<?php
header('Content-Type: application/json');
include('guiltyParty.php');
$requestTime = strftime('%Y-%m-%d %H:%M:%S');

if (isset($_GET["username"]))
{
    $username = $_GET["username"];
} else
{
    die('{"queryResult":"FAIL","failReason":"Missing username"}');
}

// Connecting, selecting database
$dbconn = pg_connect("host=rodb dbname=pbxs user=postgres ")
    or die('{"queryResult":"FAIL","failReason":"Could not connect to DB"}');

$escapedUsername = pg_escape_string($username);

// SQL Query
$usernameQuery = "SELECT line_assignment.name AS username,user_agent_configuration.name,line_assignment.callerid,
  resource_group.domain,resource_group.assigned_server,
  user_agent.ip_address,user_agent.last_checkin,user_agent.type_id,user_agent.sis_checkin,user_agent.provisioning_unlock
 FROM line_assignment
 LEFT JOIN user_agent_configuration on line_assignment.configuration_id = user_agent_configuration.id
 LEFT JOIN resource_group on user_agent_configuration.resource_group_id = resource_group.id
 LEFT JOIN user_agent on user_agent_configuration.id = user_agent.configuration_id
 WHERE line_assignment.name = '" . $escapedUsername . "';";
$usernameResult = pg_query($usernameQuery) or die('Type query failed: ' . pg_last_error());

$resultCount = 0;
echo '{"queryResult":"OK","results":';
while ($usernameRow = pg_fetch_array($usernameResult, null, PGSQL_ASSOC)) {
  echo '{' . 
   '"username":"' . $usernameRow['username'] . '",' . 
   '"name":"' . $usernameRow['name'] . '",' . 
   '"callerid":"' . $usernameRow['callerid'] . '",' . 
   '"domain":"' . $usernameRow['domain'] . '",' . 
   '"assigned_server":"' . $usernameRow['assigned_server'] . '",' . 
   '"ip_address":"' . $usernameRow['ip_address'] . '",' . 
   '"last_checkin":"' . $usernameRow['last_checkin'] . '",' . 
   '"type_id":"' . $usernameRow['type_id'] . '",' . 
   '"sis_checkin":"' . $usernameRow['sis_checkin'] . '",' . 
   '"provisioning_unlock":"' . $usernameRow['provisioning_unlock'] . '"' . 
   '},';
   $resultCount = $resultCount + 1;
}
if ($resultCount == 0) { echo '{},'; } // so we don't malform the json if there are no results
echo '"resultCount":' . $resultCount . '}';

pg_free_result($usernameResult);



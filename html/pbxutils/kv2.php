
<html>
<head>
<title>Pull data from Sherlock</title>
</head>
<body>
<!-- action can just be "", which refers to self -->
<form method="post" action="<?php $_PHP_SELF ?>"> 
 <table width="400" border="0" cellspacing="1" cellpadding="2">
 <tr>
 <td width="100">Search for PBX ID:</td>
 <td width="150"><input name="pbxid" type="text" id="pbxid"></td>
<!-- Rather than having separte names like "pbxid", "putpbxid", and "delpbxid", just have "pbxid" and then add a hidden action field in the form.
  For example:

  <form> 
    <input type="text" name="id" />
    <input type="hidden" action="search" />
    <input type="submit" value="Search!" />
  </form> 

Then your php would just unload the id and action:

  if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = $_POST['id'];
  }

  if ($action == "search") {
    //===== search code here
  } else if ($action == "add") {
    //===== add a new entry
  } else if ($action == "del") {
    //===== delete an entry
  } else {
    die("Your request didn't match any available funcitons!");
  }
-->
<td><input name="input" type="submit" id="send"></td>

 </tr>
 </table>
 </form>
 <form method="post" action="<?php $_PHP_SELF ?>">
<table>

 <form method="post" action="<?php $_PHP_SELF ?>">
<table>
<tr>
<td>Please fill out below to remove a PBX ID.<br> (Warning! This will remove all entries for the selected PBX ID.)</td></tr></table>
<table>
<br>
<tr>
<td width="100">Remove PBX ID:</td>
<td width="150"><input name="delpbxid" type="text" id="delpbxid"></td>
<td><input name="input" type="submit" id="send"></td>
</tr><table></form><br>
<tr>
<td>Please fill out below to add a region with the associated PBX ID.</td></tr></table>
<table>
<br>
<tr>
<td width="100">Add PBX ID:</td>
<td width="150"><input name="putpbxid" type="text" id="putpbxid"></td>
<td width="75">Region:</td>
<td width="150"><input name="putregion" type="text" id="putregion"></td>
<td><input name="input" type="submit" id="send"></td>
</tr><table></form><br>


<?php
// We don't use magic quotes, so you can skip this.
if(! get_magic_quotes_gpc() )
{
     $pbxid = addslashes ($_POST['pbxid']);
        
}
else
{
     $pbxid = $_POST['pbxid'];
        
}
//As mentioned earlier, this block would be avoided by using an $action value.
$putpbxid = $_POST['putpbxid'];
$putregion = $_POST['putregion'];
$delregion = $_POST['delregion'];
$delpbxid = $_POST['delpbxid'];

// Typically, I let the user worry about whether or not something is typed correctly. Rather than removing spaces for them, 
// I would just see if the input matches the exact type of an ID or Region. If it doesn't, output a message stating what was expected.
// For example:
// 
// $pattern = '--some regex that only matches a full uuid--';
// if (!preg_match($pattern, $id)) {
//   $id = ''; 
//   $action = 'show-all';
//   echo "<p style='color: red;'>Please enter a valid UUID</p>";
// }
$pbxid = preg_replace('/\s/', '', $pbxid);
$putpbxid = preg_replace('/\s/', '', $putpbxid);
$putregion = preg_replace('/\s/', '', $putregion);
$delpbxid = preg_replace('/\s/', '', $delpbxid);
$delregion = preg_replace('/\s/', '', $delregion);

if($delpbxid != NULL) {
  // ---- I notice you have /region/all'.$delregion? 
  // Would that result in a url like ....tenant/12340-1234098-1230948-1234092/region/allCU ?
  $url = 'http://10.125.255.66:6666/tenant/'.$delpbxid.'/region/all'.$delregion;
  $curl = curl_init($url);
  //Also, I see that you're sending json to the server. Do you need that when you're deleting an entry?
  $jsonarray = '{ "tenantId" : "'.$delpbxid.'", "region" : "'.$delregion.'", "risk" : 0, "description" : null }';
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonarray);
  $response = curl_exec($curl);
  $errno = curl_errno($curl);
  if ($errno == 0) {
    echo 'You have removed the PBX ID '. $delpbxid;
  }
  else{ 
  // Even if you have a positive response, you should ignore the content if errno was anything but 0
  // I would just remove the second if block here. 
  if (!$response) {
    die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
  }
  }
  return $reponse;
  
  curl_close($curl);
}

elseif($putpbxid != NULL) {
  $url = 'http://10.125.255.66:6666/tenant/'.$putpbxid.'/region/'.$putregion;
  $curl = curl_init($url);
  $jsonarray = '{ "tenantId" : "'.$putpbxid.'", "region" : "'.$putregion.'", "risk" : 0, "description" : null }';
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonarray);
  $response = curl_exec($curl);
  $errno = curl_errno($curl);
  if ($errno == 0) {
    echo 'You have successfully added the region '. $putregion. 'to the PBX ID '. $putpbxid;
  }
  else{ 
  if (!$response) {
    die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
  }
  }
  return $reponse;
  
  curl_close($curl);
}
else {
$url = 'http://10.125.255.66:6666/tenant/test/region';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
       'Accept: application/json'
                        ));
$data = curl_exec($ch);
$array =json_decode($data, true);
// Since you don't actually need to do anything with the output later, just echo it.
// Typically only use a variable if you intend to read or change the contents afterwards. :)
$output = '<table border="1">
  <tr><td>Id</tda
  ><td>PBX ID</td>
  <td>region</td
  ><td>Risk</td></tr><tr>';
//Totally optional: for better readability use a flag name that's more descriptive
// e.g. $foundResults = false
//Then when someone reads "if ($foundResults) {" they'll know what it means.
$f = 1;
if ($pbxid == NULL ) {
  //On a similar note, programmers often use $i and $j for counters. If you use $i people will 
  // know what that it is probably just a counter that increments/decrements with each loop. 
  $x = 1;
  global $output, $array, $pbxid;
  foreach ($array as $value) {
      $output .= '<td>'. $x++. '</td><td>'. $value['tenantId']. '</td>';
      $output .= '<td>'. $value['region']. '</td>';
      $output .= '<td>'. $value['risk']. '</td></tr>'; 
                             } 
  $output .= '</table>';
  curl_close($ch);
  echo $output;
                 }
else {
  $x = 1;
  global $output, $array, $pbxid, $f;
  foreach ($array as $value){
    if($value['tenantId'] == $pbxid) {
    $output .= '<td>'. $x++. '</td><td>'. $value['tenantId']. '</td>';
    $output .= '<td>'. $value['region']. '</td>';
    $output .= '<td>'. $value['risk']. '</td></tr>';
  //Rather than using an integer and testing value, just use a boolean (true/false)
  // Then your statement can just say 'if ($f) { stuff; }' because a boolean already 
  // evaluates to true or false. 
    $f++;
    } 
    else {
    }
  }
  $output .= '</table>';
  curl_close($ch);
  if($f > 1){
    echo $output;
  }
  else {
    echo "There is no entry for this pbx ID";
  }
}
}
?>

</body>
</html>

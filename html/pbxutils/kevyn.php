
<html>
<head>
<title>Pull data from Sherlock</title>
</head>
<body>
<form method="post" action="<?php $_PHP_SELF ?>">
 <table width="400" border="0" cellspacing="1" cellpadding="2">
 <tr>
 <td width="100">Search for PBX ID:</td>
 <td width="150"><input name="pbxid" type="text" id="pbxid"></td>
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

if(! get_magic_quotes_gpc() )
{
     $pbxid = addslashes ($_POST['pbxid']);
        
}
else
{
     $pbxid = $_POST['pbxid'];
        
}
$putpbxid = $_POST['putpbxid'];
$putregion = $_POST['putregion'];
$delregion = $_POST['delregion'];
$delpbxid = $_POST['delpbxid'];
$pbxid = preg_replace('/\s/', '', $pbxid);
$putpbxid = preg_replace('/\s/', '', $putpbxid);
$putregion = preg_replace('/\s/', '', $putregion);
$delpbxid = preg_replace('/\s/', '', $delpbxid);
$delregion = preg_replace('/\s/', '', $delregion);

if($delpbxid != NULL) {
  $url = 'http://10.125.255.66:6666/tenant/'.$delpbxid.'/region/all'.$delregion;
  $curl = curl_init($url);
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
$output = '<table border="1">
  <tr><td>Id</tda
  ><td>PBX ID</td>
  <td>region</td
  ><td>Risk</td></tr><tr>';
$f = 1;
if ($pbxid == NULL ) {
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

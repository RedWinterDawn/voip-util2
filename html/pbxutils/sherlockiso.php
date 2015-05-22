<html>
<head>
<title>Pull data from Sherlock</title>
<style type="text/css">
   #pretty {vertical-align: bottom;}
   </style><link rel="stylesheet" href="stylesheet.css">
</head>
<body>
<?php
include('menu.html');
?>
<br>
<h2> Sherlock ISO Codes </h2>
<form method="post" action="<?php $_PHP_SELF ?>">
Search for PBX ID:
 <input name="pbxid" type="text" id="pbxid">
<input name="input" type="submit" id="send"> 
 </form>
 <form method="post" action="<?php $_PHP_SELF ?>">
 <form method="post" action="<?php $_PHP_SELF ?>"><br>
Please fill out below to remove a PBX ID.<br> (Warning! This will remove all entries for the selected PBX ID.)
<br>
Remove PBX ID:
<input name="delpbxid" type="text" id="delpbxid">
<input name="input" type="submit" id="send">
</form><br>

Please fill out below to add a region with the associated PBX ID.
<br>
 <form method="post" action="<?php $_PHP_SELF ?>">
Add PBX ID:
<input name="putpbxid" type="text" id="putpbxid">
Region:
<input name="putregion" type="text" id="putregion">
<input name="input" type="submit" id="send">
</form><br>
<br>


<?php

        
     $pbxid = $_POST['pbxid'];
        
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
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
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

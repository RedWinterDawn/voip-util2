<html>
<head>
<?php
$accesslevel = 4;
include('checksession.php');
?>
<title>SSH Keys</title>
<style type="text/css">
   #pretty {vertical-align: bottom;}
#rowbody {
float: left;
width: 500px;
margin-right: 20px;
}
#rowhead {
color: LimeGreen;
width: 500px;
float: left;
margin-top:20px;
font-size: 20px;
background-color:#000000;
}
#rowhead-num {
float: left;
width: 50px;
}
#rowhead-type {
float: left;
width: 200px;
}
#rowhead-del {
float: left;
width: 150px;
}
#rowhead-hide {
}
#addrow-odd {
width: 500px;
float: left;
background-color:#000000;
border-bottom: 2px solid black;
}
#addrow-even {
width: 500px;
float: left;
background-color:#191919;
border-bottom: 2px solid black;
}
#addrow-num {
float: left;
width: 50px;
}
#addrow-type {
float: left;
width: 200px;
}
#addrow-check {
float: left;
width: 150px;
}
#delsubmit {
float: right;
}
#sshkey {
width: 440px;
height: 200px;
}
#addrow-show {
float: left;
display: inline;
}
.addrow-hide {
float: left;
display: none;
}
#update {
float: left;
}
#update2 {
float: right;
margin-right: 20px;
}
#username {
float:left;
margin-right: 100px;
}
#errorbody {
float: left;
width: 600px;
}
#errorhead-id {
float:left;
width: 70px;
}
#errorhead-status {
float:left;
width: 100px;
}
#errorhead-change {
float:left;
width: 100px;
}
#errorhead-startdate {
float:left;
width: 148px;
}
#errorhead-finishdate {
float:left;
width: 148px;
}
#errorhead-tfailed {
float:left;
width: 140px;
}
#errorhead-ttime {
float:left;
width: 140px;
}
#sshtitle {
float: left;
}
.sshkeyrow {
width: 480px;
float: left;
word-wrap:break-word;
display: none;
padding: 10px;
}
#errorhead {
color: LimeGreen;
padding-left: 5px;
width: 700px;
float: left;
font-size: 20px;
background-color:#000000;
border-top: 1px solid white;
padding-top: 3px;
padding-bottom: 3px;
}
#errorheadbelow {
float:left;
color: black;
font-size: 14px;
background-color: #5A5A5A;
width: 700px;
border-bottom: 2px solid black;
border-top: 1px solid white;
padding-left: 5px;
cursor: pointer;
}
#errorheadbelow:hover {
background-color: #808080
}
.hiddenerrors {
float:left;
display: none;
color: black;
font-size: 12px;
background-color: #5A5A5A;
width: 700px;
border-bottom: 1px solid black;
cursor: pointer;
}

.hiddenerrors-row {
display:none;
}
input[type=button]
{
  background-color: #999999; 
}

input[type=button]:hover
{
  background-color: #CCCCCC;
}
#error-even {
background-color:#BDBDBD;
width: 700px;
float:left;
padding-left: 5px;
}
#error-odd {
background-color:#5A5A5A;
width: 700px;
float:left;
padding-left:5px;
}
   </style><link rel="stylesheet" href="stylesheet.css">
</head>
<body>

<?php
 include('menu.html');
 ?>
<div id="body">
<div id="rowbody">
<div id="body-title"><h2>SSH Keys Manager</h2></div>
<div id="submit">
<form action="" method="post">
Username:<br><input type="text" name="username" id="username">
<div id="update"> Update all? <input type="checkbox" name="updatebutton" id="updatebutton"></div><br>
<div id="sshtitle">SSH Key:</title><br><textarea  name="sshkey" id="sshkey"></textarea>
<input type="submit">
</form>
</div>
<?php
## Setting boolean whether the system update should be push out.
 $update = false;
 if (isset($_POST['updatebutton'])) {
     $updatebutton = $_POST['updatebutton'];
     $update = true;
 }    
## Setting connection to v5 rw on ORD.
$servername = "172.25.9.34";
$username = "sshkeys";
$dbname = "sshkeys";
$port = "5432";

// Create connection
$conn = pg_connect("host=172.25.9.34 dbname=sshkeys user=sshkeys")
  or die('Could not connect: ' . pg_last_error());

if(isset($_POST["username"]) && isset($_POST["sshkey"])) {
  $username = $_POST["username"];
  $sshkey = $_POST["sshkey"];

  $query = "INSERT INTO sshkeys (username, sshkey)
    VALUES ('".$username."', '".$sshkey."')";
  
  $result = pg_query($conn, $query);
 
  ##Define variables for new job to add a key
  $startdate = date("Y-m-d H:i:s");
  $user =  $_SERVER["PHP_AUTH_USER"];
  $change = 'add.'.$username;
  $status = 'PENDING';
  
  ##If Update all? is set, send $updateall as true so that the cron job on bootstrap will process this correctly.
  if ($update) {
    $updateall = "true";
    $queryupdate = "INSERT INTO jobs (startDate, username, change, status, updateall) VALUES ('".$startdate."', '".$user."', '".$change."', '".$status."', '".$updateall."')";
  $result = pg_query($conn, $queryupdate);
  }
  ## if update all? is false, let the cron job know not to process this.
  else {
    $updateall = "false";
  $status = 'FINISHED';
    $queryupdate = "INSERT INTO jobs (startDate, username, change, status, updateall) VALUES ('".$startdate."', '".$user."', '".$change."', '".$status."', '".$updateall."')";
    $result = pg_query($conn, $queryupdate);
          }

  }
## Checking to see if "Delete" is selected. If selected, it will run the Delete Query.
$test = false;
if (isset($_POST['del'])) {
  $del = $_POST['del'];
  $test = true;
}
if ($test) {
  ## Get the username of the ssh key being deleted.
  $getusername = "SELECT username FROM sshkeys where id = '".$del."';";
    $result = pg_query($conn, $getusername);
    while ($row = pg_fetch_array($result, null, PGSQL_ASSOC)) {
          $username = $row["username"];
            }
  ## Run the deletion query.
  $sqldel = "DELETE from sshkeys where id = '".$del."';";
  $result = pg_query($conn, $sqldel);


  ##Define variables for new job to add a key
    $startdate = date("Y-m-d H:i:s");
    $user =  $_SESSION["user"];
    $change = 'del.'.$username;
    $status = 'PENDING';

    ##If Update all? is set, send $updateall as true so that the cron job on bootstrap will process this correctly.
      if ($update) {
        $updateall = "true";
        $queryupdate = "INSERT INTO jobs (startDate, username, change, status, updateall) VALUES ('".$startdate."', '".$user."', '".$change."', '".$status."', '".$updateall."')";
        $result = pg_query($conn, $queryupdate);
              }
      ## if update all? is false, let the cron job know not to process this.
      else {
        $updateall = "false";
  $status = 'FINISHED';
        $queryupdate = "INSERT INTO jobs (startDate, username, change, status, updateall) VALUES ('".$startdate."', '".$user."', '".$change."', '".$status."', '".$updateall."')";
        $result = pg_query($conn, $queryupdate);
                          }
}

$sql2 = "SELECT username, sshkey, id FROM sshkeys";
$result = pg_query($conn, $sql2);
$x=0;
      // output data of each row
    echo '<div id="rowhead"><div id="rowhead-num">#</div><div id="rowhead-type">Username</div><div id="rowhead-del">Delete?</div><div id="rowhead-hide">Show/Hide</div></div>';
      echo '<form method="post" action="">';
        while($row = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            $x++;
              if ($x % 2 == 0) {
                echo '<div id="addrow-odd">
                  <div id="addrow-num">'.$x.'</div>
                  <div id="addrow-type">'. $row["username"]. '</div>
                  <div id="addrow-check"><input type="radio" value="'.$row["id"].'" name="del" ></div>
                  <div id="addrow-show"><input type="button" id="keyshow'.$row["id"].'" name="ShowBox" value="Show Key" onclick="document.getElementById(\'sshkey'.$row["id"].'\').style.display = \'block\'; document.getElementById(\'keyshow'.$row["id"].'\').style.display = \'none\'; document.getElementById(\'keyhide'.$row["id"].'\').style.display = \'block\';"></div>
                  <div id="addrow-hide"><input type="button" class="addrow-hide" id="keyhide'.$row["id"].'" name="ShowBox" value="Hide Key" onclick="document.getElementById(\'sshkey'.$row["id"].'\').style.display = \'none\'; document.getElementById(\'keyshow'.$row["id"].'\').style.display = \'block\'; document.getElementById(\'keyhide'.$row["id"].'\').style.display = \'none\'"></div></div>';
                        echo '<div class="sshkeyrow" id="sshkey'.$row["id"].'">'.$row["sshkey"].'</div>';
                           }
              else {
                echo '<div id="addrow-even">
                                <div id="addrow-num">'.$x.'</div>
                                <div id="addrow-type">'. $row["username"]. '</div>
                                <div id="addrow-check"><input type="radio" value="'.$row["id"].'" name="del" ></div>
                                <div id="addrow-show"><input type="button" id="keyshow'.$row["id"].'" name="ShowBox" value="Show Key" onclick="document.getElementById(\'sshkey'.$row["id"].'\').style.display = \'block\'; document.getElementById(\'keyshow'.$row["id"].'\').style.display = \'none\'; document.getElementById(\'keyhide'.$row["id"].'\').style.display = \'block\';"></div>
                                <div id="addrow-hide"><input type="button" class="addrow-hide" id="keyhide'.$row["id"].'" name="ShowBox" value="Hide Key" onclick="document.getElementById(\'sshkey'.$row["id"].'\').style.display = \'none\'; document.getElementById(\'keyshow'.$row["id"].'\').style.display = \'block\'; document.getElementById(\'keyhide'.$row["id"].'\').style.display = \'none\'"></div></div>';
                                      echo '<div class="sshkeyrow" id="sshkey'.$row["id"].'">'.$row["sshkey"].'</div>';}
        }
echo '<div id="delsubmit"><input type="submit" value="delete"></div><div id="update2"> Update all? <input type="checkbox" name="updatebutton" id="updatebutton"></div></form></div></div>';

?>
</div>
</div>
<div id="errorbody">
<h3> Error Output</h3>
<br>
<div id="errors">
<?php
$sql = "SELECT jobid, startdate, username, change, status, finishdate, updateall FROM jobs ORDER BY jobid DESC limit 20;";
$result = pg_query($conn, $sql);


$x=0;
      // output data of each row
echo '<div id="errorhead">
  <div id="errorhead-id">JobID</div>
  <div id="errorhead-status">Status</div>
  <div id="errorhead-change">change</div>
  <div id="errorhead-startdate">Start Time</div>
  <div id="errorhead-ttime">Finish Time</div>
  <div id="errorhead-tfailed">User</div></div></div>';

  while($row = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    if ($row["finishdate"] == NULL) {
      $row["finishdate"] = '-';
    }
    echo '<div id="errorheadbelow" onclick="toggle_visibility(\'errors'.$row["jobid"].'\');">
       <div id="errorhead-id">'.$row["jobid"].'</div>
         <div id="errorhead-status">'.$row["status"].'</div>
           <div id="errorhead-change">'.$row["change"].'</div>
             <div id="errorhead-startdate">'.$row["startdate"].'</div>
               <div id="errorhead-ttime">'.$row["finishdate"].'</div>
                 <div id="errorhead-tfailed"></div>'.$row["username"].'</div></div>';
    echo '<div class="hiddenerrors" id="errors'.$row["jobid"].'">';
      $sqlerrors = "SELECT jobid, time, site, rid, ip, class, error FROM joberrors where jobid ='".$row['jobid']."' ORDER BY time ASC;";
      $sqlerrors = pg_query($conn, $sqlerrors);
      $x = 0;
      while($row = pg_fetch_array($sqlerrors, null, PGSQL_ASSOC)) {
        $x++;
if ($x % 2 == 0) {
       echo '<div id="error-odd"> <div id="errorhead-id">'.$row["time"].'</div>
        <div id="errorhead-status">'.$row["rid"].'</div>
        <div id="errorhead-change">'.$row["class"].'</div>
        <div id="errorhead-startdate">'.$row["site"].'</div>
        <div id="errorhead-ttime">'.$row["ip"].'</div>
       <div id="errorhead-tfailed">'.$row["error"].'</div></div>';
}

else {
  echo '<div id="error-even"> <div id="errorhead-id">'.$row["time"].'</div>
            <div id="errorhead-status">'.$row["rid"].'</div>
                    <div id="errorhead-change">'.$row["class"].'</div>
                            <div id="errorhead-startdate">'.$row["site"].'</div>
                                    <div id="errorhead-ttime">'.$row["ip"].'</div>
                                           <div id="errorhead-tfailed">'.$row["error"].'</div></div>';
}

      }
      echo '</div>';
  }
  pg_close($conn);
?>
</div>


</div>

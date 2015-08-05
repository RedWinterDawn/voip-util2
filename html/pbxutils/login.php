
<head>
<style>
body {
background-color: #A7A8AA;
font-family: Calibri, Candara, Segoe, 'Segoe UI', Optima, Arial, sans-serif;
}
#loginform {
width: 400px;
height: 250px;
margin: 150px auto;
background-color: white;
border-radius: 5px;
box-shadow: 10px 10px 5px 0px #5B6770;
}
img {
position: relative;
margin-left: 5px;
margin-right: 20px;
float:left;
}
#logintitle {
margin: 10px;
margin-bottom: 15px;
padding-top: 15px;
vertical-align: middle;
font-weight: bold;
font-size: 30px;
text-align: center;
}
#forminfo {
width: 350px;
margin: 0 auto;
padding: 5px;
padding-bottom: 0px;
clear: left;
background-color: #D9D9D6;
}
.boxtitle {
width: 30px;
}
input[type=text], input[type=password] {
widtH: 100%;
margin-bottom: 5px;
}
input[type=submit] {
margin-top: 10px;
width: 360px;
margin-left: -5px;
margin-right: -5px;
margin-bottom: 0px;
border-radius: 0px;
font-family: Calibri, Candara, Segoe, 'Segoe UI', Optima, Arial, sans-serif;
font-size: 16px;
font-weight: bold;
background-color: #5B6770;
color: white;
text-shadow: 1px 1px black;
}
input[type=submit]:hover {
background-color:#49525A;
cursor: pointer;
}
#loginfail {
text-align: center;
}
</style>
</head>
<body>

<?php

include("authenticate.php");

// check to see if user is logging out
if(isset($_GET['out'])) {
  // destroy session
  session_unset();
  $_SESSION = array();
  unset($_SESSION['user'],$_SESSION['access']);
  session_destroy();
}
 
// check to see if login form has been submitted
if(isset($_POST['userLogin'])){
  // run information through authenticator
  if(authenticate($_POST['userLogin'],$_POST['userPassword']))
  {
    // authentication passed
    header("Location: index.php");
    die();
  } else {
    // authentication failed
    $error = 1;
  }
}
 
 
// output logout success
if (isset($_GET['out'])) echo "Logout successful";
?>
<div id="loginform">
  <div id="logintitle">Prodtools Login</div>
<div id="forminfo">
<form action="login.php" method="post">
 <div class="boxtitle"> Username: </div><input type="text" name="userLogin" /><br />
 <div class="boxtitle"> Password:</div> <input type="password" name="userPassword" />
  <input type="submit" name="submit" value="Login" />
</div>
</form>
<div id="loginfail">
<?php
if (isset($error)) echo "Login failed: Incorrect user name, password, or rights<br /-->";
?>
</div>
</div>
</body>

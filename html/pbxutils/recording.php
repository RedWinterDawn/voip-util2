
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Jinst Logs</title>

            <script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
            <script type="text/javascript" src="js/jquery-ui-1.7.2.custom.min.js"></script>
            <script type="text/javascript" src="js/jinst.js"></script>

          </head>
<link rel="stylesheet" href="stylesheet.css">
          <style>
#jivegreen {
display: inline;
vertical-align: 30%;
margin-left:25px;
color: #A9C23F;
font-size: 30px;
}
body {
background-color:black;
}
.leftedge {
margin-top:10px;
background-color: #232323;
border:1px solid black;
border-right:2px solid black;
width: 98%;
margin-bottom:3px;
overflow:hidden;
}
.sidebar {
height: 100%;
width: 200px;
float:left;
background-color:#232323;
border:1px solid black;
border-top:0px;
border-right:2px solid black;
}
.item {
font-size: 20px;
border-bottom: 1px solid #5E5E5E;
padding: 5px;
margin:3px;
}
.sub-item {
font-size: 18px;
border-bottom: 1px solid #5E5E5E;
padding: 5px;
padding-left: 15px;
background-color: #000412;
}
.item:hover {
background-color: #5E5E5E;
cursor: pointer;
color: #191919;
border-top:1px solid #A9A9A9;
}
.sub-item:hover {
cursor:pointer;
color:#A9C23F;
}
.item-hide {
font-size: 15px;
border-bottom: 1px solid #5E5E5E;
padding: 5px;
}
.item-hide:hover {
background-color: #5E5E5E;
cursor: pointer;
color: #191919;
}
.hider {
display: none;
}
#output-body {
background-color: #303030;
height: auto;
width: auto;
overflow:hidden;
}
#output-title {
height: auto;
background-color:#454545;
padding: 10px;
font-size: 30px;
width: auto;
color: #191919;
border-bottom: 2px solid black;
margin-bottom: 10px;
}
#title-sub {
display:inline;
color: #BFBFBF;
font-size:20;
}
#output-logs {
margin-left: 15px;
height: auto;
}
.container {
width: 98%;
height:100%;
background-color: #303030;
border-top:1px solid black;
}
.status {
width: 7px;
float:right;
height: 7px;
border: 1px solid black;
background-color: white;
display: inline-block;
position: relative;
top: 5px;
right: 125px;
}
#logo {
display:inline;
overflow:hidden;
}
#image {
position: relative;
top:-2px;
}
#rebuildall {
height: auto;
width: auto;
font-size: 10;
display: none;
}
#rebuildall-head {
color: #A9C23F;
float:left;
font-size: 20px;
width: 19%;
border: 1px solid black;
padding: 3px;
}
#rebuildall-ord {
width: 19%;
height: 100%;
border:1px solid black;
float:left;
padding:3px;
}
#rebuildall-lax {
width: 19%;
height: 100%;
border:1px solid black;
float:left;
padding:3px;
}
#rebuildall-nyc {
width: 19%;
height: 100%;
border:1px solid black;
float:left;
padding:3px;
}
#rebuildall-atl {
width: 19%;
height: 100%;
border:1px solid black;
float: left;
padding:3px;
}
#rebuildall-geg {
width: 19%;
height: 100%;
border:1px solid black;
float:left;
padding:3px;
}                </style>
              </head>
              <body onload="checkStatus();">
                <?php
                include('menu.html');
                ?>
              </div>
              <div class="input-area">
                <div class="leftedge"><div id="logo"><img id="image" src="green_breathing.png" width="35"></div><div id="jivegreen">ActiveMQ/Recording Manager</div></div>
<div class="container">
<div class="sidebar">
<div id='chi-show'  onclick="document.getElementById('chi').style.display = 'block'; document.getElementById('chi-hide').style.display = 'block'; document.getElementById('chi-show').style.display = 'none';" class="item">
CHI<div  class="status" id="chi-status"></div>
</div>
<div id='chi-hide'   onclick="document.getElementById('chi').style.display = 'none'; document.getElementById('chi-hide').style.display = 'none'; document.getElementById('chi-show').style.display = 'block';" = 'block';" class="item hider">
CHI<div  class="status" id="chi-status2"></div>
</div>
<div class="hider" id="chi">
<div class="sub-item" onclick="dc = '10.101.15.1'; confirmRestart(dc);">
Rebuild MQ
</div>
<div class="sub-item" onclick="dc = '10.101.15.1'; getMQLogsRun(dc);">
Active MQ Log
</div>
<div class="sub-item" onclick="dc = 'c1'; retryRec(dc);">
Retry Old Recordings
</div>
<div class="sub-item" onclick="dc = 'c1'; remoteRestart(dc);">
Asterisk-Remote Restart
</div>
<div class="sub-item" onclick="dc = 'c1'; activeMQPage(dc);">
ActiveMQ Page
</div>
</div>
<div id='ord-show'  onclick="document.getElementById('ord').style.display = 'block'; document.getElementById('ord-hide').style.display = 'block'; document.getElementById('ord-show').style.display = 'none';" class="item">
ORD <div  class="status" id="ord-status"></div>
</div>
<div id='ord-hide'   onclick="document.getElementById('ord').style.display = 'none'; document.getElementById('ord-hide').style.display = 'none'; document.getElementById('ord-show').style.display = 'block';" = 'block';" class="item hider">
ORD <div  class="status" id="ord-status2"></div>
</div>
<div class="hider" id="ord">
<div class="sub-item" onclick="dc = '172.25.9.15'; confirmRebuild(dc);">
Rebuild MQ
</div>
<div class="sub-item" onclick="item = 'ord-status'; dc = '172.25.9.15'; getLogsRun(dc);">
MQ Jinst Log
</div>
<div class="sub-item" onclick="dc = 'c1'; serviceRecUp(dc);">
Rec-Upload Restart
</div>
<div class="sub-item" onclick="dc = 'c1'; retryRec(dc);">
Retry Old Recordings
</div>
<div class="sub-item" onclick="dc = 'c25'; remoteRestart(dc);">
Asterisk-Remote Restart
</div>
<div class="sub-item" onclick="dc = 'c25'; activeMQPage(dc);">
ActiveMQ Page
</div>
</div>
<div id='lax-show'  onclick="document.getElementById('lax').style.display = 'block'; document.getElementById('lax-hide').style.display = 'block'; document.getElementById('lax-show').style.display = 'none';" class="item">
LAX <div  class="status" id="lax-status"></div>
</div>
<div id='lax-hide'   onclick="document.getElementById('lax').style.display = 'none'; document.getElementById('lax-hide').style.display = 'none'; document.getElementById('lax-show').style.display = 'block';" = 'block';" class="item hider">
LAX <div  class="status" id="lax-status2"></div>
</div>
<div class="hider" id="lax">
<div class="sub-item" onclick="dc = '172.19.9.15'; confirmRebuild(dc);">
Rebuild MQ
</div>
<div class="sub-item" onclick="dc = '172.19.9.15'; getLogsRun(dc);">
MQ Jinst Log
</div>
<div class="sub-item" onclick="dc = 'c19'; serviceRecUp(dc);">
Rec-Upload Restart
</div>
<div class="sub-item" onclick="dc = 'c19'; retryRec(dc);">
Retry Old Recordings
</div>
<div class="sub-item" onclick="dc = 'c19'; remoteRestart(dc);">
Asterisk-Remote Restart
</div>
<div class="sub-item" onclick="dc = 'c19'; activeMQPage(dc);">
ActiveMQ Page
</div>
</div>
<div id='nyc-show'  onclick="document.getElementById('nyc').style.display = 'block'; document.getElementById('nyc-hide').style.display = 'block'; document.getElementById('nyc-show').style.display = 'none';" class="item">
NYC <div  class="status" id="nyc-status"></div>
</div>
<div id='nyc-hide'   onclick="document.getElementById('nyc').style.display = 'none'; document.getElementById('nyc-hide').style.display = 'none'; document.getElementById('nyc-show').style.display = 'block';" = 'block';" class="item hider">
NYC <div  class="status" id="nyc-status2"></div>
</div>
<div class="hider" id="nyc">
<div class="sub-item" onclick="dc = '172.20.9.15'; confirmRebuild(dc);">
Rebuild MQ
</div>
<div class="sub-item" onclick="dc = '172.20.9.15'; getLogsRun(dc);">
MQ Jinst Log
</div>
<div class="sub-item" onclick="dc = 'c20'; serviceRecUp(dc);">
Rec-Upload Restart
</div>
<div class="sub-item" onclick="dc = 'c20'; retryRec(dc);">
Retry Old Recordings
</div>
<div class="sub-item" onclick="dc = 'c20'; remoteRestart(dc);">
Asterisk-Remote Restart
</div>
<div class="sub-item" onclick="dc = 'c20'; activeMQPage(dc);">
ActiveMQ Page
</div>
</div>
<div id='atl-show'  onclick="document.getElementById('atl').style.display = 'block'; document.getElementById('atl-hide').style.display = 'block'; document.getElementById('atl-show').style.display = 'none';" class="item">
ATL <div  class="status" id="atl-status"></div>
</div>
<div id='atl-hide'   onclick="document.getElementById('atl').style.display = 'none'; document.getElementById('atl-hide').style.display = 'none'; document.getElementById('atl-show').style.display = 'block';" = 'block';" class="item hider">
ATL <div  class="status" id="atl-status2"></div>
</div>
<div class="hider" id="atl">
<div class="sub-item" onclick="dc = '172.22.9.15'; confirmRebuild(dc);">
Rebuild MQ
</div>
<div class="sub-item" onclick="dc = '172.22.9.15'; getLogsRun(dc);">
MQ Jinst Log
</div>
<div class="sub-item" onclick="dc = 'c22'; serviceRecUp(dc);">
Rec-Upload Restart
</div>
<div class="sub-item" onclick="dc = 'c22'; retryRec(dc);">
Retry Old Recordings
</div>
<div class="sub-item" onclick="dc = 'c22'; remoteRestart(dc);">
Asterisk-Remote Restart
</div>
<div class="sub-item" onclick="dc = 'c22'; activeMQPage(dc);">
ActiveMQ Page
</div>
</div>
<div id='geg-show'  onclick="document.getElementById('geg').style.display = 'block'; document.getElementById('geg-hide').style.display = 'block'; document.getElementById('geg-show').style.display = 'none';" class="item">
GEG <div  class="status" id="geg-status"></div>
</div>
<div id='geg-hide'   onclick="document.getElementById('geg').style.display = 'none'; document.getElementById('geg-hide').style.display = 'none'; document.getElementById('geg-show').style.display = 'block';" = 'block';" class="item hider">
GEG <div  class="status" id="geg-status2"></div>
</div>
<div class="hider" id="geg">
<div class="sub-item" onclick="dc = '172.23.9.15'; confirmRebuild(dc);">
Rebuild MQ
</div>
<div class="sub-item" onclick="dc = '172.23.9.15'; getLogsRun(dc);">
MQ Jinst Log
</div>
<div class="sub-item" onclick="dc = 'c23'; serviceRecUp(dc);">
Rec-Upload Restart
</div>
<div class="sub-item" onclick="dc = 'c23'; retryRec(dc);">
Retry Old Recordings
</div>
<div class="sub-item" onclick="dc = 'c23'; remoteRestart(dc);">
Asterisk-Remote Restart
</div>
<div class="sub-item" onclick="dc = 'c23'; activeMQPage(dc);">
ActiveMQ Page
</div>
</div>
<div class="item" onclick="confirmRebuildAll();">
REBUILD ALL
</div>
</div>
<!-- The following section contains the body of the command output, title & body -->
<div id="output-body">
<div id="output-title">
Command Output - <div id="title-sub"> Pending Request</div>
</div>
<div id="output-logs">
<div id="rebuildall">
<div id="rebuildall-head">
ORD
</div>
<div id="rebuildall-head">
LAX
</div>
<div id="rebuildall-head">
NYC
</div>
<div id="rebuildall-head">
ATL
</div>
<div id="rebuildall-head">
GEG
</div>
<div id="rebuildall-ord">
</div>
<div id="rebuildall-lax">
</div>
<div id="rebuildall-nyc">
</div>
<div id="rebuildall-atl">
</div>
<div id="rebuildall-geg">
</div>
</div>
</div>
</div>

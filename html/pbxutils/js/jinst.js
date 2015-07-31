var interval;
var starter = document.getElementById('start');
var stopper = document.getElementById('stop');
var dc = 'pending';

function confirmRebuild(dc) {
  if(confirm("Are you sure you want to rebuild this instance?")) {
    mqRebuild(dc);
  }
}

function confirmRebuildAll() {
    if(confirm("Are you sure you want to rebuild 'All the things'?")) {
          mqRebuildAll();
            }
}

function mqRebuildAll () {
  stop();
  document.getElementById("rebuildall").style.display = "block";
  document.getElementById("title-sub").innerHTML = "Rebuilding all!"
setTimeout(mqRebuildAllOrd, 1000);
  setTimeout(mqRebuildAllLax, 20000);
  setTimeout(mqRebuildAllNyc, 40000);
  setTimeout(mqRebuildAllAtl, 60000);
  setTimeout(mqRebuildAllGeg, 80000);
}

function mqRebuildAllOrd () {
//Rebuilding ORD in rebuild all
  document.getElementById("rebuildall-ord").innerHTML = "Rebuilding ORD!"
  $.ajax({
    type: "post",
    url: "mqrebuild.php",
    dataType: 'json',
    data: {'dc': '172.25.9.15'},
    success: function(get) {
    document.getElementById("rebuildall-ord").innerHTML = 'ORD has been rebuilt!'
    chiint = setInterval(checkStatusORD, 30000)
    rebuildAllStopOrd = setInterval(rebuildAllLogsOrd, 1000);
                            }
        });
}

function mqRebuildAllLax () {
//Rebuilding LAX in rebuild all
  document.getElementById("rebuildall-lax").innerHTML = "Rebuilding LAX!"
  $.ajax({
    type: "post",
    url: "mqrebuild.php",
    dataType: 'json',
    data: {'dc': '172.19.9.15'},
    success: function(get) {
    document.getElementById("rebuildall-lax").innerHTML = 'LAX has been rebuilt!'
    laxint = setInterval(checkStatusLAX, 30000)
    rebuildAllStopLax = setInterval(rebuildAllLogsLax, 1000);
                            }
        });
}

function mqRebuildAllNyc () {
//Rebuilding NYC in rebuild all
  document.getElementById("rebuildall-nyc").innerHTML = "Rebuilding NYC!"
  $.ajax({
    type: "post",
    url: "mqrebuild.php",
    dataType: 'json',
    data: {'dc': '172.20.9.15'},
    success: function(get) {
    document.getElementById("rebuildall-nyc").innerHTML = 'NYC has been rebuilt!'
    nycint = setInterval(checkStatusNYC, 30000)
    rebuildAllStopNyc = setInterval(rebuildAllLogsNyc, 1000);
                            }
        });
}

function mqRebuildAllAtl () {
//Rebuilding ATL in rebuild all
  document.getElementById("rebuildall-atl").innerHTML = "Rebuilding ATL!"
  $.ajax({
    type: "post",
    url: "mqrebuild.php",
    dataType: 'json',
    data: {'dc': '172.22.9.15'},
    success: function(get) {
    document.getElementById("rebuildall-atl").innerHTML = 'ATL has been rebuilt!'
    atlint = setInterval(checkStatusATL, 30000)
    rebuildAllStopAtl = setInterval(rebuildAllLogsAtl, 1000);
                            }
        });
}

function mqRebuildAllGeg () {
//Rebuilding GEG in rebuild all
  document.getElementById("rebuildall-geg").innerHTML = "Rebuilding GEG!"
  $.ajax({
    type: "post",
    url: "mqrebuild.php",
    dataType: 'json',
    data: {'dc': '172.23.9.15'},
    success: function(get) {
    document.getElementById("rebuildall-geg").innerHTML = 'GEG has been rebuilt!'
    gegint = setInterval(checkStatusGEG, 30000)
    rebuildAllStopGeg = setInterval(rebuildAllLogsGeg, 1000);
                            }
        });
}

//Rebuild all get logs request and restart asterisk remote after rebuild
function rebuildAllLogsOrd () {
     $.ajax({
     type: "post",
     url: "jinst.php",
     dataType: 'json',
     data: {'dc': '172.25.9.15'},
     success: function(get) {
       var myJSONString = JSON.stringify(get.jinstLog);
       var myEscapedJSONString = myJSONString.replace(/\\n/g, "<br>")
                                             .replace(/\\'/g, "\\'")
                                             .replace(/\\"/g, '\\"')
                                             .replace(/\\&/g, "\\&")
                                             .replace(/\\t/g, "\\t")
                                             .replace(/\\b/g, "\\b")
                                             .replace(/\\f/g, "\\f");
       document.getElementById("rebuildall-ord").innerHTML = myEscapedJSONString

      if(get.statusalert == 'finished') {
          clearInterval(rebuildAllStopOrd);
          document.getElementById("rebuildall-ord").style.color = "limegreen"
}}});}
//Rebuild all get logs request and restart asterisk remote after rebuild
function rebuildAllLogsLax () {
     $.ajax({
     type: "post",
     url: "jinst.php",
     dataType: 'json',
     data: {'dc': '172.19.9.15'},
     success: function(get) {
       var myJSONString = JSON.stringify(get.jinstLog);
       var myEscapedJSONString = myJSONString.replace(/\\n/g, "<br>")
                                             .replace(/\\'/g, "\\'")
                                             .replace(/\\"/g, '\\"')
                                             .replace(/\\&/g, "\\&")
                                             .replace(/\\t/g, "\\t")
                                             .replace(/\\b/g, "\\b")
                                             .replace(/\\f/g, "\\f");
       document.getElementById("rebuildall-lax").innerHTML = myEscapedJSONString

      if(get.statusalert == 'finished') {
          clearInterval(rebuildAllStopLax);
          document.getElementById("rebuildall-lax").style.color = "limegreen"
}}});}
//Rebuild all get logs request and restart asterisk remote after rebuild
function rebuildAllLogsNyc () {
     $.ajax({
     type: "post",
     url: "jinst.php",
     dataType: 'json',
     data: {'dc': '172.20.9.15'},
     success: function(get) {
       var myJSONString = JSON.stringify(get.jinstLog);
       var myEscapedJSONString = myJSONString.replace(/\\n/g, "<br>")
                                             .replace(/\\'/g, "\\'")
                                             .replace(/\\"/g, '\\"')
                                             .replace(/\\&/g, "\\&")
                                             .replace(/\\t/g, "\\t")
                                             .replace(/\\b/g, "\\b")
                                             .replace(/\\f/g, "\\f");
       document.getElementById("rebuildall-nyc").innerHTML = myEscapedJSONString

      if(get.statusalert == 'finished') {
          clearInterval(rebuildAllStopNyc);
          document.getElementById("rebuildall-nyc").style.color = "limegreen"
}}});}
//Rebuild all get logs request and restart asterisk remote after rebuild
function rebuildAllLogsAtl () {
     $.ajax({
     type: "post",
     url: "jinst.php",
     dataType: 'json',
     data: {'dc': '172.22.9.15'},
     success: function(get) {
       var myJSONString = JSON.stringify(get.jinstLog);
       var myEscapedJSONString = myJSONString.replace(/\\n/g, "<br>")
                                             .replace(/\\'/g, "\\'")
                                             .replace(/\\"/g, '\\"')
                                             .replace(/\\&/g, "\\&")
                                             .replace(/\\t/g, "\\t")
                                             .replace(/\\b/g, "\\b")
                                             .replace(/\\f/g, "\\f");
       document.getElementById("rebuildall-atl").innerHTML = myEscapedJSONString

      if(get.statusalert == 'finished') {
          clearInterval(rebuildAllStopAtl);
          document.getElementById("rebuildall-atl").style.color = "limegreen"
}}});}
//Rebuild all get logs request and restart asterisk remote after rebuild
function rebuildAllLogsGeg () {
     $.ajax({
     type: "post",
     url: "jinst.php",
     dataType: 'json',
     data: {'dc': '172.23.9.15'},
     success: function(get) {
       var myJSONString = JSON.stringify(get.jinstLog);
       var myEscapedJSONString = myJSONString.replace(/\\n/g, "<br>")
                                             .replace(/\\'/g, "\\'")
                                             .replace(/\\"/g, '\\"')
                                             .replace(/\\&/g, "\\&")
                                             .replace(/\\t/g, "\\t")
                                             .replace(/\\b/g, "\\b")
                                             .replace(/\\f/g, "\\f");
       document.getElementById("rebuildall-geg").innerHTML = myEscapedJSONString

      if(get.statusalert == 'finished') {
          clearInterval(rebuildAllStopGeg);
          document.getElementById("rebuildall-geg").style.color = "limegreen"
}}});}






function confirmRestart(dc) {
  if(confirm("Are you sure you want to rebuild this instance?")) {
    mqRestart(dc);
  }
}
function activeMQPage(dc) {
  if(dc == 'c1') {document.getElementById('output-logs').innerHTML = '<iframe src="http://10.101.15.1:8161" id="serverurl" width="98%" height="650"></iframe>'}
if(dc == 'c25') {document.getElementById('output-logs').innerHTML = '<iframe src="http://mq.ord.devops.jive.com/admin/queues.jsp" id="serverurl" width="98%" height="650"></iframe>'}
if(dc == 'c19') {document.getElementById('output-logs').innerHTML = '<iframe src="http://mq.lax.devops.jive.com/admin/queues.jsp" id="serverurl" width="98%" height="650"></iframe>'}
if(dc == 'c20') {document.getElementById('output-logs').innerHTML = '<iframe src="http://mq.nyc.devops.jive.com/admin/queues.jsp" id="serverurl" width="98%" height="650"></iframe>'}
if(dc == 'c22') {document.getElementById('output-logs').innerHTML = '<iframe src="http://mq.atl.devops.jive.com/admin/queues.jsp" id="serverurl" width="98%" height="650"></iframe>'}
if(dc == 'c23') {document.getElementById('output-logs').innerHTML = '<iframe src="http://mq.geg.devops.jive.com/admin/queues.jsp" id="serverurl" width="98%" height="650"></iframe>'}
}


function checkStatus() {
 ordint  =  setInterval(checkStatusORD, 10000);
 laxint  =  setInterval(checkStatusLAX, 10000);
 nycint  =  setInterval(checkStatusNYC, 10000);
 atlint  =  setInterval(checkStatusATL, 10000);
 gegint  =  setInterval(checkStatusGEG, 10000);
 chiint =  setInterval(checkStatusCHI, 10000);
}

function checkStatusCHI() {
$.ajax({
       type: "post",
       url: "c1activemqstatus.php",
       dataType: 'json',
       data: {'dc': '172.25.9.15'},
       success: function(get) {
         if(get.statusalert == 'finished') {
            clearInterval(chiint);
            document.getElementById('chi-status').style.background = 'limegreen';
            document.getElementById('chi-status2').style.background = 'limegreen';
              }
         else {
          document.getElementById('chi-status').style.background = 'red';
          document.getElementById('chi-status2').style.background = 'red';
              }
      }
});
}


function checkStatusORD() {
$.ajax({
       type: "post",
       url: "jinst.php",
       dataType: 'json',
       data: {'dc': '172.25.9.15'},
       success: function(get) {
         if(get.statusalert == 'finished') {
            clearInterval(ordint);
            document.getElementById('ord-status').style.background = 'limegreen';
            document.getElementById('ord-status2').style.background = 'limegreen';
              }
         else {
          document.getElementById('ord-status').style.background = 'red';
          document.getElementById('ord-status2').style.background = 'red';
              }
      }
});
}

function checkStatusLAX() {
$.ajax({
         type: "post",
         url: "jinst.php",
         dataType: 'json',
         data: {'dc': '172.19.9.15'},
         success: function(get) {
          if(get.statusalert == 'finished') {
          clearInterval(laxint);
          document.getElementById('lax-status').style.background = 'limegreen';
          document.getElementById('lax-status2').style.background = 'limegreen';
                                            }
          else {
          document.getElementById('lax-status').style.background = 'red';
          document.getElementById('lax-status2').style.background = 'red';
                }
      }
});
}

function checkStatusNYC() {
$.ajax({
         type: "post",
         url: "jinst.php",
         dataType: 'json',
         data: {'dc': '172.20.9.15'},
         success: function(get) {
          if(get.statusalert == 'finished') {
          clearInterval(nycint);
          document.getElementById('nyc-status').style.background = 'limegreen';
          document.getElementById('nyc-status2').style.background = 'limegreen';
                                                            }
        else {
          document.getElementById('nyc-status').style.background = 'red';
          document.getElementById('nyc-status2').style.background = 'red';
                                                       }
      }
});
}

function checkStatusATL() {
$.ajax({
         type: "post",
         url: "jinst.php",
         dataType: 'json',
         data: {'dc': '172.22.9.15'},
         success: function(get) {
        if(get.statusalert == 'finished') {
          clearInterval(atlint);
          document.getElementById('atl-status').style.background = 'limegreen';
          document.getElementById('atl-status2').style.background = 'limegreen';
                                                            }
        else {
          document.getElementById('atl-status').style.background = 'red';
          document.getElementById('atl-status2').style.background = 'red';
                                                       }
      }
});
}

function checkStatusGEG() {
$.ajax({
         type: "post",
         url: "jinst.php",
         dataType: 'json',
         data: {'dc': '172.23.9.15'},
         success: function(get) {
        if(get.statusalert == 'finished') {
          clearInterval(gegint);
        document.getElementById('geg-status').style.background = 'limegreen';
        document.getElementById('geg-status2').style.background = 'limegreen';
                                                            }
      else {
        document.getElementById('geg-status').style.background = 'red';
        document.getElementById('geg-status2').style.background = 'red';
                                                       }
      }
});
}





function getLogs (dc, item) {
     $.ajax({
     type: "post",
     url: "jinst.php",
     dataType: 'json',
     data: {'dc': dc},
     success: function(get) {
       var myJSONString = JSON.stringify(get.jinstLog);
       var myEscapedJSONString = myJSONString.replace(/\\n/g, "<br>")
                                             .replace(/\\'/g, "\\'")
                                             .replace(/\\"/g, '\\"')
                                             .replace(/\\&/g, "\\&")
                                             .replace(/\\t/g, "\\t")
                                             .replace(/\\b/g, "\\b")
                                             .replace(/\\f/g, "\\f");
       document.getElementById("output-logs").innerHTML = myEscapedJSONString
       
      if(get.statusalert == 'finished') {
          stop();
          document.getElementById("output-logs").style.color = "limegreen"
  

}
     }

    });
}

function getActiveMQLogs (dc) {
     $.ajax({
     type: "post",
     url: "c1activemqlog.php",
     dataType: 'json',
     success: function(get) {
       var myJSONString = JSON.stringify(get.jinstLog);
       var myEscapedJSONString = myJSONString.replace(/\\n/g, "<br>")
                                             .replace(/\\'/g, "\\'")
                                             .replace(/\\"/g, '\\"')
                                             .replace(/\\&/g, "\\&")
                                             .replace(/\\t/g, "\\t")
                                             .replace(/\\b/g, "\\b")
                                             .replace(/\\f/g, "\\f");
       document.getElementById("output-logs").innerHTML = myEscapedJSONString
       if(get.statusalert == 'finished') {
         clearInterval(interval);
       }
     }
    });
}

function retryRec (dc) {
      stop();
      document.getElementById("title-sub").innerHTML = "Retry-old-recordings on enc1." + dc
      document.getElementById("output-logs").innerHTML = "Request is being processed!"
       $.ajax({
              type: "post",
            url: "retryrec.php",
            dataType: 'json',
            data: {'dc': dc},
            success: function(get) {
                     var myJSONString = JSON.stringify(get.jinstLog);
                            var myEscapedJSONString = myJSONString.replace(/\\n/g, "<br>")
                                                      .replace(/\\'/g, "\\'")
                                                      .replace(/\\"/g, '\\"')
                                                      .replace(/\\&/g, "\\&")
                                                      .replace(/\\t/g, "\\t")
                                                      .replace(/\\b/g, "\\b")
                                                      .replace(/\\f/g, "\\f");
              document.getElementById("output-logs").innerHTML = 'Retry-old-recordings script has been run on the requested server'
                   }
           });
}

function serviceRecUp (dc) {
  stop();
  document.getElementById("title-sub").innerHTML = "Service service-recording-upload restart on enc1." + dc
  document.getElementById("output-logs").innerHTML = "Request is being processed!"
    $.ajax({
    type: "post",
    url: "servicerecup.php",
    dataType: 'json',
    data: {'dc': dc},
    success: function(get) {
    var myJSONString = JSON.stringify(get.jinstLog);
   document.getElementById("output-logs").innerHTML = 'Service service-recording-upload restart script has been run on the requested server'
                          }
            });
}




function remoteRestart (dc) {
stop();
   document.getElementById("title-sub").innerHTML = "Service-asterisk-remote restart on " + dc
   document.getElementById("output-logs").innerHTML = "Request is being processed!"
   $.ajax({
     type: "post",
     url: "remoterestart.php",
     dataType: 'json',
     data: {'dc': dc},
     success: function(get) {
     var myJSONString = JSON.stringify(get.jinstLog);
     var myEscapedJSONString = myJSONString.replace(/\\n/g, "<br>")
         .replace(/\\'/g, "\\'")
         .replace(/\\"/g, '\\"')
         .replace(/\\&/g, "\\&")
         .replace(/\\t/g, "\\t")
         .replace(/\\b/g, "\\b")
         .replace(/\\f/g, "\\f");
    document.getElementById("output-logs").innerHTML = 'Service-remote-asterisk restart script has been run on the requested server'
                              }
          });
}

function mqRebuild (dc) {
  stop();
  document.getElementById("title-sub").innerHTML = "Rebuilding activeMQ on " + dc
  document.getElementById("output-logs").innerHTML = "Request is being processed!"
  $.ajax({
    type: "post",
    url: "mqrebuild.php",
    dataType: 'json',
    data: {'dc': dc},
    success: function(get) {
    var myJSONString = JSON.stringify(get.jinstLog);
    var myEscapedJSONString = myJSONString.replace(/\\n/g, "<br>")
     .replace(/\\'/g, "\\'")
     .replace(/\\"/g, '\\"')
     .replace(/\\&/g, "\\&")
     .replace(/\\t/g, "\\t")
     .replace(/\\b/g, "\\b")
     .replace(/\\f/g, "\\f");
    document.getElementById("output-logs").innerHTML = 'MQ has been rebuilt! Status will update in the next thirty seconds.'
                            }
        });

      if(dc == '172.25.9.15') {ordint = setInterval(checkStatusORD, 30000) }
      if(dc == '172.19.9.15') {laxint = setInterval(checkStatusLAX, 30000) }
      if(dc == '172.20.9.15') {nycint = setInterval(checkStatusNYC, 30000) }
      if(dc == '172.22.9.15') {atlint = setInterval(checkStatusATL, 30000) }
      if(dc == '172.23.9.15') {gegint = setInterval(checkStatusGEG, 30000) }
}

function mqRestart (dc) {
  stop();
  document.getElementById("title-sub").innerHTML = "Restarting activeMQ on " + dc
  document.getElementById("output-logs").innerHTML = "Request is being processed!"
  $.ajax({
    type: "post",
    url: "c1activemqrestart.php",
    dataType: 'json',
    data: {'dc': dc},
    success: function(get) {
    document.getElementById("output-logs").innerHTML = 'MQ has been restarted'
                            }
        });
 chiint = setTimeout(checkStatusCHI, 30000)
  setTimeout(getMQLogsRun, 5000, dc);
}






function getLogsRun (dc) {
  stop();
  document.getElementById("title-sub").innerHTML = "Jinst Logs from " + dc
  document.getElementById("output-logs").innerHTML = "Request is being processed!"

  interval =  setInterval(getLogs, 1000, dc);
     
};

function getMQLogsRun (dc) {
    stop();
    document.getElementById("title-sub").innerHTML = "Active MQ logs from " + dc
    document.getElementById("output-logs").innerHTML = "Request is being processed!"
    interval  =  setInterval(getActiveMQLogs, 1000, dc);

};



function stop () {
          clearInterval(interval);
          document.getElementById("output-logs").style.color = "#AAA";
          //If rebuild all had been run, we need to hide it. if != null.... :)
          if ($('#rebuildall').length > 0) {
          document.getElementById("rebuildall").style.display = "none";
          }
};


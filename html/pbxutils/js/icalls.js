

function getCalls () {
       $.ajax({
              type: "post",
            url: "icallstest.php",
            dataType: 'json',
            success: function(get) {
                calls = get.jinstLog
                console.log(calls.length);
                for (i = 0; i < calls.length; i++) {
                      console.log(calls[i]);
                      var regip = /[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/
                      var regcalls = /"calls", [0-9]+/
                      var pbx = regip.exec(calls[i])
                      pbx = pbx[0].replace(/\./g,'')
                      pbxid = 'calls' + pbx
                      var channels = regcalls.exec(calls[i])
                      regcalls2 = /[0-9]+/
                      channels2 = regcalls2.exec(channels[0]);
                      if (document.getElementById(pbxid)) {
                              document.getElementById(pbxid).innerHTML = channels2
                        }

                     }
                   }
           });
}

function disableAlerts (disableip) {
       $.ajax({
            type: "post",
            url: "disablealert.php",
            dataType: 'json',
            data: {'ip': disableip},
               success: function(pingJsonData) {}
           });
}




$(document).ready ( function(){
     getCalls();
});

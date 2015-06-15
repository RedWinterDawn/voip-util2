var counting = 1;
var doChange = true;
var hop = 0;
function pingHost (remoteHost, dc) {
  hop++;
  if(doChange) {
     $('#ping-status').addClass('status-info').html('Querying...').show();
     $.ajax({
     type: "post",
     url: "ping.php",
     dataType: 'json',
     data: {'remoteHost': remoteHost, 'dc': $('#dc').val(), 'hop': hop, 'round' : 1},
     success: function(pingJsonData) {
        $('#ping-row').append($("<div class=\"tracerow-hop\">"+counting+"</div>"+pingJsonData.pingInfo).hide().fadeIn(500));
        counting++;
        if ( pingJsonData.status == remoteHost) {
           stopPing ();
        }
        }
    });
     setTimeout(pingHost2, 300, remoteHost, dc);
  }
}

     function pingHost2 (remoteHost, dc) {
 $.ajax({
        type: "post",
        url: "ping.php",
        dataType: 'json',
        data: {'remoteHost': remoteHost, 'dc': $('#dc').val(), 'hop': hop, 'round' : 2},
        success: function(pingJsonData) {
                  $('#ping-row').append($(pingJsonData.pingInfo).hide().fadeIn(500));
                                          }
     });
      setTimeout(pingHost3, 300, remoteHost, dc);

     }
function pingHost3 (remoteHost, dc) {
$.ajax({
          type: "post",
          url: "ping.php",
          dataType: 'json',
          data: {'remoteHost': remoteHost, 'dc': $('#dc').val(), 'hop': hop, 'round': 3},
          success: function(pingJsonData) {
          $('#ping-row').append($(pingJsonData.pingInfo).hide().fadeIn(500));
         if(counting >= 15) {
            $('#ping-status').fadeOut();
           }
         }
     });
 }


function ping5 (remoteHost, dc) {
  counting  = 1;
  doChange = true;
  $('#ping-row').children().remove();
  $('#ping-table').fadeIn();
  for(var i=1; i<=16; i++) {
    nextRun = (i-1) * 1000;
    setTimeout(pingHost, nextRun, remoteHost,  dc);
     }
};

function stopPing () {
          doChange = false;
          $('#ping-status').fadeOut();
};


$(document).ready(function() {
      $('#remoteHost').keyup(function(e) {
     if(e.keyCode == 13) {
     ping5($('#remoteHost').val(), $('#dc').val());
     }
                  });
          $('#ping-table').hide();
});

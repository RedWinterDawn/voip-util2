var counting = 1;
var hop = 0;
function pingHost (remoteHost, dc) {
  hop++;
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


function ping5 (remoteHost, dc, counting) {
     $('#ping-status').addClass('status-info').html('Querying...').show();
  $('#ping-table').fadeIn();
  $('#ping-row').children().remove();
    pingInterval = setInterval(pingHost, 1500, remoteHost,  dc);
};

function stopPing () {
  clearInterval(pingInterval);
  hop = 0;
  counting = 1;
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

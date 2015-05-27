var counting = 1;
var doChange = true;
function pingHost (remoteHost, count, dc) {
  if(doChange) {
     $('#ping-status').addClass('status-info').html('Querying...').show();
     $.ajax({
     type: "post",
     url: "ping.php",
     dataType: 'json',
     data: {'remoteHost': remoteHost, 'dc': $('#dc').val()},
     success: function(pingJsonData) {
        $('#ping-row').append($("<div class=\"rowping-packet\">"+counting+"</div>"+pingJsonData.pingInfo).hide().fadeIn(500));
        if(counting >= count) {
          $('#ping-status').fadeOut();
          }
        counting++;
        }
    });
 }
}

function ping5 (remoteHost, count, dc) {
  counting  = 1;
  doChange = true;
  $('#ping-row').children().remove();
  $('#ping-table').fadeIn();
  for(var i=1; i<=count; i++) {
    nextRun = (i-1) * 1000;
    setTimeout(pingHost, nextRun, remoteHost, count, dc);
     }
};

function stopPing () {
          doChange = false;
          $('#ping-status').fadeOut();
};


$(document).ready(function() {
      $('#remoteHost').keyup(function(e) {
     if(e.keyCode == 13) {
     ping5($('#remoteHost').val(), $('#count').val(), $('#dc').val());
     }
                  });
          $('#ping-table').hide();
});

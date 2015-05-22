var counting = 1;
var doChange = true;
function pingHost (remoteHost, count) {
  if(doChange) {
     $('#ping-status').addClass('status-info').html('Querying...').show();
     $.ajax({
     type: "post",
     url: "ping.php",
     dataType: 'json',
     data: "remoteHost=" + remoteHost,
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

function ping5 (remoteHost, count) {
  counting  = 1;
  doChange = true;
  $('#ping-row').children().remove();
  $('#ping-table').fadeIn();
  for(var i=1; i<=count; i++) {
    nextRun = (i-1) * 1000;
    setTimeout(pingHost, nextRun, remoteHost, count);
     }
};

function stopPing () {
          doChange = false;
          $('#ping-status').fadeOut();
};


$(document).ready(function() {
      $('#remoteHost').keyup(function(e) {
     if(e.keyCode == 13) {
     ping5($('#remoteHost').val(), $('#count').val());
     }
                  });
          $('#ping-table').hide();
});

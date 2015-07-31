
function featureSubmit() {
  var feature = prompt("What is your feature request?");
  document.getElementById("feature-requester").value = feature;
  document.getElementById("feature-requester-form").submit();
}

function toggle_visibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
}


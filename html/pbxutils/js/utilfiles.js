// Function runs scipt lsscript.php, which updates the util_files table in the util db with the current files and directories. 
// First runs and ajax, and gets a response of 4 key value pairs in json. Each pair is filled with the changes separated by a ;.
// With the data we get back, replace ; with <br>. Check if there is any info, if not == 'no changes made.'.
// then apply it to the body of the popup window. id=result-body
function updateFileDB () {
     document.getElementById("result-title").innerHTML = "Update DB Script Results";
     $.ajax({
     type: "post",
     url: "lsscript.php",
     dataType: 'json',
     success: function(result) {
          result.addFile = result.addFile.replace(/;/g, "<br>");
          result.deleteFile = result.deleteFile.replace(/;/g, "<br>");
          result.addDirectory = result.addDirectory.replace(/;/g, "<br>");
          result.deleteDirectory = result.deleteDirectory.replace(/;/g, "<br>");
       if (result.addFile == '') {
         result.addFile = 'No changes made.';
       }
       if (result.deleteFile == '') {
         result.deleteFile = 'No changes made.';
       }
       if (result.addDirectory == '') {
         result.addDirectory = 'No changes made.';
       }
       if (result.deleteDirectory == '') {
         result.deleteDirectory = 'No changes made.';
       }
        document.getElementById("result-body").innerHTML = '<div class="update-title">Files Added</div><div>' + result.addFile + '</div><div class="update-title">Files Deleted</div><div>'+result.deleteFile + '</div><div class="update-title">Directories Added</div><div>'+result.addDirectory + '</div><div class="update-title">Directories Deleted</div><div>'+result.deleteDirectory +'</div><div id="refresh"><a href="utilfiles.php">For the changes to show up, Please click here to refresh!</a></div>';
        }
    });
}

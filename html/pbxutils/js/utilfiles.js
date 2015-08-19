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
          result.updateaccess = result.updateaccess.replace(/;/g, "<br>");
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
       if (result.updateaccess == '') {
         result.updateaccess = 'No changes made.';
       }
        document.getElementById("result-body").innerHTML = '<div class="update-title">Files Added</div><div>' + result.addFile + '</div><div class="update-title">Files Deleted</div><div>'+result.deleteFile + '</div><div class="update-title">Directories Added</div><div>'+result.addDirectory + '</div><div class="update-title">Directories Deleted</div><div>'+result.deleteDirectory +'</div><div class="update-title">Access Set Update</div><div>' + result.updateaccess + '</div><div id="refresh"><a href="utilfiles.php">For the changes to show up, Please click here to refresh!</a></div>';
        }
    });
}

function getUtilFileContents (filename) {
     document.getElementById("result-title").innerHTML = "Contents of " + filename;
     $.ajax({
     type: "post",
     url: "utilfilecontents.php",
     dataType: 'json',
       data: {'filename': filename},
     success: function(result) {
       if (result.image == 'false') {
       result.filecontents = result.filecontents.replace(/>/g, "&gt");
       result.filecontents = result.filecontents.replace(/</g, "&lt;");
       result.filecontents = result.filecontents.replace(/aAAbBaZZzyxxB/g, "<br>");
        document.getElementById("result-body").innerHTML =  result.filecontents ;
       }
       else {
         document.getElementById("result-body").innerHTML =  result.filecontents ;
       }
        }
    });
}
function fileDocumentation() {
document.getElementById("result-title").innerHTML = "Util File Documentation";
var resultBody = document.getElementById("result-body");
resultBody.innerHTML = "<div class='docLevel'>";
resultBody.innerHTML += "<div class='levelRow'><div class='docLevels' id='docLevelOne'>1</div><div class='docGroups'> - Tech Solutions Team<br></div></div>";
resultBody.innerHTML += "<div class='levelRow'><div class='docLevels' id='docLevelTwo'>2</div><div class='docGroups'> - Tech Solutions Team Leads, Field Engineers<br></div></div>";
resultBody.innerHTML += "<div class='levelRow'><div class='docLevels' id='docLevelThree'>3</div><div class='docGroups'> - Development<br></div></div>";
resultBody.innerHTML += "<div class='levelRow'><div class='docLevels' id='docLevelFour'>4</div><div class='docGroups'> - Devops<br></div></div>";
resultBody.innerHTML += "<p>This page is used to manage all of the files and subdirectories on produtils /var/www/html/pbxutils. This page uses the following resources.";
resultBody.innerHTML += "<ul><li>Database: -h rwdb -d util -tables util_files util_directories</li><li>lsscript.php -- Pulls information of files and puts it into db.</li><li>utilfilecontents.php - Pulls the contents of a file and outputs in json format. Also checks if file is an image</li><li>utilfiles.php - homebase to view information and call scripts </li></ul>";
resultBody.innerHTML += "<p>The page utilfiles.php contains the follow features:";
resultBody.innerHTML += "<ul><li>Update DB - This button calls the script lsscript.php, which determines all files deleted or added, directories delted or added, or files with checksession.php added or removed.</li><li>Documentation - This button shows this page.</li><li>filename - click on the filename and a window will pop up with the contents of file. If file is an image, it will display the image.</li><li>Access-Level - click here and it will allow you to select options 1-4 to change access level of file. If it is colored, checksession.php has been added to the file and authorization will be applied.</li><li> Description";
}

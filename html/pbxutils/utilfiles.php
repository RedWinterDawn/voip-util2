<!DOCTYPE html>
<html>
<head>
	<title>ProdTools Main</title>
<!-- setting the access level below, and referring to checksession to confrim login in and users access level -->
<?php
$accesslevel = 1;
include('checksession.php');
$dbconn = pg_connect("host=rwdb dbname=util user=postgres ") or die('Could not connect to util to look up util_files: '.pg_last_error());

if (isset($_POST['fileid'])) {
  $fileid = $_POST['fileid'];
  $fileaccess = $_POST['fileaccess'];
  $filedesc = $_POST['filedesc'];
  $fileauthor = $_POST['fileauthor'];
  
  $updatequery = "UPDATE util_files SET (access_level, file_description, author) = ('".$fileaccess."', '".$filedesc."', '".$fileauthor."') WHERE id = '".$fileid."';";
  $result = pg_query($dbconn, $updatequery);
  if (!$result) {
    $queryresult = "An error occurred on the query request!";
        exit;
  }
  else {
    $queryresult = "You have updated field ".$fileid." with the following values: ".$fileaccess.", ".$filedesc.", and ".$fileauthor.".";
  }
}

?>
	<? include 'menu.html'; ?>
	<link rel='stylesheet' href='stylesheet.css'>
  <link rel='stylesheet' href='style/utilfiles.css'>
    <script type='text/javascript'>
    function setFocus() {
		document.getElementById('search').focus();
	}
	</script>
</head>
<div id="result-feature" class="result-position" onclick="toggle_visibility('result-feature');">
  <div id="result-wrapper" onclick="toggle_visibility('result-feature');">
    <div id="result-container">
      <div id="result-exit" onclick="toggle_visibility('result-feature');">
        X
      </div> <!-- end of div result-exit-->
      <div id="result-title"></div>
      <div id="result-body"></div>
    </div> <!-- end of div result-container-->
  </div> <!-- end of div result-wrapper-->
</div> <!-- end of div result-feature-->

<body>
<div id="utilfilestitle"><h2>Util Files Management</h2></div>
<!-- Button shows the result feature div, and well as runs the script lsscript.php, and then displays the updates in the feature popup - with a link to refresh the page-->
<div id="updatescript" onclick="toggle_visibility('result-feature');updateFileDB();">Update DB</div><div id="queryresult"><?php if ($queryresult) { echo $queryresult;} ?> </div>
<div id="file-container">
  <div id="menu-head">
    <div id="file-head"> FILENAME </div>
    <div id="access-head">ACCESS-LEVEL</div>
    <div id="desc-head">DESCRIPTION</div>
    <div id="author-head">AUTHOR</div>
    <div id="date-head">DATE ADDED</div>
    <div id="submit-head">SUBMIT</div>
  </div> <!-- End of menu-head -->
  <div id="directories-list">
    <div class="directories-row" onclick="toggle_visibility('pbxutils/');">pbxutils/</div>
  <?php
$queryfiles = "SELECT filename, access_level, file_description, author, date_created, id FROM util_files WHERE directory = 'pbxutils/';";
       $resultfiles = pg_query($dbconn, $queryfiles);
        $x = 1;
        echo '<div id="pbxutils/" class="hidden-files">';
        while ($rowfiles = pg_fetch_row($resultfiles)) {
          $x++;
          if ($x % 2 == 0) {
                            echo '<div id="menu-row-even"><div id="file-head-body">'.$rowfiles[0].'</div>
                  <form method="post" action="">
                  <div id="access-head-body"><select id="accessoptions" name="fileaccess">
                      <option value="'.$rowfiles[1].'">'.$rowfiles[1].'</option>
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4</option>
                      </select>
                      </div>
                  <div id="desc-head"><textarea id="desctext" name="filedesc">'.$rowfiles[2].'</textarea></div>
                  <div id="author-head"><input type="text" id="authortext" name="fileauthor" value="'.$rowfiles[3].'"></div>
                  <div id="date-head">'.$rowfiles[4].'</div>
                  <div id="submit-head"><input type="hidden" name="fileid" value="'.$rowfiles[5].'"><input type="submit" id="filesubmit"></form></div></div>';
          }
          else {
                    echo '<div id="menu-row-odd"><div id="file-head-body">'.$rowfiles[0].'</div>
                  <form method="post" action="">
                  <div id="access-head-body"><select id="accessoptions" name="fileaccess">
                      <option value="'.$rowfiles[1].'">'.$rowfiles[1].'</option>
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4</option>
                      </select>
                      </div>
                  <div id="desc-head"><textarea id="desctext" name="filedesc">'.$rowfiles[2].'</textarea></div>
                  <div id="author-head"><input type="text" id="authortext" name="fileauthor" value="'.$rowfiles[3].'"></div>
                  <div id="date-head">'.$rowfiles[4].'</div>
                  <div id="submit-head"><input type="hidden" name="fileid" value="'.$rowfiles[5].'"><input type="submit" id="filesubmit"></form></div></div>';
          }
                                }
        echo '</div>';
    $query = "SELECT directory FROM util_directories;";
    $result = pg_query($dbconn, $query);
    if (!$result) {
      echo "An error occurred.\n";
      exit;
      }
    while ($row = pg_fetch_row($result)) {
      echo '
        <div class="directories-row" onclick="toggle_visibility(\''.$row[0].'\');">pbxutils/'.$row[0].'</div>';
      $queryfiles = "SELECT filename, access_level, file_description, author, date_created, id FROM util_files WHERE directory = 'pbxutils/".$row[0]."';";
       $resultfiles = pg_query($dbconn, $queryfiles);
      $x = 1;
      echo '<div id="'.$row[0].'" class="hidden-files">';
        while ($rowfiles = pg_fetch_row($resultfiles)) {
          $x++;
          if ($x % 2 == 0) {
            echo '<div id="menu-row-even"><div id="file-head-body">'.$rowfiles[0].'</div>
                  <form method="post" action="">
                  <div id="access-head-body"><select id="accessoptions" name="fileaccess">
                      <option value="'.$rowfiles[1].'">'.$rowfiles[1].'</option>
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4</option>
                      </select>
                      </div>
                  <div id="desc-head"><textarea id="desctext" name="filedesc">'.$rowfiles[2].'</textarea></div>
                  <div id="author-head"><input type="text" id="authortext" name="fileauthor" value="'.$rowfiles[3].'"></div>
                  <div id="date-head">'.$rowfiles[4].'</div>
                  <div id="submit-head"><input type="hidden" name="fileid" value="'.$rowfiles[5].'"><input type="submit" id="filesubmit"></form></div></div>';
          }
          else {
                echo '<div id="menu-row-odd"><div id="file-head-body">'.$rowfiles[0].'</div>
                  <form method="post" action="">
                  <div id="access-head-body"><select id="accessoptions" name="fileaccess">
                      <option value="'.$rowfiles[1].'">'.$rowfiles[1].'</option>
                      <option value="1">1</option>
                      <option value="2">2</option>
                      <option value="3">3</option>
                      <option value="4">4</option>
                      </select>
                      </div>
                  <div id="desc-head"><textarea id="desctext" name="filedesc">'.$rowfiles[2].'</textarea></div>
                  <div id="author-head"><input type="text" id="authortext" name="fileauthor" value="'.$rowfiles[3].'"></div>
                  <div id="date-head">'.$rowfiles[4].'</div>
                  <div id="submit-head"><input type="hidden" name="fileid" value="'.$rowfiles[5].'"><input type="submit" id="filesubmit"></form></div></div>';
          }
        }
      echo '</div>';
      }
  ?>
  </div> <!-- End of directories-list -->
</div> <!-- End of file-container -->


</body>
</html>
<!-- Javascript documents added below so css and page content can load faster. Jquery needed for ajax call -->
<script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="js/utilfiles.js"></script>

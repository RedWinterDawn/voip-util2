<!DOCTYPE html>
<html>
<head>
	<title>ProdTools Main</title>
<!-- setting the access level below, and referring to checksession to confrim login in and users access level -->
<?php
$accesslevel = 1;
include('checksession.php');
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
<div id="updatescript" onclick="toggle_visibility('result-feature');updateFileDB();">Update DB</div>
<div id="file-container">
  <div id="menu-head">
    <div id="file-head"> FILENAME </div>
    <div id="access-head">ACCESS-LEVEL</div>
    <div id="desc-head">DESCRIPTION</div>
    <div id="author-head">AUTHOR</div>
    <div id="date-head">DATE ADDED</div>
    <div id="submit-head">SUBMIT</div>
  </div> <!-- End of menu-head -->

</div> <!-- End of file-container -->


</body>
</html>
<script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="js/utilfiles.js"></script>

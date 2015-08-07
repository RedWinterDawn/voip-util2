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
<body>
<h2>Util Files Management</h2>
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

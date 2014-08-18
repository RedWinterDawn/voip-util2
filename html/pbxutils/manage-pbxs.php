<!DOCTYPE html>
<html>
<head>
	<title>PBX Management</title>
	<link rel='stylesheet' href='stylesheet.css' />
	<style>
	.fatty {
		align-self: center;
		font-size: 2em;
		font-weight: bolder;
	}
	.highlighted {
		color: white;
	}
	</style>
	<script type='text/javascript'>
		function showhide(pbx) {
			row = document.getElementById(pbx);
			row.style.display = (row.style.display == 'none' ? '' : 'none');
			button = document.getElementById(pbx.concat('_expander'));
			button.innerHTML = (row.style.display == 'none' ? '+' : '-');
		}
	</script>
</head>
<body>
<?
include('pbx-menu.html');
?>
<table border=1>
<?
for ($i = 0; $i < 15; $i ++) {
	if ($i % 2 == 0) {
		$col = 'highlighted';
	} else {
		$col = '';
	}
	echo "<tr class='$col'><td><a class='fatty' id='a${i}_expander' onclick='showhide(\"a$i\")' href='#'>+</a></td><td>This is number $i</td></tr>
	<tr class='$col' id='a$i' style='display:none'><td></td><td>This is the stuff about it!</td></tr>
	";
}
?>

</table>
</body>
</html>

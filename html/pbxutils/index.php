<html>
  <head>
    <link rel="stylesheet" href="stylesheet.css">
  </head>
  <body>
    <br/>
	<?php 
	if ($_SERVER['SERVER_ADDR'] == '10.101.8.1') {
		echo "<h1>Production PBX Utilities</h1>";
	} else {
		echo "<h1>Dev PBX Utilities</h1>";
	} ?>
	<ul>
	  <li class="dvdr"></li>
	  <li class="top">
		<div class="button_location">
		  <a class="button_design" type="submit" href="simple-migration.php"><b>Move Single Domain</b></a>
		</div>
	  </li>
	  <li class="top">
		<div class="button_location">
		  <a class="button_design" type="submit" href="mass-exodus.php">Mass Exodus</a>
		  <img src="new-small.png" />
		</div>
	  </li>
	  <li class="top bottom">
		<div class="button_location">
		  <a class="button_design" type="submit" href="events-report.php">Event Reports</a>
		  <img src="new-small.png" />
		</div>
	  </li>
	  <li class="top">
		<div class="button_location">
		  <a class="button_design" type="submit" href="pbx-availability.php">PBX Availability (abandon ship here)</a>
		</div>
	  </li>
	  <li class="top bottom">
		<div class="button_location">
		  <a class="button_design" type="submit" href="presence-status.php">Abandon Sleigh</a>
		  <img src="new-small.png" />
		</div>
	  </li>
	  <li class="top">
		<div class="button_location">
		  <a class="button_design" type="submit" href="domain-info.php?domain=jive">Customer PBX (domain) info (modify url for specific domain)</a>
		</div>
	  </li>
	  <li class="top">
		<div class="button_location">
		  <a class="button_design" type="submit" href="pbx-presence.php">PBX Presence / Santa</a>
		</div>
	  </li>
	  <li class="top">
		<div class="button_location">
		  <a class="button_design" type="submit" href="pbx-presence-vip-list.php">VIP list</a>
		</div>
	  </li>
	  <li class="dvdr"></li>
	  <li class="top bottom">
		<div class="button_location">
		  <a class="button_design" type="submit" href="peer.php">peer information (basic)</a>
		</div>
	  </li>
	  <li class="top">
		<div class="button_location">
		  <a class="button_design" type="submit" href="phone-model-count.php">phone count by type</a>
		</div>
	  </li>
	  <li class="top">
		<div class="button_location">
		  <a class="button_design" type="submit" href="pbx-state.php">PBX State</a>
		</div>
	  </li>
	  <li class="dvdr"></li>
	  <li class="top bottom">
		<div class="button_location">
		  <a class="button_design" type="submit" href="manage-pbxs.php">Manage PBXs</a>
		</div>
	  </li>
	  <li class="top bottom">
		<div class="button_location">
		  <a class="button_design" type="submit" href="manage-santas.php">Manage Santas</a>
		  <img src="new-small.png" />
		</div>
	  </li>
	  <li class="top">
		<div class="button_location">
		  <a class="button_design" type="submit" href="thetool.php">PBX Comment Updater</a>
		</div>
	  </li>
	  <li class="dvdr"></li>
	</ul>
  </body>
</html>

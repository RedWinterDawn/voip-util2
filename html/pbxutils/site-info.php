<html>
<head>
<title>Site Info Page</title>
<link rel='stylesheet' href='expand.css'>
<link rel='stylesheet' href='stylesheet.css'>

<script type="text/javascript">
/* Copied from jdstiles.com */
var collapseDivs, collapseLinks;

function createDocumentStructure (tagName) {
  if (document.getElementsByTagName) {
    var elements = document.getElementsByTagName(tagName);
    collapseDivs = new Array(elements.length);
    collapseLinks = new Array(elements.length);
    for (var i = 0; i < elements.length; i++) {
      var element = elements[i];
      var siblingContainer;
      if (document.createElement && 
          (siblingContainer = document.createElement('div')) &&
          siblingContainer.style) 
      {
        var nextSibling = element.nextSibling;
        element.parentNode.insertBefore(siblingContainer, nextSibling);
        var nextElement = elements[i + 1];
        while (nextSibling != nextElement && nextSibling != null) {
          var toMove = nextSibling;
          nextSibling = nextSibling.nextSibling;
          siblingContainer.appendChild(toMove);
        }
        siblingContainer.style.display = 'none';
        
        collapseDivs[i] = siblingContainer;
        
        createCollapseLink(element, siblingContainer, i);
      }
      else {
        // no dynamic creation of elements possible
        return;
      }
    }
    createCollapseExpandAll(elements[0]);
  }
}

function createCollapseLink (element, siblingContainer, index) {
  var span;
  if (document.createElement && (span = document.createElement('span'))) {
    span.appendChild(document.createTextNode(String.fromCharCode(160)));
    var link = document.createElement('a');
    link.collapseDiv = siblingContainer;
    link.href = '#';
    link.appendChild(document.createTextNode('expand'));
    link.onclick = collapseExpandLink;
    collapseLinks[index] = link;
    span.appendChild(link);
    element.appendChild(span);
  }
}

function collapseExpandLink (evt) {
  if (this.collapseDiv.style.display == '') {
    this.parentNode.parentNode.nextSibling.style.display = 'none';
    this.firstChild.nodeValue = 'expand';
  }
  else {
    this.parentNode.parentNode.nextSibling.style.display = '';
    this.firstChild.nodeValue = 'collapse';
  }

  if (evt && evt.preventDefault) {
    evt.preventDefault();
  }
  return false;
}

function createCollapseExpandAll (firstElement) {
  var div;
  if (document.createElement && (div = document.createElement('div'))) {
    var link = document.createElement('a');
    link.href = '#';
    link.appendChild(document.createTextNode('expand all'));
    link.onclick = expandAll;
    div.appendChild(link);
    div.appendChild(document.createTextNode(' '));
    link = document.createElement('a');
    link.href = '#';
    link.appendChild(document.createTextNode('collapse all'));
    link.onclick = collapseAll;
    div.appendChild(link);
    firstElement.parentNode.insertBefore(div, firstElement);
  }
}

function expandAll (evt) {
  for (var i = 0; i < collapseDivs.length; i++) {
    collapseDivs[i].style.display = '';
    collapseLinks[i].firstChild.nodeValue = 'collapse';
  }
  
  if (evt && evt.preventDefault) {
    evt.preventDefault();
  }
  return false;
}

function collapseAll (evt) {
  for (var i = 0; i < collapseDivs.length; i++) {
    collapseDivs[i].style.display = 'none';
    collapseLinks[i].firstChild.nodeValue = 'expand';
  }
  
  if (evt && evt.preventDefault) {
    evt.preventDefault();
  }
  return false;
}
</script>
<script type="text/javascript">
window.onload = function (evt) {
  createDocumentStructure('h3');
}
</script>
<?
$subnetSites = array('101'=>'chicago-legacy','117'=>'pvu','118'=>'dfw','119'=>'lax','120'=>'nyc','121'=>'ord','122'=>'atl','123'=>'geg','124'=>'lon');
function rekey ($multiArray, $key, $value) {
	$newArray = Array();
	foreach ($multiArray as $array) {
		$newArray[$array[$key]] = $array[$value];
	}	
	return $newArray;
}

function countKey ($inArray, $key) {
	$newArray = Array();
	foreach ($inArray as $array) {
		$newArray[$array[$key]] += 1;
	}
	return $newArray;
}
?>
</head>
<body>
<? include('menu.html'); ?>
<h2>Site Info Page</h2>
Expand sections below to see information.
<br><br>
<?

$utilConn = pg_connect("host=rodb dbname=util user=postgres ") or die ("Could not connect to database ".pg_last_error());
$pbxsConn = pg_connect("host=rodb dbname=pbxs user=postgres ") or die ("Could not connect to database ".pg_last_error());

$mplsUtilQuery = "SELECT domain FROM mpls";
$mplsPbxsQuery = "SELECT location, assigned_server FROM resource_group WHERE domain in ('ihopenoonehasthisridiculousdomain'";
$mplsDomains = pg_fetch_all(pg_query($utilConn, $mplsUtilQuery)) or die ("Failed while listing MPLS domains ".pg_last_error());
foreach ($mplsDomains as $domain) {
	$mplsPbxsQuery .= ",'${domain['domain']}'";	
}
$mplsPbxsQuery .= ")";
$mplsData = pg_fetch_all(pg_query($pbxsConn, $mplsPbxsQuery)) or die ("Failed to get MPLS data ".pg_last_error());
$mplsSites = countKey($mplsData, "location");
$mplsPbxs = countKey($mplsData, "assigned_server");

$deviceQuery = "SELECT rg.assigned_server, count(*) FROM user_agent AS ua INNER JOIN resource_group AS rg ON ua.resource_group_id = rg.id WHERE rg.state = 'ACTIVE' GROUP BY rg.assigned_server";
$siteDeviceQuery = "SELECT rg.location, count(*) FROM user_agent AS ua INNER JOIN resource_group AS rg ON ua.resource_group_id = rg.id WHERE rg.state = 'ACTIVE' GROUP BY rg.location";
$santaQuery = "SELECT presence_server, count(*) FROM resource_group WHERE state = 'ACTIVE' GROUP BY presence_server";
$countQuery = "SELECT assigned_server, count(*) FROM resource_group GROUP BY assigned_server";
$siteCountQuery = "SELECT location, count(*) FROM resource_group GROUP BY location";

$deviceR = pg_fetch_all(pg_query($pbxsConn, $deviceQuery)) or die ("Failed to fetch devices".pg_last_error());
$siteDeviceR = pg_fetch_all(pg_query($pbxsConn, $siteDeviceQuery)) or die ("Failed to fetch devices".pg_last_error());
$santaR = pg_fetch_all(pg_query($pbxsConn, $santaQuery)) or die ("Failed to fetch santas".pg_last_error());
$countR = pg_fetch_all(pg_query($pbxsConn, $countQuery)) or die ("Failed to fetch counts".pg_last_error());
$siteCountR = pg_fetch_all(pg_query($pbxsConn, $siteCountQuery)) or die ("Failed to fetch counts".pg_last_error());

$devices = rekey($deviceR, "assigned_server", "count");
$siteDevs = rekey($siteDeviceR, "location", "count");
$counts = rekey($countR, "assigned_server", "count");
$siteCounts = rekey($siteCountR, "location", "count");

$santas = array();
foreach ($santaR as $santa) {
	$santas[$subnetSites[substr($santa['presence_server'],3,3)]][$santa['presence_server']] = $santa['count'];
}
pg_close($pbxsConn);
$sitesQuery = "SELECT DISTINCT location FROM pbxstatus";
$sites = pg_fetch_all(pg_query($utilConn, $sitesQuery)) or die ("Failed to fetch sites ".pg_last_error());

foreach ($sites as $site) {
	$perSite = "SELECT ip, load, status FROM pbxstatus WHERE location = '${site['location']}' ORDER BY ip";
	$activeCounts = "SELECT count(*) FROM pbxstatus WHERE location = '${site['location']}' AND status = 'active'";
	$standbyCounts = "SELECT count(*) FROM pbxstatus WHERE location = '${site['location']}' AND status = 'standby'";
	$siteInfo["Data"] = pg_fetch_all(pg_query($utilConn, $perSite));
	$activeR = pg_fetch_all(pg_query($utilConn, $activeCounts));
	$active = $activeR[0]['count'];
	$standbyR = pg_fetch_all(pg_query($utilConn, $standbyCounts));
	$standby = $standbyR[0]['count'];
?>
<h3><? echo "${site['location']}"; ?></h3>
<hr align='left' width='900px'>
<div class='leftpanel'>
<h4>Overview</h4>
<table border='1'>
	<tr><th>Active Nodes</th><th>Standby Nodes</th><th># Customers</th><th># Devices</th><th># MPLS</th></tr>
<?
	$siteCount = $siteCounts[$site['location']];
	$siteDev = $siteDevs[$site['location']];
	$siteMPLS = $mplsSites[$site['location']];
echo "<tr><td align='center'>$active</td>
	<td align='center'>$standby</td>
		<td align='center'>$siteCount</td>
		<td align='center'>$siteDev</td>
		<td align='center'>$siteMPLS</td></tr>";
?>
</table>

<h4>Presence</h4>
<table border='1'>
	<tr><th>Santa Host</th><th># PBXs</th></tr>
<?	
	$none = true;
   	foreach ($santas[$site['location']] as $pserver => $pcount) {
		echo "<tr><td>$pserver</td><td align='center'>$pcount</td></tr>";
		$none = false;
	} 
	if ($none) {
		echo "<tr><td>No servers</td><td align='center'>No data</td></tr>";
	}
?>
</table>
</div>
<div class='autopanel'>
<h4>PBXs</h4>
<table border='1'>
	<tr><th>Host</th><th>Load</th><th># Customers</th><th># Devices</th><th># MPLS</th></tr>
<?
foreach ($siteInfo["Data"] as $record) {
	$load = round($record['load'] / 140000, 1);
	$color = 'green';
	if ($load > 85) { $color = 'yellow'; }
	if ($load > 95) { $color = 'red'; }
	$custs = $counts[$record['ip']];
	$devs = $devices[$record['ip']];
	$pbxMPLS = $mplsPbxs[$record['ip']];
	echo "<tr>
		<td>${record['ip']}</td>
		<td align='right' style='color: $color'>$load%</td>
		<td align='center'>$custs</td>
		<td align='center'>$devs</td>
		<td align='center'>$pbxMPLS</td></tr>";
}
?>
</table>
</div>
<hr align='left' width='900px'>
<?
}
pg_close($utilConn);
echo "</body></html>";
?>

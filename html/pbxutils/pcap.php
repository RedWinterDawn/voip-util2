
<!DOCTYPE html>

<html>
<head>
    <title>PCAP Pull</title>
<style type="text/css">
  #pretty {vertical-align: bottom;}
  </style><link rel="stylesheet" href="stylesheet.css">
</head>

<body>
<?php
echo '<br>
<h2> Jive Packet Capture </h2>';
if ($file != '') {
	$message = 'Please wait while we process your request';
}
else {
	$message = 'Please fill out the following information, and the pcap will download once the requested packets have all been captured.';
}

echo $message;
?>
<br>
<br>
    <form action='' method='post'>
        <table><tr><td><select name="dc" id='dc' width="50">
            <option value="chi">chi</option>
            <option value="c25">ord</option>
            <option value="c20">nyc</option>
            <option value="c22">atl</option>
            <option value="c19">lax</option>
        </select></td>
      <td>  PBX #: <input type="text" name='pbx' id='pbx'></td>
      <td>  Packets:<input type='text' name='innum' id='innum'></td>
      <td>  File Name:<input type='text' name='file' id='file'></td>
       <td> <input type="submit"></td></tr></table>
    </form>
<?php
    if (isset($_REQUEST['innum'])) {
		        $innum = $_REQUEST['innum'];
				        }
	    else {
			       $innum = '';
				          }
	    if (isset($_REQUEST['file'])) {
			        $file = $_REQUEST['file'];
					        }
	    else {
			       $file = '';
				          }
	    if (isset($_REQUEST['dc'])) {
			        $dc = $_REQUEST['dc'];
					        }
	    else {
			       $dc = '';
				          }
	    if (isset($_REQUEST['pbx'])) {
			        $pbx = $_REQUEST['pbx'];
					        }
	    else {
			       $pbx = '';
				          }
	$clear = 'rm *.pcap';
	$clearoutput = exec($clear);
	if ($file == '') {
				        }
	        else {
				        
				        $output2 = exec ($remover);
				        $packet = 'sudo salt "megapbx'. $pbx. '.'. $dc. '.*" cmd.run "tcpdump -i eth0 -c '. $innum. ' -w '. $file. '.pcap"' ;
				        $output = exec ($packet);
						$filepcap = $file. '.pcap';
						$filehex = $file. '.hex';
				        $hexcon = 'sudo salt "megapbx'. $pbx. '.'. $dc. '.*" cmd.run "xxd '. $filepcap. ' '. $filehex. '"' ;
						$output3 = exec ($hexcon);				        
						$cathex = 'sudo salt "megapbx'. $pbx. '.'. $dc. '.*" cmd.run "cat '. $filehex. '"  > '. $filehex ;
						$output4 = exec ($cathex);
						$remline = 'sed -i -e "1d" '. $filehex;
						$output5 = exec ($remline);
						$pcapcon = 'xxd -r '. $filehex. ' '. $filepcap;
						$output6 = exec($pcapcon);
						$location = 'location: '. $filepcap;
						$remsalt = 'sudo salt "megapbx'. $pbx. '.'. $dc. '.*" cmd.run "rm '. $filehex. ' && rm '. $filepcap. '"';
						$outpu7 = exec($remsalt);
						$remhex = 'rm '. $filehex;
						$output8 = exec($remhex);
						header( $location);

										        }
	    ?>



			</body>
			</html>

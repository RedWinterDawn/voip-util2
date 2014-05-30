<?php

//get the csv file
$file = "/tmp/restore-extensions.csv";
$handle = fopen($file,"r");

// Connecting, selecting database
$dbconn = pg_connect("host=rwdb dbname=pbxs user=postgres ")
    or die('Could not connect: ' . pg_last_error());


echo "<table border='1'>\n";
//loop through the csv file and insert into database
while ($data = fgetcsv($handle,1500,",","'")) {
    if ($data[0]) {
		echo "<tr>";
		// echo "<td>" . $data[0] . "</td><td>" . $data[1] . "</td><td>" . $data[2] . "</td>";
		echo "<td>" . $data[0] . "</td><td>" . $data[1] . "</td>";
		
		// Make the change in the pbxs DB
		$query = "SELECT extension_number from extension WHERE id='" . $data[0] . "';" ;
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());		

		while ($row = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			$extension_number = $row['extension_number'];
			echo "<td>" . $row['extension_number'] . "</td>";
		}

		pg_free_result($result);
		
	    // Make the change in the pbxs DB
		$query = "UPDATE extension SET extension_number='" . $data[1] . "' WHERE id='" . $data[0] . "';" ;

		if ($extension_number != '') {
		    // $result = pg_query($query) or die('Query failed: ' . pg_last_error());	
		    echo "<td>$query</td>";
		}

		echo "<tr/>\n";
    }
};

// Closing connection
pg_close($dbconn);

echo "</table>\n";



?>

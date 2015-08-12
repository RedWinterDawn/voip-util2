  <?php
  // This script takes the posted filename and cats out the output into jsonoutput and encodes it into json for a webpage to display it. IF no file name is posted, it will display in json 'no filename set'. 
  
  $jsonoutput = array();
  $jsonoutput['filecontents'] = '';
  $jsonoutput['image'] = 'false';
  
  if (isset($_POST['filename'])) {
    $filename = $_POST['filename'];
    

    if (strpos($filename, '.png') !== false) {
      $jsonoutput['filecontents'] = '<img src="'.$filename.'">';
      $jsonoutput['image'] = 'true';
    }

   elseif (strpos($filename, '.gif') !== false) {
      $jsonoutput['filecontents'] = '<img src="'.$filename.'">';
      $jsonoutput['image'] = 'true';
    } 

   elseif (strpos($filename, '.jpg') !== false) {
      $jsonoutput['filecontents'] = '<img src="'.$filename.'">';
      $jsonoutput['image'] = 'true';
    }
   
   elseif (strpos($filename, '.jpeg') !== false) {
      $jsonoutput['filecontents'] = '<img src="'.$filename.'">';
      $jsonoutput['image'] = 'true';
    }  

   else {
  
  // creates command to be executed 'cat $filename'
  $cmd = 'cat '. $filename;
  $result = exec($cmd, $content);
  //this foreach loop adds each line of the cat command as an appended line to the json array.
  foreach ($content as $line) {
      $jsonoutput['filecontents'] .= $line. "aAAbBaZZzyxxB";
  }
   }
  }
  // else if no filename was provided, set the jsonoutput to "no filename set"
  else {
    $jsonoutput = array();
      $jsonoutput['filecontents'] = 'No filename Set';
  }
  echo json_encode($jsonoutput);
?>

<?php
// This script is used to compile an array of all files in pbxutils and sub directories - it then compares it with a same list of files in a database, and updates any differences. This provides information for the page with all files, descricptions and access levels... Questions or comments ask Kevyn Hale. khale@jive.com
// $cmd lists all files in pbxutils
$jsonoutput = array();
$jsonoutput['addFile'] = '';
$jsonoutput['addDirectory'] = '';
$jsonoutput['deleteFile'] = '';
$jsonoutput['deleteDirectory'] = '';
$jsonoutput['updateaccess'] = '';
$cmd = 'ls -a *.*';
// adds all files into an array $names
$result = exec($cmd, $names);
// $current is the array with all of file names of the current directory. We will be adding entries with each command.
$current = array();
//keyvalue is used to determine whether or not the access level is set on a particular page.
$keyvalue = array();
// adds all items in array $names to array $current
foreach ($names as $name) {
  $current[] = $name;
  $cmd = 'cat '.$name;
  unset($file);
  $result = exec($cmd, $file);
  $rows = implode("\n", $file);
  //if checksession.php exists, the access level is set - therefore we set the keyvalue array to true.
  if (preg_match("/checksession.php/", $rows)) {
    $keyvalue[$name] = 't';
  }
  else {
    $keyvalue[$name] = 'f';
  }
}
// This command will list all of the subdirectories.
$cmd2 = 'ls -d */';
// Lists all directories into array $dirs
$result = exec($cmd2, $dirs);
// loop adds all  files in subdirectories to array current. Also setting new directories array and adding all directories to it to use for later.
$subdirectories = array();
foreach ($dirs as $dir) {
  $subdirectories[] = $dir;
  // $cmd3 lists all files in the subdirectory, and then we process those files and add them to current.
  $cmd3 = 'ls -a '.$dir.'*.*';
  unset($subnames);
  $result = exec($cmd3, $subnames);
  foreach ($subnames as $subname) {
    $current[] = $subname;
  $cmd = 'cat '.$subname;
  unset($file);
  $result = exec($cmd, $file);
  $rows = implode("\n", $file);
  //if checksession.php exists, the access level is set - therefore we set the keyvalue array to true.
  if (preg_match("/checksession.php/", $rows)) {
    $keyvalue[$subname] = 't';
  }
  else {
    $keyvalue[$subname] = 'f';
  }

  }
}
// Select from database all the filenames already added inorder to compare, and add only the missing filenames.
$dbconn = pg_connect("host=rwdb dbname=util user=postgres ") or die('Could not connect to util to look up util_files: '.pg_last_error());
$query = "SELECT filename, access_set FROM util_files;";
$result = pg_query($dbconn, $query);
if (!$result) {
    echo "An error occurred.\n";
      exit;
}
// set array for the current files in the database
$dbcurrent = array();
$accesscurrent = array();
while ($row = pg_fetch_row($result)) {
  $dbcurrent[] = $row[0];
  $accesscurrent[$row[0]] = $row[1];
}
//compar $keyvalue to $accesscurrent in order to determine that changes to whether files have access set or not.
$accesschanges = array_diff_assoc($keyvalue, $accesscurrent);
// compare $current to $dbcurrent will create array $files2update containing all files current has that $dbcurrent does not.
$files2update = array_diff($current, $dbcurrent);
// compare $dbcurrent to $current will create array $files2remove containing all files needed to be removed from database.
$files2remove = array_diff($dbcurrent, $current);

//foreach loop updating util_files with the right status for access_set:
foreach ($accesschanges as $name2change => $access2change) {
  $accessupdate = 'UPDATE util_files SET (access_set) = (\''.$access2change.'\') WHERE filename = \''.$name2change.'\';'; 
  $result = pg_query($dbconn, $accessupdate);
  $jsonoutput['updateaccess'] .= $accessupdate;
}


// foreach loop naming each file to be written and creating an insert querry with each name.
$date = date('Y-m-d H:i:s');
foreach ($files2update as $file2update) {
  // subdirectories refers to the directories within pbxutils.
  foreach ($subdirectories as $subdirectory) {
    //setting delimiters for preg_match, we want to determine the directories that they are in.
    $subdirectorydel = '!'.$subdirectory. '!';
    if (preg_match($subdirectorydel, $file2update)) {
      $directory = 'pbxutils/'.$subdirectory;
      break;
    }
    else {
      $directory = 'pbxutils/';
    }
  }
  // inserting the new files into the database.
  $insert = 'INSERT INTO util_files (filename, directory, date_created, access_level, author, file_description) VALUES (\''.$file2update.'\', \''.$directory.'\', \''.$date.'\', \'4\', \'unkown\', \'unkown\');';
  $result = pg_query($dbconn, $insert);
  $jsonoutput['addFile'] .= $insert;
}

//After adding the new files, now we will process files2remove.
foreach ($files2remove as $file2remove) {
  $delete = 'DELETE FROM util_files WHERE filename = \''. $file2remove .'\';';
  $result = pg_query($dbconn, $delete);
  $jsonoutput['deleteFile'] .= $delete;
}

//FROM HERE -- we will do the same as above, but will do it to the table util_directories, so we can have a list of all current directories.
$directoryquery = "SELECT directory FROM util_directories;";
$result = pg_query($dbconn, $directoryquery);
if (!$result) {
      echo "An error occurred.\n";
            exit;
}
// set array for the current subdirectories in the database
$dbsubdirectories = array();
while ($row = pg_fetch_row($result)) {
    $dbsubdirectories[] = $row[0];
}
// compare $subdirectories to $subdirectories will create array $subdirectories2update containing all subdirectories current has that $dbsubdirectories does not.
$subdirectories2update = array_diff($subdirectories, $dbsubdirectories);
// compare $dbsubdirectories to $subdirectories will create array $subdirectories2remove containing all subdirectories needed to be removed from database.
$subdirectories2remove = array_diff($dbsubdirectories, $subdirectories);
foreach ($subdirectories2update as $subdirectory2update) {
  // inserting the new directories into the database.
  $insert = 'INSERT INTO util_directories (directory) VALUES (\''.$subdirectory2update.'\');';
  $result = pg_query($dbconn, $insert);
  $jsonoutput['addDirectory'] .= $insert;
}
//After adding the new directories, now we will process subdirectories2remove.
foreach ($subdirectories2remove as $subdirectory2remove) {
  $delete = 'DELETE FROM util_directories WHERE directory = \''. $subdirectory2remove .'\';';
  $result = pg_query($dbconn, $delete);
  $jsonoutput['deleteDirectory'] .= $delete;
}

echo json_encode($jsonoutput);

pg_close($dbconn);
?>

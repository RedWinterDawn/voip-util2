<?php
// This script is used to compile an array of all files in pbxutils and sub directories - it then compares it with a same list of files in a database, and updates any differences. This provides information for the page with all files, descricptions and access levels... Questions or comments ask Kevyn Hale. khale@jive.com
// $cmd lists all files in pbxutils
$cmd = 'ls -a *.*';
// adds all files into an array $names
$result = exec($cmd, $names);
// $current is the array with all of file names of the current directory. We will be adding entries with each command.
$current = array();
// adds all items in array $names to array $current
foreach ($names as $name) {
  $current[] = $name;
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
  $result = exec($cmd3, $subnames);
  foreach ($subnames as $subname) {
    $current[] = $subname;
  }
}
// Select from database all the filenames already added inorder to compare, and add only the missing filenames.
$dbconn = pg_connect("host=rwdb dbname=util user=postgres ") or die('Could not connect to util to look up util_files: '.pg_last_error());
$query = "SELECT filename FROM util_files;";
$result = pg_query($dbconn, $query);
if (!$result) {
    echo "An error occurred.\n";
      exit;
}
// set array for the current files in the database
$dbcurrent = array();
while ($row = pg_fetch_row($result)) {
  $dbcurrent[] = $row[0];
}
// compare $current to $dbcurrent will create array $files2update containing all files current has that $dbcurrent does not.
$files2update = array_diff($current, $dbcurrent);
// foreach loop naming each file to be written and creating an insert querry with each name.
$date = date('Y-m-d H:i:s');
foreach ($files2update as $file2update) {
  foreach ($subdirectories as $subdirectory) {
    $subdirectory = '\''.$subdirectory.'\'';
    echo $subdirectory;
    if (preg_match($subdirectory, $file2update)) {
      $directory = 'pbxutils/'.$subdirectory;
    }
    else {
      $directory = 'pbxutils/';
    }
  }
  $insert = 'INSERT INTO util_files (filename, directory, date_created) VALUES (\''.$file2update.'\', \''.$directory.'\', \''.$date.'\');';
 # $result = pg_query($dbconn, $insert);
  echo $insert;
  var_dump($result);
}
pg_close($dbconn);
?>

<?php
require_once('../config.php');
require_once('../functions.php');

checkAdmin();
  
printHeader(true);
?>

<div class="box">Admin Area</div>

<h1>Recent Uploads</h1>
<ul>
<?php
  
  $out = array();
  exec('find ' . $files_libraryRoots . ' -mtime -5 -type f', $out);
  
  foreach($out as $f) {
    $fixedPath = substr($f, strlen($files_libraryRoots));
    $stat = stat($f);
    
    echo '<li><a href="/Shared/' . $fixedPath . '">' . $fixedPath . '</a> :: '
      . FormatSize($stat['size']) . ' :: ' . date('n/j/Y G:i:s', $stat['mtime']) . '</li>';
  }
?>
</ul>
<?php printFooter(); ?>

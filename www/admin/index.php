<?php
require_once('../config.php');
require_once('../functions.php');

checkAdmin();
  
printHeader(true);
?>

<div class="box">Admin Area</div>

<h1>Network Info</h1>
<pre><?= `ifconfig wlan1 | grep -A1 wlan1`?></pre>
<pre><?= `iwconfig wlan1 | grep wlan1`?></pre>
<hr/>

<h1>Hardware Info:</h1>
<pre><?= `/usr/bin/sudo /etc/bin/chiptemp.sh` ?></pre>
<pre><?= `/usr/bin/sudo /etc/bin/bananatemp.sh` ?></pre>
<b>Uptime:</b><pre><?=`uptime`?></pre><br/>

<h1>Recent Uploads</h1>
<ul>
<?php
  
  $out = array();
  exec('find ' . $files_libraryRoots . ' -mtime -5 -type f -not -name ' . TAG_NOUPLOAD, $out);
  
  foreach($out as $f) {
    $fixedPath = substr($f, strlen($files_libraryRoots));
    $stat = stat($f);
    
    echo '<li><a href="/Shared/' . $fixedPath . '">' . $fixedPath . '</a> :: ';
    echo '<a onclick="return confirm(\'Are you sure you want to delete this file or directory?\')" href="/admin/files.php?return=i&f=d&p=' . urlencode($fixedPath) . '">Delete</a>';
    echo ' :: ' . FormatSize($stat['size']) . ' :: ' . date('n/j/Y G:i:s', $stat['mtime']) . '</li>';
  }
?>
</ul>
<?php printFooter(); ?>

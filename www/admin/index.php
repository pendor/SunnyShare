<?php
require_once('../config.php');
require_once('../functions.php');

checkAdmin();
  
printHeader(true);
?>

<div class="box">Admin Area</div>

<h1>Recent Uploads &amp; Attention Required</h1>
<?php
  $roots = glob($files_libraryRoots . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);

  foreach($roots as $dir) {
    $noUp = $dir . DIRECTORY_SEPARATOR . TAG_NOUPLOAD;
    if(file_exists($noUp)) {
      // No sense thrashing things they can't upload to.
      continue;
    }
    
    // Get one-per-line:
    // R/mnt/data/whatever <-- The xattr value, then the path.
    $xattrOut = array();
    exec('/usr/bin/find ' . $dir . ' -type f -exec /usr/bin/attr -q -g ' . XA_REPORT . ' "{}" 2>/dev/null \; -print', $xattrOut);  
    $reported = array();
    foreach($xattrOut as $f) {
      // If first letter is the report value, drop it & add it to list of reported.
      echo "Checking line $f<br/>";
      if(substr($f, 0, 1) == XA_REPORTED) {
        $reported[] = substr($f, 1);
      }
    }
    
    if(count($reported)) {
      echo '<h1>' . substr($dir, strlen($files_libraryRoots)) . ' :: Reported files:</h1><ul>';
      foreach($reported as $f) {
        $fixedPath = substr($f, strlen($files_libraryRoots));
        $stat = stat($f);
    
        echo '<li>';
        echo '<a onclick="return confirm(\'Are you sure you want to delete this file or directory?\')" href="/admin/files.php?return=i&f=d&p=' . urlencode($fixedPath) . '">Delete</a>';
        echo ' :: <a onclick="return confirm(\'Are you sure you want to approve this file?\')" href="/admin/files.php?return=i&f=a&p=' . urlencode($fixedPath) . '">Mark Approved</a>';
        echo ' <span style="font-size: 150%; line-height: 0;">&#8594;</span> <a href="/Shared/' . $fixedPath . '">' . $fixedPath . '</a> :: ';
        echo ' :: ' . FormatSize($stat['size']) . ' :: ' . date('n/j/Y G:i:s', $stat['mtime']) . '</li>';
      }
      echo '</ul>';
    }
    
    
    $out = array();
    exec('/usr/bin/find ' . $dir . ' -mtime -5 -type f -not -name ' . TAG_NOUPLOAD, $out);  
    
    if(count($out)) {
      echo '<h1>' . substr($dir, strlen($files_libraryRoots)) . ' :: New files:</h1><ul>';
    
      foreach($out as $f) {
        $fixedPath = substr($f, strlen($files_libraryRoots));
        $stat = stat($f);
    
        echo '<li>';
        echo '<a onclick="return confirm(\'Are you sure you want to delete this file or directory?\')" href="/admin/files.php?return=i&f=d&p=' . urlencode($fixedPath) . '">Delete</a>';
        echo ' <span style="font-size: 150%; line-height: 0;">&#8594;</span> <a href="/Shared/' . $fixedPath . '">' . $fixedPath . '</a> :: ';
        echo ' :: ' . FormatSize($stat['size']) . ' :: ' . date('n/j/Y G:i:s', $stat['mtime']) . '</li>';
      }
      echo '</ul>';
    }
  }
?>
<hr/>
<h1>Network Info</h1>
<pre><?= `ifconfig eth0 | grep -A1 eth0`?></pre>
<hr/>

<h1>Hardware Info:</h1>
<pre><?= `/usr/bin/sudo /etc/bin/chiptemp.sh` ?></pre>
<pre><?= `/usr/bin/sudo /etc/bin/bananatemp.sh` ?></pre>
<b>Uptime:</b><pre><?=`uptime`?></pre><br/>

<hr/>


<?php printFooter(); ?>

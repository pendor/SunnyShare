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
<b>Uptime:</b><pre><?=`uptime`?></pre><br/>
<b>Board Temp:</b><?=`awk '{printf("%d",$1/1000)}' </sys/devices/virtual/thermal/thermal_zone0/temp`?>ºC<br/>

<?php
	$sysinfo = `/etc/update-motd.d/30-sysinfo`
	
	
?>

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

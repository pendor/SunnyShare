<?php
require_once('../config.php');
require_once('../functions.php');

checkAdmin();

if(isset($_GET['r']) && $_GET['r'] == '1') {
  $_SESSION['rd'] = '1';
  header('Location: /admin/net.php');
  exit;
} 

printHeader(true);

if(isset($_SESSION['rd']) && $_SESSION['rd'] == '1') {
  unset($_SESSION['rd']);

  echo '<h2>Bringing WiFi down...</h2><pre>';
  passthru('/usr/bin/sudo /sbin/ifdown wlan1 2>&1');
  sleep(2);
  
  echo '</pre><hr/><h2>Bringing WiFi back up...</h2><pre>';
  
  $out = passthru('/usr/bin/sudo /sbin/ifup wlan1 2>&1');
  sleep(2);
  
  echo '</pre><hr/><span>Refresh in a few seconds to check DHCP results.</span><hr/>';
} else {
  echo '<a class="delbtn" href="?r=1">Reassociate WiFi</a>';
}
?>
  
  <b>HW Temp:</b> <?= getHwTemp() ?> ºC<br/>
  
  <h1>Network Info:</h1>
  <b>Client MAC:</b> <?= getClientMac() ?><br/>
  
  <h2>Associated WiFi</h2>
  <pre><?php system('iwconfig wlan1');?></pre><hr/>
  
  <h2>IP Addresses</h2>
  <pre><?php system('ifconfig');?></pre><hr/>
  
  <h2>Available SSID's</h2>
  <pre><?php system('iwlist wlan1 scan');?></pre><hr/>
  
<?php printFooter(); ?>

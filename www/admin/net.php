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

  echo '<h2>Reassociating WiFi...</h2>';
  
  $out = `/usr/bin/sudo /sbin/ifdown --verbose wlan1 2>&1`;
  echo '<pre>' . $out . '</pre>';
  
  sleep(2);
  
  $out = `/usr/bin/sudo /sbin/ifup --verbose wlan1 2>&1`;
  echo '<pre>' . $out . '</pre>';
  
  sleep(2);
  
  echo '<span>Refresh in a few seconds to check DHCP results.</span><br/>';
} else {
  echo '<a class="delbtn" href="?r=1">Reassociate WiFi</a>';
}
?>
  
  <h1>Network Info:</h1>
  <b>Client MAC:</b> <?= getClientMac() ?><br/>
  
  <h2>Associated WiFi</h2>
  <pre><?php system('iwconfig wlan1');?></pre><hr/>
  
  <h2>IP Addresses</h2>
  <pre><?php system('ifconfig');?></pre><hr/>
  
  <h2>Available SSID's</h2>
  <pre><?php system('iwlist wlan1 scan');?></pre><hr/>
  
<?php printFooter(); ?>

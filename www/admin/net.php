<?php
require_once('../config.php');
require_once('../functions.php');

checkAdmin();
  
printHeader(true);
?>

  <h1>Network Info:</h1>
  <h2>Associated WiFi</h2>
  <pre><?php system('iwconfig wlan1');?></pre><hr/>
  
  <h2>IP Addresses</h2>
  <pre><?php system('ifconfig');?></pre><hr/>
  
  <h2>Available SSID's</h2>
  <pre><?php system('iwlist wlan1 scan');?></pre><hr/>
  
<?php printFooter(); ?>

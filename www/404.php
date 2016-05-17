<?php
$no_mac_register = 1;
require_once('functions.php');

$appleMagicNames = [
  '/library/test/success.html',
  '/hotspot-detect.html'
];

$msMagicNames = [
  '/ncsi.txt'
];

$androidMagicNames = [ 
  '/generate_204' 
];

// 192.168.1.207 captive.apple.com - [07/May/2016:01:11:00 -0400] "GET /hotspot-detect.html HTTP/1.0" 200 1028 "-" "CaptiveNetworkSupport-325.10.1 wispr"

$magicNames = array_merge($appleMagicNames, $msMagicNames, $androidMagicNames);

$uri = $_SERVER['REQUEST_URI'];
$agent = $_SERVER['HTTP_USER_AGENT'];

// 192.168.1.207 captive.apple.com - [07/May/2016:01:13:24 -0400] "GET /hotspot-detect.html HTTP/1.0" 200 1350 "-" "CaptiveNetworkSupport-325.10.1 wispr"
// 192.168.1.207 captive.apple.com - [07/May/2016:01:13:25 -0400] "GET /hotspot-detect.html HTTP/1.1" 404 742 "-" "Mozilla/5.0 (iPhone; CPU iPhone OS 9_3_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Mobile/13E238"
// 192.168.1.207 captive.apple.com - [07/May/2016:01:13:25 -0400] "GET /style.css HTTP/1.1" 200 1031 "http://captive.apple.com/hotspot-detect.html" "Mozilla/5.0 (iPhone; CPU iPhone OS 9_3_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Mobile/13E238"
// 192.168.1.207 captive.apple.com - [07/May/2016:01:13:26 -0400] "GET /logo.png HTTP/1.1" 200 3551 "http://captive.apple.com/hotspot-detect.html" "Mozilla/5.0 (iPhone; CPU iPhone OS 9_3_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Mobile/13E238"
//


if(in_array($uri, $magicNames)) {
  // This request is for one of the portal detectors
  $mac = getClientMac();
  if(isMacSet($mac)) {
    // We've been here, so give them the real thing.
    if(in_array($uri, $appleMagicNames)) {
      printAppleSuccess();
      exit;
    } else if(in_array($uri, $msMagicNames)) {
      printMsSuccess();
      exit;
    } else {
      // Hmmm....
      printAppleSuccess();
      exit;
    }
  } else {
    // First time.  Show them the portal page or 404?
    printWelcome();
    exit;
  }
} else {
  // Not one of the magic names, so legit 404...
  httperr(404, 'Not Found', true);
  printWelcome();
  exit;
}

function printAppleSuccess() { 
  header('Content-type: text/html');
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">
<HTML>
<HEAD>
	<TITLE>Success</TITLE>
</HEAD>
<BODY>
Success
</BODY>
</HTML>
<?php }

function printMsSuccess() {
  header('Content-type: text/plain');
  echo "Microsoft NCSI\n";
}

function printWelcome() {
  global $domainName;
  
  printHeader(-1);
  ?>
<div class="box">
  <h1>What's This?!?</h1>
  
  <p>You've discovered a Sunny+Share box placed near-by by someone who
    values sharing and collaboration in the local area.  
  </p>
  
  <p class="readme">This WiFi access point does <b>NOT</b> provide access to the internet.
    If you need to get online, you should disconnect now &amp; try a different
    access point.
  </p>
    
  <p>This service provides a free, anonymous sharing platform for the local
    area.  If you continue, you can access files left by others as well as
    share anything you think others near-by might enjoy.  For more info
    about this system, see <a href="http://<?= $domainName ?>/about.php">About Sunny+Share</a>.
  </p>
  
  <p style="text-align:center;">If you'd like to connect and access this service, just press the button:<br/>
    <br/>
    <a style="margin-left: auto; margin-right:auto;" class="okbtn" href="http://<?= $domainName ?>/">Continue</a>
  </p>
  
  <h2>You can come back here later by connecting to this WiFi and going to 
    <a href="http://<?= $domainName ?>/">http://<?= $domainName ?>/</a>.</h2>
</div>
  
  <?php
  printFooter();
}
?>

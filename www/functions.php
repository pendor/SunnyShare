<?php
# require_once 'random_compat.phar';
require_once('config.php');

if(!isset($no_mac_register)) {
  
  // Check if we're in on a bad domain name but not an IP:
  $reqHost = $_SERVER['HTTP_HOST'];
  if(preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $reqHost) === false
    && strcasecmp($reqHost, $domainName) != 0) {
    // It's not an IP, and we're not on the right domain.  Redirect.
    header('Location: http://' . $domainName . $_SERVER['REQUEST_URI']);
    exit;
  }
  
  if(!session_id()) {
    session_start();
  }

  if(isset($_COOKIE['secid'])) {
    $secId = $_COOKIE['secid'];
  } else {
    $secId = random_str(32);
    setcookie('secid', $secId, $cookieTime, '/');
  }
  
  if(!isset($_SESSION['mc'])) {
    $mac = getClientMac();
    $_SESSION['mc'] = $mac;
    
    $m = getMemcache();
    if($m->get($mac) === FALSE) { 
      $m->add($mac, '1', $macCacheTtl);
    } else {
    	$m->touch($mac, $macCacheTtl);
    }
  }
}

function isMacSet($mac) {
  if(isset($_SESSION['mc']) && $_SESSION['mc'] == $mac) {
    return true;
  }
  
  $m = getMemcache();
  if($m->get($mac) !== FALSE) {
    return true;
  }
  
  return false;
}

function getMemcache() {
  $m = new Memcached();
  $m->addServer('127.0.0.1', 11211);
  return $m;
}

function getClientMac() {
  $ipAddress=$_SERVER['REMOTE_ADDR'];
  #run the external command, break output into lines
  $arp=`arp -n $ipAddress`;
  $lines=explode("\n", $arp);

  #look for the output line describing our IP address
  foreach($lines as $line) {
    $cols=preg_split('/\s+/', trim($line));
      if($cols[0]==$ipAddress)    {
        return $cols[2];
      }
  }
  return false;
}

function checkAdmin() {
  global $adminKey, $cookieTime;

  if(isset($_GET['lo']) && $_GET['lo'] == '1') {
    setcookie('admin', '', $cookieTime, '/');
    unset($_GET['k']);
    unset($_COOKIE['admin']);
    loginForm();
    exit;
  }

  if(!isset($_COOKIE['admin']) || $_COOKIE['admin'] != $adminKey) {
    if(!isset($_GET['k']) || $_GET['k'] != $adminKey) {
      loginForm();
      exit;
    } else {
      setcookie('admin', $_GET['k'], $cookieTime, '/');
    }
  }
}

function isAdmin() {
  global $adminKey;
  return
    (isset($_COOKIE['admin']) && $_COOKIE['admin'] == $adminKey) ||
    (isset($_GET['k']) && $_GET['k'] == $adminKey);
}

function loginForm() {
  printHeader();
?>
  <div class="box">
    <form method="get">
      <p>You need to login first:</p>
      <input type="password" name="k"/>
      <input type="submit" value="Login"/>
    </form>
  </div>
<?php  
  printFooter();
}

/**
 * @param $adminType: 0 = Normal, 1 = Admin, -1 = none.
 */
function printHeader($adminType=false) {
?><!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="/style.css"/>
	<title>Sunny+Share - Share Freely!</title>
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width"/>
  <script src="/combo.js" type="text/javascript"></script>
</head>
<body>
  <div id="header">
		<img src="/logo.png" alt="Sunny+Share" title="Sunny+Share - Share Freely"/>
  </div>
  <?php
  if($adminType == 1) {
    ?><div class="nav"><a href="/admin/">Admin Home</a> • <a href="/admin/announce.php">Announcements</a> • <a href="/admin/net.php">Network</a> • <a href="/admin/files.php">Files</a> :: <a href="/">Exit Admin</a></div><?php
  } else if ($adminType == 0){
    ?><div class="topbar nav"><a href="/">Announcements</a> • <a href="/Shared/">Files</a> • <a href="/about.php">About</a></div><?php
  } 
  // else {
//     // Nothing...
//}
  
  if(isAdmin()) {?>
  <div class="topbar warn">You're logged in as admin.  <a href="/admin/?lo=1">Logout</a> :: <a href="/admin/">Admin Home</a></div>
  <?php } ?>

  <div id="content"><?php
}

function printFooter() {
?>  </div>
</body>
</html><?php
}

function httperr($code, $text, $continue = false) {
  // Special handling for CGI:
  header('Status: ' . $code . ' ' . $text);
  $GLOBALS['http_response_code'] = $code;
  
  if(!$continue) {
    echo $text . "\n";
    exit;
  }
}

// Normalize line endings to unix format  
function normalize($s) {
  $s = str_replace("\r\n", "\n", $s);
  $s = str_replace("\r", "\n", $s);
  // Don't allow out-of-control blank lines
  $s = preg_replace("/\n{2,}/", "\n\n", $s);
  return $s;
}

function FormatSize($bytes) {
	$units = array('B', 'KB', 'MB', 'GB', 'TB');

	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);

	$bytes /= pow(1024, $pow);

	return ceil($bytes) . ' ' . $units[$pow];
}

function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
  $str = '';
  $max = strlen($keyspace) - 1;
  for($i = 0; $i < $length; ++$i) {
    $str .= $keyspace[random_int(0, $max)];
  }
  return $str;
}


function relDate($date) {
  $now = time();
  $diff = $now - $date;
  if($diff < 60){
    return sprintf($diff > 1 ? '%s seconds ago' : 'a second ago', $diff);
  }

  $diff = floor($diff/60);
  if($diff < 60){
    return sprintf($diff > 1 ? '%s minutes ago' : 'one minute ago', $diff);
  }

  $diff = floor($diff/60);
  if($diff < 24){
    return sprintf($diff > 1 ? '%s hours ago' : 'an hour ago', $diff);
  }

  $diff = floor($diff/24);
  if($diff < 7){
    return sprintf($diff > 1 ? '%s days ago' : 'yesterday', $diff);
  }

  if ($diff < 30) {
    $diff = floor($diff / 7);
    return sprintf($diff > 1 ? '%s weeks ago' : 'one week ago', $diff);
  }

  $diff = floor($diff/30);
  if($diff < 12){
    return sprintf($diff > 1 ? '%s months ago' : 'last month', $diff);
  }

  $diff = date('Y', $now) - date('Y', $date);
  return sprintf($diff > 1 ? '%s years ago' : 'last year', $diff);
}

function chat_readMessages() {
  global $chat_dataFile;
  
  $ret = array();
  
  $fp = fopen($chat_dataFile, 'r');
  if(flock($fp, LOCK_SH)) {
    $flen = filesize($chat_dataFile);
    if($flen > 0) {
      $ret = json_decode(fread($fp, $flen), true);
    }
    flock($fp, LOCK_UN);
    fclose($fp);
  }
  return $ret;
}

function chat_addMessage($name, $message, $secId) {
  global $chat_dataFile, $chat_colors;
  
  $time = time();
  $delHash = sha1($secId . $time);
  
  $fp = fopen($chat_dataFile, 'r+');
  if(flock($fp, LOCK_EX)) {  // acquire an exclusive lock
    $flen = filesize($chat_dataFile);
    if($flen == 0) {
      $json = array();
    } else {
      $json = fread($fp, $flen);
      $msgs = json_decode($json, true);
    }
    $msgs[] = array(
      't' => $time,
      'n' => $name,
      'm' => $message,
      'c' => $chat_colors[array_rand($chat_colors)],
      'i' => $delHash,
    );
    ftruncate($fp, 0);
    rewind($fp);
    $newJson = json_encode($msgs);
    fwrite($fp, $newJson);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
  } else {
    die('Couldn\'t lock messages file.');
  }
  
  return $newJson;
}

function chat_delMessage($secId, $time) {
  $expectedHash = sha1($secId . $time);
  return chat_delMessageHash($expectedHash, $time);
}

function chat_delMessageHash($expectedHash, $time) {
  global $chat_dataFile;
    
  $fp = fopen($chat_dataFile, 'r+');
  if(flock($fp, LOCK_EX)) {  // acquire an exclusive lock
    $flen = filesize($chat_dataFile);
    if($flen == 0) {
      $json = array();
    } else {
      $json = fread($fp, $flen);
      $msgs = json_decode($json, true);
    }
    
    $changed = false;
    $newAr = array();
    foreach($msgs as $m) {
      if($m['t'] == $time && $m['i'] == $expectedHash) {
        $changed = true;
        continue;
      }
      $newAr[] = $m;
    }
    
    if($changed) {
      // No sense rewriting if we didn't delete anything.
      ftruncate($fp, 0);
      rewind($fp);
      $newJson = json_encode($newAr);
      fwrite($fp, $newJson);
      fflush($fp);
      $outJson = $newJson;
    } else {
      $outJson = $json;
    }
    
    flock($fp, LOCK_UN);
    fclose($fp);
  } else {
    die('Couldn\'t lock messages file.');
  }
  
  return $outJson;
}

function rmrf($dir) {
  if(!strlen($dir)) {
    return false;
  }
  
  if(!is_dir($dir)) {
    return unlink($dir);
  }
  
  $files = array_diff(scandir($dir), array('.','..'));
  foreach ($files as $file) {
    (is_dir("$dir/$file")) ? rmrf("$dir/$file") : unlink("$dir/$file");
  }
  return rmdir($dir);
} 

function getHwTemp() {
  $t = file_get_contents('/sys/devices/virtual/thermal/thermal_zone0/temp');
  $tdec = ((int)$t) / 1000;
  return $tdec;
}

?>
<?php
include('config.php');

if(!session_id()) {
  session_start();
}

if(isset($_COOKIE['secid'])) {
  $secId = $_COOKIE['secid'];
} else {
  $secId = random_str(32);
  setcookie('secid', $secId, $cookieTime);
}

function httperr($code, $text) {
  // Special handling for CGI:
  header('Status: ' . $code . ' ' . $text);
  echo $text . "\n";
  $GLOBALS['http_response_code'] = $code;
  exit;
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

function random_str($length) {
  $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
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
?>
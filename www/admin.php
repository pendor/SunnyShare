<?php
include('config.php');
include('functions.php');

$cookieTime = ((2021-1970) * 60 * 60 * 24 * 365);

if(!isset($_COOKIE['admin']) || !$_COOKIE['admin'] == $adminKey) {
  if(!isset($_GET['k']) || $_GET['k'] != $adminKey) {
    httperr(404, 'Not found.');
    exit;
  } else {
    setcookie('admin', $_GET['k'], $cookieTime);
  }
}
  
if(isset($_GET['r']) && isset($_GET['p'])) {
  $fn = $_GET['p'];
  
  if(strpos($fn, '..') !== false) {
    die('Bad path');
  }
  
  $enPath = $files_libraryEnabled . DIRECTORY_SEPARATOR . $fn;
  $rPath =  $files_libraryRoots . DIRECTORY_SEPARATOR . $fn;
  if($_GET['r'] == 'd') {
    if(is_dir($rPath) && is_link($enPath)) {
      unlink($enPath);
    } else {
      die("Not a link or not a dir");
    }
  } else if($_GET['r'] == 'e') {
    if(is_dir($rPath) && !file_exists($enPath)) {
      symlink($rPath, $enPath);
    } else {
      die('Already linked or not dir');
    }
  } else {
    die('Bad action');
  }
} else if(isset($_GET['m']) && isset($_GET['t']) && isset($_GET['h']) && $_GET['m'] == 'd') {
  chat_delMessageHash($_GET['h'], $_GET['t']);
}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="/style.css"/>
	<title>Sunny+Share - Share Freely!</title>
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width"/>
  <script src="combo.js" type="text/javascript"></script>
</head>
<body>
  <div id="header">
		<a id="logo" href="/"><img src="/logo.png" alt="Sunny+Share" title="Sunny+Share - Share Freely"/></a>
		<a href="/Shared/">Library</a>
  </div>

  <div id="content">
    <h1>Announcments:</h1>
    <ol>
<?php
$msgs = chat_readMessages();
foreach($msgs as $m) {
  echo '<li>' . htmlentities($m['n']) . ' @ ' . $m['t'] . ': ' . htmlentities($m['m']) . ' || <a href="?m=d&t=' . $m['t'] . '&h=' . $m['i'] . '">Delete</a></li>';
}

/*
      't' => $time,
      'n' => $name,
      'm' => $message,
      'c' => $chat_colors[array_rand($chat_colors)],
      'i' => $delHash,
*/
?>
    </ol>
    <h1>File Roots:</h1>
    <ol>
<?php
	$dh = opendir($files_libraryRoots);
	while(false !== ($filename = readdir($dh))) {
    if($filename == '.' || $filename == '..') {
      continue;
    }
    $link = is_link($files_libraryEnabled . DIRECTORY_SEPARATOR . $filename);

    echo '<li>' . htmlentities($filename) . ': ';
    
    if($link) {
      // It's enabled already.
      echo '<span style="color: green;">ENABLED</span> | <a href="?r=d&p=' . urlencode($filename) . '">Disable</a>';
    } else {
      echo '<span style="color: red;">DISABLED</span> | <a href="?r=e&p=' . urlencode($filename) . '">Enable</a>';
    }
    echo '</li>';
  }
  closedir($dh);
?>
    </ol>
   </div>
</body>
</html>

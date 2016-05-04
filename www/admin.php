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
  } else if($_GET['r'] == 'c') {
    mkdir($rPath, 0755, true);
  } else {
    die('Bad action');
  }
  header('Location: admin.php');
} else if(isset($_GET['m']) && isset($_GET['t']) && isset($_GET['h']) && $_GET['m'] == 'd') {
  chat_delMessageHash($_GET['h'], $_GET['t']);
  header('Location: admin.php');
} else if(isset($_GET['f']) && isset($_GET['p'])) {
  $fn = $_GET['p'];
  if(strpos($fn, '..') !== false) {
    die('Bad path');
  }

  $rPath =  $files_libraryRoots . DIRECTORY_SEPARATOR . $fn;
  
  if($_GET['f'] == 'd') {
    rmrf($rPath);
  } else {
    die('Bad action');
  }
  header('Location: admin.php');
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
  echo '<li>' . htmlentities($m['n']) . ' @ ' . $m['t'] . ': ' . htmlentities($m['m']) . ' || <a onclick="return confirm(\'Are you sure you want to delete this message?\')" href="?m=d&t=' . $m['t'] . '&h=' . $m['i'] . '">Delete</a></li>';
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
    <hr/>
    
    <h1>File Roots:</h1>
    <form method="get">
      <input type="hidden" name="r" value="c"/>
      <input type="text" name="p"/><input type="submit" value="Create New Root"/>
    </form>
    <ol>
<?php

	foreach(new FilesystemIterator($files_libraryRoots) as $path => $dirent) {
    if(!$dirent->isDir()) {
      continue;
    }
    
    $filename = $dirent->getBasename();
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
?>
    </ol>
    <hr/>
    
    <h1>Files</h1>
<?php
function printFiles($path, $level) {
  global $files_libraryRoots, $files_libraryEnabled;
  
  if(is_dir($path)) {
    if($dh = opendir($path)) {
      $spaces = 20 + ($level * 20);
      while(($file = readdir($dh)) !== false) {
        $fullpath = $path . DIRECTORY_SEPARATOR . $file;
        $trimname = substr($fullpath, strlen($files_libraryRoots));
        if(strpos($file, '.') === 0) {
          continue;
        }

        echo '<span style="padding-left: ' . $spaces . 'px;"><span style="font-size: 150%; line-height: 0;">&#8594;</span> ' . 
          '<a href="Shared/' . $trimname . '">' .
          $file . '</a> :: <a onclick="return confirm(\'Are you sure you want to delete this file or directory?\')" href="?f=d&p=' . urlencode($trimname) . '">Delete</a></span><br/>';
        if(is_dir($fullpath)) {
          printFiles($fullpath, $level + 1);
        }
      
      }
      closedir($dh);
    }
  }
}

printFiles($files_libraryRoots, 0);
?>
  <i>Note: Links to download files only work for enabled roots.</i>
   </div>
</body>
</html>

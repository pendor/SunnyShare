<?php
require_once('../config.php');
require_once('../functions.php');

checkAdmin();
  
if(isset($_GET['r']) && isset($_GET['p'])) {
  $fn = $_GET['p'];
  
  if(strpos($fn, '..') !== false) {
    die('Bad path');
  }
  
  $enPath = $files_libraryEnabled . DIRECTORY_SEPARATOR . $fn;
  $noUpPath = $files_libraryRoots . DIRECTORY_SEPARATOR . $fn . DIRECTORY_SEPARATOR . TAG_NOUPLOAD;
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
  } else if($_GET['r'] == 'du') {
    touch($noUpPath);
  } else if($_GET['r'] == 'eu') {
    unlink($noUpPath);
  } else if($_GET['r'] == 'c') {
    mkdir($rPath, 0755, true);
  } else {
    die('Bad action');
  }
  header('Location: /admin/files.php');
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
  header('Location: /admin/files.php');
}
printHeader(true);
?>
    
    <h1>File Roots:</h1>
    <form method="get">
      <input type="hidden" name="r" value="c"/>
      <input type="text" name="p"/><input type="submit" value="Create New Root"/>
    </form>
    <table border="1" style="border-collapse: collapse;">
      <thead style="padding: 2px;">
      <tr>
        <th>Name</th>
        <th>Visible?</th>
        <th>Uploads?</th>
      </tr>
      </thead>
      <tbody>
<?php
  
	foreach(new FilesystemIterator($files_libraryRoots) as $path => $dirent) {
    if(!$dirent->isDir()) {
      continue;
    }
    
    $filename = $dirent->getBasename();
    $link = is_link($files_libraryEnabled . DIRECTORY_SEPARATOR . $filename);
    $upload = !file_exists($files_libraryRoots . DIRECTORY_SEPARATOR . $filename . DIRECTORY_SEPARATOR . TAG_NOUPLOAD);

    echo '<tr><th style="padding: 2px;">' . htmlentities($filename) . '</th>';

    // FIXME: Style is bad.  Make background color, text black, entire cell is link, click to toggle
    echo '<td>';
    if($link) {
      echo '<a class="toggle-yes" href="?r=d&p=' . urlencode($filename) . '">Yes</a>';
    } else {
      echo '<a class="toggle-no" href="?r=e&p=' . urlencode($filename) . '">No</a>';
    }
    echo '</td>';

    echo '<td>';
    if($upload) {
      echo '<a class="toggle-yes" href="?r=du&p=' . urlencode($filename) . '">Yes</a>';
    } else {
      echo '<a class="toggle-no" href="?r=eu&p=' . urlencode($filename) . '">No</a>';
    }
    echo '</td>';
    echo '</tr>' . "\n";
  }
?>
    </tbody>
    </table>
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
          '<a href="/Shared/' . $trimname . '">' .
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
  
<?php printFooter(); ?>

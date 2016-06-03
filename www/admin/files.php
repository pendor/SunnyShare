<?php
require_once('../config.php');
require_once('../functions.php');

checkAdmin();

function redirect() {
  $toIndex = isset($_GET['return']) && $_GET['return'] == "i";
  if($toIndex) {
    header('Location: /admin/');
  } else {
    header('Location: /admin/files.php');
  }
}
  
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

  redirect();
} else if(isset($_GET['f']) && isset($_GET['p'])) {
  $fn = $_GET['p'];
  if(strpos($fn, '..') !== false) {
    die('Bad path');
  }

  $rPath =  $files_libraryRoots . DIRECTORY_SEPARATOR . $fn;
  
  if($_GET['f'] == 'd') {
    rmrf($rPath);
  } else if($_GET['f'] == 'a') {
    markFileApproved($rPath);
  } else {
    die('Bad action');
  }
  
  redirect();
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
function printFiles(&$files, &$reported, $path, $level) {
  global $files_libraryRoots, $files_libraryEnabled;
  
  if(is_dir($path)) {
    if($dh = opendir($path)) {
      while(($file = readdir($dh)) !== false) {
        $fullpath = $path . DIRECTORY_SEPARATOR . $file;
        
        if(strpos($file, '.') === 0) {
          continue;
        }

        $att = getXattr($fullpath);
        if($att == XA_REPORTED) {
          $reported[] = $fullpath;
        } else {
          $entry = array();
          $entry['fullpath'] = $fullpath;
          $entry['level'] = $level;
          $entry['att'] = $att;
          $files[] = $entry;
        }
        if(is_dir($fullpath)) {
          printFiles($files, $reported, $fullpath, $level + 1);
        }
      }
      closedir($dh);
    }
  }
}

$reported = array();
$files = array();
printFiles($files, $reported, $files_libraryRoots, 0);

if(count($reported) > 0) {
  echo '<h1>Reported Files</h1>';
  foreach($reported as $fullpath) {
    $trimname = substr($fullpath, strlen($files_libraryRoots));
    $file = basename($fullpath);
    
    echo '<a onclick="return confirm(\'Are you sure you want to delete this file?\')" href="?f=d&p=' . urlencode($trimname) . '">Delete</a>';
    echo ' :: <a onclick="return confirm(\'Are you sure you want to approve this file?\')" href="?f=a&p=' . urlencode($trimname) . '">Mark Approved</a>';
    echo ' <span style="font-size: 150%; line-height: 0;">&#8594;</span> ' . 
      '<a href="/Shared/' . $trimname . '">' . $trimname . '</a> ';
    echo '</span><br/>'; 
  }
  
  echo '<hr/>';
}

foreach($files as $f) {
  $fullpath = $f['fullpath'];
  $level = $f['level'];
  $att = $f['att'];
  $spaces = 20 + ($level * 20);
  $trimname = substr($fullpath, strlen($files_libraryRoots));
  $file = basename($fullpath);
  
  echo '<a onclick="return confirm(\'Are you sure you want to delete this file or directory?\')" href="?f=d&p=' . urlencode($trimname) . '">Delete</a></span>';
  
  echo '<span style="padding-left: ' . $spaces . 'px;">';
  echo ' <span style="font-size: 150%; line-height: 0;">&#8594;</span> ' . 
    '<a href="/Shared/' . $trimname . '">' .
    $file . '</a>';
  if($att == XA_APPROVED) {
    echo ' :: <span style="color: green;">Approved</span>';
  }
  echo '<br/>';
}

?>
  <i>Note: Links to download files only work for enabled roots.</i>
  
<?php printFooter(); ?>

<?php
	/*
		This program is free software: you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation, either version 3 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program.  If not, see <http://www.gnu.org/licenses/>.
	*/
  
  // FIXME: Too much hardcoded crap here...
  $upRoot = '/mnt/data';
  $allowedUploadRoots = array( 'Shared', 'Extra' );
	
  if(isset($_FILES['file'])) {
    $urlPath = rtrim(ltrim($_POST['uploaddir'], '/'), '/');
  } else {
    list($urlPath) = explode('?', $_SERVER['REQUEST_URI']);
    $urlPath = rtrim(ltrim(rawurldecode($urlPath), '/'), '/');  
  }
    
  if(strpos($urlPath, '..') !== FALSE) {
    httperr(403, 'Bad upload path - no double dots allowed in filename');
  }
  
  $path = $upRoot . '/' . $urlPath;
  
  // Can't call the script directly since REQUEST_URI won't be a directory
  if($_SERVER['PHP_SELF'] == $path || strpos($path, $_SERVER['PHP_SELF']) !== FALSE) {
    httperr(500, 'Can\'t call index directly');
  }
  
  $rootOk = false;
  foreach($allowedUploadRoots as $rt) {
    if($urlPath == $rt || strpos($urlPath, $rt . '/') === 0) {
      $rootOk = true;
    }
  }
  if(!$rootOk) {
    httperr(403, 'Bad upload path (not in approved root): ' . $urlPath);
  }

  // Make sure it is valid.
  if(!is_dir($path)) {
  	httperr(403, 'Bad upload path (not directory): ' . $path);
  }
  
  $canupload = !is_file($path . '/.noupload');

	$settings = array(
		'uploaddir'         => $path,
    'urlpath'           => $urlPath,
		'ignores'           => array( 
      '.', 'LICENSE', 'README.md', 'README.txt', 
      'readme.txt', '.noupload',
      pathinfo(__FILE__, PATHINFO_BASENAME) 
    ),
		'badext'            => array( 
      'php', 'php3', 'php4', 'php5', 'pl', 
      'cgi', 'sh', 'noupload' 
    ),
  );
  
  // Omit .. if in one of the roots already
  if(in_array($urlPath, $allowedUploadRoots)) {
    $settings['ignores'][] = '..';
  } 

  function httperr($code, $text) {
    // Special handling for CGI:
    header('Status: ' . $code . ' ' . $text);
    echo $text . "\n";
    $GLOBALS['http_response_code'] = $code;
    exit;
  }

	function FormatSize($bytes) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		$bytes /= pow(1024, $pow);

		return ceil($bytes) . ' ' . $units[$pow];
	}

  function normalize_files_array($files = []) {
    $normalized_array = [];

    foreach($files as $index => $file) {
      if(!is_array($file['name'])) {
        $normalized_array[$index][] = $file;
        continue;
      }

      foreach($file['name'] as $idx => $name) {
        $normalized_array[$index][$idx] = [
          'name' => $name,
          'type' => $file['type'][$idx],
          'tmp_name' => $file['tmp_name'][$idx],
          'error' => $file['error'][$idx],
          'size' => $file['size'][$idx]
        ];
      }
    }
    return $normalized_array;
  }

	// Handling file upload
	function UploadFile($file_data, $field_name) {
		global $settings;

    /*
      Normalized data should look like this:
        Array ( 
          [file] => Array ( 
            [0] => Array ( 
              [name] => rainbowkilt.jpg 
              [type] => image/jpeg 
              [tmp_name] => /mnt/data/tmp/phpMpMGjd 
              [error] => 0 
              [size] => 108936 
            ) 
          ) 
        )
      idx increases if we have multiples in one upload.
    */
    foreach($file_data[$field_name] as $idx => $data) {
      if($data['error'] != 0) {
        httperr(500, 'Upload error: ' . $data['error']); 
      }
      
      $filename = $data['name'];
      
      if(strpos($filename, '..') !== FALSE || strpos($filename, '/') !== FALSE) {
        httperr(401, 'Invalid filename - no .. or / allowed.');
      }

      // Defang any bad extensions.
      $ext = pathinfo($filename, PATHINFO_EXTENSION);
  		if(in_array($ext, $settings['badext'])) {
        $filename = $filename . '.txt';
  		}
      
      $dest = $settings['uploaddir'] . DIRECTORY_SEPARATOR . $filename;
      
  		// Do now allow to overwriting files
  		if(file_exists($dest)) {
        httperr(401, 'File already exists');
  		}
      
      if(!move_uploaded_file($data['tmp_name'], $dest)) {
        $err = error_get_last();
        httperr(500, 'Error uploading file: ' . $err['message']);
      }
      
      echo "Received: $filename\n";
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
  
	// List files in a given directory, excluding certain files
	function ListFiles($dir, $exclude) {
		$file_array = array();
		$dir_array = array();
		$dh = opendir($dir);
		while(false !== ($filename = readdir($dh))) {
		  if(in_array($filename, $exclude)) {
		    continue;
		  }
      
	  	$fullname = $dir . DIRECTORY_SEPARATOR . $filename;
	    if(is_file($fullname)) {
				$file_array[] = $filename;
			} else {
			  $dir_array[] = $filename . '/';
			}
		}
		sort($dir_array);
		sort($file_array);
		return array_merge($dir_array, $file_array);
	}
  
	// Files are being POSTed. Uploading them one by one.
	if($canupload && isset($_FILES['file'])) {
		header('Content-type: text/plain');
		UploadFile(normalize_files_array($_FILES), 'file');
		exit;
	}
  
  $file_array = ListFiles($settings['uploaddir'], $settings['ignores']);
  
?><!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="/style.css"/>
	<title>Sunny+Share - Share Freely!</title>
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width"/>
</head>
<body>
  <div id="header">
		<a id="logo" href="/"><img src="/logo.png" alt="Sunny+Share" title="Sunny+Share - Share Freely"/></a>
		<a href="/">Home</a> &bull;
		<a href="/Shared/" class="current">Library</a> &bull;
		<a href="/about.html">About</a>
  </div>

  <div id="content">
	<?php
    $readme = $path . DIRECTORY_SEPARATOR . "readme.txt";
    if(is_file($readme)) {
      $txt = file_get_contents($readme, false, NULL, 0, 8192);
      $txt = normalize($txt);
      $txt = htmlentities($txt);
      $txt = str_replace("\n", "<br/>", $txt);
      echo '<div class="readme"><h2>About this directory:</h2>' . $txt . '</div>' . "\n";
    } else {
      echo '<h2>Files shared by others:</h2>';
    }
  
	  if(!count($file_array)) {
	    echo "<h2>Nothing shared yet!</h2>";
	    if($canupload) {
	      echo "<p>Why not be the first?</p>";
      }
	  } else {
  		echo '<ul>';
  		foreach($file_array as $filename) {
        $fullname = $path . DIRECTORY_SEPARATOR . $filename;
        if($filename == '../') {
          $info = ' (Parent Directory)';
        } else if(substr($filename, -1) == '/') {
          try {
            $fi = new FilesystemIterator($fullname, FilesystemIterator::SKIP_DOTS);
            $info = '(' . iterator_count($fi) . ' files)';
          } catch(Exception $ex) {
            $info = ' (Read error)';
          }
  		  } else {
  		    $info = ' - ' . FormatSize(filesize($fullname));
  		  }
  			echo '<li><a href="' . $filename . '">' . $filename . 
  			  '</a>' . $info . '</li>';
  		}
  		echo '</ul>';
		}
		
		if(!$canupload) { ?>
		  <div class="box">
		    <h2>Share your files:</h2>
		    <p>Some directories on this box may allow you to upload files (but this directory
		      doesn't).  You might look for an 'incoming' or similar place to share files others
		      might find interesting.</p>
	    </div>
	<?php } else { ?>
		<div class="box">
  		<h2>Share your files:</h2>
  		<form method="post" enctype="multipart/form-data" id="upload_form">
  			<p>Choose files you wish to share.  Files can only be removed by this box's owner.
          Uploaded files will be viewable by anyone in WiFi range.  Inappropriate 
          uploads will be removed, and you should feel ashamed of yourself!</p>
      
        <input type="file" name="file[]" id="file1"/>
        <input type="button" value="Upload File" onclick="uploadFile()"/><br/>
        Maximum upload size: <?= ini_get('upload_max_filesize') ?><br/>
        <progress id="progressBar" value="0" style="width:280px;"></progress>
        <h3 id="status"></h3>
  		</form>
    </div>

<script charset="utf-8" type="text/javascript">
function uploadFile() {
  var prog = document.getElementById("progressBar");
  var status = document.getElementById("status");
	var file = document.getElementById("file1").files[0];

  prog.value = 0;
	var ajax = new XMLHttpRequest();
	ajax.upload.onprogress = function(event) {
  	var percent = (event.loaded / event.total) * 100;
  	prog.max = event.total;
  	prog.value = event.loaded;
  	if(percent >= 100) {
  	  status.innerHTML = '<img src="/ball.gif" width="25" height="25"/> ' + 
  	    'Finishing upload.  Please wait...';
  	} else {
  	  status.innerHTML = percent.toFixed(2) + "% uploaded...";
	  }
	};
  
  ajax.onreadystatechange = function(event) {
    if(ajax.readyState == 4) {
      if(ajax.status == 200) {
        status.innerHTML = '<img src="/check.png" width="25" height="25"/> ' + 
    	    'Upload complete:<br/>' + ajax.responseText;
        window.setTimeout(function() {location.reload();}, 1500);
      } else {
        status.innerHTML = '<img src="/error.png" width="25" height="25"/> ' + 
          'Error uploading:<br/>' + ajax.status + ' ' + ajax.statusText + ' : ' + ajax.responseText;
      }
    }
  };

	ajax.open("POST", "/<?= pathinfo(__FILE__, PATHINFO_BASENAME) ?>");
  
	var formdata = new FormData();
	formdata.append("uploaddir", "<?= $settings['urlpath'] ?>");
	formdata.append("file[]", file);
  
	ajax.send(formdata);
}
</script>
		<?php } ?>
</div>
<!-- Preload: -->
<img src="/ball.gif" width="1" height="1" style="opacity: 0.01;"/>
<img src="/check.png" width="1" height="1" style="opacity: 0.01;"/>
<img src="/error.png" width="1" height="1" style="opacity: 0.01;"/>
</body>
</html>

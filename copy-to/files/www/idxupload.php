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
	$phproot = '/www/';
  if (isset($_FILES['file'])) {
    $path = $_POST['uploaddir'];
    if(strpos($path, $phproot) !== 0 || strpos($path, '..') !== FALSE) {
      echo "Bad upload path";
      exit;
    }
    if(substr($path, -1) != '/') {
      $path .= '/';
    }
  } else {
    list($path) = explode('?', $_SERVER['REQUEST_URI']);
    $path = $phproot . ltrim(rawurldecode($path), '/');
  }
  
  // Can't call the script directly since REQUEST_URI won't be a directory
  if($_SERVER['PHP_SELF'] == '/'.$path) {
  	die("Unable to call " . $path . " directly.");
  }

  // Make sure it is valid.
  if(!is_dir($path)) {
  	die("<b>" . $path . "</b> is not a valid path.");
  }
  $canupload = !is_file($path . '.noupload');
  $urlpath = substr($path, strlen($phproot) - 1);

  $ignores = array( '.', 'LICENSE', 'README.md', '.noupload' );

  $realparent = realpath($path . '/..');
  if($realparent == $phproot || $realparent . '/' == $phproot || strpos($realparent.'/', $phproot) !== 0) {
    $ignores[] = '..';
  } 

	// =============={ Configuration Begin }==============
	$settings = array(
		// Directory to store uploaded files
		'uploaddir' => $path,
    'urlpath'   => $urlpath,
    
		// Display debugging information
		'debug'     => false,

		// Complete URL to your directory (including tracing slash)
		'url'       => 'http://sunnyshare.lan/' . $urlpath,

		// Files that will be ignored
		'ignores'   => $ignores,
		
		'badext'    => array( 'php', 'php3', 'php4', 'php5', 
		                      'pl', 'cgi', 'sh', 'noupload' ),
	);
	// =============={ Configuration End }==============

	// Enabling error reporting
	if ($settings['debug']) {
		error_reporting(E_ALL);
		ini_set('display_startup_errors',1);
		ini_set('display_errors',1);
	}

	$data = array();

	// Name of this file
	$data['scriptname'] = pathinfo(__FILE__, PATHINFO_BASENAME);

	// Adding current script name to ignore list
	$data['ignores'] = $settings['ignores'];
	$data['ignores'][] = $data['scriptname'];

	// Use canonized path
	$data['uploaddir'] = realpath($settings['uploaddir']);

	// Maximum upload size, set by system
	$data['max_upload_size'] = ini_get('upload_max_filesize');

	// If debug is enabled, logging all variables
	if ($settings['debug']) {
		// Displaying debug information
		echo '<h2>Settings:</h2>';
		echo '<pre>';
		print_r($settings);
		echo '</pre>';

		// Displaying debug information
		echo '<h2>Data:</h2>';
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		echo '</pre>';

		// Displaying debug information
		echo '<h2>SESSION:</h2>';
		echo '<pre>';
		print_r($_SESSION);
		echo '</pre>';
	}

	// Format file size
	function FormatSize ($bytes) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		$bytes /= pow(1024, $pow);

		return ceil($bytes) . ' ' . $units[$pow];
	}

	// Rotating a two-dimensional array
	function DiverseArray ($vector) {
		$result = array();
		foreach ($vector as $key1 => $value1) {
			foreach ($value1 as $key2 => $value2) {
				$result[$key2][$key1] = $value2;
			}
		}
		return $result;
	}

	// Handling file upload
	function UploadFile ($file_data) {
		global $settings;
		global $data;
		global $_SESSION;

		$file_data['uploaded_file_name'] = basename($file_data['name']);
		$file_data['target_file_name'] = $file_data['uploaded_file_name'];
		$file_data['upload_target_file'] = $data['uploaddir'] . DIRECTORY_SEPARATOR . $file_data['target_file_name'];

		// Do now allow to overwriting files
		if (file_exists($file_data['upload_target_file'])) {
			echo 'File name already exists' . "\n";
			return;
		}
		
		$ext = pathinfo($file_data['upload_target_file'], PATHINFO_EXTENSION);
		if(in_array($ext, $settings['badext'])) {
		  $file_data['upload_target_file'] = $file_data['upload_target_file'] . '.txt';
		  $file_data['target_file_name'] = $file_data['target_file_name'] . '.txt';
		}

		// Moving uploaded file OK
		if (move_uploaded_file($file_data['tmp_name'], $file_data['upload_target_file'])) {
			echo $settings['url'] .  $file_data['target_file_name'] . "\n";
		} else {
			echo 'Error: unable to upload the file.';
		}
	}

	// Files are being POSTed. Uploading them one by one.
	if($canupload && isset($_FILES['file'])) {
		header('Content-type: text/plain');
		if(is_array($_FILES['file'])) {
			$file_array = DiverseArray($_FILES['file']);
			foreach ($file_array as $file_data) {
				UploadFile($file_data);
			}
		} else {
			UploadFile($_FILES['file']);
		}
		exit;
	}

	// List files in a given directory, excluding certain files
	function ListFiles ($dir, $exclude) {
		$file_array = array();
		$dir_array = array();
		$dh = opendir($dir);
		while(false !== ($filename = readdir($dh))) {
		  if(in_array($filename, $exclude)) {
		    continue;
		  }
	  	$fullname = $dir . $filename;
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
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="/style.css"/>
		<style media="screen">
			body ul li form {
				display: inline-block;
				padding: 0;
				margin: 0;
			}

			li.owned {
				margin: 8px;
			}

			body ul li form button {
				opacity: 0.5;
				display: inline-block;
				padding: 4px 16px;
				margin: 0;
				border: 0;
			}

			li.uploading {
				animation: upanim 1s linear 0s infinite alternate;
			}

			@keyframes upanim {
				from {
					opacity: 0.1;
				}
				to {
					opacity: 0.7;
				}
			}
		</style>
	<title>Sunny+Share - Share Freely!</title>
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width"/>
</head>
<body>
  <div id="header">
		<a id="logo" href="/"><img src="/logo.png" alt="Sunny+Share" title="Sunny+Share - Share Freely"/></a>
		<a href="/">Home</a> &bul;
		<a href="/Shared/">Library</a> &bul;
		<a href="/about.html">About</a>
  </div>

  <div id="content">
    <h2>Files shared by others:</h2>
	<?php
	  $file_array = ListFiles($settings['uploaddir'], $data['ignores']);
	  if(count($file_array) == 0) {
	    echo "<h2>Nothing shared yet!</h2>";
	    if($canupload) {
	      echo "<p>Why not be the first?</p>";
      }
	  } else {
  		echo '<ul id="simpleupload-ul">';
  		foreach($file_array as $filename) {
  		  if(substr($filename, -1) == '/') {
  		    $fi = new FilesystemIterator($path . $filename, FilesystemIterator::SKIP_DOTS);
  		    $info = '(' . iterator_count($fi) . ' files)';
  		  } else {
  		    $info = FormatSize(filesize($path . $filename));
  		  }
  			echo '<li><a href="' . $urlpath . $filename . '">' . $filename . 
  			  '</a> - ' . $info . '</li>';
  		}
  		echo '</ul>';
		}
		
		if(!$canupload) { ?>
		  <div class="box">
		    <h2>Share your files:</h2>
		    <p>Some directories on this box may allow you to upload files (but this directory)
		      doesn't).  You might look for an 'incoming' or similar place to share files others
		      might find interesting.</p>
	    </div>
	<?php } else { ?>
		<div class="box" id="dropzone">
		<h2>Share your files:</h2>
		<form method="post" enctype="multipart/form-data" id="upload_form">
			<p>Choose files you wish to share.  Files can only be removed by this box's owner.
        Uploaded files will be viewable by anyone in WiFi range.  Inappropriate 
      uploads will be removed, and you should feel ashamed of yourself!</p>
      
      <input type="file" name="file[]" id="file1"/>
      <input type="button" value="Upload File" onclick="uploadFile()"/><br/>
      <progress id="progressBar" value="0" max="100" style="width:300px;"></progress>
      <h3 id="status"></h3>
			Maximum upload size: <?php echo $data['max_upload_size']; ?>
		</form>
    </div>

		<script charset="utf-8" type="text/javascript">
    /* Script written by Adam Khoury @ DevelopPHP.com */
    /* Video Tutorial: http://www.youtube.com/watch?v=EraNFJiY0Eg */
    function _(el) {
    	return document.getElementById(el);
    }
    function uploadFile() {
      _("progressBar").value = 0;
    	var file = _("file1").files[0];
    	// alert(file.name+" | "+file.size+" | "+file.type);
    	var formdata = new FormData();
    	formdata.append("file[]", file);
    	formdata.append("uploaddir", "<?= $data['uploaddir'] ?>");
    	var ajax = new XMLHttpRequest();
    	ajax.upload.addEventListener("progress", progressHandler, false);
    	ajax.addEventListener("load", completeHandler, false);
    	ajax.addEventListener("error", errorHandler, false);
    	ajax.addEventListener("abort", abortHandler, false);
    	ajax.open("POST", "/<?= $data['scriptname'] ?>");
    	ajax.send(formdata);
    }
    
    function progressHandler(event) {
    	var percent = (event.loaded / event.total) * 100;
    	_("progressBar").max = event.total;
    	_("progressBar").value = event.loaded;
    	if(percent >= 100) {
    	  _("status").innerHTML = '<img src="/ball.gif" width="25" height="25"/>' + 
    	    'Finishing upload.  Please wait... ';
    	} else {
    	  _("status").innerHTML = percent.toFixed(2) + "% uploaded... Please wait";
  	  }
    }
    
    function completeHandler(event) {
    	_("status").innerHTML = '<img src="/check.png" width="25" height="25"/>' + 
    	  'Upload complete: ' + event.target.responseText;
    	window.setTimeout(function() {location.reload();}, 3000);
    }
    
    function errorHandler(event) {
    	_("status").innerHTML = "Upload Failed";
    }
    
    function abortHandler(event) {
    	_("status").innerHTML = "Upload Aborted";
    }
		</script>
		<?php } ?>
</div>
</body>
</html>

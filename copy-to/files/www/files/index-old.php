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

	// =============={ Configuration Begin }==============
	$settings = array(

		// Website title
		'title' => 'Sunny & Share File Uploads',

		// Directory to store uploaded files
		'uploaddir' => '.',

		// Display list uploaded files
		'listfiles' => true,

		// Allow users to delete files that they have uploaded (will enable sessions)
		'allow_deletion' => false,

		// Allow users to mark files as hidden
		'allow_private' => false,

		// Display file sizes
		'listfiles_size' => true,

		// Display file dates
		'listfiles_date' => false,

		// Display file dates format
		'listfiles_date_format' => 'F d Y H:i:s',

		// Randomize file names (number of 'false')
		'random_name_len' => false,

		// Keep filetype information (if random name is activated)
		'random_name_keep_type' => true,

		// Random file name letters
		'random_name_alphabet' => 'qazwsxedcrfvtgbyhnujmikolp1234567890',

		// Display debugging information
		'debug' => false,

		// Complete URL to your directory (including tracing slash)
		'url' => 'http://sunnyshare.lan/files/',

		// Amount of seconds that each file should be stored for (0 for no limit)
		// Default 30 days
		'time_limit' => FALSE,

		// Files that will be ignored
		'ignores' => array('.', '..', 'LICENSE', 'README.md', '*.php'),
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

	// If file deletion or private files are allowed, starting a session.
	// This is required for user authentification
	if ($settings['allow_deletion'] || $settings['allow_private']) {
		session_start();

		// 'User ID'
		if (!isset($_SESSION['upload_user_id']))
			$_SESSION['upload_user_id'] = rand(100000, 999999);

		// List of filenames that were uploaded by this user
		if (!isset($_SESSION['upload_user_files']))
			$_SESSION['upload_user_files'] = array();
	}

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
		foreach ($vector as $key1 => $value1)
			foreach ($value1 as $key2 => $value2)
				$result[$key2][$key1] = $value2;
		return $result;
	}

	// Handling file upload
	function UploadFile ($file_data) {
		global $settings;
		global $data;
		global $_SESSION;

		$file_data['uploaded_file_name'] = basename($file_data['name']);
		$file_data['target_file_name'] = $file_data['uploaded_file_name'];

		// Generating random file name
		if ($settings['random_name_len'] !== false) {
			do {
				$file_data['target_file_name'] = '';
				while (strlen($file_data['target_file_name']) < $settings['random_name_len'])
					$file_data['target_file_name'] .= $settings['random_name_alphabet'][rand(0, strlen($settings['random_name_alphabet']) - 1)];
				if ($settings['random_name_keep_type'])
					$file_data['target_file_name'] .= '.' . pathinfo($file_data['uploaded_file_name'], PATHINFO_EXTENSION);
			} while (file_exists($file_data['target_file_name']));
		}
		$file_data['upload_target_file'] = $data['uploaddir'] . DIRECTORY_SEPARATOR . $file_data['target_file_name'];

		// Do now allow to overwriting files
		if (file_exists($file_data['upload_target_file'])) {
			echo 'File name already exists' . "\n";
			return;
		}

		// Moving uploaded file OK
		if (move_uploaded_file($file_data['tmp_name'], $file_data['upload_target_file'])) {
			if ($settings['allow_deletion'] || $settings['allow_private'])
				$_SESSION['upload_user_files'][] = $file_data['target_file_name'];
			// echo $settings['url'] .  $file_data['target_file_name'] . "\n";
			header('Location: ' . $data['scriptname'] . "\r\n");
		} else {
			echo 'Error: unable to upload the file.';
		}
	}


	// Files are being POSEed. Uploading them one by one.
	if (isset($_FILES['file'])) {
		header('Content-type: text/plain');
		if (is_array($_FILES['file'])) {
			$file_array = DiverseArray($_FILES['file']);
			foreach ($file_array as $file_data)
				UploadFile($file_data);
		} else
			UploadFile($_FILES['file']);
		exit;
	}

	// Other file functions (delete, private).
	if (isset($_POST)) {
		if ($settings['allow_deletion'])
			if (isset($_POST['action']) && $_POST['action'] === 'delete')
				if (in_array(substr($_POST['target'], 1), $_SESSION['upload_user_files']) || in_array($_POST['target'], $_SESSION['upload_user_files']))
					if (file_exists($_POST['target'])) {
						unlink($_POST['target']);
						echo 'File has been removed';
						exit;
					}

		if ($settings['allow_private'])
			if (isset($_POST['action']) && $_POST['action'] === 'privatetoggle')
				if (in_array(substr($_POST['target'], 1), $_SESSION['upload_user_files']) || in_array($_POST['target'], $_SESSION['upload_user_files']))
					if (file_exists($_POST['target'])) {
						if ($_POST['target'][0] === '.') {
							rename($_POST['target'], substr($_POST['target'], 1));
							echo 'File has been made visible';
						} else {
							rename($_POST['target'], '.' . $_POST['target']);
							echo 'File has been hidden';
						}
						exit;
					}
	}

	// List files in a given directory, excluding certain files
	function ListFiles ($dir, $exclude) {
		$file_array = array();
		$dh = opendir($dir);
			while (false !== ($filename = readdir($dh)))
				if (is_file($filename) && !in_array($filename, $exclude))
					$file_array[filemtime($filename)] = $filename;
		ksort($file_array);
		$file_array = array_reverse($file_array, true);
		return $file_array;
	}

	$file_array = ListFiles($settings['uploaddir'], $data['ignores']);

	// Removing old files
	foreach ($file_array as $file)
		if ($settings['time_limit'] !== FALSE && $settings['time_limit'] < time() - filemtime($file))
			unlink($file);

	$file_array = ListFiles($settings['uploaddir'], $data['ignores']);

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
		<a href="/board/">Forum</a> &bul;
		<a href="/Shared/">Library</a> &bul;
		<a href="/files/" class="current">Upload</a> &bul;
		<a href="/about.html">About</a>
  </div>

  <div id="content">
		<?php if ($settings['listfiles']) { ?>
		  <h2>Files shared by others:</h2>
		  <?php
		  if(count($file_array) == 0) {
		    echo "<h2>Nothing shared yet!</h2><p>Why not be the first?</p>";
		  }
		  ?>
			<ul id="simpleupload-ul">
				<?php
					foreach ($file_array as $mtime => $filename) {
						$file_info = array();
						$file_owner = false;
						$file_private = $filename[0] === '.';

						if ($settings['listfiles_size'])
							$file_info[] = FormatSize(filesize($filename));

						if ($settings['listfiles_date'])
							$file_info[] = date($settings['listfiles_date_format'], $mtime);

						if ($settings['allow_deletion'] || $settings['allow_private'])
							if (in_array(substr($filename, 1), $_SESSION['upload_user_files']) || in_array($filename, $_SESSION['upload_user_files']))
								$file_owner = true;

						$file_info = implode(', ', $file_info);

						if (strlen($file_info) > 0)
							$file_info = ' (' . $file_info . ')';

						$class = '';
						if ($file_owner)
							$class = 'owned';

						if (!$file_private || $file_owner) {
							echo "<li class=\"' . $class . '\">";

							echo "<a href=\"$filename\">$filename<span>$file_info</span></a>";

							if ($file_owner) {
								if ($settings['allow_deletion'])
									echo '<form action="' . $data['scriptname'] . '" method="POST"><input type="hidden" name="target" value="' . $filename . '" /><input type="hidden" name="action" value="delete" /><button type="submit">delete</button></form>';

								if ($settings['allow_private'])
									if ($file_private)
										echo '<form action="' . $data['scriptname'] . '" method="POST"><input type="hidden" name="target" value="' . $filename . '" /><input type="hidden" name="action" value="privatetoggle" /><button type="submit">make public</button></form>';
									else
										echo '<form action="' . $data['scriptname'] . '" method="POST"><input type="hidden" name="target" value="' . $filename . '" /><input type="hidden" name="action" value="privatetoggle" /><button type="submit">make private</button></form>';
							}

							echo "</li>";
						}
					}
				?>
			</ul>
		<?php } ?>

		<div class="box" id="dropzone">
		<h2>Share your files:</h2>
		<form action="<?= $data['scriptname'] ?>" method="POST" enctype="multipart/form-data" id="simpleupload-form">			
			<p>Choose files you wish to share.  Files can only be removed by this box's owner.
        Uploaded files will be viewable by anyone in WiFi range.  Inappropriate 
      uploads will be removed, and you should feel ashamed of yourself!</p>
      
			<input type="file" name="file[]" multiple required id="simpleupload-input"/><br/>
			Maximum upload size: <?php echo $data['max_upload_size']; ?>
		</form>
    </div>

		<script charset="utf-8" type="text/javascript">
			var target_form = document.getElementById('simpleupload-form');
			var target_ul = document.getElementById('simpleupload-ul');
			var target_input = document.getElementById('simpleupload-input');

			target_form.addEventListener('dragover', function (event) {
				event.preventDefault();
			}, false);

			function AddFileLi (name, info) {
				target_form.style.display = 'none';

				var new_li = document.createElement('li');
				new_li.className = 'uploading';

				var new_a = document.createElement('a');
				new_a.innerHTML = '';
				new_li.appendChild(new_a);

				var new_span = document.createElement('span');
				new_span.innerHTML = 'Uploading your file now: ' + name + ' - ' + info;
				new_a.appendChild(new_span);

				target_ul.insertBefore(new_li, target_ul.firstChild);
			}

			function HandleFiles (event) {
				event.preventDefault();

				var i = 0,
					files = event.dataTransfer.files,
					len = files.length;

				var form = new FormData();

				for (; i < len; i++) {
					form.append('file[]', files[i]);
					AddFileLi(files[i].name, files[i].size + ' bytes');
				}

				var xhr = new XMLHttpRequest();
				xhr.onload = function() {
					window.location.reload();
				};

				xhr.open('post', '<?php echo $data['scriptname']; ?>', true);
				xhr.send(form);
			}

			document.getElementById('dropzone').addEventListener('drop', HandleFiles, false);

			document.getElementById('simpleupload-input').onchange = function () {
				AddFileLi(' ', document.getElementById('simpleupload-input').value);
				target_form.submit();
			};
		</script>
</div>
</body>
</html>

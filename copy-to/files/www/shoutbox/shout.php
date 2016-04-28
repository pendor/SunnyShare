<?php
/*********************************************************************************************************
 This code is part of the ShoutBox software (www.gerd-tentler.de/tools/shoutbox), copyright by
 Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
*********************************************************************************************************/

	error_reporting(E_WARNING);
	if(function_exists('session_start')) session_start();

//========================================================================================================
// Set variables, if they are not registered globally; needs PHP 4.1.0 or higher
//========================================================================================================

	if(isset($_POST['sbID'])) $sbID = $_POST['sbID'];
	if(isset($_POST['sbName'])) $sbName = $_POST['sbName'];
	if(isset($_POST['sbEMail'])) $sbEMail = $_POST['sbEMail'];
	if(isset($_POST['sbText'])) $sbText = $_POST['sbText'];
	if(isset($_POST['sbSpr'])) $sbSpr = $_POST['sbSpr'];

	if(isset($_POST['create'])) $create = $_POST['create'];
	if(isset($_REQUEST['delete'])) $delete = $_REQUEST['delete'];
	if(isset($_REQUEST['admin'])) $admin = $_REQUEST['admin'];

	if(isset($_SERVER['PHP_SELF'])) $PHP_SELF = $_SERVER['PHP_SELF'];
	if(isset($_SERVER['HTTP_HOST'])) $HTTP_HOST = $_SERVER['HTTP_HOST'];
	if(isset($_SERVER['HTTP_USER_AGENT'])) $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];

//========================================================================================================
// Make sure that the following variables are integers, e.g. to avoid possible database problems
//========================================================================================================

	$delete = (int) $delete;

//========================================================================================================
// Includes
//========================================================================================================

	if($HTTP_HOST == 'localhost' || ereg('^192\.168\.0\.[0-9]+$', $HTTP_HOST)) {
		include('config_local.inc.php');
	}
	else {
		include('config_main.inc.php');
	}
	if(!isset($language)) $language = 'en';
	include("languages/lang_$language.inc.php");
	include('smilies.inc');
	include('funclib.inc');

//========================================================================================================
// Set session variables (admin login); needs PHP 4.1.0 or higher
//========================================================================================================

	if($admin) $_SESSION['sb_admin'] = ($admin == $adminPass) ? $admin : '';

//========================================================================================================
// Functions
//========================================================================================================

	function is_admin() {
		global $adminPass;
		return ($_SESSION['sb_admin'] && $_SESSION['sb_admin'] == $adminPass);
	}

	function read_data() {
		$data = array();
		clearstatcache();

		if(file_exists('data/shoutbox.txt')) {
			$size = filesize('data/shoutbox.txt');

			if($size > 0) {
				if($fp = fopen('data/shoutbox.txt', 'r')) {
					$data = fread($fp, $size);
					$data = explode(chr(8) . "\r\n", $data);
					for($i = 0; $i < count($data); $i++) $data[$i] = explode(chr(7), $data[$i]);
					fclose($fp);
				}
			}
		}
		return $data;
	}

	function write_data($data) {
		if($fp = fopen('data/shoutbox.txt', 'w')) {
			for($i = 0; $i < count($data); $i++) $data[$i] = join(chr(7), $data[$i]);
			$data = join(chr(8) . "\r\n", $data);
			if(get_magic_quotes_gpc()) $data = stripslashes($data);
			fwrite($fp, $data);
			fclose($fp);
		}
	}

	function delete_entry($id) {
		global $db_name, $tbl_name, $fld_id;

		$error = '';

		if($db_name) {
			if(!mysql_query("DELETE FROM $tbl_name WHERE $fld_id='$id'")) $error = mysql_error();
		}
		else {
			$data = read_data();

			if(count($data)) foreach($data as $key => $val) {
				if($val[0] == $id) {
					array_splice($data, $key, 1);
					write_data($data);
					break;
				}
			}
		}
		return $error;
	}

	function new_entry($name, $email, $text) {
		global $db_name, $tbl_name, $fld_id, $fld_timestamp, $fld_name, $fld_email, $fld_text, $boxEntries, $reservedNames;

		$error = '';
		$tstamp = date('YmdHis');

		if(!is_admin() && in_array(strtolower($name), $reservedNames)) {
			$name = 'xxx';
		}

		if($db_name) {
			if(!get_magic_quotes_gpc()) {
				$name = addslashes($name);
				$email = addslashes($email);
				$text = addslashes($text);
			}
			$sql = "INSERT INTO $tbl_name ($fld_timestamp, $fld_name, $fld_email, $fld_text) ";
			$sql .= "VALUES ('$tstamp', '$name', '$email', '$text')";

			if(!mysql_query($sql)) $error = mysql_error();
			else {
				$sql = "SELECT $fld_id FROM $tbl_name ORDER BY $fld_timestamp DESC LIMIT $boxEntries, 1";
				$result = mysql_query($sql);
				if(mysql_num_rows($result)) {
					if($row = mysql_fetch_row($result)) {
						$sql = "DELETE FROM $tbl_name WHERE $fld_id<=$row[0]";
						if(!mysql_query($sql)) $error = mysql_error();
					}
				}
			}
		}
		else {
			$data = read_data();
			$len = count($data);
			$id = $len ? $data[$len-1][0] + 1 : 1;
			if($len >= $boxEntries) array_shift($data);
			$data[] = array($id, $tstamp, $name, $email, $text);
			write_data($data);
		}
		return $error;
	}

	function read_entries() {
		global $msg, $db_name, $tbl_name, $fld_timestamp, $messageOrder, $messageBGColors,
			   $boxEntries, $boxWidth, $wordLength, $timeOffset, $reservedNames, $dateFormat;

		if($db_name) {
			$sql = "SELECT * FROM $tbl_name ORDER BY $fld_timestamp $messageOrder LIMIT $boxEntries";
			$result = mysql_query($sql);
			while($row = mysql_fetch_row($result)) $data[] = $row;
		}
		else {
			$data = read_data();
			if(strtoupper($messageOrder) != 'ASC') rsort($data);
		}

		for($i = 0; $i < count($data); $i++) {
			$id = $data[$i][0];
			$tstamp = timeStamp($data[$i][1]);
			$name = $data[$i][2] ? format($data[$i][2], $wordLength, $boxWidth - 22, true) : '???';
			$email = strstr($data[$i][3], '@') ? $data[$i][3] : '';
			$text = format($data[$i][4], $wordLength, $boxWidth - 22, false);
			$bgcolor = ($bgcolor != $messageBGColors[0]) ? $messageBGColors[0] : $messageBGColors[1];

			if($dateFormat != 'Y-m-d' || (int) $timeOffset != 0) {
				$a = explode(' ', $tstamp);
				$d = explode('-', $a[0]);
				$t = explode(':', $a[1]);
				$ts = mktime($t[0], $t[1], $t[2], $d[1], $d[2], $d[0]);
				if((int) $timeOffset != 0) $ts += (int) $timeOffset * 3600;
				if(!$dateFormat) $dateFormat = 'Y-m-d';
				$tstamp = date($dateFormat . ' H:i:s', $ts);
			}

			if(is_admin()) {
?>
				<div class="cssShoutRaised" style="float:right" title="<?php echo $msg['delete']; ?>"
				onMouseDown="this.className='cssShoutPressed'"
				onMouseUp="this.className='cssShoutRaised'"
				onMouseOut="this.className='cssShoutRaised'"
				onClick="confirmDelete(<?php echo $id; ?>)">
				<img src="delete.gif" width="10" height="10">
				</div>
<?php
			}
			$class = in_array(strtolower($name), $reservedNames) ? 'cssShoutTextAdmin' : 'cssShoutText';
?>
			<div class="cssShoutTime" style="background-color:<?php echo $bgcolor; ?>">
			<?php echo $tstamp; ?>
			</div>
			<div class="<?php echo $class; ?>" style="background-color:<?php echo $bgcolor; ?>">
			<?php if($email) echo '<a href="mailto:' . $email . '">'; ?>
			<b><?php echo $name; ?>:</b><?php if($email) echo '</a>'; ?> <?php echo $text; ?>
			</div>
<?php
		}
	}

//========================================================================================================
// Main
//========================================================================================================

	if(!$db_name || db_open($db_server, $db_user, $db_pass, $db_name)) {
		$error = '';
		$table_exists = true;

		if($db_name) {
			if(!mysql_query("SELECT 1 FROM $tbl_name LIMIT 1")) $table_exists = false;
		}

		header('Cache-control: private, no-cache, must-revalidate');
		header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
		header('Date: Sat, 01 Jan 2000 00:00:00 GMT');
		header('Pragma: no-cache');
?>
		<html>
		<head>
		<meta name="robots" content="noindex, nofollow">
<?php
		if($table_exists) {
?>
			<meta http-equiv="refresh" content="<?php echo $boxRefresh; ?>; URL=<?php echo basename($PHP_SELF); ?>">
<?php
		}
?>
		<title>Output</title>
<?php
		$messageOrder = strtoupper($messageOrder);
		if($messageOrder != 'ASC' && $messageOrder != 'DESC') $messageOrder = 'DESC';

		if($messageOrder == 'ASC') {
?>
			<script type="text/javascript"> <!--
			function autoscroll() {
				if(document.documentElement && document.documentElement.offsetHeight)
					window.scrollBy(0, document.documentElement.offsetHeight + 1000);
				else if(document.body && document.body.offsetHeight)
					window.scrollBy(0, document.body.offsetHeight + 1000);
				else if(window.innerHeight)
					window.scrollBy(0, window.innerHeight + 1000);
				else if(document.height)
					window.scrollBy(0, document.height + 1000);
			}
			window.onload = autoscroll;
			//--> </script>
<?php
		}

		if(is_admin()) {
?>
			<script type="text/javascript"> <!--
			function confirmDelete(id) {
				var check = confirm("<?php echo $msg['confirm']; ?>");
				if(check) document.location.href = '<?php echo $PHP_SELF; ?>?delete=' + id;
			}
			//--> </script>
<?php
		}
?>
		<link rel="stylesheet" href="shoutbox.css" type="text/css">
		</head>
		<body style="margin:2px">
<?php
		if($db_name && !$table_exists) {

			if($create == 'yes') {
				$sql = "CREATE TABLE $tbl_name ( " .
					   "$fld_id INT(10) NOT NULL auto_increment, " .
					   "$fld_timestamp VARCHAR(14) NOT NULL, " .
					   "$fld_name VARCHAR(20), " .
					   "$fld_email VARCHAR(75), " .
					   "$fld_text TEXT NOT NULL, " .
					   "PRIMARY KEY ($fld_id))";
				if(!mysql_query($sql)) $error = mysql_error();
				else $table_exists = true;
			}
			else if($create == 'no') $error = 'Operation cancelled.';
			else {
				echo '<div class="cssShoutText" style="padding:4px">';
				echo '<form name="f1" action="' . $PHP_SELF . '" method="post" style="margin:0px">';
				echo "<b>Table $tbl_name doesn't exist. Create it now?</b><br><br>";
				echo '<input type="radio" name="create" value="yes" onClick="document.f1.submit()">yes &nbsp; ';
				echo '<input type="radio" name="create" value="no" onClick="document.f1.submit()">no';
				echo '</form></div>';
			}
		}
		else {

			if($admin && $admin != $_SESSION['sb_admin']) $error = $msg['wrongPass'];
			else if(is_admin() && $delete) {
				$error = delete_entry($delete);
			}
			else if($sbText) {
				if(checkSpam($sbID, -1, $sbName, $sbEMail, '', $sbText, '', $sbSpr)) $error = $msg['noSpam'];
				else $error = new_entry($sbName, $sbEMail, $sbText);
			}

			if($error) echo '<div class="cssShoutError">' . $error . '</div>';

			read_entries();
		}
?>
		</body>
		</html>
<?php
		if($db_name) mysql_close();
	}
?>

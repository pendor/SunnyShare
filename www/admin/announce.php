<?php
require_once('../config.php');
require_once('../functions.php');

checkAdmin();
  
if(isset($_GET['m']) && isset($_GET['t']) && isset($_GET['h']) && $_GET['m'] == 'd') {
  chat_delMessageHash($_GET['h'], $_GET['t']);
  header('Location: /admin/announce.php');
}

printHeader(true);

echo '<h1>Announcments:</h1><ol>';

$msgs = chat_readMessages();
foreach($msgs as $m) {
  echo '<li>' . htmlentities($m['n']) . ' @ ' . $m['t'] . ': ' . htmlentities($m['m']) . ' || <a onclick="return confirm(\'Are you sure you want to delete this message?\')" href="?m=d&t=' . $m['t'] . '&h=' . $m['i'] . '">Delete</a></li>';
}
echo '</ol>';

printFooter(); 

?>

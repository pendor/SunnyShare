<?php
// Change key & rename to config.php
$adminKey = 'changeme';
$domainName = 'sunnyshare.space';

$cookieTime = ((2021-1970) * 60 * 60 * 24 * 365);
$macCacheTtl = 60 * 60 * 2;

// Shoutbox settings
$chat_maxLen = "512";
$chat_dataFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'chat.json';
$chat_colors = array('#FFFFA5', '#E69D36', '#58C3EC', 
  '#B68CC2', '#C9DF6F', '#EEA1BC', '#87EBEE');
$chat_refreshTime = 5000;
  
// File upload settings
$files_upRoot = '/mnt/data';
$files_allowedUploadRoots = array( 'Shared' );
$files_libraryRoots = '/mnt/data/roots';
$files_libraryEnabled = '/mnt/data/Shared';

?>
<?php
/*********************************************************************************************************
 This code is part of the ShoutBox software (www.gerd-tentler.de/tools/shoutbox), copyright by
 Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
*********************************************************************************************************/

  error_reporting(E_WARNING);

//========================================================================================================
// Set variables, if they are not registered globally; needs PHP 4.1.0 or higher
//========================================================================================================

  if(isset($_SERVER['HTTP_HOST'])) $HTTP_HOST = $_SERVER['HTTP_HOST'];

//========================================================================================================
// Includes
//========================================================================================================

  if($HTTP_HOST == 'localhost' || $HTTP_HOST == '127.0.0.1' || ereg('^192\.168\.0\.[0-9]+$', $HTTP_HOST)) {
    include('config_local.inc.php');
  }
  else {
    include('config_main.inc.php');
  }
  if(!isset($language)) $language = 'en';
  include("languages/lang_$language.inc");
  include('smilies.inc');

//========================================================================================================
// Main
//========================================================================================================
?>
<html>
<head>
<title><?php echo $msg['smilies']; ?></title>
<script language="JavaScript"> <!--
function insert(txt) {
  if(window.opener) {
    el = window.opener.document.fShout.sbText;
    if(!el.value) el.value = txt + ' ';
    else el.value += ((el.value.charAt(el.value.length-1) == ' ') ? '' : ' ') + txt + ' ';
    self.close();
  }
}
//--> </script>
<link rel="stylesheet" href="shoutbox.css" type="text/css">
</head>
<body leftmargin="5" topmargin="5" marginwidth="5" marginheight="5">
<table border="0" cellspacing="0" cellpadding="4" align="center">
<?php
  $img_old = '';

  while(list($code, $img) = each($sm)) {
    if($img != $img_old) {
      $bgcolor = ($bgcolor != '#E0E0E0') ? '#E0E0E0' : '#F0F0F0';
?>
      <tr bgcolor="<?php echo $bgcolor; ?>">
      <td><a href="javascript:insert('<?php echo $code; ?>')">
      <img src="smilies/<?php echo $img; ?>" border="0" width="15" height="15"></a></td>
      <td class="cssShoutText"><b><?php echo $code; ?></b></td>
      </tr>
<?php
    }
    $img_old = $img;
  }
?>
</table>
</body>
</html>

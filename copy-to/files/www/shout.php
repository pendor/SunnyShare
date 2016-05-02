<?php 
if(!session_id()) session_start();

$maxLen = "512";
$dataFile = "chat.json";

function relDate($date) {
  $now = time();
  $diff = $now - $date;
  if($diff < 60){
    return sprintf($diff > 1 ? '%s seconds ago' : 'a second ago', $diff);
  }

  $diff = floor($diff/60);
  if($diff < 60){
    return sprintf($diff > 1 ? '%s minutes ago' : 'one minute ago', $diff);
  }

  $diff = floor($diff/60);
  if($diff < 24){
    return sprintf($diff > 1 ? '%s hours ago' : 'an hour ago', $diff);
  }

  $diff = floor($diff/24);
  if($diff < 7){
    return sprintf($diff > 1 ? '%s days ago' : 'yesterday', $diff);
  }

  if ($diff < 30) {
    $diff = floor($diff / 7);
    return sprintf($diff > 1 ? '%s weeks ago' : 'one week ago', $diff);
  }

  $diff = floor($diff/30);
  if($diff < 12){
    return sprintf($diff > 1 ? '%s months ago' : 'last month', $diff);
  }

  $diff = date('Y', $now) - date('Y', $date);
  return sprintf($diff > 1 ? '%s years ago' : 'last year', $diff);
}

if(isset($_POST['message'])) {
  $message = $_POST['message'];
  $name = $_POST['name'];
  if(strlen($name) && strlen($message)) {
    if(strlen($message) > $maxLen) {
    	$message = substr($message, 0, $maxLen);
    	$str_position = strrpos($message, ' ');
    	$message = substr($message, 0, $str_position) . " ...";
    }

    //add cookie to store name
    setcookie("name_chat", $name, ((2021-1970) * 60 * 60 * 24 * 365));

    $time = time();

    $fp = fopen($dataFile, 'r+');
    if(flock($fp, LOCK_EX)) {  // acquire an exclusive lock
      $flen = filesize($dataFile);
      if($flen == 0) {
        $json = array();
      } else {
        $json = fread($fp, $flen);
        $msgs = json_decode($json, true);
      }
      $msgs[] = array(
        't' => $time,
        'n' => $name,
        'm' => $message,
      );
      ftruncate($fp, 0);
      rewind($fp);
      $newJson = json_encode($msgs);
      fwrite($fp, $newJson);
      fflush($fp);
      flock($fp, LOCK_UN);
      fclose($fp);
    } else {
      die('Couldn\'t lock messages file.');
    }
    
    echo $newJson;
    exit;
  }
} else if(isset($_GET['json'])) {
  $fp = fopen($dataFile, 'r');
  if(flock($fp, LOCK_SH)) {
    $flen = filesize($dataFile);
    if($flen == 0) {
      echo "[]";
    } else {
      echo fread($fp, $flen);
    }
    flock($fp, LOCK_UN);
    fclose($fp);
  }
  exit;
}

  function drawChatBox() {
?>
<script src="masonry.pkgd.min.js"></script>
<div class="grid" id="chat"></div>

<script type="text/javascript">
function postMessage() {
  var ajax = new XMLHttpRequest();
  ajax.onreadystatechange = function() {
    if(ajax.readyState == 4 && ajax.status == 200) {
      drawChat(JSON.parse(ajax.responseText));
      document.getElementById('name').value = '';
      document.getElementById('message').value = '';
    }
  }
  
  
	ajax.open("POST", "/<?= pathinfo(__FILE__, PATHINFO_BASENAME) ?>");
	var formdata = new FormData();
	formdata.append("name", document.getElementById('name').value);
	formdata.append("message", document.getElementById('message').value);
	ajax.send(formdata);
}

function fetchChat() {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      var myArr = JSON.parse(xmlhttp.responseText);
      drawChat(myArr);
    }
  };
  xmlhttp.open("GET", "/<?= pathinfo(__FILE__, PATHINFO_BASENAME) ?>?json=1");
  xmlhttp.send();
}

function drawChat(arr) {
  var out = "";
  for(var i = 0; i < arr.length; i++) {
    out += '<div class="grid-item box"><b>' + htmlEntities(arr[i].n) + '</b>: ' +
      htmlEntities(arr[i].m) + ' -- ' + arr[i].t + '</div>';    
    }
    document.getElementById("chat").innerHTML = out;
    
  var msnry = new Masonry( '.grid', {
    itemSelector: '.grid-item',

  });
}

function htmlEntities(str) {
  return str.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
     return '&#'+i.charCodeAt(0)+';';
  });
}

fetchChat();

</script>

<?php } 

function drawChatForm() {
?>
<form name="inpform" method="post" onsubmit="postMessage(); return false;">
<table width="98%">
  <tr>
    <td>Name :</td>
    <td><input name="name" id="name" type="text" value="<?= isset($_COOKIE['name_chat']) ? $_COOKIE['name_chat'] : '' ?>" size="25" />
    </td>
  </tr>

  <tr><td colspan="2">Message :</td></tr>
  <tr>
    <td colspan="2"><textarea id="message" name="message" cols="60" rows="2"></textarea></td>
  </tr>
  <tr align="center"><td colspan="2"><input type="submit" value="Send" /></td></tr>
</table>
</form> 
<?php } ?>

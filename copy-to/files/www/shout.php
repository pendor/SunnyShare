<?php 
if(!session_id()) {
  session_start();
}

$maxLen = "512";
$dataFile = "chat.json";
$colors = array('#FFFFA5', '#E69D36', '#58C3EC', 
  '#B68CC2', '#C9DF6F', '#EEA1BC', '#87EBEE');

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
  $message = trim($_POST['message']);
  $name = trim($_POST['name']);
  
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
        'c' => $colors[array_rand($colors)],
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
    global $dataFile;
?>
<div class="grid" id="chat"></div>
<div class="curtime" id="curtime"></div>
<script type="text/javascript">
var DATE_RFC2822 = "ddd, DD MMM YYYY HH:mm:ss ZZ";
var refresh = 5000;

function postMessage() {
  var ajax = new XMLHttpRequest();
  ajax.onreadystatechange = function() {
    if(ajax.readyState == 4 && ajax.status == 200) {
      document.getElementById('message').value = '';
      drawChat(JSON.parse(ajax.responseText));
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
      drawChat(JSON.parse(xmlhttp.responseText));
    }
  };

  xmlhttp.open("GET", "/<?= pathinfo($dataFile, PATHINFO_BASENAME) ?>");
  xmlhttp.send();
}

function drawChat(arr) {
  var chat = $('#chat');
  
  for(var i = 0; i < arr.length ; i++) {
    var posted = moment.unix(arr[i].t);
    var relTime = posted.fromNow();
    var absTime = posted.format('MMMM Do YYYY, h:mm:ss a');
    var msg;
    if(arr[i].hasOwnProperty('p') && arr[i].p == '1') {
      msg = arr[i].n;
    } else {
      msg = htmlEntities(arr[i].n);
    }
    
    if(chat.find('#el' + arr[i].t).length == 0) {    
      chat.prepend('<div id="el' + arr[i].t + '" class="grid-item chatbox" style="background-color: ' + arr[i].c + '">' + 
        '<span class="chat-name">' + msg + '</span><br/> ' +
        '<span class="chat-message">' + htmlEntities(arr[i].m) + '</span><br/>' + 
        '<abbr class="chat-time" title="' + absTime + '">' + relTime + '</abbr></div>');
    }
  }
  
  chat.masonry('reloadItems');
  chat.masonry('layout');
}

function htmlEntities(str) {
  return str.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
     return '&#'+i.charCodeAt(0)+';';
  });
}

$('#chat').masonry({itemSelector: '.grid-item'});

fetchChat();
window.setInterval(fetchChat, refresh);
</script>

<?php } 

function drawChatForm() {
?>
<form name="inpform" method="post" onsubmit="postMessage(); return false;">
<b>Nickname:</b> 
<input name="name" id="name" type="text" 
  value="<?= isset($_COOKIE['name_chat']) ? $_COOKIE['name_chat'] : '' ?>" size="20" /><input type="submit" value="Post" /><br/>
<input type="text" id="message" name="message" placeholder="Type your message here" size="40"/>
</form> 
<?php } ?>
